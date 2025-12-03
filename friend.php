
<?php
	/* Template Name: Friend Page Template */
	if (!is_user_logged_in()) {
		//auth_redirect();
		$url = get_site_url() . '/log-in/';
		wp_safe_redirect( $url );
	}
	
	$wp_current_user_id = get_current_user_id();
	$friend_id = $_GET['id'];
	
	if ($friend_id == $wp_current_user_id) {
		$url = get_site_url() . '/account/';
		wp_safe_redirect( $url );
	}
	
	get_header();
	get_template_part('templates/content', 'inner-header');
	
	global $post;
	
	$friend_query = "SELECT * FROM {$wpdb->prefix}bookworm_friends WHERE user_id = '$wp_current_user_id' AND friend_id = '$friend_id' AND status = 'friends'";
	$are_friends = $wpdb->get_results($friend_query);
	if ($are_friends) {
	
		$friend = get_user_by('id', $friend_id);
		$friend_avatar = get_avatar( $friend_id, 96 );
?>

<main id="main" role="main" class="community">

	<div class="bookshelf-heading-filter-wrapper flex align-items-center" style="justify-content: flex-start; margin-bottom: 20px;">
		<div class="friend-avatar">
			<div class="useravatar"><?php echo $friend_avatar; ?></div>
		</div>
		<h3 style="margin: 0 0 0 10px;"><?php echo $friend->user_login; ?>'s Profile</h3>
	</div>
	
	<a id="popupRec_trigger" class="button button-primary">Recommend a Book to <?php echo $friend->user_login; ?></a>
	
	<div class="recommended-books">
		<?php
			// Recommendations FROM
			$recommendation_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bookworm_recommendations WHERE recipient_id = '$wp_current_user_id' AND recommender_id = '$friend_id'");
			if (!empty($recommendation_entries)) {
		?>
				<h4>Recommendations from <?php echo $friend->user_login; ?>:</h4>
				<div class="flex wrap gap-20">
		<?php
				foreach ($recommendation_entries as $recommendation_entry) {
					$google_books_ID = $recommendation_entry->google_books_ID;
					$book_title = stripslashes($recommendation_entry->title);
					// Book Deets
		?>
					<div class="book-entry">
						<div class="book-entry-meta flex">
							<div class="book-entry-thumb">
								<img class="book_entry_img" src="<?php echo $recommendation_entry->small_thumbnail_url;?>">
							</div>
							<div class="book-entry-details">
								<div id="book_entry_title" class="book-entry-title"><?php echo $book_title; ?></div>
								<div id="book_entry_author" class="book-entry-author">by <?php echo $recommendation_entry->author;?></div>
							</div>
						</div>
					</div> <!-- close book entry -->
		<?php 
				} // close foreach	
				echo "</div>";
			} // close if recommendations
		?>
		
		<?php
			// Recommendations TO
			$recommendation_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bookworm_recommendations WHERE recipient_id = '$friend_id' AND recommender_id = '$wp_current_user_id'");
			if (!empty($recommendation_entries)) {
		?>
				<h4>Recommendations to <?php echo $friend->user_login; ?>:</h4>
				<div class="flex wrap gap-20">
		<?php
				foreach ($recommendation_entries as $recommendation_entry) {
					$google_books_ID = $recommendation_entry->google_books_ID;
					// Book Deets
		?>
					<div class="book-entry">
						<div class="book-entry-meta flex">
							<div class="book-entry-thumb">
								<img class="book_entry_img" src="<?php echo $recommendation_entry->small_thumbnail_url;?>">
							</div>
							<div class="book-entry-details">
								<div id="book_entry_title" class="book-entry-title"><?php echo stripslashes($recommendation_entry->title);?></div>
								<div id="book_entry_author" class="book-entry-author">by <?php echo $recommendation_entry->author;?></div>
							</div>
						</div>
					</div> <!-- close book entry -->
		<?php 
				} // close foreach	
				echo "</div>";
			} // close if recommendations
		?>
	</div>
	
	<section class="bookshelf" id="bookshelf">
		<?php
			$currently_reading = new BookshelfCategory('Currently Reading', '', "SELECT * FROM {$wpdb->prefix}bookworm_books WHERE (user_id_shelf = '$friend_id' AND date_started IS NOT NULL AND date_started != '0000-00-00' AND date_started != '1970-01-01') AND (date_finished IS NULL OR date_finished = '0000-00-00' OR date_finished = '1970-01-01') ORDER BY id DESC", $friend->user_login . " claims they're not reading anything? Maybe they're just hiding it on BookWorm. Recommend a title.", '', '');
			$recently_finished = new BookshelfCategory('Recently Finished', '', "SELECT * FROM {$wpdb->prefix}bookworm_books WHERE (user_id_shelf = '$friend_id' AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01') ORDER BY date_finished DESC", "Encourage " . $friend->user_login . " to finish a book, any book.", '', '');
			
			$bookshelf_categories = array($currently_reading, $recently_finished);
			$books_null = 0;
			
			foreach ($bookshelf_categories as $bookshelf_category) {
				
				$book_entries = $wpdb->get_results($bookshelf_category->query);
				
				// Friend Deets
			?>
					<div class="book-entries">
			<?php 
					if (!empty($book_entries)) { ?>
					    <h2><?php echo $friend->user_login; ?>'s <?php echo $bookshelf_category->title; ?></h2>
			<?php
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
							
							if ($book_entry->shared_on_shelf == 'shared') {
		?>
						        <div class="book-entry flex">
									<div class="book-entry-meta">
										<div class="flex">
									        <div class="book-entry-thumb">
								                <img class="book_entry_img" src="<?php echo $book_entry->small_thumbnail_url; ?>">
								            </div>
											<div class="book-entry-details">
										        <div class="book-entry-title"><?php echo $book_entry_title; ?></div>
										        <div class="book-entry-author"><?php echo $book_entry->author; ?></div>
										        <?php if ($book_entry->description != '') { ?>
													<div class="book-entry-description-wrapper active">
														<div id="book_entry_description" class="book-entry-notes overage overage-short">
															<a id="popup_description_trigger" class="popup-trigger" href="#book_entry_description_<?php echo $book_entry->id; ?>">
																<strong>Google Books Description:</strong> <?php echo $book_entry_description; ?>
															</a>
														</div>
														<div id="book_entry_description_<?php echo $book_entry->id; ?>" class="popup-wrapper popup-description">
															<div class="popup popup-large">
																<a class="close-button popup-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
																<p><strong>Google Books Description:</strong> <?php echo $book_entry_description; ?></p>
															</div>
														</div>
													</div>
												<?php } ?>
												<p><a href="/add-book/?id=<?php echo $book_entry->google_books_ID; ?>&fromFriend=true">Add to Bookshelf</a></p>
							    			</div> <?php // close book entry details ?>
										</div>
						    			<?php if ($book_entry_notes != '' && $book_entry_notes != 'Enter your notes for this book.') { ?>
							    			<div id="book_entry_notes" class="book-entry-notes <?php if ($notes_word_count > 40) { echo 'overage'; } ?>">
												<a class="popup-trigger" href="#popup_notes_<?php echo $book_entry->id; ?>">
													<strong><?php echo $friend->user_login; ?>'s Notes: </strong><?php echo $book_entry_notes; ?>	
												</a>
											</div>	
											<div id="popup_notes_<?php echo $book_entry->id; ?>" class="popup-wrapper popup-notes">
												<div class="popup popup-large">
													<a class="close-button popup-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
													<p><strong><?php echo $friend->user_login; ?>'s Notes: </strong><?php echo $book_entry_notes;?></p>
												</div>
											</div>
										<?php } ?>
									</div>
						    	</div> <?php // close book entry ?>
		<?php 
							} // end if shared
						} // end for each	
					} else { 
						$books_null = $books_null++;
					} // end if current books 
					
					if ($books_null == 2) {
						echo '<div class="user-message"><p>Your friend is not currently reading anything. Or at least not tracking it on BookWorm...</p></div>';
					}
	
				echo "</div>"; // close book entries
				} // end foreach
		} else { // not friends ?>
			<main id="main" role="main" class="community">
				<section class="bookshelf" id="bookshelf">
					<h3>Become Friends First</h3>
					<p>You can only view the BookWorm profiles of your friends.</p>
				</section>
			</main>
		<?php } ?>
		
		<form action="#" method="post" id="remove_friends_form" name="remove_friends_form">
			<div class="delete-button-wrapper">
				<a href="#" class="button-alt remove-friend" id="remove-friend">Remove Friend</a>
			</div>
			<input id="remove_friend_id" type="hidden" name="remove_friend_id" value="<?php echo $friend_id; ?>">
			<input id="current_user_id" type="hidden" name="current_user_id" value="<?php echo $wp_current_user_id; ?>">
		</form>
		
	</section>
	<?php //get_template_part('templates/content', 'side-nav'); ?>

