<?php
// awaryjnie: kazdy inny adres prowadzi na strone glowna
defined('ABSPATH') || exit;
wp_safe_redirect(home_url('/'), 301);
exit;
