<?php
	/* Template Name: Bookshelf Page Template */
	if (!is_user_logged_in()) {
		//auth_redirect();
		$url = get_site_url() . '/log-in';
		wp_safe_redirect( $url );
	}
	get_header();
	get_template_part('templates/content', 'inner-header');
	
	global $post;
	$wp_current_user_id = get_current_user_id();
	
	//books_add_descriptions();
	
	$bookshelf_category = $post->post_name;
	
	if ($bookshelf_category == 'nightstand') {
		$book_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bookworm_books WHERE (user_id_shelf = '$wp_current_user_id' AND date_started IS NOT NULL AND date_started != '0000-00-00' AND date_started != '1970-01-01') AND (date_finished IS NULL OR date_finished = '0000-00-00' OR date_finished = '1970-01-01') ORDER BY id DESC");
		$date_query_field = 'date_started';
	} elseif ($bookshelf_category == 'finished') {
		$book_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = '$wp_current_user_id' AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01' ORDER BY date_finished DESC");
		$date_query_field = 'date_finished';
	} elseif ($bookshelf_category == 'wishlist') {
		$book_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = '$wp_current_user_id' AND (date_started IS NULL OR date_started = '0000-00-00' OR date_started = '1970-01-01') AND (date_finished IS NULL OR date_finished = '0000-00-00' OR date_finished = '1970-01-01') ORDER BY id DESC");
		$date_query_field = '';
	} elseif ($bookshelf_category == 'notes') {
		$book_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = '$wp_current_user_id' AND (notes IS NOT NULL AND notes != '' AND notes != 'Enter your notes for this book.') ORDER BY date_finished DESC");
		$date_query_field = 'date_finished';
	}
?>

