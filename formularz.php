<?php
// formularz kontaktowy inVolte: mail HTML do firmy, honeypot, limit wysylek z jednego IP
// bez parametru -f: home.pl wtedy podpisuje DKIM i SPF przechodzi (Return-Path serwera)
declare(strict_types=1);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex');
header('Cache-Control: no-store');

const DO_KOGO = 'involte@involte.pl';
const NADAWCA = 'formularz@serwer2619213.hosting-home.pl';
const LIMIT_GODZ = 5;

function odp(bool $ok, string $blad = '', int $kod = 200): void {
    http_response_code($kod);
    echo json_encode($ok ? ['ok' => true] : ['ok' => false, 'blad' => $blad], JSON_UNESCAPED_UNICODE);
    exit;
}
function pole(string $n, int $max): string {
    $v = isset($_POST[$n]) && is_string($_POST[$n]) ? trim($_POST[$n]) : '';
    $v = str_replace("\0", '', $v);
    if (function_exists('mb_substr')) return mb_substr($v, 0, $max, 'UTF-8');
    return preg_match('/^.{0,' . $max . '}/us', $v, $m) ? $m[0] : substr($v, 0, $max);
}
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') odp(false, 'Niedozwolona metoda.', 405);

$blad_ogolny = 'Nie udało się wysłać wiadomości. Zadzwoń: 570 083 207 albo napisz na involte@involte.pl.';

// honeypot i czas wypelnienia: boty dostaja "ok" bez wysylki
$ts = (int)pole('ts', 12);
if (pole('www', 200) !== '' || ($ts > 0 && time() - $ts < 3)) odp(true);

$imie = pole('n', 120);
$kontakt = pole('k', 160);
$temat = pole('t', 80);
$wiad = pole('w', 5000);
$strona = pole('strona', 200);
if ($imie === '' || $kontakt === '') odp(false, 'Uzupełnij imię i dane kontaktowe.', 422);
if (preg_match('~https?://|www\.~i', $imie . ' ' . $kontakt)) odp(false, 'Usuń linki z imienia i danych kontaktowych.', 422);
if (preg_match_all('~https?://~i', $wiad) > 3) odp(true);
$tematy = ['Instalacja na domu', 'Instalacja dla firmy lub farma', 'Projekt i dokumentacja', 'Pomiary i odbiory instalacji', 'Serwis instalacji'];
if (!in_array($temat, $tematy, true)) $temat = 'Zapytanie';
if (!preg_match('~^/[a-z0-9\-/]*$~', $strona)) $strona = '/';

// limit: LIMIT_GODZ wysylek na godzine z jednego IP
$dir = __DIR__ . '/.limit';
if (!is_dir($dir)) { @mkdir($dir, 0700); @file_put_contents($dir . '/.htaccess', "Require all denied\n"); }
$ip = $_SERVER['REMOTE_ADDR'] ?? '0';
$plik = $dir . '/' . hash('sha256', $ip . 'involte') . '.txt';
$teraz = time();
$lista = array_filter(array_map('intval', @file($plik, FILE_IGNORE_NEW_LINES) ?: []), fn($t) => $teraz - $t < 3600);
if (count($lista) >= LIMIT_GODZ) odp(false, 'Wysłano już kilka wiadomości. Spróbuj później albo zadzwoń: 570 083 207.', 429);

$email = filter_var($kontakt, FILTER_VALIDATE_EMAIL) ? $kontakt : '';
$telCyfry = preg_replace('~[^0-9+]~', '', $kontakt);
$kontaktHtml = $email !== ''
    ? '<a href="mailto:' . e($email) . '" style="color:#E52000">' . e($email) . '</a>'
    : (strlen($telCyfry) >= 9 ? '<a href="tel:' . e($telCyfry) . '" style="color:#E52000">' . e($kontakt) . '</a>' : e($kontakt));
$data = (new DateTime('now', new DateTimeZone('Europe/Warsaw')))->format('d.m.Y H:i');
$adresStrony = 'https://involte.pl' . $strona;

$wiersz = fn(string $k, string $v) => '<tr><td style="padding:10px 14px;border-bottom:1px solid #E3E7EC;color:#5A5F67;font-size:13px;width:150px;vertical-align:top">' . $k . '</td><td style="padding:10px 14px;border-bottom:1px solid #E3E7EC;color:#16171A;font-size:15px">' . $v . '</td></tr>';
$html = '<!doctype html><html lang="pl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>'
  . '<body style="margin:0;padding:0;background:#F1F3F5;font-family:Arial,Helvetica,sans-serif">'
  . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F1F3F5;padding:24px 0"><tr><td align="center">'
  . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-top:4px solid #FF2400">'
  . '<tr><td style="padding:22px 24px 8px"><img src="https://involte.pl/img/logo.png" width="150" alt="inVolte" style="display:block;border:0;width:150px;height:auto"></td></tr>'
  . '<tr><td style="padding:8px 24px 4px"><h1 style="margin:0;font-size:20px;line-height:1.3;color:#16171A">Nowe zapytanie ze strony</h1>'
  . '<p style="margin:6px 0 0;font-size:13px;color:#5A5F67">' . e($data) . ' &middot; <a href="' . e($adresStrony) . '" style="color:#5A5F67">' . e($adresStrony) . '</a></p></td></tr>'
  . '<tr><td style="padding:16px 24px"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #E3E7EC">'
  . $wiersz('Imię i nazwisko', e($imie)) . $wiersz('Kontakt', $kontaktHtml) . $wiersz('Dotyczy', e($temat))
  . '</table></td></tr>'
  . '<tr><td style="padding:0 24px 22px"><p style="margin:0 0 6px;font-size:13px;color:#5A5F67">Wiadomość</p>'
  . '<div style="background:#F1F3F5;border-left:3px solid #FF2400;padding:14px 16px;font-size:15px;line-height:1.55;color:#16171A">' . ($wiad !== '' ? nl2br(e($wiad)) : '<i style="color:#5A5F67">(bez treści)</i>') . '</div></td></tr>'
  . '<tr><td style="padding:14px 24px;background:#131416;color:#AEB4BC;font-size:12px;line-height:1.5">Wiadomość z formularza na stronie <a href="https://involte.pl/" style="color:#ffffff">involte.pl</a>.'
  . ($email !== '' ? ' Odpowiedz na ten e-mail, aby napisać do nadawcy.' : '') . '</td></tr>'
  . '</table></td></tr></table></body></html>';
