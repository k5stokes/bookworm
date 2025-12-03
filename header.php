<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- <link rel="apple-touch-icon" sizes="76x76" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/imgfavicon-16x16.png">
		<link rel="manifest" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/site.webmanifest">
		<link rel="mask-icon" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/safari-pinned-tab.svg" color="#7c0080"> -->
		<meta name="msapplication-TileColor" content="#da532c">
		<meta name="theme-color" content="#ffffff">
		<link href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon.ico" rel="shortcut icon">
		<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_stylesheet_directory_uri(); ?>/img/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_stylesheet_directory_uri(); ?>/img//favicon-16x16.png">
		<!-- old font, Astoria -->
		<link rel="stylesheet" href="https://use.typekit.net/jwq8uga.css">
		
		<!-- Google fonts, Lora and Montserrat -->
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400..700;1,400..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

		<script src="<?php echo get_stylesheet_directory_uri(); ?>/dist/air-datepicker/air-datepicker.js"></script>
		<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/dist/air-datepicker/air-datepicker.css">
        <?php wp_head(); ?>
    </head>
    <body <?php body_class(); ?>>

        