<main id="main" role="main" class="bookshelf">
	<div class="bookshelf-heading-wrapper flex justify-space-between align-items-center">
		<h3><?php echo get_the_title(); ?> <span class="small">(<?php echo count($book_entries); ?>)</span></h3>
		<?php
			get_template_part('templates/content', 'booklist-filters', array(
				'book_entries' => $book_entries,
				'wp_current_user_id' => $wp_current_user_id,
				'bookshelf_category' => $bookshelf_category
			));
		?>
	</div>
	<div class="bookshelf-heading-wrapper flex justify-space-between align-items-center">
		<?php
			if ($bookshelf_category == 'wishlist' || $bookshelf_category == 'finished') {
				if ($bookshelf_category == 'wishlist') {
					$search_placeholder = 'Search Wishlist';
				} else {
					$search_placeholder = 'Search Finished Books';
				}
		?>
			<form id="search_wishlist_form" class="bookshelf-search-form" action="/" method="POST">
				<label aria-hidden="false" style="display: none;" for="title"><?php echo $search_placeholder; ?></label>
				<div class="book-title-search">
					<input id="book_title" name="title" type="text" value="" placeholder="<?php echo $search_placeholder; ?>">
					<input id="bookshelf_category" name="bookshelf_category" type="hidden" value="<?php echo $search_placeholder; ?>">
					<div class="tooltip search-tooltip">
						<a style="top: -38px;" class="close-button search-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
						<section id="search_results" class="search-results">
						</section>
					</div>
				</div>
				<div id="validation_error" class="validation_error"><p>Please enter a book</p></div>
				<div class="book-display">
					<div class="book-entry-thumb">
						<img class="book_entry_img" src="">
					</div>
					<div class="book-entry-details">
						<div id="book_entry_title" class="book-entry-title">&nbsp;</div>
						<div id="book_entry_author" class="book-entry-author">&nbsp;</div>
						<div id="book_entry_pub_date" class="book-entry-publication-date">&nbsp;</div>
						<div id="book_entry_description" class="book-entry-description">&nbsp;</div>
					</div>
				</div>
				<input id="user_id_from" name="user_id_from" type="hidden" value="<?php echo $wp_current_user_id; ?>" />
			</form>
		<?php } ?>
		<div class="category-meta">
			<?php 
				// Apply Filters and Count
				if ($date_query_field != '') {
					$filterClosure = create_year_filter($date_query_field);
					$countCurrentYear = count(array_filter($book_entries, $filterClosure));
					echo "<p><span>This Year: <strong>" . $countCurrentYear . "</strong></span> &sdot; ";

					$filterClosure = create_date_filter($date_query_field, 3);
					$countThreeMonths = count(array_filter($book_entries, $filterClosure));
					echo "<span>Last 3 Months: <strong>" . $countThreeMonths . "</strong></span> &sdot; ";
					$filterClosure = create_date_filter($date_query_field, 6);
					$countSixMonths = count(array_filter($book_entries, $filterClosure));
					echo "<span>Last 6 Months: <strong>" . $countSixMonths . "</strong></span> &sdot; ";
					$filterClosure = create_date_filter($date_query_field, 12);
					$countTwelveMonths = count(array_filter($book_entries, $filterClosure));
					echo "<span>Last 12 Months: <strong>" . $countTwelveMonths . "</strong></span></p>";
				}
			?>
		</div>
	</div>

	<section class="bookshelf" id="bookshelf">
		<?php 
			if (empty($book_entries)) {
				if ($bookshelf_category == 'nightstand') {
			?>
				<div class="user-message"><p>You're not currently reading any books? Do you not...like books? If you want to give it a shot, <a href="/add-book/">add one</a> you're currently reading.</p></div>
			<?php } else if ($bookshelf_category == 'finished') { ?>
				<div class="user-message"><p>Reading is good for the soul. <a href="/add-book/">Add a book</a> you've made it all the way through.</p></div>
			<?php } else if ($bookshelf_category == 'wishlist') { ?>
				<div class="user-message"><p>There are maybe 130 million books published. Would you like to <a href="/add-book/">add one</a> of them to your wishlist?</p></div>
			<?php
					} 
				} else {
			foreach ($book_entries as $book_entry) {
				$book_entry_published_date = strtotime($book_entry->published_date);
				$book_entry_published_date = date('j F Y',$book_entry_published_date);
				$book_entry_title = stripslashes($book_entry->title);
				if ($book_entry->notes != '' && $book_entry->notes != NULL && $book_entry->notes != 'Enter your notes for this book.') {
					$book_entry_notes = stripslashes($book_entry->notes);
					$notes_word_count = str_word_count($book_entry_notes);
				}
				if ($book_entry->description != '') {
					$book_entry_description = stripslashes($book_entry->description);
					if (preg_match('/^.{1,120}\b/s', $book_entry_description, $match)) {
					    $book_description_excerpt = $match[0] . "...";
					}
				}
		?>
				<div class="book-entry flex">
					<div class="book-entry-meta">
						<a class="book-entry-edit-link" href="/update-book/?id=<?php echo $book_entry->id; ?>">
							<div class="book-entry-thumb">
								<img class="book_entry_img" src="<?php echo $book_entry->small_thumbnail_url;?>">
							</div>
							<div class="book-entry-details">
								<div id="book_entry_title" class="book-entry-title"><?php echo $book_entry_title;?></div>
								<div id="book_entry_author" class="book-entry-author">by <?php echo $book_entry->author;?></div>
								<!-- <div id="book_entry_pub_date" class="book-entry-publication-date">published <?php // echo $book_entry_published_date;?></div> -->
								<?php if ($book_entry->description != '') { ?>
									<div id="book_entry_description" class="book_entry_description"><?php echo $book_description_excerpt; ?></div>
								<?php } ?>
								<?php
									if ($book_entry->tags != NULL && $book_entry->tags != '' && !empty($book_entry->tags)) { 
										// $tagsArray = json_decode(stripslashes($book_entry->tags), true);
										$tagsArray = explode(',', $book_entry->tags);
										echo '<ul class="bookshelf-book-tags">';
										foreach ($tagsArray as $tagID) {
											$tag = get_tag($tagID);
											echo '<li class="' . $tag->slug . ' active" data-id="' . $tag->term_id . '">' . $tag->name . '</li>';
										}
									}	
								?>
							</div>
							<?php if ($bookshelf_category == 'notes') { ?>
								<div id="book_entry_notes" class="book-entry-notes <?php if ($notes_word_count > 40) { echo 'overage'; } ?>">
									<a class="popup-trigger" href="#popup_notes_<?php echo $book_entry->id; ?>">
										<strong>My Notes: </strong><?php echo $book_entry_notes; ?>	
									</a>
								</div>	
								<div id="popup_notes_<?php echo $book_entry->id; ?>" class="popup-wrapper popup-notes">
									<div class="popup popup-large">
										<a class="close-button popup-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
										<p><strong>My Notes: </strong><?php echo $book_entry_notes;?></p>
										<p class="margin-bottom-0"><a href="/update-book/?id=<?php echo $book_entry->id; ?>" class="popup-link">Edit</a></p>
									</div>
								</div>
							<?php } ?>
						</a>
					</div>
					
				</div>
		<?php
				}
			}
		?>

	</section>
	<?php // get_template_part('templates/content', 'side-nav'); ?>

