<?php
	if (!is_user_logged_in()) {
		//auth_redirect();
		$url = get_site_url() . '/login';
		wp_safe_redirect( $url );
	}

	get_header();
	get_template_part('templates/content', 'inner-header');
?>

<main id="main" role="main" class="">

<h1>404 Page Not Found</h1>
<p>Something went awry.  Want to try your bookshelf?</p>
<div class="nav-icon flex align-items-center justify-center">
	<a class="nav-icon-anchor" href="/bookshelf/">
		<?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/iconb_bookshelf.svg'); ?>
	</a>
</div>

</main>
</div> <!-- close wrapper -->

<?php get_template_part('templates/content', 'footer-nav'); ?>

<div class="loading-animation">
	<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><circle cx="4" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce0" attributeName="cy" begin="0;svgSpinners3DotsBounce1.end+0.25s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="12" cy="12" r="3" fill="currentColor"><animate attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.1s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="20" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce1" attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.2s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle></svg>
</div>

<?php get_footer(); ?>