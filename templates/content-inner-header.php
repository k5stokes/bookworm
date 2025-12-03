<?php
	$wp_user_id = get_current_user_id();
	$wp_user_info = get_userdata($wp_user_id);
	$wp_username = $wp_user_info->user_login;
	if ($wp_user_info->user_firstname != '' && $wp_user_info->user_firstname != NULL) {
		$wp_firstname = $wp_user_info->user_firstname;
	} else {
		$wp_firstname = $wp_username;
	}
	$firstchar = strtoupper($wp_firstname[0]);
?>
<header>
	<div class="header-background">
		<div class="wrapper">
			<div class="logo-wrapper flex nowrap align-items-center gap-15">
				<a href="/bookshelf/" class="logo">
					<?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/icon_bookworm-logo.svg' ); ?>
				</a>
				<div class="logo-text"><a href="/bookshelf/">BookWorm</a></div>
			</div>
			<div class="top-nav">
			    <div class="flex nowrap align-items-center gap-15">
			        <div class="nav-icon nav-icon-add-book">
			            <a class="nav-icon-anchor" href="/add-book/" alt="Add Book" title="Add Book">
			                <?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/icon_plus.svg'); ?>
			            </a>
			        </div>
			        <div class="nav-icon nav-icon-bookshelf">
			            <a id="bookshelf_nav_item_side" class="nav-icon-anchor" href="/bookshelf/" alt="Bookshelf" title="Bookshelf">
			                <?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/iconb_bookshelf_white.svg'); ?>
			            </a>
			            <div id="bookshelf_nav_side" class="popup popup-small">
			                <p><a href="/bookshelf/nightstand/">Currently Reading</a></p>
			                <p><a href="/bookshelf/finished/">Finished</a></p>
			                <p class="margin-bottom-0"><a href="/bookshelf/wishlist/">Wishlist</a></p>
			            </div>
			        </div>
			        <div class="nav-icon nav-icon-notes">
			            <a class="nav-icon-anchor" href="/notes/" alt="Notes" title="Notes">
			                <?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/iconb_notes.svg'); ?>
			            </a>
			        </div>
			        <div class="nav-icon nav-icon-recommend">
			            <a class="nav-icon-anchor" href="/community/" alt="Community" title="Comunity">
			                <?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/community.svg'); ?>
			            </a>
			        </div>
			    </div>
			</div>
			<div class="user-meta">
				<?php 
					$notifications_query = "SELECT * FROM {$wpdb->prefix}bookworm_notifications WHERE recipient_id = '$wp_user_id'";
					$notifications = $wpdb->get_results($notifications_query);
					if ($notifications) {
						if (count($notifications) > 0) {
							$unread_notifications_query = "SELECT * FROM {$wpdb->prefix}bookworm_notifications WHERE recipient_id = '$wp_user_id' AND read_status = 'unread'";
							$unread_notifications = $wpdb->get_results($unread_notifications_query);
				?>
					<div class="notifications">
						<a href="/notifications/">
							<span class="notifications-icon">
								<?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/bell.svg' ); ?>
							</span>
							<?php if (count($unread_notifications) > 0) { ?>
								<span class="notifications-indicator">
									<?php echo count($unread_notifications); ?>
								</span>
							<?php } ?>
						</a>
					</div>
				<?php
						}
					}
				?>
				<span class="useravatar-wrapper">
					<?php // echo file_get_contents( get_stylesheet_directory_uri() . '/img/user-circle.svg' ); ?>
					<!-- <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
					<path fill-rule="evenodd" d="M12 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-2 9a4 4 0 0 0-4 4v1a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1a4 4 0 0 0-4-4h-4Z" clip-rule="evenodd"/>
					</svg>-->
					<a class="useravatar" href="/account/">
						<?php
							if (get_avatar($wp_user_id)) {
								if ( strpos( get_avatar_url($wp_user_id), 'gravatar') == false) {
									echo get_avatar($wp_user_id, 64);
								} else {
						?>
								<span class="username"><?php echo $firstchar; ?></span>
						<?php
								}
							}
						?>
					</a>
				</span>
			</div>
			<!-- <div class="mobile-menu-button-wrapper">            
				<button id="menuButton" class="mobile-menu-toggle">
					<div id="menu-icon">
						<span></span>
						<span></span>
						<span></span>
					</div>
				</button>
			</div> -->
		</div>
	</div>
</header>

<?php
	if (isset($_SESSION['previous_url']) || isset($_SERVER['HTTP_REFERER'])) {
		if (!isset($_SESSION['previous_url'])) {
			$_SESSION['previous_url'] = $_SERVER['HTTP_REFERER']; // Store previous URL
		}
?>
	<div class="wrapper">
		<a href="#" onclick="goBack()" class="back-button">&larr; Back</a>
	</div>

	<script>
		function goBack() {
			window.location.href = "<?php echo $_SESSION['previous_url']; ?>";
		}
	</script>
<?php } ?>