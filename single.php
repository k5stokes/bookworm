<?php get_header(); ?>

	<div class="amsv-page container">
		
		<h1 class="mb-10"><?php echo get_the_title(); ?></h1>
		
		<div class="amsv-featured-image-wrapper max-w-4xl mx-auto mb-10">
			<?php echo get_the_post_thumbnail(); ?>
		</div>
		
		<article class="amsv-main-content amsv-single-post max-w-2xl mx-auto mb-20">
				
			<div class="flex">
				<div class="amsv-single-published flex-initial justify-self-start w-2/4 mb-5">
					<span class="font-display">Published <?php echo get_the_date('m.d.Y'); ?></span>
				</div>
		        <div class="amsv-social-wrapper flex-initial justify-self-end w-2/4 text-right">
		            <a style="width:30px; height:30px;" class="amsv-social-icon inline-flex justify-center items-center bg-grey-med rounded-full mr-5" href="http://linkedin.com/shareArticle?mini=true&title=<?php echo get_the_title(); ?>&url=<?php echo get_permalink(); ?>">
		                <i class="fab fa-linkedin-in text-white flex-initial"></i>
		            </a>
		            <a style="width:30px; height:30px;" class="amsv-social-icon inline-flex justify-center items-center bg-grey-med rounded-full mr-5" href="http://www.facebook.com/sharer.php?u=<?php echo get_permalink(); ?>">
		                <i class="fab fa-facebook-square text-white flex-initial"></i>
		            </a>
		            <a style="width:30px; height:30px;" class="amsv-social-icon inline-flex justify-center items-center bg-grey-med rounded-full" href="https://twitter.com/share?text=<?php echo get_the_title(); ?>&url=<?php echo get_permalink(); ?>">
		                <i class="fab fa-twitter text-white flex-initial"></i>
		            </a>
		            <!-- <a style="width:30px; height:30px;" class="amsv-social-icon flex justify-center items-center bg-grey-med rounded-full" href="mailto:?subject=<?php echo $mailtitle; ?>&body=<?php echo get_permalink(); ?>">
		                <i class="fas fa-envelope"></i>
		            </a> -->
		        </div>
			</div>
	        
	    	<?php the_content(); ?>
		</article>
	</div>

<?php get_footer(); ?>