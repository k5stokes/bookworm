<?php
	/* Template Name: Add Book Form Page Template */
	if (!is_user_logged_in()) {
		//auth_redirect();
		$url = get_site_url() . '/log-in/';
		wp_safe_redirect( $url );
	}
	get_header();
	get_template_part('templates/content', 'inner-header');
?>

<main id="main" role="main" class="book-search">
	<h3 id="add_book">Add a Book</h3>
	<section class="book-form">
		<?php 
			$wp_current_user_id = get_current_user_id();
			$recommendation = false;
			if (!empty($_GET)) {
				if ($_GET['fromFriend'] == true) {
					$book_data_string = "https://books.googleapis.com/books/v1/volumes/" . $_GET['id'] . "?key=AIzaSyCCLPPisgOw5nhZmrCuKMZnWDNSJ6-hCWY";
				    $book_call = callAPI('GET', $book_data_string, '');
				    $book_item = json_decode($book_call, TRUE);
				    if (!empty($book_item)) {
					    $recommendation = true;
				    }
				} else {
					$recommendation_id = $_GET['id'];
					$recommendation_entry = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bookworm_recommendations WHERE id = '$recommendation_id'");
					if (!empty($recommendation_entry)) {
						$book_data_string = "https://books.googleapis.com/books/v1/volumes/" . $recommendation_entry[0]->google_books_ID . "?key=AIzaSyCCLPPisgOw5nhZmrCuKMZnWDNSJ6-hCWY";
					    $book_call = callAPI('GET', $book_data_string, '');
					    $book_item = json_decode($book_call, TRUE);
					    if (!empty($book_item) && $recommendation_entry[0]->recipient_id == $wp_current_user_id) {
						    $recommendation = true;
							$recommended_by = true;
					    }
					}
				}
			}
			
		?>
				
		<form id="book_form" action="/bookshelf/" method="POST">

			<div class="form-section">
				<label for="book_title">Search for Books</label>
				<div class="book-title-search">
					<input id="book_title" name="title" type="text" value="<?php if ($recommendation == true) { echo sanitizeInput($book_item['volumeInfo']['title']); } ?>">
					<div class="tooltip search-tooltip">
						<a class="close-button search-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
						<section id="search_results" class="search-results">
						</section>
					</div>
				</div>
				<div id="validation_error" class="validation_error"><p>Please enter a book</p></div>
				<div class="book-display<?php if ($recommendation == true) { echo ' active'; } ?>">
					<div class="book-entry-thumb">
						<img class="book_entry_img" src="<?php
							if ($recommendation == true) {
								if (isset($book_item['volumeInfo']['imageLinks']['smallThumbnail'])) {
									$book_cover_link = $book_item['volumeInfo']['imageLinks']['smallThumbnail'];
								} else {
									$book_cover_link = get_stylesheet_directory_uri() . '/img/icon_open-book.svg';
								}
								echo $book_cover_link; } ?>">
					</div>
					<div class="book-entry-details">
						<div id="book_entry_title" class="book-entry-title"><?php if ($recommendation == true) { echo sanitizeInput($book_item['volumeInfo']['title']); } ?></div>
						<div id="book_entry_author" class="book-entry-author"><?php if ($recommendation == true) { echo sanitizeInput($book_item['volumeInfo']['authors'][0]); } ?></div>
						<!-- <div id="book_entry_pub_date" class="book-entry-publication-date">&nbsp;</div> -->
				
						<div class="book-entry-description-wrapper<?php if ($recommendation == true && isset($book_item['volumeInfo']['description'])) { echo ' active'; } ?>">
							<div id="book_entry_description" class="book-entry-notes overage">
								<a id="popup_description_trigger" class="popup-trigger" href="#popup_description">
									<strong>Google Books Description:</strong> <span><?php if ($recommendation == true && isset($book_item['volumeInfo']['description'])) { echo sanitizeInput($book_item['volumeInfo']['description']); } ?></span>
								</a>
							</div>
							<div id="popup_description" class="popup-wrapper popup-description">
								<div class="popup popup-large">
									<a class="close-button popup-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
									<p><strong>Google Books Description:</strong> <span><?php if ($recommendation == true && isset($book_item['volumeInfo']['description'])) { echo sanitizeInput($book_item['volumeInfo']['description']); } ?></span></p>
								</div>
							</div>
						</div>
						
						<!-- <div class="form-entry form-entry-small inline-wrapper flex">
							<div class="radio-choice flex align-items-center">
								<input type="radio" id="fiction" name="fiction_or_non" value="fiction"><label for="fiction">Fiction</label>
							</div>
							<div class="radio-choice flex">
								<input type="radio" id="nonfiction" name="fiction_or_non" value="nonfiction"><label for="nonfiction">Nonfiction</label>
							</div>
						</div> -->
					</div>
				</div>
				
				<div class="form-entry form-entry-small flex align-items-center">
					<input type="checkbox" id="shared_on_shelf" name="shared_on_shelf" value="NULL">
					<label for="shared_on_shelf">Invisible to Friends?</label>
				</div>
				
				<div class="tags-wrapper">
					<?php 
						$tags = get_tags(array(
						  'hide_empty' => false
						));
						echo '<ul>';
						foreach ($tags as $tag) {
						  echo '<li class="' . $tag->slug . '" data-id="' . $tag->term_id . '">' . $tag->name . '</li>';
						}
						echo '</ul>';
					?>
				</div>
				
				<div class="submit">
					<a id="alt-submit" href="#" class="button button-primary">Add Book</a>
				</div>
				
			</div>
			<div class="form-section">

				<div class="icon-wrapper icon-medium full-width bg-lt-yellow">
					<div class="icon-background">
						<?php echo file_get_contents( get_stylesheet_directory() . '/img/iconb_calendar.svg'); ?>
					</div>
					<h4>Reading Dates</h4>
				</div>
				<label for="start_reading_date">Date Started</label>
				<input id="start_reading_date" class="calendar-ui" name="date_started" type="text" value="">
				<label for="finished_reading_date">Date Finished</label>
				<input id="finished_reading_date" class="calendar-ui" name="date_finished" type="text" value="">
			</div>

			<div class="form-section">
				<!-- <div class="flex nowrap justify-space-between align-items-center"> -->
					<div class="icon-wrapper icon-medium full-width bg-lt-blue">
						<div class="icon-background">
							<?php echo file_get_contents( get_stylesheet_directory() . '/img/iconb_notes.svg'); ?>
						</div>
						<h4>My Notes</h4>
					</div>
				<!-- </div> -->
				<textarea id="book_notes" name="notes" aria-label="Book notes">Enter your notes for this book.</textarea>

				<div class="range-inputs">
					<div class="form-entry">
						<label for="book_mood">Mood</label>
						<input class="input-range" type="range" name="rating_mood" id="book_mood" value="3" min="1" max="5" step="1" />
						<div class="range flex nowrap justify-space-between align-items-center">
							<span class="range-left">Funny</span>
							<span class="range-right">Tragic</span>
						</div>
					</div>

					<div class="form-entry">
						<label for="book_language">Language</label>
						<input class="input-range" type="range" name="rating_language" id="book_language" value="3" min="1" max="5" step="1" />
						<div class="range flex nowrap justify-space-between align-items-center">
							<span class="range-left">Dense</span>
							<span class="range-right">Accessible</span>
						</div>
					</div>

					<div class="form-entry">
						<label for="book_romance">Romance</label>
						<input class="input-range" type="range" name="rating_romance" id="book_romance" value="3" min="1" max="5" step="1" />
						<div class="range flex nowrap justify-space-between align-items-center">
							<span class="range-left">Not at All</span>
							<span class="range-right">Chock Full</span>
						</div>
					</div>

					<div class="form-entry">
						<label for="book_suspension_disbelief">Suspension of Disbelief</label>
						<input class="input-range" type="range" name="rating_suspension_disbelief" id="book_suspension_disbelief" value="3" min="1" max="5" step="1" />
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
						<?php echo file_get_contents( get_stylesheet_directory() . '/img/iconb_recommend.svg'); ?>
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
							<textarea id="recommendation_note" name="recommendation_note" aria-label="Recommentation note">Enter a note about your book recommendation.</textarea>
					<?php } ?>
					<div class="bookworm-recommended">
						<label for="recommended_by_text">Recommended By (Outside of BookWorm):</label>
						<input id="recommended_by_text" name="recommended_by" type="text" value="">
					</div>
				<?php
					if ($recommended_by == true) {
						$friend_id = $recommendation_entry[0]->recommender_id;
						$friend_user = get_user($recommendation_entry[0]->recommender_id);
						$friend_avatar = get_avatar( $friend_id, 96 );
						
						// Friend Deets
				?>
						<label for="">Recommended By:</label>
						<div class="friend-entry">
							<a style="display:block;" href="/friend?id=<?php echo $friend_id; ?>">
								<div class="friend-avatar">
									<div class="useravatar"><?php echo $friend_avatar; ?></div>
								</div>
								<div class="friend-username"><?php echo $friend_user->user_login; ?></div>
							</a>
						</div> <!-- close friend entry -->
				<?php } ?>
				
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
					<div class="bookworm-recommended">
						<label for="recommended_to_text">Recommend To (Outside of BookWorm):</label>
						<input id="recommended_to_text" name="recommended_to" type="text" value="">	
					</div>	
				
			</div>

			<!-- Hidden Fields -->
			<div id="hidden_form_inputs">
				<?php if ($recommendation == true) { ?>
					<input id="book_google_id" name="google_books_id" type="hidden" value="<?php echo $recommendation_entry[0]->google_books_ID; ?>">
					<input id="book_author" name="author" type="hidden" value="<?php echo sanitizeInput($book_item['volumeInfo']['authors'][0]); ?>">
					<input id="book_pub_date" name="published_date" type="hidden" value="<?php echo $book_item['volumeInfo']['publishedDate']; ?>">
					<input id="book_description" name="description" type="hidden" value="<?php if (isset($book_item['volumeInfo']['description'])) { echo sanitizeInput($book_item['volumeInfo']['description']); } ?>">
					<input id="book_page_count" name="page_count" type="hidden" value="<?php echo $book_item['volumeInfo']['pageCount']; ?>">
					<input id="book_img" name="small_thumbnail_url" type="hidden" value="<?php
							if ($recommendation == true) {
								if (isset($book_item['volumeInfo']['imageLinks']['smallThumbnail'])) {
									$book_cover_link = $book_item['volumeInfo']['imageLinks']['smallThumbnail'];
								} else {
									$book_cover_link = get_stylesheet_directory_uri() . '/img/icon_open-book.svg';
								}
								echo $book_cover_link; } ?>">
					<?php if (isset($book_item['volumeInfo']['industryIdentifiers'][0]['identifier'])) { ?>
						<input id="book_isbn_10" name="isbn_10" type="hidden" value="<?php echo $book_item['volumeInfo']['industryIdentifiers'][0]['identifier']; ?>">
					<?php } ?>
					<?php if (isset($book_item['volumeInfo']['industryIdentifiers'][1]['identifier'])) { ?>
						<input id="book_isbn_13" name="isbn_13" type="hidden" value="<?php echo $book_item['volumeInfo']['industryIdentifiers'][1]['identifier']; ?>">
					<?php } ?>
				<?php } ?>
			</div>
			
			<input type="hidden" name="user_id_shelf" id="wp_user_id" value="<?php echo get_current_user_id(); ?>">

			<div class="submit">
				<input type="submit" name="add_book_submit" id="add_book_submit" class="button button-primary" value="Add Book">
			</div>

		</form>
	</section>
	<?php // get_template_part('templates/content', 'side-nav'); ?>

