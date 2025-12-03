<?php
	/* Template Name: Edit Book Form Page Template */
	if (!is_user_logged_in()) {
		//auth_redirect();
		$url = get_site_url() . '/log-in/';
		wp_safe_redirect( $url );
	}
	global $wpdb;
	get_header();
	get_template_part('templates/content', 'inner-header');
?>

<main id="main" role="main" class="book-search">
	<h3>Update Book</h3>
	<section class="book-form">
		<?php 
			$wp_current_user_id = get_current_user_id();
			if (!empty($_GET)) {
				$book_id = $_GET['id'];
				$book_entry = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bookworm_books WHERE id = '$book_id' AND user_id_shelf = '$wp_current_user_id'");
			}
			if (empty($book_entry) || empty($_GET)) {
		?>
			<div class="user-message"><p>No book was selected to update. Want to go to your bookshelf?</p></div>
				<div class="nav-icon flex align-items-center justify-center">
					<a class="nav-icon-anchor" href="/bookshelf/">
						<?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/iconb_bookshelf.svg'); ?>
					</a>
				</div>
		<?php 
			} else {
				
				$published_date = strtotime($book_entry[0]->published_date);
				$published_date = date('j F Y', $published_date);
				$book_entry_title = stripslashes($book_entry[0]->title);
				if ($book_entry[0]->description != '') {
					$book_entry_description = stripslashes($book_entry[0]->description);
					$description_word_count = str_word_count($book_entry_description);
				}
				if ($book_entry[0]->notes != '' && $book_entry[0]->notes != 'Enter your notes for this book.') {
					$book_entry_notes = stripslashes($book_entry[0]->notes);
					$notes_word_count = str_word_count($book_entry_notes);
				}
		?>
		<form id="book_form" action="/bookshelf/" method="POST">

			<div class="form-section">
				<div class="book-display active">
					<div class="book-entry-thumb">
						<img class="book_entry_img" src="<?php echo $book_entry[0]->small_thumbnail_url;?>">
					</div>
					<div class="book-entry-details">
						<div id="book_entry_title" class="book-entry-title"><?php echo $book_entry_title;?></div>
						<div id="book_entry_author" class="book-entry-author">by <?php echo $book_entry[0]->author;?></div>
						<!-- <div id="book_entry_pub_date" class="book-entry-publication-date">published <?php // echo $published_date?></div> -->
						
						<?php if ($book_entry[0]->description != '') { ?>
							<div id="book_entry_description" class="book-entry-notes <?php if ($description_word_count > 40) { echo 'overage'; } ?>">
								<a class="popup-trigger" href="#popup_description_<?php echo $book_entry[0]->id; ?>">
									<strong>Google Books Description:</strong> <?php echo $book_entry_description; ?>	
								</a>
							</div>
							<div id="popup_description_<?php echo $book_entry[0]->id; ?>" class="popup-wrapper popup-description">
								<div class="popup popup-large">
									<a class="close-button popup-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
									<p><strong>Google Books Description:</strong> <?php echo $book_entry_description; ?></p>
								</div>
							</div>	
						<?php } ?>
				
						<!--<div class="form-entry form-entry-small inline-wrapper flex">
							<div class="radio-choice flex align-items-center">
								<input type="radio" id="fiction" name="fiction_or_non" value="fiction"<?php if ($book_entry[0]->fiction_or_non == 'fiction') { ?> checked<?php } ?>><label for="fiction">Fiction</label>
							</div>
							<div class="radio-choice flex">
								<input type="radio" id="nonfiction" name="fiction_or_non" value="nonfiction"<?php if ($book_entry[0]->fiction_or_non == 'nonfiction') { ?> checked<?php } ?>><label for="nonfiction">Nonfiction</label>
							</div>
						</div>-->
					</div>
				</div>
				<div class="form-entry form-entry-small flex align-items-center">
					<input type="checkbox" id="shared_on_shelf" name="shared_on_shelf" <?php if ($book_entry[0]->shared_on_shelf != 'shared') { ?> checked<?php } ?>>
					<label for="shared_on_shelf">Invisible to Friends?</label>
				</div>
				
				<?php ?>
				<div class="tags-wrapper">
					<?php 
						if ($book_entry[0]->tags != NULL && $book_entry[0]->tags != '' && !empty($book_entry[0]->tags)) { 
							$tagsArray = explode(',', $book_entry[0]->tags);
							echo '<ul>';
							foreach ($tagsArray as $tagID) {
								$tag = get_tag($tagID);
								echo '<li class="' . $tag->slug . ' active" data-id="' . $tag->term_id . '">' . $tag->name . '</li>';
							}
							$tags = get_tags(array(
							  'hide_empty' => false
							));
							foreach ($tags as $tag) {
								if (!in_array($tag->term_id, $tagsArray)) {
				                    echo '<li class="' . $tag->slug . '" data-id="' . $tag->term_id . '">' . $tag->name . '</li>';
				                }
				            }
							echo '</ul>';
						} else {
							$tags = get_tags(array(
							  'hide_empty' => false
							));
							echo '<ul>';
							foreach ($tags as $tag) {
							  echo '<li class="' . $tag->slug . '" data-id="' . $tag->term_id . '">' . $tag->name . '</li>';
							}
							echo '</ul>';
						}
					?>
				</div>
				
				<div class="submit">
					<a id="alt-submit" href="#" class="button button-primary">Update Book</a>
				</div>
				<a href="#" class="button-alt delete_button" id="delete_button">Delete Book</a>
				
				<?php if ($book_entry[0]->notes != '' && $book_entry[0]->notes != 'Enter your notes for this book.') { ?>
					<div id="book_entry_notes" class="book-entry-notes <?php if ($notes_word_count > 40) { echo 'overage'; } ?>">
						<a class="popup-trigger" href="#popup_notes_<?php echo $book_entry[0]->id; ?>">
							<strong>My Notes: </strong><?php echo $book_entry_notes; ?>	
						</a>
					</div>
					<div id="popup_notes_<?php echo $book_entry[0]->id; ?>" class="popup-wrapper popup-notes">
						<div class="popup popup-large">
							<a class="close-button popup-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
							<p><strong>My Notes: </strong><?php echo $book_entry_notes;?></p>
						</div>
					</div>	
				<?php } ?>
			</div>

			<div class="form-section">
				<div class="icon-wrapper icon-medium full-width bg-lt-yellow">
					<div class="icon-background">
						<?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/iconb_calendar.svg'); ?>
					</div>
					<h4>Reading Dates</h4>
				</div>
				<label for="date_started">Date Started</label>
				<input id="start_reading_date" class="calendar-ui" name="date_started" type="text" placeholder="Choose date" value="<?php if ($book_entry[0]->date_started != NULL && $book_entry[0]->date_started != '0000-00-00' && $book_entry[0]->date_started != '1970-01-01') {
					$date_started = strtotime($book_entry[0]->date_started);
					$date_started = date('m/d/Y', $date_started);
					echo $date_started;
				} ?>">
				<label for="date_finished">Date Finished</label>
				<input id="finished_reading_date" class="calendar-ui" name="date_finished" type="text" placeholder="Choose date" value="<?php if ($book_entry[0]->date_finished != NULL && $book_entry[0]->date_finished != '0000-00-00' && $book_entry[0]->date_finished != '1970-01-01') {
					$date_finished = strtotime($book_entry[0]->date_finished);
					$date_finished = date('m/d/Y', $date_finished);
					echo $date_finished;
				} ?>">
			</div>

			<div class="form-section">
				<!-- <div class="flex nowrap justify-space-between align-items-center"> -->
					<div class="icon-wrapper icon-medium full-width bg-lt-blue">
						<div class="icon-background">
							<?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/iconb_notes.svg'); ?>
						</div>
						<h4>My Notes</h4>
					</div>
				<!-- </div> -->
				<textarea id="book_notes" name="notes" aria-label="Book notes"><?php echo stripslashes($book_entry[0]->notes); ?></textarea>

				<div class="range-inputs">
					<div class="form-entry">
						<label for="book_mood">Mood</label>
						<input class="input-range" type="range" name="rating_mood" id="book_mood" value="<?php echo $book_entry[0]->rating_mood; ?>" min="1" max="5" step="1" />
						<div class="range flex nowrap justify-space-between align-items-center">
							<span class="range-left">Funny</span>
							<span class="range-right">Tragic</span>
						</div>
					</div>

					<div class="form-entry">
						<label for="book_language">Language</label>
						<input class="input-range" type="range" name="rating_language" id="book_language" value="<?php echo $book_entry[0]->rating_language; ?>" min="1" max="5" step="1" />
						<div class="range flex nowrap justify-space-between align-items-center">
							<span class="range-left">Dense</span>
							<span class="range-right">Accessible</span>
						</div>
					</div>

					<div class="form-entry">
						<label for="book_romance">Romance</label>
						<input class="input-range" type="range" name="rating_romance" id="book_romance" value="<?php echo $book_entry[0]->rating_romance; ?>" min="1" max="5" step="1" />
						<div class="range flex nowrap justify-space-between align-items-center">
							<span class="range-left">Not at All</span>
							<span class="range-right">Chock Full</span>
						</div>
					</div>

					<div class="form-entry">
						<label for="book_suspension_disbelief">Suspension of Disbelief</label>
						<input class="input-range" type="range" name="rating_suspension_disbelief" id="book_suspension_disbelief" value="<?php echo $book_entry[0]->rating_suspension_disbelief; ?>" min="1" max="5" step="1" />
						<div class="range flex nowrap justify-space-between align-items-center">
							<span class="range-left">Entirely Speculative</span>
							<span class="range-right">Entirely Realist</span>
						</div>
					</div>
				</div>
			</div>

			<div class="form-section">
				<div class="icon-wrapper icon-medium full-width bg-lt-purple">
					<div class="icon-background">
						<?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/iconb_recommend.svg'); ?>
					</div>
					<h4>Recommendations</h4>
				</div>
				
				<?php
					// Recommended By Manually Enter
					$friend_query = "SELECT friend_id FROM {$wpdb->prefix}bookworm_friends WHERE user_id = '$wp_current_user_id' AND status = 'friends'";
					$friends = $wpdb->get_results($friend_query);
					if ($friends) {
				?>
						<label for="recommended_by">Recommended by BookWorm Friend:</label>
						<select class="recommend_in_bookworm" id="recommended_by" name="user_id_from">
							<option value="#">Select Friend</option>
					<?php
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
								}
					?>
								<option value="<?php echo $friend_id; ?>"><?php echo $user->user_login; ?></option>
					<?php } ?>
							<option value="outside">Outside of BookWorm</option>
							</select>
					<?php } ?>
					<div class="bookworm-recommended<?php if ( (isset($book_entry[0]->recommended_to) && $book_entry[0]->recommended_to != NULL && $book_entry[0]->recommended_to != '') || empty($friends) ) { echo ' active'; } ?>">
						<label for="recommended_by_text">Recommended By (Outside of BookWorm):</label>
						<input id="recommended_by_text" name="recommended_by" type="text" value="<?php echo $book_entry[0]->recommended_by; ?>">
					</div>
					
					<!-- Recommended By List -->
					<div id="recommended_by_wrapper">
				<?php
					$google_books_ID = $book_entry[0]->google_books_ID;
					$recommendation_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bookworm_recommendations WHERE recipient_id = '$wp_current_user_id ' AND google_books_ID = '$google_books_ID'");
					if (!empty($recommendation_entries)) {
						$friendArray = array();
				?>
						<label for="">Recommended By:</label>
						<div class="flex wrap gap-20">
				<?php
						foreach ($recommendation_entries as $recommendation_entry) {
							$friend_id = $recommendation_entry->recommender_id;
							
							if (!in_array($friend_id, $friendArray)) {
								array_push($friendArray, $friend_id);
								$friend_user = get_user($recommendation_entry->recommender_id);
								$friend_avatar = get_avatar( $friend_id, 96 );
							
							// Friend Deets
				?>
							<div class="friend-entry">
								<a style="display:block;" href="/friend?id=<?php echo $friend_id; ?>">
									<div class="friend-avatar">
										<div class="useravatar"><?php echo $friend_avatar; ?></div>
									</div>
									<div class="friend-username"><?php echo $friend_user->user_login; ?></div>
								</a>
							</div> <!-- close friend entry -->
				<?php 
							} // close if in friend array
						} // close foreach	
						echo "</div>";
					} // close if recommendations
				?>
						</div> <!-- close recommended by wrapper -->
				<?php
					
						if ($friends) {
					?>
							<label for="recommend_to">Recommend to BookWorm Friend:</label>
							<select class="recommend_in_bookworm" id="recommend_to" name="user_id_to">
								<option value="#">Select Friend</option>
					<?php
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
								}
					?>
								<option value="<?php echo $friend_id; ?>"><?php echo $user->user_login; ?></option>
					<?php } ?>
							<option value="outside">Outside of BookWorm</option>
							</select>
							<textarea id="recommendation_note" name="recommendation_note" aria-label="Recommentation note">Enter a note about your book recommendation.</textarea>
					<?php } // close if Friends ?>
					<div class="bookworm-recommended<?php if ( (isset($book_entry[0]->recommended_to) && $book_entry[0]->recommended_to != NULL && $book_entry[0]->recommended_to != '') || empty($friends) ) { echo ' active'; } ?>">
						<label for="recommended_to_text">Recommend To (Outside of BookWorm):</label>
						<input id="recommended_to_text" name="recommended_to" type="text" value="<?php echo $book_entry[0]->recommended_to; ?>">	
					</div>
					
					<!-- Recommended To List -->
					<div id="recommended_to_wrapper">
				<?php

					$recommendation_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bookworm_recommendations WHERE recommender_id = '$wp_current_user_id ' AND google_books_ID = '$google_books_ID'");
					if (!empty($recommendation_entries)) {
				?>
						<label for="">Recommended To:</label>
						<div class="flex wrap gap-20">
				<?php
						foreach ($recommendation_entries as $recommendation_entry) {
							$friend_id = $recommendation_entry->recipient_id;
							$friend_user = get_user($recommendation_entry->recipient_id);
							$friend_avatar = get_avatar( $friend_id, 96 );
							
							// Friend Deets
				?>
							<div class="friend-entry">
								<a style="display:block;" href="/friend?id=<?php echo $friend_id; ?>">
									<div class="friend-avatar">
										<div class="useravatar"><?php echo $friend_avatar; ?></div>
									</div>
									<div class="friend-username"><?php echo $friend_user->user_login; ?></div>
								</a>
							</div> <!-- close friend entry -->
				<?php 
						} // close foreach
						echo "</div>";
					} // close if recommendations
				?>
					</div>
				
			</div>

			<!-- Hidden Fields -->
			<div id="hidden_form_inputs"></div>
			<input type="hidden" name="book_id" id="book_id" value="<?php echo $book_id; ?>">
			<input type="hidden" name="user_id_shelf" id="wp_user_id" value="<?php echo get_current_user_id(); ?>">
			<input type="hidden" id="book_title" name="title" value="<?php echo stripslashes($book_entry[0]->title); ?>">
			<input type="hidden" name="google_books_id" id="book_google_id" value="<?php echo $book_entry[0]->google_books_ID; ?>">
			<input type="hidden" id="book_author" name="author" value="<?php echo $book_entry[0]->author; ?>">
			<input type="hidden" id="book_img" name="small_thumbnail_url" value="<?php echo $book_entry[0]->small_thumbnail_url; ?>">
			<input type="hidden" id="book_isbn_10" name="isbn_10" value="<?php echo $book_entry[0]->ISBN_10; ?>">
			<input type="hidden" id="book_isbn_13" name="isbn_13" value="<?php echo $book_entry[0]->ISBN_13; ?>">

			<div class="submit">
				<input type="submit" name="add_book_submit" id="add_book_submit" class="button button-primary" value="Update Book">
			</div>
			<div class="delete-button-wrapper">
				<a href="#" class="button-alt delete_button" id="delete_button">Delete Book</a>
			</div>

		</form>
		<?php } // end if GET ID ?>
	</section>
	
	<?php // get_template_part('templates/content', 'side-nav'); ?>

