<?php	
// Create Class
class BookshelfCategory {
	public string $title;
	public string $icon;
	public string $queryString;
	public string $ytdQueryString;
	public string $userMessage;
	public string $iconBackground;
	public string $link;
	
	public function __construct($title, $icon, $queryString, $ytdQueryString, $userMessage, $iconBackground, $link) {
	    $this->title = $title;
	    $this->icon = $icon;
	    $this->queryString = $queryString;
	    $this->ytdQueryString = $ytdQueryString;
	    $this->userMessage = $userMessage;
	    $this->iconBackground = $iconBackground;
	    $this->link = $link;
	}			
}

/*
** Date Filtering Functions **
*/
/**
 * Creates a configured Closure for filtering records by a specific calendar year.
 *
 * @param int $date_query_field The field in the entry object that contains the date to be checked.
 * @return \Closure A Closure ready for use with array_filter().
 */
function create_year_filter(string $date_query_field): \Closure {
    
    /* Variables for Date Filtering */
	// Define the "Today" reference date for consistent testing
	$now = new DateTime(); 

	// Start of Current Calendar Year
	$startOfCurrentYear = (clone $now)->setDate($now->format('Y'), 1, 1);

	return function ($record) use ($startOfCurrentYear, $date_query_field) {
		// Reject empty or placeholder dates
		if (empty($record->$date_query_field) || in_array($record->$date_query_field, ['0000-00-00', '1970-01-01'], true)) {
			return false;
		}

		// Try parsing as Y-m-d first (most likely format), fall back to DateTime parser
		$recordDate = DateTime::createFromFormat('Y-m-d', $record->$date_query_field);
		if ($recordDate === false) {
			try {
				$recordDate = new DateTime($record->$date_query_field);
			} catch (Exception $e) {
				return false;
			}
		}

		$recordDate->setTime(0, 0, 0);
		return $recordDate >= $startOfCurrentYear;
	};
}

function create_date_filter(string $date_query_field, int $numMonths): \Closure {
    
    /* Variables for Date Filtering */
	// Define the "Today" reference date for consistent testing
	$now = new DateTime(); 

	// Start of Last X Months
	$xMonthsAgo = (clone $now)->sub(new DateInterval('P' . $numMonths . 'M'));

	return function ($record) use ($xMonthsAgo, $date_query_field) {
		// Reject empty or placeholder dates
		if (empty($record->$date_query_field) || in_array($record->$date_query_field, ['0000-00-00', '1970-01-01'], true)) {
			return false;
		}

		// Try parsing as Y-m-d first (most likely format), fall back to DateTime parser
		$recordDate = DateTime::createFromFormat('Y-m-d', $record->$date_query_field);
		if ($recordDate === false) {
			try {
				$recordDate = new DateTime($record->$date_query_field);
			} catch (Exception $e) {
				return false;
			}
		}

		$recordDate->setTime(0, 0, 0);
		return $recordDate >= $xMonthsAgo;
	};
}

// Sanitize text function
function sanitizeInput($input) {
	$input = trim($input);
	$input = strip_tags($input);
	$input = htmlspecialchars($input);
	
	return $input;
}

/* Send HTML Emails */
add_filter( 'wp_mail_content_type', 'set_content_type' );
function set_content_type( $content_type ) {
	return 'text/html';
}
	
// Call API Function
function callAPI($method, $url, $authorization){
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_ENCODING, "identity");
    curl_setopt($curl, CURLOPT_REFERER, "https://bookworm.madhurimachakraborty.net/");

    $headers = array(
        'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
        'Accept: text/xml; charset=UTF-8',
        'Accept-Encoding: gzip, deflate, br',
        'Connection: keep-alive',
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    // EXECUTE:
    $result = curl_exec($curl);
    if(!$result){die("Connection Failure");}
    if ($result === false) {
        $result = curl_error($curl);
        echo stripslashes($result);
    }

    curl_close($curl);

    return $result;
}

// Download descriptions for books that don't have them
function books_add_descriptions() {
	global $wpdb;
    $wpdb->show_errors();
    
	$book_entry_query = "SELECT * FROM {$wpdb->prefix}bookworm_books";
	$book_entries = $wpdb->get_results($book_entry_query);

    foreach ($book_entries as $book_entry) {
		if ($book_entry->description == '' || $book_entry->description == NULL || !isset($book_entry->description)) {
			$bookworm_id = $book_entry->id;
			$google_books_ID = $book_entry->google_books_ID; 
			
			// Google Books
		    $book_data_string = "https://books.googleapis.com/books/v1/volumes/" . $google_books_ID . "?key=AIzaSyCCLPPisgOw5nhZmrCuKMZnWDNSJ6-hCWY";
		    $book_call = callAPI('GET', $book_data_string, '');
		    $book_item = json_decode($book_call, TRUE);
		  
		    $google_book_description = sanitizeInput($book_item['volumeInfo']['description']);
		    
		    $form_data = array(
				'description' => $google_book_description
			);
		    
		    // Add it back to the DB
		    $add_description = $wpdb->update( "{$wpdb->prefix}bookworm_books", $form_data, array('id' => $bookworm_id) );
		    
		    if ($add_description) {
			    echo "description added";
		    } else {
		        echo $wpdb->print_error();
		        echo $wpdb->last_error;
		        echo $wpdb->last_query;
		        echo $wpdb->last_result;
	        }
	    }
	}
		
	//die();
}
//add_action('wp_ajax_books_add_descriptions', 'books_add_descriptions');
//add_action('wp_ajax_nopriv_books_add_descriptions', 'books_add_descriptions');

/****************************
** Google Books API Search **
****************************/
function google_books_api_search() {
    check_ajax_referer('bookworm_thinking_nonce', 'nonce');

    $s = !empty($_POST['data']['s'])
        ? $_POST['data']['s']
        : null;

    // If no search term 
    if (!$s) {
        die;
    }

    // Book search
    $book_search_terms = urlencode($s);
    $book_data_string = "https://www.googleapis.com/books/v1/volumes?q=" . $book_search_terms . "&key=AIzaSyCCLPPisgOw5nhZmrCuKMZnWDNSJ6-hCWY";
    $book_call = callAPI('GET', $book_data_string, '');
    $book_results = json_decode($book_call, TRUE);
    $i = 0;
    /*
    if ($book_results) {
	    echo "there are results";
	    print_r($book_results);
    } else {
	    echo "no results";
    }
    */
    
    foreach ($book_results['items'] as $book_item) {
	    $i++;
        $book_id = $book_item['id'];
        $book_title = sanitizeInput($book_item['volumeInfo']['title']);
        $book_authors = sanitizeInput($book_item['volumeInfo']['authors'][0]);
        $book_description = sanitizeInput($book_item['volumeInfo']['description']);
		if (preg_match('/^.{1,90}\b/s', $book_description, $match)) {
		    $book_description_excerpt = $match[0] . "...";
		}
        $book_published_date = $book_item['volumeInfo']['publishedDate'];
        $display_published_date = strtotime($book_published_date);
		$display_published_date = date('j F Y', $display_published_date);
		$book_isbn_10 = $book_item['volumeInfo']['industryIdentifiers'][0]['identifier'];
		$book_isbn_13 = $book_item['volumeInfo']['industryIdentifiers'][1]['identifier'];
        $book_img = $book_item['volumeInfo']['imageLinks']['smallThumbnail'];
        if ($book_img == '') {
            $book_img = get_stylesheet_directory_uri() . "/img/icon_open-book.svg";
        }

        echo '<div class="book-search-entry">';
        // echo $book_title;
        // echo $book_description;
        echo '<a class="book-search-entry-link" id="' . $book_id . '" data-title="' . $book_title . '" data-img="' . $book_img . '" data-author="' . $book_authors . '" data-pubdate="' . $book_published_date . '"  data-disppubdate="' . $display_published_date . '" data-description="' . $book_description . '" data-isbn10="' . $book_isbn_10 . '" data-isbn13="' . $book_isbn_13 . '"href="#">';
        echo '<div class="book-search-thumb"><img src="' . $book_img . '"></div>';
        echo '<div class="book-search-details">';
        echo '<span class="book-search-title">' . $book_title . '</span>';
        echo '<span class="book-search-author">by ' . $book_authors . '</span>';
        echo '<span class="book-search-description">' . $book_description_excerpt . '</span>';
        echo '</div>';
        echo '</a>';
        echo '</div>';
    }
        
    die();
}
add_action('wp_ajax_google_books_api_search', 'google_books_api_search');
add_action('wp_ajax_nopriv_google_books_api_search', 'google_books_api_search');