</main>

<?php get_template_part('templates/content', 'footer-nav'); ?>

<div id="popup_recommendation" class="popup-wrapper">
	<div class="popup popup-xlarge">
		<a class="close-button popup-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
		<div class="popup-heading-wrapper">
			<div class="icon-background">
				<?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/icon_bookworm-logo.svg' ); ?>
			</div>
			<h4>Recommend a Book</h4>
		</div>
		
		<form id="recommend_book_form" action="/" method="POST">
			<div class="form-section">
				<label for="title">Search for a Book from your Bookshelf:</label>
				<div class="book-title-search">
					<input id="book_title" name="title" type="text" value="">
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
			</div>
			
			<textarea id="recommendation_note" name="recommendation_note" aria-label="Recommentation note">Enter a note about your book recommendation.</textarea>
			
			<!-- Hidden Fields -->
			<div id="hidden_form_inputs"></div>
			<input type="hidden" name="user_id_from" id="user_id_from" value="<?php echo get_current_user_id(); ?>">
			<input type="hidden" name="user_id_to" id="user_id_to" value="<?php echo $friend_id; ?>">
			<div class="submit">
				<input type="submit" name="recommend_book_submit" id="recommend_book_submit" class="button button-primary" value="Recommend Book">
			</div>
	
		</form>
	</div>
