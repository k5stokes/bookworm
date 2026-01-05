<?php

add_action('wp_enqueue_scripts', function () {
    
    $cache_buster = date('YmdHi', filemtime(get_stylesheet_directory() . '/dist/main.css'));
    wp_enqueue_style('bookworm-main', get_template_directory_uri() . '/dist/main.css', [], $cache_buster);
    
    // Enqueue Chart.js
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], '4.4.0', true);
    
    $cache_buster = date('YmdHi', filemtime(get_stylesheet_directory() . '/dist/main.js'));
    wp_enqueue_script('bookworm-main', get_template_directory_uri() . '/dist/main.js', ['jquery'], $cache_buster, true);

    wp_localize_script('bookworm-main', 'bookwormAjax', [
        'url'                   => admin_url('admin-ajax.php'),
        'bookworm_thinking_nonce' => wp_create_nonce('bookworm_thinking_nonce'),
        'bookworm_thinking_nonce_cs' => wp_create_nonce('bookworm_thinking_nonce_cs'),
    ]);

    // Pass yearly data to JS
    global $post, $wpdb;
    if (is_page_template('analytics.php')) {
        $wp_current_user_id = get_current_user_id();
        $book_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = '$wp_current_user_id' AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01' ORDER BY date_finished DESC");
        $yearlyData = [];
        $currentYear = date('Y');
        for ($year = $currentYear - 6; $year <= $currentYear; $year++) {
            $startOfYear = DateTime::createFromFormat('Y-m-d', $year . '-01-01');
            $endOfYear = DateTime::createFromFormat('Y-m-d', $year . '-12-31');
            $count = count(array_filter($book_entries, function($book) use ($startOfYear, $endOfYear) {
                if (empty($book->date_finished) || in_array($book->date_finished, ['0000-00-00', '1970-01-01'], true)) {
                    return false;
                }
                $bookDate = DateTime::createFromFormat('Y-m-d', $book->date_finished);
                return $bookDate >= $startOfYear && $bookDate <= $endOfYear;
            }));
            $yearlyData[] = ['year' => $year, 'count' => $count];
        }
        wp_localize_script('bookworm-main', 'yearlyBookData', $yearlyData);
    }
});