</main>
</div> <!-- close wrapper -->

<?php get_template_part('templates/content', 'footer-nav'); ?>

<div class="loading-animation">
	<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><circle cx="4" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce0" attributeName="cy" begin="0;svgSpinners3DotsBounce1.end+0.25s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="12" cy="12" r="3" fill="currentColor"><animate attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.1s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="20" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce1" attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.2s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle></svg>
</div>

<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function () {
		let bookFilterForm = document.getElementById('book_filter_form');
		let bookSortSelect = document.getElementById('bookshelf-sort-select');
		let bookFilterSelect = document.getElementById('bookshelf_tag_filters');
		let bookshelf = document.getElementById('bookshelf');
		let loadingAnimation = document.querySelector('.loading-animation');
		let bookSortingButton = document.querySelector('.sort-button');
		let bookshelfSortingInput = document.querySelector('#book_filter_form input[name="bookshelf_sorting"]');
		let searchEl = jQuery('#book_title');
		let resultsEl = jQuery('#search_results');
		let searchClose = document.querySelector('.search-close');
		
		// let filterButton = document.querySelector('#filter_button');
		// let filterWrapper = document.querySelector('#filter_wrapper');
		// let filterItems = document.querySelectorAll('#filter_wrapper input');
		
		/* Checkboxes */
		/*
		filterButton.addEventListener("click", function(event) {
			event.preventDefault();
			
			if (filterWrapper.classList.contains('active')) {
				filterWrapper.classList.remove('active');
			} else {
				filterWrapper.classList.add('active');
			}
		});
		
		filterItems.forEach(function (filterItem, index) {
			filterItem.addEventListener("change", function bookFilter() {
				loadingAnimation.classList.add('active');
				var formData = new FormData(bookFilterForm);
				//formData.append("bookshelf_sorting", bookshelfSortingInput.value);
				formData.append("action", "book_list");
				
				fetch([bookwormAjax.url], {
					method: "POST",
					credentials: "same-origin",
					body: formData
				})
				.then((response) => response.text())
				.then((text) => {
					//console.log(text);
					bookshelf.innerHTML = text;
				})
				.then((data) => {
					if (data) {
						//console.log(data);
						bookshelf.innerHTML = data;
					}
					loadingAnimation.classList.remove('active');
					window.scrollTo({top: 0, behavior: 'smooth'});
				})
				.catch((error) => {
					console.log("Error: ");
					console.error(error);
				});
			})	
		})
		*/
		
		if (bookFilterSelect) {
			bookFilterSelect.addEventListener("change", function bookFilter() {
				loadingAnimation.classList.add('active');
				var formData = new FormData(bookFilterForm);
				//formData.append("bookshelf_sorting", bookshelfSortingInput.value);
				formData.append("action", "book_list");
				
				fetch([bookwormAjax.url], {
					method: "POST",
					credentials: "same-origin",
					body: formData
				})
				.then((response) => response.text())
				.then((text) => {
					//console.log(text);
					bookshelf.innerHTML = text;
				})
				.then((data) => {
					if (data) {
						//console.log(data);
						bookshelf.innerHTML = data;
					}
					loadingAnimation.classList.remove('active');
					window.scrollTo({top: 0, behavior: 'smooth'});
				})
				.catch((error) => {
					console.log("Error: ");
					console.error(error);
				});
			})
		}

		bookSortSelect.addEventListener('change', function(event) {
			
			loadingAnimation.classList.add('active');
			var formData = new FormData(bookFilterForm);
			
			formData.append("action", "book_list");
			
			fetch([bookwormAjax.url], {
				method: "POST",
				credentials: "same-origin",
				body: formData
			})
			.then((response) => response.text())
			.then((text) => {
				//console.log(text);
				bookshelf.innerHTML = text;
			})
			.then((data) => {
				if (data) {
					//console.log(data);
					bookshelf.innerHTML = data;
				}
				loadingAnimation.classList.remove('active');
				window.scrollTo({top: 0, behavior: 'smooth'});
			})
			.catch((error) => {
				console.log("Error: ");
				console.error(error);
			});
		})

		bookSortingButton.addEventListener('click', function(e){
			//e.preventDefault();
			if (bookshelfSortingInput.value == 'ASC') {
				bookshelfSortingInput.value = 'DESC';
			} else {
				bookshelfSortingInput.value = 'ASC';
			}
			loadingAnimation.classList.add('active');
			var formData = new FormData(bookFilterForm);
			//formData.append("bookshelf_sorting", bookshelfSortingInput.value);
			formData.append("action", "book_list");
			
			fetch([bookwormAjax.url], {
				method: "POST",
				credentials: "same-origin",
				body: formData
			})
			.then((response) => response.text())
			.then((text) => {
				//console.log(text);
				bookshelf.innerHTML = text;
			})
			.then((data) => {
				if (data) {
					//console.log(data);
					bookshelf.innerHTML = data;
				}
				loadingAnimation.classList.remove('active');
				window.scrollTo({top: 0, behavior: 'smooth'});
			})
			.catch((error) => {
				console.log("Error: ");
				console.error(error);
			});
		})
		
		searchEl.on('input', function () {
			jQuery('#search_results').addClass('active');
			jQuery('.close-button').addClass('active');
			jQuery.post(bookwormAjax.url, {
				action: 'book_search',
				nonce: bookwormAjax.bookworm_thinking_nonce,
				data: {
					s: jQuery(this).val(),
					id: jQuery('#user_id_from').val()
				}
			}).done(function(data) {
				resultsEl.html(data);
				
				// Loop through search result book links
				var bookLinks = document.querySelectorAll('.book-search-entry-link');
				
				bookLinks.forEach(function (bookLink, index) {
					var bwId = bookLink.dataset.bwid;
					
					bookLink.href = '/update-book/?id=' + bwId;
				});
			})
		})
		
		if (searchClose) {
			searchClose.addEventListener('click', function(e){
				e.preventDefault();
				searchClose.classList.remove('active');
				jQuery('#search_results').removeClass('active');
			})
		}
		
		let popupClose = document.querySelectorAll('.popup-close');
		let popupNotesTriggers = document.querySelectorAll('.popup-trigger');
		
		popupNotesTriggers.forEach((popupNotesTrigger) => {
			popupNotesTrigger.addEventListener('click', function(e) {
				e.preventDefault();
				
				let popupId = e.target.getAttribute('href');
				console.log('click ' + popupId);
				document.querySelector(popupId).classList.add('active');
			})
		})

		popupClose.forEach((popupCloser) => {
			popupCloser.addEventListener('click', function(e){
				e.preventDefault();
				/*popupCloser.classList.remove('active');*/
				e.target.closest('.popup-wrapper').classList.remove('active');
			});
		})

	}, false); // close document loaded
</script>

<?php get_footer(); ?>