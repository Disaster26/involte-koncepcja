<?php
// formularz zapytania: wp_mail przez SMTP skrzynki formularz@involte.pl (home.pl), honeypot, limit z jednego IP
defined('ABSPATH') || exit;

// wszystkie maile strony wychodza uwierzytelnionym SMTP z adresu w domenie (SPF/DMARC)
add_action('phpmailer_init', function ($m) {
    if (!defined('IV_SMTP_HOST') || !defined('IV_SMTP_PASS') || IV_SMTP_PASS === '' || !empty($GLOBALS['iv_bez_smtp'])) return;
    $m->isSMTP();
    $m->Host = IV_SMTP_HOST;
    $m->Port = IV_SMTP_PORT;
    $m->SMTPAuth = true;
    $m->SMTPSecure = IV_SMTP_PORT == 465 ? 'ssl' : 'tls';
    $m->Username = IV_SMTP_USER;
    $m->Password = IV_SMTP_PASS;
    $m->From = IV_SMTP_USER;
    $m->Sender = IV_SMTP_USER;
    $m->CharSet = 'UTF-8';
    $m->Timeout = 20;
    if (!empty($GLOBALS['iv_alt'])) $m->AltBody = $GLOBALS['iv_alt'];
});
add_filter('wp_mail_from', fn($e) => defined('IV_SMTP_USER') ? IV_SMTP_USER : $e);
add_filter('wp_mail_from_name', fn($n) => $n === 'WordPress' ? 'inVolte' : $n);

add_action('wp_ajax_iv_formularz', 'iv_formularz_obsluga');
add_action('wp_ajax_nopriv_iv_formularz', 'iv_formularz_obsluga');