</main>
</div> <!-- close wrapper -->

<?php get_template_part('templates/content', 'footer-nav'); ?>

<div id="popup" class="popup-wrapper">
	<div class="popup popup-large">
		<a class="close-button popup-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
		<div class="popup-heading-wrapper">
			<div class="icon-background">
				<?php echo file_get_contents( get_stylesheet_directory() . '/img/icon_bookworm-logo.svg' ); ?>
			</div>
			<h4>Book added!</h4>
		</div>
		<p><a id="close-popup" href="#" class="popup-link popup-close">Add Another?</a></p>
		<p><a href="/bookshelf/" class="popup-link">Go to Bookshelf</a>
			<ul>
				<li><a href="/bookshelf/nightstand/">Currently Reading</a></li>
				<li><a href="/bookshelf/finished/">Finished</a></li>
				<li><a href="/bookshelf/wishlist/">Wishlist</a></li>
			</ul>
		</p>
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
				console.log(formattedDate);
				startReadingDate.value = formattedDate;
			}
		})

		let finishedReadingDate = document.getElementById('finished_reading_date');
		new AirDatepicker(finishedReadingDate, {
			isMobile: true,
    		autoClose: true,
			buttons: ['clear'],
			onSelect: (date) => {
				const formattedDate = date.formattedDate; // Access formatted date string
				finishedReadingDate.value = formattedDate;
			}
		})

		let searchEl = jQuery('#book_title');
		let resultsEl = jQuery('#search_results');
		//let hiddenFieldsEl = document.getElementById('hidden_form_inputs');
		let hiddenFieldsEl = jQuery('#hidden_form_inputs');
		let bookForm = document.getElementById('book_form');
		let searchClose = document.querySelector('.search-close');
		let loadingAnimation = document.querySelector('.loading-animation');
		let popupNextStep = document.querySelector('#popup');
		let validationError = document.getElementById('validation_error'); 
		let popupClose = document.querySelectorAll('.popup-close');
		let recommendTo = document.getElementById('recommend_to');
		let recommendedBy = document.getElementById('recommended_by');
		let addBookSubmit = document.getElementById('add_book_submit');
		let recNote = document.getElementById('recommendation_note');
		let tags = document.querySelectorAll('.tags-wrapper li');
		let popupH4 = document.querySelector('#popup h4');
		let altSubmit = document.getElementById('alt-submit');
		let tagsArray = [];

		searchEl.on('input', function () {
			// resultsEl.classList.add("active");
			jQuery('#search_results').addClass('active');
			jQuery('.close-button').addClass('active');
			if (validationError.classList.contains('active')) {
				validationError.classList.remove('active');
			}
			jQuery.post(bookwormAjax.url, {
				action: 'google_books_api_search',
				nonce: bookwormAjax.bookworm_thinking_nonce,
				data: {
					s: jQuery(this).val()
				}
			}).done(function(data) {
				resultsEl.html(data);
				
				// Loop through search result book links
				var bookLinks = document.querySelectorAll('.book-search-entry-link');
				
				bookLinks.forEach(function (bookLink, index) {
					bookLink.addEventListener('click', function (event) {
					    event.preventDefault();
					    
					    function setBookDetails(bookLinkElement, callback) {
						    var googleBooksId = bookLinkElement.id;
							var bookTitle = bookLinkElement.dataset.title;
							var bookAuthor = bookLinkElement.dataset.author;
							var bookImg = bookLinkElement.dataset.img;
							var bookPubDate = bookLinkElement.dataset.disppubdate;
							var bookDescription = bookLinkElement.dataset.description;
							searchEl.val(bookTitle);
							document.querySelector('.book-display').classList.add('active');
							document.getElementById('search_results').classList.remove('active');
							searchClose.classList.remove('active');
							document.querySelector('#book_entry_title').innerHTML = bookTitle;
							document.querySelector('#book_entry_author').innerHTML = 'by ' + bookAuthor;
							// document.querySelector('#book_entry_pub_date').innerHTML = 'published ' + bookPubDate;
							document.querySelector('.book_entry_img').src = bookImg;
							document.querySelector('.popup-description').id = "popup_description_" + googleBooksId;
							document.querySelector('.popup-trigger').href = "#popup_description_" + googleBooksId;
							document.querySelector('.popup-trigger span').innerHTML = bookDescription;
							document.querySelector('.popup-description p span').innerHTML = bookDescription;
							document.querySelector('.book-entry-description-wrapper').classList.add('active');
							
							jQuery.post(bookwormAjax.url, {
								action: 'google_books_api_volume',
								nonce: bookwormAjax.bookworm_thinking_nonce,
								data: {
									id: googleBooksId
								}
							}).done(function(data) {
								hiddenFieldsEl.html(data)
							})
							
							callback();
						}
						
						function popupDescription() {
							let popupNotesTriggers = document.querySelectorAll('.popup-trigger');
							
							popupNotesTriggers.forEach((popupNotesTrigger) => {
								popupNotesTrigger.addEventListener('click', function(e) {
									e.preventDefault();
									
									let popupId = this.getAttribute('href');
									console.log(this);
									console.log('click ' + popupId);
									document.querySelector(popupId).classList.add('active');
								})
							})
						}
						
						setBookDetails(this, popupDescription);
						
					});
				});
			})
		})
		
		recommendTo.addEventListener("change", function(event) {
			console.log('rec to is changing');
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
					addBookSubmit.value = 'Add and Recommend Book';
					// Rec note
					this.nextElementSibling.classList.add('active');
					document.querySelector('#popup h4').innerHTML = 'Book added and recommended';
					
					// bookForm.addEventListener('submit', addRecommendation(event, bookForm));
					bookForm.addEventListener("submit", function addRecommendation(event) {
						event.preventDefault();
						
						var formData = new FormData(this);
						formData.append('user_id_from', <?php echo get_current_user_id(); ?>);
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
							})
							.catch((error) => {
								console.log("[ Does this error ever appear? add_recommendation ]");
								console.error(error);
							});
					});
					
				} else {
					addBookSubmit.value = 'Add Book';
					document.querySelector('#popup h4').innerHTML = 'Book added!';
					// Rec note
					this.nextElementSibling.classList.remove('active');
					bookForm.removeEventListener("submit", addRecommendation);
				}
			}
		});
		
		recommendedBy.addEventListener("change", function(event) {
			console.log('recced by is changing');
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
					bookForm.addEventListener("submit", function addRecommendation(event) {
						event.preventDefault();
						
						var formData = new FormData(this);
						formData.append('user_id_to', <?php echo get_current_user_id(); ?>);
						formData.append('add_notification', 'false');
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
							})
							.catch((error) => {
								console.log("[ Does this error ever appear? add_recommendation ]");
								console.error(error);
							});
					});
					
				} else {
					bookForm.removeEventListener("submit", addRecommendation);
				}
			}
		});
		
		let popupNotesTriggers = document.querySelectorAll('.popup-trigger');
							
		if (popupNotesTriggers) {
			popupNotesTriggers.forEach((popupNotesTrigger) => {
				popupNotesTrigger.addEventListener('click', function(e) {
					e.preventDefault();
					
					let popupId = this.getAttribute('href');
					console.log(this);
					console.log('click ' + popupId);
					document.querySelector(popupId).classList.add('active');
				})
			})
		}

		searchClose.addEventListener('click', function(e){
			e.preventDefault();
			searchClose.classList.remove('active');
			jQuery('#search_results').removeClass('active');
		})

		popupClose.forEach((popupCloser) => {
			popupCloser.addEventListener('click', function(e){
				e.preventDefault();
				/*popupCloser.classList.remove('active');*/
				e.target.closest('.popup-wrapper').classList.remove('active');
			});
		})
		
		tags.forEach((tag, index) => {
			tag.addEventListener("click", function addTag(event) {
				event.preventDefault();
				tagID = tag.dataset.id;
				
				if (tag.classList.contains('active')) {
					tag.classList.remove('active');
					tagsArray = tagsArray.filter(tag => tag !== tagID);
					console.log('tag removed: ' + tagID);
					console.log(tagsArray);
				} else {
					tag.classList.add('active');
					tagsArray.push(tagID);
					console.log('tag added: ' + tagID);
					console.log(tagsArray);
				}
			})
		})
		
		function addBook(event) {
			event.preventDefault();
			
			if (document.getElementById('book_title').value !== '') {
				loadingAnimation.classList.add('active');
				var formData = new FormData(bookForm);
				
				formData.append('tagsArray', tagsArray);
				// formData.append('tagsArray', JSON.stringify(tagsArray));
				formData.append("action", "add_book");
				fetch([bookwormAjax.url], { // use your ajax url
					method: "POST",
					credentials: "same-origin",
					body: formData // put your data into fetch body
				})
					.then((response) => response.text())
					.then((text) => {
						console.log(text);
						if (text == 'duplicate') {
							document.querySelector('#popup h4').innerHTML = 'That book is already on your bookshelf';
						}
						if (text == 'success') {
							document.querySelector('#popup h4').innerHTML = 'Book added!';
						}
					})
					.then((data) => {
					if (data) {
						console.log(data);
					}
					loadingAnimation.classList.remove('active');
					bookForm.reset();
					document.getElementById('book_title').value = '';
					document.querySelector('.popup-trigger span').innerHTML = '';
					document.querySelector('.popup-description p span').innerHTML = '';
					document.querySelector('.book-display').classList.remove('active');
					tags.forEach((tag, index) => {
						if (tag.classList.contains('active')) {
							tag.classList.remove('active');
						}
					});
					tagsArray = [];
					window.scrollTo({top: 0, behavior: 'smooth'});
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
		}
		
		altSubmit.addEventListener("click", addBook);
		bookForm.addEventListener("submit", addBook);

	}, false);
</script>

<?php get_footer(); ?>