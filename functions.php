<?php

require_once get_template_directory() . '/inc/wp-setup.php';
require_once get_template_directory() . '/inc/wp-ajax.php';
require_once get_template_directory() . '/inc/wp-cleanup.php';
require_once get_template_directory() . '/inc/wp-enqueue.php';
require_once get_template_directory() . '/inc/custom-post-types.php';

// Require Gutenburg blocks
foreach (glob(get_template_directory() . '/templates/blocks/gutenberg/**/register.php') as $file) {
    require_once $file;
}
