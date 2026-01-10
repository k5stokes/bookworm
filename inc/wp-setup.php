<?php

add_action('after_setup_theme', function () {
    // Support featured images
    add_theme_support('post-thumbnails');
});

/**
 * Menus
 */
add_action('init', function () {
    register_nav_menus([
        'header-menu' => 'Header Menu',
        'mobile-menu' => 'Mobile Menu',
    ]);
});

// Custom Login Logo Support    
function opm_custom_login_logo() {
    echo '<style type="text/css">
        h1 a { background-image:url('.get_site_url().'/wp-content/themes/bookworm/img/BookWorm-logo.svg) !important; background-size: 250px auto !important; width: 100% !important; height:225px !important; }
    </style>';
}

add_action('login_head', 'opm_custom_login_logo');

/* Custom Registration Form */
add_filter( 'wpum_get_registration_fields', function ( $fields ) {
	if ( isset( $fields['privacy'] ) ) {
		unset( $fields['privacy'] );
	}

	return $fields;
} );

// Get Initial Consonants 
function get_initial_consonants($string, $limit = 4) {
    preg_match_all('/[b-df-hj-np-tv-z]/i', $string, $matches);
	$consonants = implode('', $matches[0]);
	if ($consonants >= 4) {
		return substr($consonants, 0, 4);
	} else {
		$length = strlen($consonants);
		return substr($consonants, 0, $length);
	}
}

// Create User friend codes
function generate_token_id($user_id, $length = 4) {
	$user = get_userdata($user_id);
    $nicename = $user->user_nicename;
	
	$consonants = get_initial_consonants($nicename);
	
    $characters = '0123456789';
    //$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $result = strtoupper($consonants);
    
    for ($i = 0; $i < $length; ++$i) {
        $result .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    
    return $result;
}

add_action('user_register', 'save_token_id', 10, 2);

function save_token_id($user_id) {
    $meta_key = "token_id";
    $key = generate_token_id($user_id);
    update_user_meta($user_id, $meta_key, $key);
}

// One-time only: or every time? Create token IDs for users that don't have one already
add_action('init', function () {
	$users = get_users();
	
	foreach ($users as $user) {
		$user_id = $user->ID;
		$meta_key = "token_id";
		$meta_value = get_user_meta($user_id, $meta_key, true);
	   	if (empty($meta_value)) {
		    $key = generate_token_id($user_id);
		    update_user_meta($user_id, $meta_key, $key);
		}
	}
});

/* Gemini */
function get_gemini_user_notes_summary($user_id, $date_range_query) {
    global $wpdb;

    $bookshelf_start_date = $date_range_query['start_date'];
    $bookshelf_end_date = $date_range_query['end_date'];
    $bookshelf_date_range = $date_range_query['range'];	

    // 1. Securely fetch the user's text from your custom table
    // We use $wpdb->prepare to prevent SQL injection
    $table_name = $wpdb->prefix . 'bookworm_books';
    $user_content = $wpdb->get_col($wpdb->prepare(
        "SELECT notes FROM $table_name WHERE user_id_shelf = %d AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01' AND date_finished BETWEEN %s AND %s",
        $user_id,
        $bookshelf_start_date,
        $bookshelf_end_date
    ));

	if (!empty($user_content)) {
		// You now have an array. To combine them for Gemini, use implode:
		$combined_notes = implode("\n\n---\n\n", $user_content);
	}

    // 2. Prepare the Gemini API Request
    $api_key = constant('GEMINI_API_KEY'); // Use a constant or environment variable
    $model   = 'gemini-2.5-flash'; // Using the latest high-speed model
    $url     = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$api_key}";

    // Construct the prompt
	$prompt_from_db = get_field('ai_notes_summaries_prompt', 'option');
	if (empty($prompt_from_db)) {
		$prompt_from_db = "I am uploading some qualitative data. These notes are my responses to books I've read. They are short reviews. Considering only these notes, please generate a paragraph of about 100-150 words that summarizes my reading preferences, including what I don't like if evident from the data. Please make the paragraph objective and drawn only from the data I provide below. Please address me directly. Consider only the included data here:";
	}
    $prompt = $prompt_from_db . "\n\n" . $combined_notes;

    $body = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ]
    ];

    // 3. Send the request via WordPress HTTP API
    $response = wp_remote_post($url, [
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => json_encode($body),
        'timeout' => 30, // Summarization can take a few seconds
    ]);

    // 4. Handle Errors and Parse Response
    if (is_wp_error($response)) {
        return "API Error: " . $response->get_error_message();
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {

        return "<h5>Successfully reviewed " . count($user_content) . " entries.</h5><div class=\"ai-summary\"><h4 class=\"title\">Summary of your book notes:</h4><p>" .$data['candidates'][0]['content']['parts'][0]['text'];
    }

    // Debug: return the full response for troubleshooting
    return "Summary could not be generated. API Response: " . esc_html($body);
}