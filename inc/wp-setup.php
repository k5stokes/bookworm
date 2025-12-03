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