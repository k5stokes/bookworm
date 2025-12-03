<?php 
	/* Template Name: Save Book Form Processor */
	
	global $wpdb;
	$form_data = array(				
		//'id' => NULL,
		'title' => isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '',
		'author' => isset($_POST['author']) ? sanitize_text_field($_POST['author']) : '',
		'google_books_id' => $_POST['google_books_id'],
		'published_date' => $_POST['published_date'],
		'small_thumbnail_url' => $_POST['small_thumbnail_url'],
		'page_count' => $_POST['page_count'],
		'date_started' => $_POST['date_started'],
		'date_finished' => $_POST['date_finished'],
		'notes' => $_POST['notes'],
		//'fiction_or_non' => $_POST['fiction_or_non'],
		'user_id_shelf' => $_POST['user_id_shelf'],
		'recommended_by' => $_POST['recommended_by'],
		'recommended_to' => $_POST['recommended_to'],
		'rating_mood' => $_POST['rating_mood'],
		'rating_language' => $_POST['rating_language'],
		'rating_romance' => $_POST['rating_romance'],
		'rating_suspension_disbelief' => $_POST['rating_suspension_disbelief']
	);
	$wpdb->insert( "zFuga_bookworm_books", $form_data );
	echo "Submit form is go?";
	
	die();?>