</main>
</div> <!-- close wrapper -->

<?php get_template_part('templates/content', 'footer-nav'); ?>

<div id="popup_update_book" class="popup-wrapper">
	<div class="popup popup-large">
		<a class="close-button popup-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
		<div class="popup-heading-wrapper">
			<div class="icon-background">
				<?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/icon_bookworm-logo.svg' ); ?>
			</div>
			<h4>Book updated</h4>
		</div>
		<p><a href="/add-book/" class="popup-link">Add a book?</a></p>
		<p><a href="/bookshelf/" class="popup-link">Go to Bookshelf</a>
			<ul>
				<li><a href="/bookshelf/nightstand/">Currently Reading</a></li>
				<li><a href="/bookshelf/finished/">Finished</a></li>
				<li><a href="/bookshelf/wishlist/">Wishlist</a></li>
			</ul>
		</p>
	</div>
</div>

<div id="popup_delete_book" class="popup-wrapper">
	<div class="popup popup-large">
		<a class="close-button popup-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
		<h4>Are you sure you want to delete this book entry?</h4>
		<p><a id="delete_book_confirm" href="#" class="popup-link">Delete Book</a></p>
	</div>
</div>

<div class="loading-animation">
	<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><circle cx="4" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce0" attributeName="cy" begin="0;svgSpinners3DotsBounce1.end+0.25s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="12" cy="12" r="3" fill="currentColor"><animate attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.1s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="20" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce1" attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.2s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle></svg>