/****************************
** Google Books API Volume **
****************************/
function google_books_api_volume() {
    check_ajax_referer('bookworm_thinking_nonce', 'nonce');

    $id = !empty($_POST['data']['id'])
        ? $_POST['data']['id']
        : null;

    // If no id 
    if (!$id) {
        die;
    }

    // Book entry
    $book_data_string = "https://books.googleapis.com/books/v1/volumes/" . $id . "?key=AIzaSyCCLPPisgOw5nhZmrCuKMZnWDNSJ6-hCWY";
    $book_call = callAPI('GET', $book_data_string, '');
    $book_item = json_decode($book_call, TRUE);
  
    $book_author = sanitizeInput($book_item['volumeInfo']['authors'][0]);
    $book_pub_date = $book_item['volumeInfo']['publishedDate'];
    $book_description = sanitizeInput($book_item['volumeInfo']['description']);
    $book_page_count = $book_item['volumeInfo']['pageCount'];
	$book_isbn_10 = $book_item['volumeInfo']['industryIdentifiers'][0]['identifier'];
	$book_isbn_13 = $book_item['volumeInfo']['industryIdentifiers'][1]['identifier'];
   
    $book_img = $book_item['volumeInfo']['imageLinks']['smallThumbnail'];
    if ($book_img == '') {
        $book_img = get_stylesheet_directory_uri() . "/img/icon_open-book.svg";
    }
    
	echo '<input id="book_google_id" name="google_books_id" type="hidden" value="' . $id . '">';
    echo '<input id="book_author" name="author" type="hidden" value="' . $book_author . '">';
	echo '<input id="book_pub_date" name="published_date" type="hidden" value="' . $book_pub_date . '">';
	echo '<input id="book_description" name="description" type="hidden" value="' . $book_description . '">';
	echo '<input id="book_page_count" name="page_count" type="hidden" value="' . $book_page_count . '">';
	echo '<input id="book_img" name="small_thumbnail_url" type="hidden" value="' . $book_img . '">';
	echo '<input id="book_isbn_10" name="isbn_10" type="hidden" value="' . $book_isbn_10 . '">';
	echo '<input id="book_isbn_13" name="isbn_13" type="hidden" value="' . $book_isbn_13 . '">';
        
    die();
}
add_action('wp_ajax_google_books_api_volume', 'google_books_api_volume');
add_action('wp_ajax_nopriv_google_books_api_volume', 'google_books_api_volume');

/*************
** Add Book **
*************/
function add_book() {
	global $wpdb;
    $wpdb->show_errors();
    
    if (isset($_POST['date_started'])) {
        $date_started = strtotime($_POST['date_started']);
        $date_started = date('Y-m-d', $date_started);
    } else {
        $date_started = NULL;
    }
    if (isset($_POST['date_finished'])) {
        $date_finished = strtotime($_POST['date_finished']);
        $date_finished = date('Y-m-d', $date_finished);
    } else {
        $date_finished = NULL;
    }
    // 'date_started' => isset($_POST['date_started']) ? sanitize_text_field( $_POST['date_started']) : '',
    //'date_finished' => isset($_POST['date_finished']) ? sanitize_text_field( $_POST['date_finished']) : '',
    
    if (isset($_POST['shared_on_shelf'])) {
	    if ($_POST['shared_on_shelf'] == 'on') {
	    	$shared_on_shelf = 'invisible';
	    } else {
		    $shared_on_shelf = 'shared';
	    }
    } else {
	    $shared_on_shelf = 'shared';
    }
    
    if (isset($_POST['tagsArray']) && $_POST['tagsArray'] != '' && $_POST['tagsArray'] != '[]' && $_POST['tagsArray'] != []) {
		$tags = $_POST['tagsArray'];
	} else {
		$tags = NULL;
	}

	$form_data = array(
		'title' => isset($_POST['title']) ? sanitize_text_field($_POST['title']) : NULL,
		'author' => isset($_POST['author']) ? sanitize_text_field($_POST['author']) : NULL,
		'google_books_id' => isset($_POST['google_books_id']) ? sanitize_text_field( $_POST['google_books_id']) : NULL,
		'ISBN_10' => isset($_POST['isbn_10']) ? sanitize_text_field( $_POST['isbn_10']) : NULL,
		'ISBN_13' => isset($_POST['isbn_13']) ? sanitize_text_field( $_POST['isbn_13']) : NULL,
		'published_date' => isset($_POST['published_date']) ? sanitize_text_field( $_POST['published_date']) : NULL,
		'description' => isset($_POST['description']) ? sanitize_text_field( $_POST['description']) : NULL,
		'small_thumbnail_url' => isset($_POST['small_thumbnail_url']) ? sanitize_text_field( $_POST['small_thumbnail_url']) : NULL,
		'page_count' => isset($_POST['page_count']) ? sanitize_text_field( $_POST['page_count']) : NULL,
		'shared_on_shelf' => $shared_on_shelf,
		'date_started' => $date_started,
		'date_finished' => $date_finished,
		'notes' => isset($_POST['notes']) ? sanitize_text_field( $_POST['notes']) : NULL,
		'fiction_or_non' =>  isset($_POST['fiction_or_non']) ? sanitize_text_field( $_POST['fiction_or_non']) : NULL,
		'tags' =>  $tags,
		'user_id_shelf' => isset($_POST['user_id_shelf']) ? sanitize_text_field( $_POST['user_id_shelf']) : NULL,
		'recommended_by' => isset($_POST['recommended_by']) ? sanitize_text_field( $_POST['recommended_by']) : NULL,
		'recommended_to' => isset($_POST['recommended_to']) ? sanitize_text_field( $_POST['recommended_to']) : NULL,
		'rating_mood' => isset($_POST['rating_mood']) ? sanitize_text_field( $_POST['rating_mood']) : NULL,
		'rating_language' => isset($_POST['rating_language']) ? sanitize_text_field( $_POST['rating_language']) : NULL,
		'rating_romance' => isset($_POST['rating_romance']) ? sanitize_text_field( $_POST['rating_romance']) : NULL,
		'rating_suspension_disbelief' => isset($_POST['rating_suspension_disbelief']) ? sanitize_text_field( $_POST['rating_suspension_disbelief']) : NULL
	);

	// Check to see if the book already exists on their bookshelf
	$google_books_ID = $form_data['google_books_id'];
	$user_id = $form_data['user_id_shelf'];
	
	$book_query = "SELECT * FROM {$wpdb->prefix}bookworm_books WHERE user_ID_shelf = '$user_id' AND google_books_ID = '$google_books_ID'";
	$check_book_entries = $wpdb->get_results($book_query);
	
	if (count($check_book_entries) > 0) {
		echo 'duplicate';
	} else {			
		$add_book_insert = $wpdb->insert( "{$wpdb->prefix}bookworm_books", $form_data );
		if ($add_book_insert) {
	        echo "success";
	    } else {
	        echo $wpdb->print_error();
	        echo $wpdb->last_error;
	        echo $wpdb->last_query;
	        echo $wpdb->last_result;
	    }
	}

    die();
}
add_action('wp_ajax_add_book', 'add_book');
add_action('wp_ajax_nopriv_add_book', 'add_book');

