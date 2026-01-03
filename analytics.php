<?php
	/* Template Name: Analytics Page Template */
	if (!is_user_logged_in()) {
		//auth_redirect();
		$url = get_site_url() . '/log-in/';
		wp_safe_redirect( $url );
	}
	get_header();
	get_template_part('templates/content', 'inner-header');
	
	global $post;
	$wp_current_user_id = get_current_user_id();
?>

<main id="main" role="main" class="community analytics">
	<style>
		h5 {
			font-size: 16px;
			font-weight: 700;
		}
	</style>

	<div class="bookshelf-heading-filter-wrapper flex justify-space-between align-items-center">
		<h3><?php echo get_the_title(); ?></h3>
	</div>
	
	<section>
		<div class="icon-wrapper icon-medium full-width bg-lt-blue">
			<div class="icon-background">
				<?php //echo file_get_contents( get_stylesheet_directory() . '/img/friends.svg'); ?>
			</div>
			<h4>Tags</h4>
		</div>
		<div id="tagsList" class="">
			<?php
				$all_book_tags = array();
				$book_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = '$wp_current_user_id' AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01' ORDER BY date_finished DESC");
				foreach ($book_entries as $book_entry) {
					echo '<br/>Book Entry ID: ' . $book_entry->id . ' — Tags: ' . htmlspecialchars($book_entry->tags);
					if ($book_entry->tags != NULL && $book_entry->tags != '' && !empty($book_entry->tags)) {
						$book_entry_tags = explode(',', $book_entry->tags);
						$book_entry_tags = json_decode(stripslashes($book_entry->tags), true);
						//print_r($book_entry_tags);
						$all_book_tags = array_merge($all_book_tags, $book_entry_tags);
					} 
				}
				if (!empty($all_book_tags)) {
					$all_book_tags = array_unique($all_book_tags);
					foreach ($all_book_tags as $tagID) {
					$tag = get_tag($tagID);
			?>
				<div class="tag-entry">
					<a href="/tag/<?php echo $tag->slug; ?>/"><?php echo $tag->name; ?></a>
				</div>
			<?php
					}
				}
			?>
				
		</div> <!-- close grid -->
	</section>
	
	<section class="grid gap-wide">
		<div class="">
			<h4>Add Friend</h4>
			<p>Enter a friend's BookWorm Code here to connect with them on BookWorm.</p>
			<form action="#" method="post" id="add_friend_form" name="add_friend_form">
				<label for="enter_bookworm_code">Enter a BookWorm Code:</label>
				<input name="enter_bookworm_code" id="enter_bookworm_code" type="text" value="">
				<input name="current_user_id" id="current_user_id" type="hidden" value="<?php echo $wp_current_user_id; ?>">
				<div id="validation_error" class="validation_error"><p>Please enter a BookWorm Code</p></div>
				<div class="submit">
					<input type="submit" name="add_friend_submit" id="add_friend_submit" class="button button-primary" value="Add Friend">
				</div>
			</form>
		</div>
		
		<div class="">
			<h4>Your BookWorm Code</h4>
			<p>Share your BookWorm Code to connect with friends on BookWorm.</p>
			<label>Your BookWorm Code:</label>
			<div class="bookworm_code"><?php echo get_user_meta($wp_current_user_id, 'token_id', true); ?></div>
		</div>
		
	</section>
	
	<section>
		<div class="icon-wrapper icon-medium full-width bg-lt-blue">
			<div class="icon-background">
				<?php echo file_get_contents( get_stylesheet_directory() . '/img/iconb_recommend.svg'); ?>
			</div>
			<h4>Recommendations</h4>
		</div>
		<?php
			$friend_query = "SELECT friend_id FROM {$wpdb->prefix}bookworm_friends WHERE user_id = '$wp_current_user_id' AND status = 'friends'";
			$friends = $wpdb->get_results($friend_query);
			
			if (!$friends) { 
				echo "<p>Become friends with someone on BookWorm first to give and receive recommendations.</p>";
			} else {
				$recommendations_query = "SELECT * FROM {$wpdb->prefix}bookworm_recommendations WHERE recipient_id = $wp_current_user_id ORDER BY id DESC";	
				$recommendations = $wpdb->get_results($recommendations_query);
				
				if ($recommendations) {
			
					foreach ($recommendations as $recommendation) {
						$recommender_id = $recommendation->recommender_id;
						$recommender_user = get_user_by( 'id', $recommender_id  );
						$recommender_avatar = get_avatar( $recommender_id, 96 );
						
						$friend_query = "SELECT * FROM {$wpdb->prefix}bookworm_friends WHERE user_id = '$wp_current_user_id' AND friend_id = '$recommender_id' AND status = 'friends'";
						$friends = $wpdb->get_results($friend_query);
					
						if ($friends) {
							// Check to see if the book already exists on their bookshelf
							$google_books_ID = $recommendation->google_books_ID;
							$recipient_id = $recommendation->recipient_id;
						    //$recommender_user = get_user_by( 'id', $recommendation->recommender_id );
						    
						    $recommendation_note = false;
							
							$recommendation_query = "SELECT * FROM {$wpdb->prefix}bookworm_books WHERE user_ID_shelf = '$recipient_id' AND google_books_ID = '$google_books_ID'";
							$rec_book_entries = $wpdb->get_results($recommendation_query);
							
							$book_cover_query = "SELECT * FROM {$wpdb->prefix}bookworm_books WHERE google_books_ID = '$google_books_ID'";
							$book_cover_entries = $wpdb->get_results($book_cover_query);
							
							if (count($rec_book_entries) > 0) {
								$recommendation_title_string = "<a href='/update-book/?id=" . $rec_book_entries[0]->id . "'>" . $rec_book_entries[0]->title . "</a>";
							} else {
								$recommendation_title_string = $recommendation->title;
							}
							if (isset($recommendation->recommendation_note) && $recommendation->recommendation_note != '' && $recommendation->recommendation_note != NULL && $recommendation->recommendation_note != 'Enter a note about your book recommendation.') {
								$recommendation_note = true;
							}
				?>
					<div class="recommendation">
						<div class="recommendation-wrapper">
						<div class="recommendation-row flex">
							<?php if (count($book_cover_entries) > 0) { ?>
								<div class="book-cover-image recommendation-image">
									<img src="<?php echo $book_cover_entries[0]->small_thumbnail_url; ?>">
								</div>
							<?php } ?>
							<div class="recommendation-message first">
								<p><a href="/friend?id=<?php echo $recommender_id; ?>"><?php echo $recommender_user->user_login; ?></a> has recommended <?php echo $recommendation_title_string; ?> by <?php echo $recommendation->author; ?> to you!<?php if ($recommendation_note == true) { ?> They say:<?php } ?></p>
							</div>
						</div>
				<?php
						if ($recommendation_note == true) {
				?>
							<div class="recommendation-row flex align-items-center justify-space-between">
								<div class="recommendation-message">
									<span class="recommendation-note">"<?php echo stripslashes($recommendation->recommendation_note) ; ?>"</span>
								</div>
								<div class="recommender-avatar recommendation-image">
									<div class="friend-avatar">
										<div class="useravatar"><?php echo $recommender_avatar; ?></div>
									</div>
								</div>
							</div>
				<?php
						}
						if (count($rec_book_entries) > 0) {
							$recommendation_close = $recommender_user->user_login . " has been added as a recommender for the book.";
						} else {
							$recommendation_close = "<a class='button button-primary' href='/add-book/?id=" . $recommendation->id . "'>Add to wishlist</a>";
						}
				?>
						<div class="recommendation-row">
							<div class="recommendation-message">
								<?php echo $recommendation_close; ?>
							</div>
						</div>
						</div> <!-- close recommendation wrapper -->
					</div> <!-- close recommendation -->
				<?php 
					} // end if the rec comes from a friend
				} // end if recommendations
			} // end foreach
		} // end if have friends
		?>
	</section>
	
	<!--
	<section>
		<h2>Book Clubs</h2>
		<p>Coming soon!</p>
	</section>
	-->
	
	<?php // get_template_part('templates/content', 'side-nav'); ?>