</div>

<script type="text/javascript">

	document.addEventListener('DOMContentLoaded', function () {
		let startReadingDate = document.getElementById('start_reading_date');

		new AirDatepicker(startReadingDate, {
			isMobile: true,
    		autoClose: true,
			buttons: ['clear'],
			onSelect: (date) => {
				const formattedDate = date.formattedDate; // Access formatted date string
				if (formattedDate == undefined) {
					startReadingDate.value = '';
				} else {
					startReadingDate.value = formattedDate;
				}
			}
		})

		let finishedReadingDate = document.getElementById('finished_reading_date');
		new AirDatepicker(finishedReadingDate, {
			isMobile: true,
    		autoClose: true,
			buttons: ['clear'],
			onSelect: (date) => {
				const formattedDate = date.formattedDate; // Access formatted date string
				if (formattedDate == undefined) {
					finishedReadingDate.value = '';
				} else {
					finishedReadingDate.value = formattedDate;
				}
			}
		})
		
		// Submit the book form
		let bookForm = document.getElementById('book_form');
		let loadingAnimation = document.querySelector('.loading-animation');
		let popupNextStep = document.querySelector('#popup_update_book');
		let popupClose = document.querySelectorAll('.popup-close');
		let recommendTo = document.getElementById('recommend_to');
		let recommendedBy = document.getElementById('recommended_by');
		let addBookSubmit = document.getElementById('add_book_submit');
		let tags = document.querySelectorAll('.tags-wrapper li');
		let altSubmit = document.getElementById('alt-submit');
		let tagsArray = [];

		popupClose.forEach((popupCloser) => {
			popupCloser.addEventListener('click', function(e){
				e.preventDefault();
				/*popupCloser.classList.remove('active');*/
				document.querySelector('.popup-wrapper').classList.remove('active');
			});
		})
		
		tags.forEach((tag, index) => {
			tagID = tag.dataset.id;
			if (tag.classList.contains('active')) {
				tagsArray.push(tagID);
			}
			tag.addEventListener("click", function addTag(event) {
				event.preventDefault();
				tagID = tag.dataset.id;
				
				if (tag.classList.contains('active')) {
					tag.classList.remove('active');
					tagsArray = tagsArray.filter(tag => tag !== tagID);
					
				} else {
					tag.classList.add('active');
					tagsArray.push(tagID);
				}
			})
		})
		
		recommendedBy.addEventListener("change", function(event) {
			var user_id_from = this.value;
			console.log('rec by is changing | value: ' + user_id_from);
			if (this.value == 'outside') {
				this.nextElementSibling.nextElementSibling.classList.add('active');
				if (this.nextElementSibling.classList.contains('active')) {
					// Rec Note
					this.nextElementSibling.classList.remove('active');
				}
			} else {
				if (this.nextElementSibling.nextElementSibling.classList.contains('active')) {
					this.nextElementSibling.nextElementSibling.classList.remove('active');
				}
				if (this.value != '#') {
					bookForm.addEventListener("submit", function() {
						// user_id_to, user_id_from, add_recommendation
						addRecommendation(<?php echo get_current_user_id(); ?>, user_id_from, false);
					});
				} else {
					bookForm.removeEventListener("submit", addRecommendation);
				}
			}
		});
		
		recommendTo.addEventListener("change", function(event) {
			var user_id_to = this.value;
			console.log('rec to is changing | value: ' + user_id_to);
			if (this.value == 'outside') {
				this.nextElementSibling.nextElementSibling.classList.add('active');
				if (this.nextElementSibling.classList.contains('active')) {
					// Rec Note
					this.nextElementSibling.classList.remove('active');
				}
			} else {
				if (this.nextElementSibling.nextElementSibling.classList.contains('active')) {
					this.nextElementSibling.nextElementSibling.classList.remove('active');
				}
				if (this.value != '#') {
					addBookSubmit.value = 'Update and Recommend Book';
					// Rec note
					this.nextElementSibling.classList.add('active');
					document.querySelector('#popup_update_book h4').innerHTML = 'Book updated and recommended';
					bookForm.addEventListener("submit", function() {
						// user_id_to, user_id_from, add_recommendation
						addRecommendation(user_id_to, <?php echo get_current_user_id(); ?>, true);
					});
					
				} else {
					addBookSubmit.value = 'Update Book';
					document.querySelector('#popup_update_book h4').innerHTML = 'Book updated';
					// Rec note
					this.nextElementSibling.classList.remove('active');
					bookForm.removeEventListener("submit", addRecommendation);
				}
			}
		});
				
		function updateBook(event) {
			event.preventDefault();
			
			loadingAnimation.classList.add('active');
			var formData = new FormData(bookForm);
			
			  formData.append('tagsArray', tagsArray);
			  //formData.append('tagsArray', JSON.stringify(tagsArray));
			  formData.append("action", "update_book");
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
			      recommendationsList();
			      let recommendation_note = document.getElementById("recommendation_note");
			      if (recommendation_note.classList.contains('active')) {
				      recommendation_note.classList.remove('active')
			      }
			      addBookSubmit.value = 'Update Book';
				  loadingAnimation.classList.remove('active');
				  window.scrollTo({top: 0, behavior: 'smooth'});
				  popupNextStep.classList.add('active');
			    })
			    .catch((error) => {
			      console.log("[ OPS!! update_book ]");
			      console.error(error);
			    });
		}
		
		function addRecommendation(user_id_to, user_id_from, add_notification) {
			var formData = new FormData(bookForm);
			formData.append('user_id_to', user_id_to);
			formData.append('user_id_from', user_id_from);
			formData.append('add_notification', add_notification);
			formData.append("action", "recommend_book");
			fetch([bookwormAjax.url], { // use your ajax url
				method: "POST",
				credentials: "same-origin",
				body: formData // put your data into fetch body
			})
				.then((response) => response.text())
				.then((text) => {
					console.log(text);
					if (text.includes('duplicate')) {
						document.querySelector('#popup_update_book h4').innerHTML = 'Book updated. That recommendation is already there.';
					}
					if (text.includes('success')) {
						document.querySelector('#popup_update_book h4').innerHTML = 'Book updated and recommended';
					}
				})
				.then((data) => {
					if (data) {
						console.log(data);
					}
				})
				.catch((error) => {
					console.log("[ Does this error ever appear? add_recommendation ]");
					console.error(error);
				});
		}
		
		function recommendationsList() {
			let recommend_to_select = document.getElementById('recommend_to');
			recommend_to_select.selectedIndex = 0;
			let recommended_by_select = document.getElementById('recommended_by');
			recommended_by_select.selectedIndex = 0;
			
			var formData = new FormData(bookForm);
			formData.append('current_user_id', <?php echo $wp_current_user_id; ?>);
			formData.append("action", "recommendations_list");
			fetch([bookwormAjax.url], { // use your ajax url
			method: "POST",
			/*headers: {
		        'Content-Type': 'application/x-www-form-urlencoded' // Important for WordPress AJAX
		    },*/
			credentials: "same-origin",
			body: formData // put your data into fetch body
			})
			.then(response => response.json())
			//.then((response) => response.text())
			.then((text) => {
				console.log('this is txt: ' + text);
				console.log(text);
				 let recommended_to_wrapper = document.getElementById('recommended_to_wrapper');
				recommended_to_wrapper.innerHTML = text[0].recommend_to;
			    let recommended_by_wrapper = document.getElementById('recommended_by_wrapper');
				recommended_by_wrapper.innerHTML = text[1].recommend_by;
			})
			.then((data) => {
			  if (data) {
				  console.log("tehre's data");
			    console.log(data);
			    let recommended_to_wrapper = document.getElementById('recommended_to_wrapper');
				recommended_to_wrapper.innerHTML = data.recommend_to;
			    let recommended_by_wrapper = document.getElementById('recommended_by_wrapper');
				recommended_by_wrapper.innerHTML = data.recommend_by;
			  }
			})
			.catch((error) => {
			  console.log("[ recommendations list is borked :( ]");
			  console.error(error);
			});
		}
		
		bookForm.addEventListener("submit", updateBook);
		altSubmit.addEventListener("click", updateBook);
		
		// Delete Book
		let deleteButtons = document.querySelectorAll('.delete_button');
		let popup_delete_book = document.querySelector('#popup_delete_book');

		deleteButtons.forEach((deleteButton) => {
			deleteButton.addEventListener('click', function(event) {
				event.preventDefault();
	
				window.scrollTo({top: 0, behavior: 'smooth'});
				popup_delete_book.classList.add('active');
	
				delete_book_confirm.addEventListener('click', function(event) {
					event.preventDefault();
					loadingAnimation.classList.add('active');
	
					formData = new FormData();
					formData.append('book_id', <?php echo $book_id; ?>);
					formData.append("action", "delete_book");
	
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
					  window.location.href = "/bookshelf/";
				    })
				    .catch((error) => {
				      console.log("[ OPS!! update_book ]");
				      console.error(error);
				    });
				});
			});
		});

		//let popupClose = document.querySelectorAll('.popup-close');
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

	}, false);
</script>

<?php get_footer(); ?>