/****************
** Update Book **
****************/
function update_book() {
	global $wpdb;
    $wpdb->show_errors();
    
    if (!isset($_POST['book_id'])) :
        echo "No book id :(";
    
    else:
    
    $book_id = $_POST['book_id'];

    if (isset($_POST['date_started'])) {
        $date_started = strtotime($_POST['date_started']);
        $date_started = date('Y-m-d', $date_started);
    } else {
        $date_started = NULL;
    }
    if (isset($_POST['date_finished'])) {
        $date_finished = strtotime($_POST['date_finished']);
        $date_finished = date('Y-m-d', $date_finished);
    } else {
        $date_finished = NULL;
    }
    if (isset($_POST['shared_on_shelf'])) {
	    if ($_POST['shared_on_shelf'] == 'on') {
	    	$shared_on_shelf = 'invisible';
	    } else {
		    $shared_on_shelf = 'shared';
	    }
    } else {
	    $shared_on_shelf = 'shared';
    }
    if (isset($_POST['tagsArray']) && $_POST['tagsArray'] != '' && $_POST['tagsArray'] != '[]' && $_POST['tagsArray'] != []) {
		$tags = $_POST['tagsArray'];
	} else {
		$tags = NULL;
	}

	$form_data = array(
		'shared_on_shelf' => $shared_on_shelf,
		'ISBN_10' => isset($_POST['isbn_10']) ? sanitize_text_field( $_POST['isbn_10']) : NULL,
		'ISBN_13' => isset($_POST['isbn_13']) ? sanitize_text_field( $_POST['isbn_13']) : NULL,
		'date_started' => $date_started,
		'date_finished' => $date_finished,
		'notes' => isset($_POST['notes']) ? sanitize_text_field( $_POST['notes']) : NULL,
		'fiction_or_non' =>  isset($_POST['fiction_or_non']) ? sanitize_text_field( $_POST['fiction_or_non']) : NULL,
		'tags' =>  $tags,
		'recommended_by' => isset($_POST['recommended_by']) ? sanitize_text_field( $_POST['recommended_by']) : NULL,
		'recommended_to' => isset($_POST['recommended_to']) ? sanitize_text_field( $_POST['recommended_to']) : NULL,
		'rating_mood' => isset($_POST['rating_mood']) ? sanitize_text_field( $_POST['rating_mood']) : NULL,
		'rating_language' => isset($_POST['rating_language']) ? sanitize_text_field( $_POST['rating_language']) : NULL,
		'rating_romance' => isset($_POST['rating_romance']) ? sanitize_text_field( $_POST['rating_romance']) : NULL,
		'rating_suspension_disbelief' => isset($_POST['rating_suspension_disbelief']) ? sanitize_text_field( $_POST['rating_suspension_disbelief']) : NULL
	);
	//print_r($form_data);
		
	$update_book_entry = $wpdb->update( "{$wpdb->prefix}bookworm_books", $form_data, array('id' => $book_id) );
	
    if ($update_book_entry) {
        echo "Book entry updated";
    } else {
        echo $wpdb->print_error();
        echo $wpdb->last_error;
        echo $wpdb->last_query;
        echo $wpdb->last_result;
    }
	
    endif;

    die();
}
add_action('wp_ajax_update_book', 'update_book');
add_action('wp_ajax_nopriv_update_book', 'update_book');

/****************
** Delete Book **
****************/
function delete_book() {
	global $wpdb;
    $wpdb->show_errors();
    
    if (!isset($_POST['book_id'])) :
        echo "No book id :(";
    
    else:
    
    $book_id = $_POST['book_id'];

	$update_book_entry = $wpdb->delete( "{$wpdb->prefix}bookworm_books", array('id' => $book_id) );
    if ($update_book_entry) {
        echo "Book entry deleted";
    } else {
        echo $wpdb->print_error();
    }
	
    endif;

    die();
}
add_action('wp_ajax_delete_book', 'delete_book');
add_action('wp_ajax_nopriv_delete_book', 'delete_book');

/****************
** Book Search **
****************/
function book_search() {
    check_ajax_referer('bookworm_thinking_nonce', 'nonce');
    global $wpdb;
    $wpdb->show_errors();

    $search_query = !empty($_POST['data']['s'])
        ? $_POST['data']['s']
        : null;
        
    $user_ID_shelf = !empty($_POST['data']['id'])
        ? $_POST['data']['id']
        : null;

    // If no search term 
    if (!$search_query) {
        die;
    }

    // Book search
    $book_results = $wpdb->get_results(
    $wpdb->prepare(
	        "SELECT * FROM {$wpdb->prefix}bookworm_books WHERE user_ID_shelf = %s AND (title LIKE %s OR author LIKE %s)", $user_ID_shelf, '%'.$search_query.'%', '%'.$search_query.'%')
	);
	
    if (count($book_results) > 0) {
	    // echo $wpdb->last_query;
	    //print_r($book_results);
	    foreach ($book_results as $book_item) {
		    $i++;
	        $book_id = $book_item->google_books_ID;
	        $bw_id = $book_item->id;
	        $book_title = stripslashes($book_item->title);
	        $book_authors = sanitizeInput($book_item->author);
	        $book_description = stripslashes($book_item->description);
			if (preg_match('/^.{1,90}\b/s', $book_description, $match)) {
			    $book_description_excerpt = $match[0] . "...";
			}
	        $book_published_date = $book_item->published_date;
	        $display_published_date = strtotime($book_published_date);
			$display_published_date = date('j F Y', $display_published_date);
	        $book_img = $book_item->small_thumbnail_url;
	        if ($book_img == '') {
	            $book_img = get_stylesheet_directory_uri() . "/img/icon_open-book.svg";
	        }
	
	        echo '<div class="book-search-entry">';
			echo '<a class="book-search-entry-link" id="' . $book_id . '" data-title="' . $book_title . '" data-img="' . $book_img . '" data-author="' . $book_authors . '" data-pubdate="' . $book_published_date . '"  data-disppubdate="' . $display_published_date . '" data-description="' . $book_description . '" data-bwid="' . $bw_id . '" href="#">';
	        echo '<div class="book-search-thumb"><img src="' . $book_img . '"></div>';
	        echo '<div class="book-search-details">';
	        echo '<span class="book-search-title">' . $book_title . '</span>';
	        echo '<span class="book-search-author">by ' . $book_authors . '</span>';
			echo '<span class="book-search-description">' . $book_description_excerpt . '</span>';
	        echo '</div>';
	        echo '</a>';
	        echo '</div>';
	    }
	} else {
	    echo 'No results found.';
	}
        
    die();
}
add_action('wp_ajax_book_search', 'book_search');
add_action('wp_ajax_nopriv_book_search', 'book_search');