</main>
</div> <!-- close wrapper -->

<?php get_template_part('templates/content', 'footer-nav'); ?>

<div id="popup" class="popup-wrapper">
	<div class="popup popup-large">
		<a class="close-button popup-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
		<div id="popupContainer"></div>
		<p><a id="close-popup" href="#" class="popup-link popup-close">Add Another Friend?</a></p>
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
		let addFriendForm = document.getElementById('add_friend_form');
		let loadingAnimation = document.querySelector('.loading-animation');
		let popupNextStep = document.querySelector('#popup');
		let popupRemFriend= document.querySelector('#popup_remove_friend');
		let remFriendForm = document.querySelector('#remove_friends_form'); 
		let remove_friend_confirm = document.getElementById('remove_friend_confirm');
		let validationError = document.getElementById('validation_error');

		addFriendForm.addEventListener('submit', function(event) {
			event.preventDefault();
			
			if (document.getElementById('enter_bookworm_code').value !== '') {
				if (validationError.classList.contains('active')) {
					validationError.classList.remove('active');
				}
				loadingAnimation.classList.add('active');
				var formData = new FormData(this);
				
				formData.append("action", "add_friend");
				fetch([bookwormAjax.url], { // use your ajax url
					method: "POST",
					credentials: "same-origin",
					body: formData // put your data into fetch body
				})
					.then((response) => response.text())
					.then((text) => {
						console.log(text);
						let popupContainer = document.getElementById('popupContainer'); 
						popupContainer.innerHTML = text;
					})
					.then((data) => {
					loadingAnimation.classList.remove('active');
					addFriendForm.reset();
					window.scrollTo({top: 0, behavior: 'smooth'});
					popupNextStep.classList.add('active');
					if (data) {
						console.log(data);
					}
					})
					.catch((error) => {
						console.log("[ OPS!! add_friend ]");
						console.error(error);
					});
					
					// Refresh friends list
					formData.append("action", "friends_list");
				fetch([bookwormAjax.url], { // use your ajax url
					method: "POST",
					credentials: "same-origin",
					body: formData // put your data into fetch body
				})
					.then((response) => response.text())
					.then((text) => {
						console.log(text);
						const friendsList = document.getElementById('remove_friends_form'); 
						friendsList.innerHTML = text;
					})
					.then((data) => {
						loadingAnimation.classList.remove('active');
						if (data) {
							console.log(data);
						}
					})
					.catch((error) => {
						console.log("Friends list refresher not working :(");
						console.error(error);
					});
			} else {
				validationError.classList.add('active');
				let yOffset = -200; 
				let element = document.getElementById('enter_bookworm_code');
				let y = element.getBoundingClientRect().top + window.pageYOffset + yOffset;
				window.scrollTo({top: y, behavior: 'smooth'});
			}
		})
		
		// Remove Friends
		let removeButtons = document.querySelectorAll('.remove-button');
		let popup_remove_friend = document.querySelector('#popup_remove_friend');

		removeButtons.forEach((removeButton) => {
			removeButton.addEventListener('click', function(event) {
				event.preventDefault();
	
				window.scrollTo({top: 0, behavior: 'smooth'});
				popup_remove_friend.classList.add('active');
				
				let remFriendId = this.dataset.friendid;
				let remove_friend_id = document.getElementById('remove_friend_id');
				console.log(remFriendId);
				remove_friend_id.value = remFriendId;
	
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
		});
		
		let popupClose = document.querySelectorAll('.popup-close');
		let popupTriggers = document.querySelectorAll('.popup-trigger');
		
		popupTriggers.forEach((popupTrigger) => {
			popupTrigger.addEventListener('click', function(e) {
				e.preventDefault();
				
				let popupId = popupTrigger.getAttribute('href');
				
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