</div>

<div id="popup_next_step" class="popup-wrapper">
	<div class="popup popup-large">
		<a class="close-button popup-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
		<div class="popup-heading-wrapper">
			<div class="icon-background">
				<?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/icon_bookworm-logo.svg' ); ?>
			</div>
			<h4>Recommendation Made</h4>
		</div>
		<p><a id="close-popup" href="#" class="popup-link popup-close">Recommend Another?</a></p>
		<p><a href="/bookshelf/" class="popup-link">Go to Bookshelf</a></p>
	</div>
</div>

<div id="popup_remove_friend" class="popup-wrapper">
	<div class="popup popup-large">
		<a class="close-button popup-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
		<h4>Are you sure you want to remove this friend?</h4>
		<p>You will no longer be connected on BookWorm and recommendations from this friend for books that are not on your bookshelf will be removed.</p>
		<p><a id="remove_friend_confirm" href="#" class="popup-link">Remove friend</a></p>
	</div>
</div>

<div class="loading-animation">
	<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><circle cx="4" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce0" attributeName="cy" begin="0;svgSpinners3DotsBounce1.end+0.25s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="12" cy="12" r="3" fill="currentColor"><animate attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.1s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="20" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce1" attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.2s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle></svg>
</div>

<script type="text/javascript">
		
	document.addEventListener('DOMContentLoaded', function () {

		let searchEl = jQuery('#book_title');
		let resultsEl = jQuery('#search_results');
		let hiddenFieldsEl = jQuery('#hidden_form_inputs');
		let recForm = document.getElementById('recommend_book_form');
		let searchClose = document.querySelector('.search-close');
		let loadingAnimation = document.querySelector('.loading-animation');
		let validationError = document.getElementById('validation_error');
		let popupRecTrigger = document.querySelector('#popupRec_trigger');
		let popupRec = document.querySelector('#popup_recommendation');
		let popupNextStep = document.querySelector('#popup_next_step');
		let popupClose = document.querySelectorAll('.popup-close');
		
		popupRecTrigger.addEventListener("click", function(event) {
			event.preventDefault();
			
			popupRec.classList.add('active');
		});

		searchEl.on('input', function () {
			jQuery('#search_results').addClass('active');
			jQuery('.close-button').addClass('active');
			if (validationError.classList.contains('active')) {
				validationError.classList.remove('active');
			}
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
					bookLink.addEventListener('click', function (event) {
					    event.preventDefault();
					    
					    var googleBooksId = this.id;
						var bookTitle = this.dataset.title;
						var bookAuthor = this.dataset.author;
						var bookImg = this.dataset.img;
						var bookPubDate = this.dataset.disppubdate;
						var bookDescription = this.dataset.description;
						var bookExcerpt = this.querySelector('.book-search-description').innerHTML;
						
						searchEl.val(bookTitle);
						document.querySelector('.book-display').classList.add('active');
						document.getElementById('search_results').classList.remove('active');
						searchClose.classList.remove('active');
						
						document.querySelector('#book_entry_title').innerHTML = bookTitle;
						document.querySelector('#book_entry_author').innerHTML = 'by ' + bookAuthor;
						//document.querySelector('#book_entry_pub_date').innerHTML = 'published ' + bookPubDate;
						document.querySelector('#popup_recommendation .book_entry_img').src = bookImg;
						document.querySelector('#book_entry_description').innerHTML = bookExcerpt;
						
						jQuery.post(bookwormAjax.url, {
							action: 'google_books_api_volume',
							nonce: bookwormAjax.bookworm_thinking_nonce,
							data: {
								id: googleBooksId
							}
						}).done(function(data) {
							hiddenFieldsEl.html(data)
						})
					});
				});
			})
		})
		
		// Remove Friends
		let removeButton = document.querySelector('.remove-friend');
		let popup_remove_friend = document.querySelector('#popup_remove_friend');
		let remFriendForm = document.querySelector('#remove_friends_form'); 
		let remove_friend_confirm = document.getElementById('remove_friend_confirm');
		
		removeButton.addEventListener('click', function(event) {
			event.preventDefault();

			window.scrollTo({top: 0, behavior: 'smooth'});
			popup_remove_friend.classList.add('active');

			remove_friend_confirm.addEventListener('click', function(event) {
				event.preventDefault();
				loadingAnimation.classList.add('active');

				formData = new FormData(remFriendForm);
				formData.append("action", "remove_friend");

				fetch([bookwormAjax.url], { // use your ajax url
			    method: "POST",
			    credentials: "same-origin",
			    body: formData // put your data into fetch body
			  })
			    .then((response) => response.text())
				.then((text) => {
					console.log(text);
				})
			    .then((data) => {
			      if (data) {
			        console.log(data);
			      }
				  loadingAnimation.classList.remove('active');
				  window.location.href = "/community/";
			    })
			    .catch((error) => {
			      console.log("I never see these errors. Anyway, removing friend didn't work.");
			      console.error(error);
			    });
			});
		});
		
		// Popups
		let popupNotesTriggers = document.querySelectorAll('.popup-trigger');
		
		popupNotesTriggers.forEach((popupNotesTrigger) => {
			popupNotesTrigger.addEventListener('click', function(e) {
				e.preventDefault();
				
				let popupId = e.target.getAttribute('href');
				console.log('click ' + popupId);
				document.querySelector(popupId).classList.add('active');
			})
		})

		searchClose.addEventListener('click', function(e){
			e.preventDefault();
			searchClose.classList.remove('active');
			jQuery('#search_results').removeClass('active');
		})
		
		popupClose.forEach((popupCloser) => {
			popupCloser.addEventListener('click', function(e){
				e.preventDefault();
				
				/*popupCloser.classList.remove('active');*/
				let popups = document.querySelectorAll('.popup-wrapper');
				popups.forEach((popup, index) => {
					popup.classList.remove('active');
				});
				
			});
		})
		
		// Recommendation Form
		recForm.addEventListener('submit', function(event) {
			event.preventDefault();
			
			if (document.getElementById('book_title').value !== '') {
				loadingAnimation.classList.add('active');
				var formData = new FormData(this);
				
				formData.append("action", "recommend_book");
				fetch([bookwormAjax.url], { // use your ajax url
					method: "POST",
					credentials: "same-origin",
					body: formData // put your data into fetch body
				})
					.then((response) => response.text())
					.then((text) => {
						console.log(text);
					})
					.then((data) => {
						if (data) {
							console.log(data);
						}
						loadingAnimation.classList.remove('active');
						recForm.reset();
						document.querySelector('.book-display').classList.remove('active');
						window.scrollTo({top: 0, behavior: 'smooth'});
						popupRec.classList.remove('active');
						popupNextStep.classList.add('active');
						/*popupClose.classList.add('active');*/
					})
					.catch((error) => {
						console.log("[ OPS!! add_book ]");
						console.error(error);
					});
			} else {
				validationError.classList.add('active');
				let yOffset = -200; 
				let element = document.getElementById('book_title');
				let y = element.getBoundingClientRect().top + window.pageYOffset + yOffset;
				window.scrollTo({top: y, behavior: 'smooth'});
			}
		})

	}, false);
</script>

<?php get_footer(); ?>	