/**************
** Book List **
**************/
function book_list() {
	global $wpdb;
    $wpdb->show_errors();

    if (!isset($_POST['bookshelf_category'])) :

        echo "No bookshelf category :(";
    
    else :

    $bookshelf_category = $_POST['bookshelf_category'];
    $wp_current_user_id = $_POST['wp_current_user_id'];
    
    if (!isset($_POST['bookshelf-sort-select']) && !isset($_POST['bookshelf_tag_filters']) ) {
        echo "No book shelf filtering or sorting";
    }
	
	if ($bookshelf_category == 'nightstand') {
		$book_entry_query = "SELECT * FROM {$wpdb->prefix}bookworm_books WHERE (user_id_shelf = '$wp_current_user_id' AND date_started IS NOT NULL AND date_started != '0000-00-00' AND date_started != '1970-01-01') AND (date_finished IS NULL OR date_finished = '0000-00-00' OR date_finished = '1970-01-01')";
		$bookshelf_filtering = "id ";
		$bookshelf_date_filter = "date_started";
		 if (isset($_POST['bookshelf_sorting'])) {
	    	$bookshelf_sorting_order = $_POST['bookshelf_sorting'];
	    } else {
		    $bookshelf_sorting_order = "DESC";
	    }
		$bookshelf_sorting_query_part_default = $bookshelf_filtering . " " . $bookshelf_sorting_order;
	} elseif ($bookshelf_category == 'finished') {
		$book_entry_query = "SELECT * FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = '$wp_current_user_id' AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01'";
		$bookshelf_filtering = "date_finished ";
		$bookshelf_date_filter = "date_finished";
		 if (isset($_POST['bookshelf_sorting'])) {
	    	$bookshelf_sorting_order = $_POST['bookshelf_sorting'];
	    } else {
		    $bookshelf_sorting_order = "DESC";
	    }
		$bookshelf_sorting_query_part_default = $bookshelf_filtering . " " . $bookshelf_sorting_order;
	} elseif ($bookshelf_category == 'wishlist') {
		$book_entry_query = "SELECT * FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = '$wp_current_user_id' AND (date_started IS NULL OR date_started = '0000-00-00' OR date_started = '1970-01-01') AND (date_finished IS NULL OR date_finished = '0000-00-00' OR date_finished = '1970-01-01')";
		$bookshelf_filtering = "id ";
		 if (isset($_POST['bookshelf_sorting'])) {
	    	$bookshelf_sorting_order = $_POST['bookshelf_sorting'];
	    } else {
		    $bookshelf_sorting_order = "DESC";
	    }
		$bookshelf_sorting_query_part_default = $bookshelf_filtering . " " . $bookshelf_sorting_order;
	} elseif ($bookshelf_category == 'notes') {
		$book_entry_query = "SELECT * FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = '$wp_current_user_id' AND (notes IS NOT NULL AND notes != '' AND notes != 'Enter your notes for this book.')";
		$bookshelf_filtering = "date_finished ";
		$bookshelf_date_filter = "date_finished";
		 if (isset($_POST['bookshelf_sorting'])) {
	    	$bookshelf_sorting_order = $_POST['bookshelf_sorting'];
	    } else {
		    $bookshelf_sorting_order = "DESC";
	    }
		$bookshelf_sorting_query_part_default = $bookshelf_filtering . " " . $bookshelf_sorting_order;
	}
	
	/* For a single data point set by a <select> element with 'bookshelf_tag_filters'
    if (isset($_POST['bookshelf_tag_filters']) && $_POST['bookshelf_tag_filters'] != '' && $_POST['bookshelf_tag_filters'] != NULL) {
        $bookshelf_tag_filters = $_POST['bookshelf_tag_filters'];
		if ($bookshelf_tag_filters != 'current' && $bookshelf_tag_filters != 'P3M' && $bookshelf_tag_filters != 'P6M' && $bookshelf_tag_filters != 'P12M') {
			$bookshelf_tag_query = " AND FIND_IN_SET('$bookshelf_tag_filters', tags)";
		} elseif ($bookshelf_tag_filters == 'P12M') {
			$bookshelf_tag_query = " AND $bookshelf_date_filter > NOW() - INTERVAL 12 MONTH";
		} elseif ($bookshelf_tag_filters == 'P6M') {
			$bookshelf_tag_query = " AND $bookshelf_date_filter > NOW() - INTERVAL 6 MONTH";
		} elseif ($bookshelf_tag_filters == 'P3M') {
			$bookshelf_tag_query = " AND $bookshelf_date_filter > NOW() - INTERVAL 3 MONTH";
		} elseif ($bookshelf_tag_filters == 'current') {
			// Return entries finished within the current calendar year
			$bookshelf_tag_query = " AND YEAR($bookshelf_date_filter) = YEAR(CURDATE())";
		}
		$book_entry_query .= $bookshelf_tag_query;
    }
	*/

	/* For checkboxes with bookshelf_tag_filters as an array */
	if (isset($_POST['bookshelf_tag_filters'])) {
		$bookshelf_tag_filters = $_POST['bookshelf_tag_filters'];
		for ($i = 0; $i < count($bookshelf_tag_filters); $i++) {
			$tag_filter = $bookshelf_tag_filters[$i];
			if ($tag_filter != 'current' && $tag_filter != 'P3M' && $tag_filter != 'P6M' && $tag_filter != 'P12M') {
				$bookshelf_tag_query_part[] = " FIND_IN_SET('$tag_filter', tags) ";
			} elseif ($tag_filter == 'P12M') {
				$bookshelf_tag_query_part[] = " $bookshelf_date_filter > NOW() - INTERVAL 12 MONTH ";
			} elseif ($tag_filter == 'P6M') {
				$bookshelf_tag_query_part[] = " $bookshelf_date_filter > NOW() - INTERVAL 6 MONTH ";
			} elseif ($tag_filter == 'P3M') {
				$bookshelf_tag_query_part[] = " $bookshelf_date_filter > NOW() - INTERVAL 3 MONTH ";
			} elseif ($tag_filter == 'current') {
				// Return entries finished within the current calendar year
				$bookshelf_tag_query_part[] = " YEAR($bookshelf_date_filter) = YEAR(CURDATE()) ";
			}
			$book_entry_query .= " AND (" . implode(" AND ", $bookshelf_tag_query_part) . ") ";
		}
	}

	if (isset($_POST['bookshelf-sort-select']) && $_POST['bookshelf-sort-select'] != '' && $_POST['bookshelf-sort-select'] != NULL) {
		$bookshelf_filter_by = $_POST['bookshelf-sort-select'];
        if ($bookshelf_filter_by == 'date_Started' || $bookshelf_filter_by == 'date_finished' || $bookshelf_filter_by == 'id') {
            $bookshelf_sorting_order = "DESC";
        } else {
            $bookshelf_sorting_order = "ASC";
        }
	    if (isset($_POST['bookshelf_sorting'])) {
	    	$bookshelf_sorting_order = $_POST['bookshelf_sorting'];
	    }
		if ($bookshelf_filter_by == 'author') {
			$book_entry_query .= " ORDER BY SUBSTRING_INDEX(TRIM(" . $bookshelf_filter_by . "), ' ', -1)" . $bookshelf_sorting_order;
		} else if ($bookshelf_filter_by == 'title') {
			$book_entry_query .= " ORDER BY CASE
				WHEN title LIKE 'The %' THEN SUBSTR(title, 5)
				WHEN title LIKE 'A %' THEN SUBSTR(title, 3)
				WHEN title LIKE 'An %' THEN SUBSTR(title, 4)
				ELSE title
			END" . ' ' . $bookshelf_sorting_order;
		} else {
			$book_entry_query .= " ORDER BY " . $bookshelf_filter_by . " " . $bookshelf_sorting_order;
		}
	} else {
		$book_entry_query .= " ORDER BY " . $bookshelf_sorting_query_part_default;
	}       
	        
    //echo $book_entry_query;
		    
    $book_entries = $wpdb->get_results($book_entry_query);

	if ($book_entries) {
		$book_count = count($book_entries);
		echo "<h5 class='filtered-results-count'>Filtered Results: <strong>" . $book_count . "</strong></h5>";
		foreach ($book_entries as $book_entry) {
			$book_entry_published_date = strtotime($book_entry->published_date);
			$book_entry_published_date = date('j F Y',$book_entry_published_date);
			$book_entry_title = stripslashes($book_entry->title);
			$book_entry_notes = stripslashes($book_entry->notes);
			$notes_word_count = str_word_count($book_entry_notes);
			if ($book_entry->description != '') {
				$book_entry_description = stripslashes($book_entry->description);
				if (preg_match('/^.{1,120}\b/s', $book_entry_description, $match)) {
					$book_description_excerpt = $match[0] . "...";
				}
			}

			echo '<div class="book-entry">';
			echo '<div class="book-entry-meta">';
			echo '<a class="book-entry-edit-link" href="/update-book/?id=' . $book_entry->id . '>">';
			echo '<div class="book-entry-thumb">
					<img class="book_entry_img" src="' . $book_entry->small_thumbnail_url . '">
				</div>';
			echo '<div class="book-entry-details">';
			echo '<div id="book_entry_title" class="book-entry-title">' . $book_entry_title . '</div>';
			echo '<div id="book_entry_author" class="book-entry-author">by ' . $book_entry->author . '</div>';
			// echo '<div id="book_entry_pub_date" class="book-entry-publication-date">published ' . $book_entry_published_date . '</div>';
			if ($book_entry->fiction_or_non != NULL && $book_entry->fiction_or_non != '') {
				// echo '<div id="book_entry_genre" class="book-entry-genre">' . ucfirst($book_entry->fiction_or_non) . '</div>';
			}
			if ($book_entry->description != '') {
				echo '<div id="book_entry_description" class="book_entry_description">' . $book_description_excerpt . '</div>';
			} 
			if ($book_entry->tags != NULL && $book_entry->tags != '' && !empty($book_entry->tags)) {
				$tagsArray = explode(',', $book_entry->tags);
				echo '<ul class="bookshelf-book-tags">';
				foreach ($tagsArray as $tagID) {
					$tag = get_tag($tagID);
					echo '<li class="' . $tag->slug . ' active" data-id="' . $tag->term_id . '">' . $tag->name . '</li>';
				}
			}
			echo '</div>';
			echo '</a>';
			if ($bookshelf_category == 'notes') {
				if ($notes_word_count > 40) {
					$book_entry_notes_classes = 'book-entry-notes overage';
				} else {
					$book_entry_notes_classes = 'book-entry-notes';
				}
				echo '<div id="book_entry_notes" class="' . $book_entry_notes_classes . '">';
				echo '<a class="popup-trigger" href="#popup_notes_' . $book_entry->id . '">';
				echo '<strong>My Notes: </strong>' . $book_entry_notes;
				echo '</a>';
				echo '</div>';
				echo '<div id="popup_notes_' . $book_entry->id . '" class="popup-wrapper popup-notes">';
				echo '<div class="popup popup-large">';
				echo '<a class="close-button popup-close" href="#"><img src="' . get_stylesheet_directory_uri() . '/img/icon_close.svg" alt="Close button" /></a>';
				echo '<p><strong>My Notes: </strong>' . $book_entry_notes . '</p>';
				echo '<p class="margin-bottom-0"><a href="/update-book/?id=' . $book_entry->id . '" class="popup-link">Edit</a></p>';
				echo '</div>';
			}
			echo '</div>';
			
			echo '</div>';
		}
	} else {
		echo '<div class="book-entry">';
		echo '<div id="book_entry_description" class="book_entry_description">';
		echo '<h4>No Results</h4>';
		echo '<p>Sorry, no books match those criteria.</p>';
		echo '</div>';
		echo '</div>';
	}
	
    endif;

    die();
}
add_action('wp_ajax_book_list', 'book_list');
add_action('wp_ajax_nopriv_book_list', 'book_list');

