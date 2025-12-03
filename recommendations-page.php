<?php
	/* Template Name: Recommendations Page Template */
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

<main id="main" role="main" class="community">

	<div class="bookshelf-heading-filter-wrapper flex justify-space-between align-items-center">
		<h3><?php echo get_the_title(); ?></h3>
	</div>
	
	<section>
		<h2>Recommend a Book to a Friend</h2>
		<p>Choose a friend to recommend a book to. To add someone as a friend and connect on Bookworm, visit the <a href="/friends/">Friends page</a>.
		<div id="friendsList" class="grid">
			<?php
				$friend_query = "SELECT friend_id FROM {$wpdb->prefix}bookworm_friends WHERE user_id = '$wp_current_user_id' AND status = 'friends'";
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
							$friend_avatar = get_avatar( $friend_id, 64 );
							
							// Friend Deets
						?>
							<div class="friend-entry">
							<a style="display:block;" href="/friend?id=<?php echo $friend_id; ?>">
								<div class="friend-avatar">
									<div class="useravatar"><?php echo $friend_avatar; ?></div>
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
			?>
		</div> <!-- close grid -->
	</section>
	
	<?php // get_template_part('templates/content', 'side-nav'); ?>

</main>
</div> <!-- close wrapper -->

<?php get_template_part('templates/content', 'footer-nav'); ?>

<div id="popup" class="popup-wrapper">
	<div class="popup popup-large">
		<a class="close-button popup-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
		<h4>Friend added</h4>
		<div id="popupContainer"></div>
		<p><a id="close-popup" href="#" class="popup-link popup-close">Add Another Friend?</a></p>
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
						const popupContainer = document.getElementById('popupContainer'); 
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
						const friendsList = document.getElementById('friendsList'); 
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