$txt = "Nowe zapytanie ze strony involte.pl\n$data, $adresStrony\n\nImię i nazwisko: $imie\nKontakt: $kontakt\nDotyczy: $temat\n\nWiadomość:\n" . ($wiad !== '' ? $wiad : '(bez treści)') . "\n";

$granica = 'b' . bin2hex(random_bytes(12));
$tytul = 'Nowe zapytanie ze strony: ' . $temat . ' - ' . preg_replace('~[\r\n]+~', ' ', $imie);
$naglowki = [
    'From: =?UTF-8?B?' . base64_encode('Strona inVolte') . '?= <' . NADAWCA . '>',
    'MIME-Version: 1.0',
    'Content-Type: multipart/alternative; boundary="' . $granica . '"',
    'X-Mailer: involte.pl',
];
if ($email !== '') $naglowki[] = 'Reply-To: =?UTF-8?B?' . base64_encode($imie) . '?= <' . $email . '>';
$tresc = "--$granica\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($txt))
    . "--$granica\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($html))
    . "--$granica--\r\n";

$ok = mail(DO_KOGO, '=?UTF-8?B?' . base64_encode($tytul) . '?=', $tresc, implode("\r\n", $naglowki));
if (!$ok) odp(false, $blad_ogolny, 500);

// potwierdzenie dla nadawcy (gdy podal e-mail)
if ($email !== '') {
    $p = '<!doctype html><html lang="pl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>'
      . '<body style="margin:0;padding:0;background:#F1F3F5;font-family:Arial,Helvetica,sans-serif">'
      . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F1F3F5;padding:24px 0"><tr><td align="center">'
      . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-top:4px solid #FF2400">'
      . '<tr><td style="padding:22px 24px 8px"><img src="https://involte.pl/img/logo.png" width="150" alt="inVolte" style="display:block;border:0;width:150px;height:auto"></td></tr>'
      . '<tr><td style="padding:8px 24px 0"><h1 style="margin:0;font-size:20px;line-height:1.3;color:#16171A">Dziękujemy za zapytanie</h1>'
      . '<p style="margin:12px 0 0;font-size:15px;line-height:1.55;color:#24262A">Dzień dobry ' . e($imie) . ', otrzymaliśmy Twoją wiadomość i odpowiemy najszybciej, jak to możliwe. W pilnej sprawie zadzwoń: <a href="tel:+48570083207" style="color:#E52000">570 083 207</a>.</p></td></tr>'
      . '<tr><td style="padding:16px 24px"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #E3E7EC">'
      . $wiersz('Dotyczy', e($temat)) . $wiersz('Wiadomość', $wiad !== '' ? nl2br(e($wiad)) : '(bez treści)')
      . '</table></td></tr>'
      . '<tr><td style="padding:14px 24px;background:#131416;color:#AEB4BC;font-size:12px;line-height:1.5">inVolte Arkadiusz Witt &middot; Gwiaździsta 57, Swarzędz &middot; Akacjowa 8, Dominowo<br>'
      . '<a href="mailto:involte@involte.pl" style="color:#ffffff">involte@involte.pl</a> &middot; <a href="https://involte.pl/" style="color:#ffffff">involte.pl</a></td></tr>'
      . '</table></td></tr></table></body></html>';
    $pt = "Dzień dobry $imie,\notrzymaliśmy Twoją wiadomość i odpowiemy najszybciej, jak to możliwe.\nW pilnej sprawie zadzwoń: 570 083 207.\n\ninVolte, involte@involte.pl, https://involte.pl/\n";
    $g2 = 'b' . bin2hex(random_bytes(12));
    $n2 = [
        'From: =?UTF-8?B?' . base64_encode('inVolte') . '?= <' . NADAWCA . '>',
        'Reply-To: ' . DO_KOGO,
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $g2 . '"',
    ];
    $t2 = "--$g2\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($pt))
        . "--$g2\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($p))
        . "--$g2--\r\n";
    @mail($email, '=?UTF-8?B?' . base64_encode('Otrzymaliśmy Twoje zapytanie - inVolte') . '?=', $t2, implode("\r\n", $n2));
}
$lista[] = $teraz;
@file_put_contents($plik, implode("\n", $lista), LOCK_EX);
odp(true);
