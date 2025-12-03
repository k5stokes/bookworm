<?php

add_action('wp_enqueue_scripts', function () {
    
    $cache_buster = date('YmdHi', filemtime(get_stylesheet_directory() . '/dist/main.css'));
    wp_enqueue_style('bookworm-main', get_template_directory_uri() . '/dist/main.css', [], $cache_buster);
    
    $cache_buster = date('YmdHi', filemtime(get_stylesheet_directory() . '/dist/main.js'));
    wp_enqueue_script('bookworm-main', get_template_directory_uri() . '/dist/main.js', ['jquery'], $cache_buster, true);

    wp_localize_script('bookworm-main', 'bookwormAjax', [
        'url'                   => admin_url('admin-ajax.php'),
        'bookworm_thinking_nonce' => wp_create_nonce('bookworm_thinking_nonce'),
        'bookworm_thinking_nonce_cs' => wp_create_nonce('bookworm_thinking_nonce_cs'),
    ]);
});
