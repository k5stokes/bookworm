<?php
	/* Template Name: Notifications Page Template */
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
		<?php
			$notifications_query = "SELECT * FROM {$wpdb->prefix}bookworm_notifications WHERE recipient_id = '$wp_current_user_id' ORDER BY id DESC";
			$notifications = $wpdb->get_results($notifications_query);
			if ($notifications) { ?>
				<form action="#" method="post" id="delete_notifications_form" name="delete_notifications_form">
			<?php
				$i = 0;
				foreach ($notifications as $notification) {
					$i++;
				?>
					<div class="notification <?php if ($notification->read_status == 'unread') { echo $notification->read_status; } ?>">
						<!--
							<a class="close-button" href="#" data-notification-id="<?php // echo $notification->id; ?>">
								<img src="<?php // echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" />
							</a>
						-->
						<p><?php echo $notification->message; ?></p>
					</div>
				<?php } ?>
					<input id="notification_id" type="hidden" name="notification_id" value="">
				</form>
			<?php	
			} else {
				?>
					<div class="notification">
						<p>You don't currently have any notifications. Check back later!</p>
					</div>
				<?php
			}
		?>
	</section>
	
	<?php // get_template_part('templates/content', 'side-nav'); ?>

</main>
</div> <!-- close wrapper -->

<?php get_template_part('templates/content', 'footer-nav'); ?>

<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function () {
		
		function fadeOut(el) {
            var opacity = 1; // Initial opacity
            var interval = setInterval(function() {
               if (opacity > 0) {
                  opacity -= 0.1;
                  el.style.opacity = opacity;
               } else {
                  clearInterval(interval); // Stop the interval when opacity reaches 0
                  el.style.display = 'none'; // Hide the element
               }
            }, 50);
         }
         
		let deleteNotificationsForm = document.getElementById('delete_notifications_form');
		let deleteNotificationsButtons = document.querySelectorAll('.notification .close-button');
		let notificationIcon = document.querySelector('.notifications');
		let notificationsIndicator = document.querySelector('.notifications-indicator');
		if (notificationsIndicator) {
			let notificationsIndicatorNumber = Number(notificationsIndicator.textContent);
			notificationsIndicator.classList.add('hidden');
		}
		
		deleteNotificationsButtons.forEach(function (deleteNotificationButton, index) {
			deleteNotificationButton.addEventListener('click', function(event) {
				event.preventDefault();
				
				let deletedNotification = deleteNotificationButton.parentElement;
				let notificationID = this.dataset.notificationId;
				let notificationIDField = deleteNotificationsForm.querySelector('#notification_id');
				notificationIDField.value = notificationID;
				
				console.log('notification id = ' + notificationID);
				
				var formData = new FormData(deleteNotificationsForm);
				formData.append("action", "delete_notification");
				
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
						console.log(data);
						fadeOut(deletedNotification);
						if (notificationsIndicatorNumber > 1) {
							notificationsIndicatorNumber = notificationsIndicatorNumber - 1;
							notificationsIndicator.textContent = notificationsIndicatorNumber;
						} else {
							notificationIcon.style.display = 'none';
						}
					})
					.catch((error) => {
						console.log("[Error Will Robinson]");
						console.error(error);
					});
				
			});
		});

	}, false);
</script>

<?php
	get_footer(); 
	
	$update_notifications = $wpdb->update( "{$wpdb->prefix}bookworm_notifications", array('read_status' => 'read'), array('recipient_id' => $wp_current_user_id) );
?>