/* Book Analytics Query */
function book_analytics_query() {
	global $wpdb;
    $wpdb->show_errors();

    if (!isset($_POST['bookshelf_start_date']) || !isset($_POST['bookshelf_end_date'])) :
        echo "No bookshelf date range :(";
    else :

    $bookshelf_start_date = $_POST['bookshelf_start_date'];
	$bookshelf_end_date = $_POST['bookshelf_end_date'];
	$bookshelf_date_range = $_POST['bookshelf_date_range'];
    $wp_current_user_id = $_POST['wp_current_user_id'];

	$table_name = $wpdb->prefix . 'bookworm_books';
    $book_entries = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id_shelf = %d AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01' AND date_finished BETWEEN %s AND %s",
        $wp_current_user_id,
		$bookshelf_start_date,
		$bookshelf_end_date
    ));
	
	echo count($book_entries);

	endif;
	
	die();
}
add_action('wp_ajax_book_analytics_query', 'book_analytics_query');
add_action('wp_ajax_nopriv_book_analytics_query', 'book_analytics_query');

/* Book Analytics Tags Query */
function book_analytics_tags_query() {
	global $wpdb;
    $wpdb->show_errors();

    if (!isset($_POST['bookshelf_start_date']) || !isset($_POST['bookshelf_end_date'])) :
        echo "No bookshelf date range :(";
    else :

    $bookshelf_start_date = $_POST['bookshelf_start_date'];
	$bookshelf_end_date = $_POST['bookshelf_end_date'];
	$bookshelf_date_range = $_POST['bookshelf_date_range'];
    $wp_current_user_id = $_POST['wp_current_user_id'];
	$all_book_tags = array();

	$table_name = $wpdb->prefix . 'bookworm_books';
	$book_entries = $wpdb->get_results($wpdb->prepare(
		"SELECT * FROM $table_name WHERE user_id_shelf = %d AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01' AND date_finished BETWEEN %s AND %s",
		$wp_current_user_id,
		$bookshelf_start_date,
		$bookshelf_end_date
	));

	foreach ($book_entries as $book_entry) {
		if ($book_entry->tags != NULL && $book_entry->tags != '' && !empty($book_entry->tags)) {
			$book_entry_tags = explode(',', $book_entry->tags);
			//$book_entry_tags = json_decode(stripslashes($book_entry->tags), true);
			$all_book_tags = array_merge($all_book_tags, $book_entry_tags);
		} 
	}

	if (!empty($all_book_tags)) {
		$all_book_tags = array_unique($all_book_tags);
		$output = '';

		foreach ($all_book_tags as $tagID) {
			$tag = get_tag($tagID);
			$book_count = $wpdb->get_var($wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = %d AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01' AND date_finished BETWEEN %s AND %s AND FIND_IN_SET(%d, tags)",
				$wp_current_user_id,
				$bookshelf_start_date,
				$bookshelf_end_date,
				$tagID
			));

			$output .= '<div class="tag-count-wrapper">';
			$output .= '<li class="tag-entry active ' . $tag->slug . '" data-id="' . $tag->slug . '">' . $tag->name . '</li>';
			$output .= '<span class="book-count">' . $book_count . '</span>';
			$output .= '</div>';
		}
		echo $output;
	}
	endif;
	die();
}
add_action('wp_ajax_book_analytics_tags_query', 'book_analytics_tags_query');
add_action('wp_ajax_nopriv_book_analytics_tags_query', 'book_analytics_tags_query');

