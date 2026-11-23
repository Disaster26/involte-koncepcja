<?php defined('ABSPATH') || exit; $k = iv_kontekst(); ?>
</main>

<?php echo iv_stopka(); ?>


<div class="mob" id="mob"><a class="z" href="<?php echo esc_attr(iv_tel_href()); ?>">Zadzwoń</a><a class="w" href="<?php echo esc_url($k['mob']); ?>">Zapytaj o wycenę</a></div>
<?php echo iv_ciastka(); ?>

<script src="<?php echo esc_url(get_template_directory_uri() . '/app.js?v=' . IV_WERSJA); ?>" defer></script>
<?php wp_footer(); ?>
</body>
</html>
