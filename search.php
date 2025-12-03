<?php
if (!is_user_logged_in()) {
	//auth_redirect();
	$url = get_site_url() . '/log-in/';
	wp_safe_redirect( $url );
}
get_header();

$num_pages     = $wp_query->max_num_pages;
$total_results = $wp_query->found_posts;
$current_page  = (get_query_var('paged')) ? get_query_var('paged') : 1;
?>

	<div class="amsv-page container">
        <section class="header">
            <span class="label"><?php echo $total_results; ?> Results For:</span>
            <h2 class="title"><?php echo ucwords(strtolower(get_search_query())); ?></h2>
            <?php if ($num_pages): ?>
                <p class="page-info">Page <?php echo $current_page; ?> of <?php echo $num_pages; ?></p>
            <?php endif; ?>
        </section>

        
        <div class="gap-6 grid grid-cols-3 mt-6 lg:grid-cols-2 sm:grid-cols-1">
	        <?php
		        if (have_posts()) : while (have_posts()) : the_post();
	                if (has_post_thumbnail()) {
	                	$thumbnail = get_the_post_thumbnail_url();
	                	$img_class = 'h-52 object-cover';
	            	} elseif (get_field('hero_image')) {
	                	$thumbnail = wp_get_attachment_image_src(get_field('hero_image'), 'full');
	                	$thumbnail= $thumbnail[0];
	                	$img_class = 'h-52 object-cover';
	            	} else {
	                	$thumbnail = '/wp-content/uploads/2021/03/amisve-logo.jpg';
	                	$img_class = 'h-auto py-16';
	            	}
	        ?>
	        <article style="min-height:400px;" class="border border-grey-med bg-white relative">
	            <a href="<?php the_permalink(); ?>"><img class="w-full <?php echo $img_class; ?>" src="<?php echo $thumbnail; ?>" alt="<?php echo get_the_title(); ?> image" /></a>
	            <div class="px-4 py-5">
	                <a href="<?php the_permalink(); ?>"><h5 style="line-height:1.35em;" class="normal-case text-grey-dark"><?php the_title(); ?></h5></a>
	                <a style="bottom: 30px;" class="font-bold inline-block mt-12 text-black absolute" href="<?php the_permalink(); ?>">Read more</a>
	            </div>
	        </article>
            <?php endwhile; endif; ?>
        </div>
        <div class="paginate-wrapper my-12">
            <?php echo paginate_links(); ?>
        </div>
    </div>

<?php get_footer(); ?>