function iv_odp($ok, $blad = '', $kod = 200) {
    status_header($kod);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Robots-Tag: noindex');
    echo wp_json_encode($ok ? ['ok' => true] : ['ok' => false, 'blad' => $blad], JSON_UNESCAPED_UNICODE);
    exit;
}
function iv_pole($n, $max) {
    $v = isset($_POST[$n]) && is_string($_POST[$n]) ? trim(wp_unslash($_POST[$n])) : '';
    return mb_substr(str_replace("\0", '', $v), 0, $max, 'UTF-8');
}
function iv_h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function iv_formularz_obsluga() {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') iv_odp(false, 'Niedozwolona metoda.', 405);
    $tel = iv_dane('tel');
    $mail_firmy = iv_dane('email');
    $blad = 'Nie udało się wysłać wiadomości. Zadzwoń: ' . $tel . ' albo napisz na ' . $mail_firmy . '.';
    // honeypot i czas wypelnienia: boty dostaja "ok" bez wysylki
    $ts = (int)iv_pole('ts', 12);
    if (iv_pole('www', 200) !== '' || ($ts > 0 && time() - $ts < 3)) iv_odp(true);
    $imie = iv_pole('n', 120); $kontakt = iv_pole('k', 160); $temat = iv_pole('t', 80); $wiad = iv_pole('w', 5000); $strona = iv_pole('strona', 200);
    if ($imie === '' || $kontakt === '') iv_odp(false, 'Uzupełnij imię i dane kontaktowe.', 422);
    if (preg_match('~https?://|www\.~i', $imie . ' ' . $kontakt)) iv_odp(false, 'Usuń linki z imienia i danych kontaktowych.', 422);
    if (preg_match_all('~https?://~i', $wiad) > 3) iv_odp(true);
    if (!in_array($temat, IV_TEMATY, true)) $temat = 'Zapytanie';
    if (!preg_match('~^/[a-z0-9\-/]*$~', $strona)) $strona = '/';
    // limit: 5 wysylek na godzine z jednego IP
    $klucz = 'iv_lim_' . md5(($_SERVER['REMOTE_ADDR'] ?? '0') . wp_salt('nonce'));
    $lista = array_filter((array)get_transient($klucz), fn($t) => time() - (int)$t < 3600);
    if (count($lista) >= 5) iv_odp(false, 'Wysłano już kilka wiadomości. Spróbuj później albo zadzwoń: ' . $tel . '.', 429);

    $email = is_email($kontakt) ? $kontakt : '';
    $telc = preg_replace('~[^0-9+]~', '', $kontakt);
    $kontaktHtml = $email !== '' ? '<a href="mailto:' . iv_h($email) . '" style="color:#E52000">' . iv_h($email) . '</a>'
        : (strlen($telc) >= 9 ? '<a href="tel:' . iv_h($telc) . '" style="color:#E52000">' . iv_h($kontakt) . '</a>' : iv_h($kontakt));
    $data = wp_date('d.m.Y H:i');
    $dom = untrailingslashit(home_url());
    $adresStrony = $dom . preg_replace('~^' . preg_quote((string)wp_parse_url($dom, PHP_URL_PATH), '~') . '~', '', $strona);
    $logo = home_url('/img/logo.png');
    $wiersz = fn($k, $v) => '<tr><td style="padding:10px 14px;border-bottom:1px solid #E3E7EC;color:#5A5F67;font-size:13px;width:150px;vertical-align:top">' . $k . '</td><td style="padding:10px 14px;border-bottom:1px solid #E3E7EC;color:#16171A;font-size:15px">' . $v . '</td></tr>';
    $gora = '<!doctype html><html lang="pl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>'
        . '<body style="margin:0;padding:0;background:#F1F3F5;font-family:Arial,Helvetica,sans-serif">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F1F3F5;padding:24px 0"><tr><td align="center">'
        . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-top:4px solid #FF2400">'
        . '<tr><td style="padding:22px 24px 8px"><img src="' . esc_url($logo) . '" width="150" alt="inVolte" style="display:block;border:0;width:150px;height:auto"></td></tr>';
    $dol = '</table></td></tr></table></body></html>';
    $html = $gora
        . '<tr><td style="padding:8px 24px 4px"><h1 style="margin:0;font-size:20px;line-height:1.3;color:#16171A">Nowe zapytanie ze strony</h1>'
        . '<p style="margin:6px 0 0;font-size:13px;color:#5A5F67">' . iv_h($data) . ' &middot; <a href="' . iv_h($adresStrony) . '" style="color:#5A5F67">' . iv_h($adresStrony) . '</a></p></td></tr>'
        . '<tr><td style="padding:16px 24px"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #E3E7EC">'
        . $wiersz('Imię i nazwisko', iv_h($imie)) . $wiersz('Kontakt', $kontaktHtml) . $wiersz('Dotyczy', iv_h($temat))
        . '</table></td></tr>'
        . '<tr><td style="padding:0 24px 22px"><p style="margin:0 0 6px;font-size:13px;color:#5A5F67">Wiadomość</p>'
        . '<div style="background:#F1F3F5;border-left:3px solid #FF2400;padding:14px 16px;font-size:15px;line-height:1.55;color:#16171A">' . ($wiad !== '' ? nl2br(iv_h($wiad)) : '<i style="color:#5A5F67">(bez treści)</i>') . '</div></td></tr>'
        . '<tr><td style="padding:14px 24px;background:#131416;color:#AEB4BC;font-size:12px;line-height:1.5">Wiadomość z formularza na stronie <a href="' . esc_url($dom . '/') . '" style="color:#ffffff">involte.pl</a>.'
        . ($email !== '' ? ' Odpowiedz na ten e-mail, aby napisać do nadawcy.' : '') . '</td></tr>' . $dol;
    $GLOBALS['iv_alt'] = "Nowe zapytanie ze strony involte.pl\n$data, $adresStrony\n\nImię i nazwisko: $imie\nKontakt: $kontakt\nDotyczy: $temat\n\nWiadomość:\n" . ($wiad !== '' ? $wiad : '(bez treści)') . "\n";
    $nagl = ['Content-Type: text/html; charset=UTF-8', 'From: Strona inVolte <' . (defined('IV_SMTP_USER') ? IV_SMTP_USER : get_option('admin_email')) . '>'];
    if ($email !== '') $nagl[] = 'Reply-To: ' . str_replace(['"', "\r", "\n"], '', $imie) . ' <' . $email . '>';
    $do = iv_dane('email_form') ?: $mail_firmy;
    $tyt = 'Nowe zapytanie ze strony: ' . $temat . ' - ' . preg_replace('~[\r\n]+~', ' ', $imie);
    $ok = wp_mail($do, $tyt, $html, $nagl);
    if (!$ok) {
        // awaryjnie bez SMTP (serwer hostingu), zeby zapytanie nie przepadlo przy awarii skrzynki
        error_log('iv_formularz: SMTP niedostepny, wysylka awaryjna');
        $GLOBALS['iv_bez_smtp'] = true;
        $ok = wp_mail($do, $tyt, $html, $nagl);
    }
    if (!$ok) iv_odp(false, $blad, 500);

    // potwierdzenie dla nadawcy (gdy podal e-mail)
    if ($email !== '') {
        $adr = implode(' &middot; ', array_map('iv_h', array_map('iv_adres_krotki', iv_adresy())));
        $p = $gora
            . '<tr><td style="padding:8px 24px 0"><h1 style="margin:0;font-size:20px;line-height:1.3;color:#16171A">Dziękujemy za zapytanie</h1>'
            . '<p style="margin:12px 0 0;font-size:15px;line-height:1.55;color:#24262A">Dzień dobry ' . iv_h($imie) . ', otrzymaliśmy Twoją wiadomość i odpowiemy najszybciej, jak to możliwe. W pilnej sprawie zadzwoń: <a href="' . esc_attr(iv_tel_href()) . '" style="color:#E52000">' . iv_h($tel) . '</a>.</p></td></tr>'
            . '<tr><td style="padding:16px 24px"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #E3E7EC">'
            . $wiersz('Dotyczy', iv_h($temat)) . $wiersz('Wiadomość', $wiad !== '' ? nl2br(iv_h($wiad)) : '(bez treści)')
            . '</table></td></tr>'
            . '<tr><td style="padding:14px 24px;background:#131416;color:#AEB4BC;font-size:12px;line-height:1.5">' . iv_h(iv_dane('firma')) . ' &middot; ' . $adr . '<br>'
            . '<a href="mailto:' . iv_h($mail_firmy) . '" style="color:#ffffff">' . iv_h($mail_firmy) . '</a> &middot; <a href="' . esc_url($dom . '/') . '" style="color:#ffffff">involte.pl</a></td></tr>' . $dol;
        $GLOBALS['iv_alt'] = "Dzień dobry $imie,\notrzymaliśmy Twoją wiadomość i odpowiemy najszybciej, jak to możliwe.\nW pilnej sprawie zadzwoń: $tel.\n\ninVolte, $mail_firmy, $dom/\n";
        wp_mail($email, 'Otrzymaliśmy Twoje zapytanie - inVolte', $p, ['Content-Type: text/html; charset=UTF-8', 'From: inVolte <' . (defined('IV_SMTP_USER') ? IV_SMTP_USER : get_option('admin_email')) . '>', 'Reply-To: ' . $mail_firmy]);
    }
    $GLOBALS['iv_alt'] = '';
    $lista[] = time();
    set_transient($klucz, array_values($lista), HOUR_IN_SECONDS);
    iv_odp(true);
}
