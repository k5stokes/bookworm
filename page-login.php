<?php 
	/* Template Name: Login */
	if (is_user_logged_in()) {
		//auth_redirect();
		$url = get_site_url();
		wp_safe_redirect( $url );
	}
	get_header();
?>
<main class="login-screen-wrapper">
	<section class="login-screen">
		<div class="logo-large">
			<!-- <img src="<?php //echo get_site_url() . '/wp-content/themes/bookworm/img/BookWorm-logo.svg'; ?>"> -->
			<?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/icon_bookworm-logo.svg' ); ?>
			<div class="logo-text">BookWorm</div>
		</div>
		<p>BookWorm is a private, low-key web app that helps you keep track of the books you've read, what you thought about them, and what you'd like to read.</p>

		<?php 
			$args = array(
				'redirect' => 'https://bookworm.madhurimachakraborty.net/bookshelf/', 
				'form_id' => 'wpum-submit-login-form',
				'label_username' => __( 'Username' ),
				'remember' => true
			);
			//wp_login_form( $args );
			
			if ( have_posts() ) : while ( have_posts() ) : the_post();
				echo get_the_content();
				the_content();
			endwhile;
			endif;
		?>
   <!-- <p id="nav">
      <a class="wp-login-lost-password" href="https://bookworm.madhurimachakraborty.net/wp-login.php?action=lostpassword">Lost your password?</a>			
   </p> -->
   <script type="text/javascript">
      /* <![CDATA[ */
      function wp_attempt_focus() {setTimeout( function() {try {d = document.getElementById( "user_login" );d.focus(); d.select();} catch( er ) {}}, 200);}
      wp_attempt_focus();
      if ( typeof wpOnload === 'function' ) { wpOnload() }
      /* ]]> */
   </script>
	</section>
</main>

<?php get_footer() ?>