/* Book Analytics Ratings Query */
function book_analytics_ratings_query() {
	global $wpdb;
	$wpdb->show_errors();	

	if (!isset($_POST['bookshelf_start_date']) || !isset($_POST['bookshelf_end_date'])) :
		echo "No bookshelf date range :(";
	else :

	$bookshelf_start_date = $_POST['bookshelf_start_date'];
	$bookshelf_end_date = $_POST['bookshelf_end_date'];
	$bookshelf_date_range = $_POST['bookshelf_date_range'];
    $wp_current_user_id = $_POST['wp_current_user_id'];
	$ratingArrayObject = array();

	$ratingArray = array('rating_mood' => 'Mood', 'rating_language' => 'Language', 'rating_romance' => 'Romance', 'rating_suspension_disbelief' => 'Suspension of Disbelief');

	foreach ($ratingArray as $rating => $label) {
		$rating_data = $wpdb->get_col($wpdb->prepare(
			"SELECT $rating FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = %d AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01'AND date_finished BETWEEN %s AND %s",
			$wp_current_user_id,
			$bookshelf_start_date,
			$bookshelf_end_date
		));

		if ($rating == 'rating_mood') {
			$bg_color = 'rgba(219, 99, 255, 1)';
		} else if ($rating == 'rating_language') {
			$bg_color = 'rgba(99, 255, 187, 1)';
		} else if ($rating == 'rating_romance') {
			$bg_color = 'rgb(255, 99, 132)';
		} else if ($rating == 'rating_suspension_disbelief') {
			$bg_color = 'rgba(99, 167, 255, 1)';
		}

		// Add to result array
		$ratingArrayObject[] = array(
			'rating' => $rating,
			'label' => $label,
			'data' => $rating_data,
			'bg_color' => $bg_color
		);
	}

	// Return as JSON
	wp_send_json_success($ratingArrayObject);

	endif;

	die();
}
add_action('wp_ajax_book_analytics_ratings_query', 'book_analytics_ratings_query');
add_action('wp_ajax_nopriv_book_analytics_ratings_query', 'book_analytics_ratings_query');

/***************
** Add Friend **
***************/
function add_friend() {
	global $wpdb;
    $wpdb->show_errors();
    
    if (!isset($_POST['enter_bookworm_code'])) :
        echo "No bookworm code :(";
    
    else:
    
    $current_user_id = $_POST['current_user_id'];
    $bookworm_code = $_POST['enter_bookworm_code'];
    $logo_img = file_get_contents( get_stylesheet_directory_uri() . '/img/icon_bookworm-logo.svg' ); 
    $users = get_users();
    $meta_key_match = false;
	
	foreach ($users as $user) {
		$user_id = $user->ID;
		$meta_key = "token_id";
		$meta_value = $bookworm_code;
		$meta_match = get_user_meta($user_id, $meta_key, true);
	   	if ($meta_match == $meta_value) {
		   	$meta_key_match = true;
		   	$friend_user_id = $user_id;
		   	
		   	// Check to see if they're already friends
		   	$friend_query = "SELECT * FROM {$wpdb->prefix}bookworm_friends WHERE user_id = '$current_user_id' AND friend_id = '$friend_user_id' AND status = 'friends'";
			$friends = $wpdb->get_results($friend_query);
			
			if ($friends) {
				$friend_user = get_user_by('id', $friend_user_id);
				echo "<div class='popup-heading-wrapper'>
			<div class='icon-background'>" . $logo_img . "</div>
			<h4>Oh, Wait</h4>
		</div>
		<p>You and " . $friend_user->user_login . " are already connected on BookWorm!</p><p><a href='/friend?id=" . $friend_user_id . "'>Check out what they're reading.</a></p>";
			} else {	
			   	// Add a friend record
			   	$add_friend_insert = $wpdb->insert( "{$wpdb->prefix}bookworm_friends", array('user_id' => $current_user_id, 'request_code' => $bookworm_code, 'friend_id' => $friend_user_id, 'status' => 'friends') );
			    if ($add_friend_insert) {
			        $current_user = get_user_by( 'id', $current_user_id );
					$current_user_avatar = get_avatar( $current_user_id, 96 );
					$friend_user = get_user_by( 'id', $friend_user_id );
					
			        
			        $reciprocal_friend_insert = $wpdb->insert( "{$wpdb->prefix}bookworm_friends", array('user_id' => $friend_user_id, 'request_code' => $bookworm_code, 'friend_id' => $current_user_id, 'status' => 'friends') );
			        
			        // also add notifications
			        $site_url = get_site_url();
			        $notification_message = $current_user->user_login . " has added you as a friend! You are now connected on BookWorm. <a href='" . $site_url . "/friend?id=" . $current_user_id . "'>Check out what they're reading.</a>";
			        $notification_insert = $wpdb->insert( "{$wpdb->prefix}bookworm_notifications", array('recipient_id' => $friend_user_id, 'message' => $notification_message) );
			        
			        // Email the notifcation as well
				    $headers = array('From: Bookworm <contact@bookworm.community>', 'Reply-To: k5stokes@gmail.com');
				    $recipient = $friend_user->user_email;
				    $subject = $current_user->user_login . ' has added you as a friend!';
				    $body = $notification_message;
				    wp_mail( $recipient, $subject, $body, $headers );
					
			        echo "<div class='popup-heading-wrapper'>
			<div class='icon-background'>" . $logo_img . "</div>
			<h4>Friend added</h4>
		</div>
		<p>You have successfully added " . $friend_user->user_login . " as a friend! You are now connected on BookWorm.</p><p><a href='/friend?id=" . $friend_user_id . "'>Check out what they're reading.</a></p>";
		        } else {
					echo $wpdb->print_error();
				}
		    }
		}
	}
	if ($meta_key_match == false) {
		echo "<div class='popup-heading-wrapper'>
			<div class='icon-background'>" . $logo_img . "</div>
			<h4>Oops</h4>
		</div>
		<p>That code does not match any BookWorm users.</p>";
	}
	
    endif;

    die();
}
add_action('wp_ajax_add_friend', 'add_friend');
add_action('wp_ajax_nopriv_add_friend', 'add_friend');

/*****************
** Friends List **
*****************/
function friends_list() {
	global $wpdb;
    $wpdb->show_errors();
    
    $current_user_id = $_POST['current_user_id'];

	$friend_query = "SELECT friend_id FROM {$wpdb->prefix}bookworm_friends WHERE user_id = '$current_user_id' AND status = 'friends'";
	$friends = $wpdb->get_results($friend_query);
		if ($friends) {
			$friend_ids = array();
			foreach ($friends as $friend) {
				$friend_ids[] = $friend->friend_id;
			}
			$users = get_users(
				array(
					'include' => $friend_ids
				)
			);
			foreach ($users as $user) {
				if ($user->ID != $wp_current_user_id) {
					$friend_id = $user->ID;
					$friend_avatar = get_avatar( $friend_id, 96 );
					
					// Friend Deets
				?>
					<div class="friend-entry">
					<a href="/friend?id=<?php echo $friend_id; ?>">
						<div class="friend-avatar">
							<div class="useravatar">
								<?php echo $friend_avatar; ?>
								<span class="remove-button remove-friend" data-friendid="<?php echo $friend_id; ?>"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon-remove.png'; ?>" alt="Remove friend" /></span>
							</div>
						</div>
						<div class="friend-username"><?php echo $user->user_login; ?></div>
					</a>
					</div> <!-- close friend entry -->
				<?php
				}
			}
		} else {
			echo "<p>You haven't connected with anyone on BookWorm yet.</p>";
		}
	die();
}
add_action('wp_ajax_friends_list', 'friends_list');
add_action('wp_ajax_nopriv_friends_list', 'friends_list');

/*******************
** Friends Search **
*******************/
function friends_search() {
	global $wpdb;
    $wpdb->show_errors();

	die();
}
add_action('wp_ajax_friends_search', 'friends_search');
add_action('wp_ajax_nopriv_friends_search', 'friends_search');

function delete_notification() {
	global $wpdb;
    $wpdb->show_errors();
    
    $notification_id = $_POST['notification_id'];
    echo $notification_id;
    $delete_notification = $wpdb->delete( "{$wpdb->prefix}bookworm_notifications", array( 'id' => $notification_id ) );
    
    if ($delete_notification) {
        echo "Book entry deleted";
    } else {
        echo $wpdb->print_error();
    }

	die();
}
add_action('wp_ajax_delete_notification', 'delete_notification');
add_action('wp_ajax_nopriv_delete_notification', 'delete_notification');

/******************
** Remove Friend **
******************/
function remove_friend() {
	global $wpdb;
    $wpdb->show_errors();
    
    $remove_friend_id = $_POST['remove_friend_id'];
    $current_user_id = $_POST['current_user_id'];
    
    $delete_friend = $wpdb->query( "DELETE from {$wpdb->prefix}bookworm_friends WHERE user_id = $current_user_id AND friend_id = $remove_friend_id AND status = 'friends'");
    $delete_friend2 = $wpdb->query( "DELETE from {$wpdb->prefix}bookworm_friends WHERE user_id = $remove_friend_id AND friend_id = $current_user_id AND status = 'friends'");
    $delete_recommendations = $wpdb->query( "DELETE from {$wpdb->prefix}bookworm_recommendations WHERE recipient_id = $remove_friend_id AND recommender_id = $current_user_id");
    $delete_recommendations2 = $wpdb->query( "DELETE from {$wpdb->prefix}bookworm_recommendations WHERE recipient_id = $current_user_id AND recommender_id = $remove_friend_id");
    
    if ($delete_friend) {
        echo "Friends removed";
    } else {
        echo $wpdb->print_error();
    }
    if ($delete_friend2) {
        echo "Friends removed";
    } else {
        echo $wpdb->print_error();
    }
    if ($delete_recommendations) {
        echo "reccos removed";
    } else {
        echo $wpdb->print_error();
    }
    if ($delete_recommendations2) {
        echo "reccos removed";
    } else {
        echo $wpdb->print_error();
    }

	die();
}
add_action('wp_ajax_remove_friend', 'remove_friend');
add_action('wp_ajax_nopriv_remove_friend', 'remove_friend');

/*******************
** Recommend Book **
*******************/
function recommend_book() {
	global $wpdb;
    $wpdb->show_errors();
    
    $form_data = array(
		'title' => isset($_POST['title']) ? sanitize_text_field($_POST['title']) : NULL,
		'author' => isset($_POST['author']) ? sanitize_text_field($_POST['author']) : NULL,
		'google_books_id' => isset($_POST['google_books_id']) ? sanitize_text_field( $_POST['google_books_id']) : NULL,
		'small_thumbnail_url' => isset($_POST['small_thumbnail_url']) ? sanitize_text_field( $_POST['small_thumbnail_url']) : NULL,
		'recommendation_note' => isset($_POST['recommendation_note']) ? sanitize_text_field( $_POST['recommendation_note']) : NULL,
		'recommender_id' => isset($_POST['user_id_from']) ? sanitize_text_field( $_POST['user_id_from']) : NULL,
		'recipient_id' => isset($_POST['user_id_to']) ? sanitize_text_field( $_POST['user_id_to']) : NULL
	);
	
	//print_r($form_data);
	
	if ($form_data['recommender_id'] != 0 && $form_data['recommender_id'] != '' && $form_data['recommender_id'] != 'undefined' && $form_data['recommender_id'] != NULL && $form_data['recommender_id'] != '#' && $form_data['recipient_id'] != 0 && $form_data['recipient_id'] != '' && $form_data['recipient_id'] != 'undefined' && $form_data['recipient_id'] != NULL && $form_data['recipient_id'] != '#') {
		// Already recommended?
		$recommender_id = $form_data['recommender_id'];
		$recipient_id = $form_data['recipient_id'];
		$google_books_ID = $form_data['google_books_id'];
		$recommendation_query = "SELECT * FROM {$wpdb->prefix}bookworm_recommendations WHERE recommender_id = '$recommender_id' AND recipient_id = '$recipient_id' AND google_books_ID = '$google_books_ID'";
		$rec_book_entries = $wpdb->get_results($recommendation_query);
		//print_r($rec_book_entries);
		//echo count($rec_book_entries);
			
		if (count($rec_book_entries) == 0) {
			$recommend_book_insert = $wpdb->insert( "{$wpdb->prefix}bookworm_recommendations", $form_data );
			// lets get and then add the recommendation id from the database so we can call all the data points on the Add Book form
			$recommendation_id = $wpdb->insert_id;
		} else {
			echo 'duplicate';
		}
	}
	

    if ($recommend_book_insert) {
        echo 'success';
        if ($_POST['add_notification'] != 'false') {
		    /* Add Notifications */
		    // Check to see if the book already exists on their bookshelf
			$google_books_ID = $form_data['google_books_id'];
			$recipient_id = $form_data['recipient_id'];
		    $recommender_user = get_user_by( 'id', $form_data['recommender_id'] );
		    $recipient_user = get_user_by( 'id', $recipient_id );
		    $site_url = get_site_url();
			
			$recommendation_query = "SELECT * FROM {$wpdb->prefix}bookworm_books WHERE user_ID_shelf = '$recipient_id' AND google_books_ID = '$google_books_ID'";
			$rec_book_entries = $wpdb->get_results($recommendation_query);
			
			if (count($rec_book_entries) > 0) {
				// Already on bookshelf
				$notification_message = $recommender_user->user_login . " has recommended " . stripslashes($form_data['title']) . " to you! <a href='" . $site_url . "/update-book/?id=" . $rec_book_entries[0]->id . "'>" . $rec_book_entries[0]->title . "</a> is <strong>already on your bookshelf</strong> and " . $recommender_user->user_login . " has been added as a recommender for the book.";
				
			    if ($form_data['recommendation_note'] != '' && $form_data['recommendation_note'] != NULL && $form_data['recommendation_note'] != "Enter a note about your book recommendation.") {
				    $notification_message .= "<br/>A message from " . $recommender_user->user_login . ": '" . stripslashes($form_data['recommendation_note']) . "'";
				}
				
			} else {
				// New book
			    $notification_message = $recommender_user->user_login . " has recommended " . stripslashes($form_data['title']) . " to you! <a href='" . $site_url . "/add-book/?id=" . $recommendation_id . "'>Add it to your wishlist.</a>";
			    if ($form_data['recommendation_note'] != '' && $form_data['recommendation_note'] != NULL && $form_data['recommendation_note'] != "Enter a note about your book recommendation.") {
				    $notification_message .= "<br/>A message from " . $recommender_user->user_login . ": '" . stripslashes($form_data['recommendation_note']) . "'";
				}
			}
			
		    $notification_insert = $wpdb->insert( "{$wpdb->prefix}bookworm_notifications", array('recipient_id' => $form_data['recipient_id'], 'message' => $notification_message) );
		    
		    // Email the notifcation as well
		    $headers = array('From: Bookworm <contact@bookworm.community>', 'Reply-To: k5stokes@gmail.com');
		    $recipient = $recipient_user->user_email;
		    $subject = $recommender_user->user_login . ' has recommended a book to you!';
		    $body = $notification_message;
		    wp_mail( $recipient, $subject, $body, $headers );
			
			if ($notification_insert) {
		        echo "Notification added";
		    }
		    /* else {
		        echo $wpdb->print_error();
		        echo $wpdb->last_error;
		        echo $wpdb->last_query;
		        echo $wpdb->last_result;
		    }*/
		}
    } 
    /*else {
        echo $wpdb->print_error();
        echo $wpdb->last_error;
        echo $wpdb->last_query;
        echo $wpdb->last_result;
    }*/
    
    
    die();
}
add_action('wp_ajax_recommend_book', 'recommend_book');
add_action('wp_ajax_nopriv_recommend_book', 'recommend_book');

/*************************
** Recommendations List **
*************************/
function recommendations_list() {
	global $wpdb;
    $wpdb->show_errors();
    
    $current_user_id = $_POST['current_user_id'];
    $google_books_id = $_POST['google_books_id'];
    $response = array();
    
    function recommendationsEntryBuilder($recommendations_entries, $recTo) {
		$recommendations = '';
    	$recommendations .= '<div class="flex wrap gap-20">';
		
		foreach ($recommendations_entries as $recommendation_entry) {
			if ($recTo == true) {
				$friend_id = $recommendation_entry->recipient_id;
			} else {
				$friend_id = $recommendation_entry->recommender_id;
			}
			$friend_user = get_user($friend_id);
			$friend_avatar = get_avatar( $friend_id, 96 );
			// Friend Deets
			$recommendations .= '<div class="friend-entry">';
			$recommendations .= '<a style="display:block;" href="/friend?id=' . $friend_id . '">';
			$recommendations .= '<div class="friend-avatar">';
			$recommendations .= '<div class="useravatar">' . $friend_avatar . '</div>';
			$recommendations .= '</div>';
			$recommendations .= '<div class="friend-username">' . $friend_user->user_login . '</div>';
			$recommendations .= '</a>';
			$recommendations .= '</div>';
		} // close foreach
		$recommendations .= '</div>';
		
		return $recommendations;
	}

	// Recs To
	$recommendations_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bookworm_recommendations WHERE recommender_id = '$current_user_id' AND google_books_ID = '$google_books_id'");
	if (!empty($recommendations_entries)) {
		$recommend_to = '<label for="">Recommended To:</label>';
		
		$recommendations_build = recommendationsEntryBuilder($recommendations_entries, true);
		$recommend_to .= $recommendations_build;
		
		array_push($response, (object)[
        	'recommend_to' => $recommend_to
        ]);
    } // close if recommendations 
    
    // Recs By
	$recommendations_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bookworm_recommendations WHERE recipient_id = '$current_user_id' AND google_books_ID = '$google_books_id'");
	if (!empty($recommendations_entries)) {
		$recommend_by = '<label for="">Recommended By:</label>';
		
		$recommend_by .= recommendationsEntryBuilder($recommendations_entries, false);
		
		array_push($response, (object)[
        	'recommend_by' => $recommend_by
        ]);
    } // close if recommendations 
	
	// Set the content type header to JSON
    header('Content-Type: application/json');
    
	//print_r($response);
	echo json_encode($response);
    		
	die();
}
add_action('wp_ajax_recommendations_list', 'recommendations_list');
add_action('wp_ajax_nopriv_recommendations_list', 'recommendations_list');

/*************************
** Get Gemini Summary **
*************************/
function get_gemini_summary() {
    check_ajax_referer('bookworm_thinking_nonce', 'nonce');

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('User not logged in');
    }

	// Read optional date range parameters sent from the analytics page
	$bookshelf_start_date = isset($_POST['bookshelf_start_date']) ? sanitize_text_field($_POST['bookshelf_start_date']) : null;
	$bookshelf_end_date = isset($_POST['bookshelf_end_date']) ? sanitize_text_field($_POST['bookshelf_end_date']) : null;
	$bookshelf_date_range = isset($_POST['bookshelf_date_range']) ? sanitize_text_field($_POST['bookshelf_date_range']) : null;

	$date_range_query = [
		'start_date' => $bookshelf_start_date,
		'end_date' => $bookshelf_end_date,
		'range' => $bookshelf_date_range,
	];

	$summary = get_gemini_user_notes_summary($user_id, $date_range_query);
    wp_send_json_success($summary);
}
add_action('wp_ajax_get_gemini_summary', 'get_gemini_summary');