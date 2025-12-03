<?php
	/* Template Name: Home Page Template */
	if (!is_user_logged_in()) {
		//auth_redirect();
		$url = get_site_url() . '/log-in/';
		wp_safe_redirect( $url );
	}
	get_header();
	get_template_part('templates/content', 'inner-header');

	$wp_current_user_id = get_current_user_id();
?>

<main id="main" role="main" class="bookshelf">
	<section class="home">

		<div class="grid home-grid">
			<div class="grid-item">
				<div class="home-icon bg-lt-blue">
					<a href="#">
						<?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/iconb_notes.svg'); ?>
					</a>
				</div>
			</div>
			<div class="grid-item">
				<div class="home-icon bg-lt-purple">
					<a href="#">
						<?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/iconb_recommend.svg'); ?>
					</a>
				</div>
			</div>
			<div class="grid-item">
				<div class="home-icon bg-lt-yellow">
					<a href="/bookshelf/">
						<?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/iconb_bookshelf.svg'); ?>
					</a>
				</div>
			</div>
			<div class="grid-item">
				<div class="home-icon home-add-book">
					<a href="/add-book/">
						<span class="icon-text">+</span>
					</a>
				</div>
			</div>
		</div>
		<div class="bookshelf-category">
			<div class="icon-wrapper icon-medium full-width bg-beige">
				<div class="icon-background">
					<?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/iconb_nightstand.svg'); ?>
				</div>
				<h4>Currently Reading</h4>
			</div>
			<?php 
				$book_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bookworm_books WHERE (user_id_shelf = '$wp_current_user_id' AND date_started IS NOT NULL AND date_started != '0000-00-00' AND date_started != '1970-01-01') AND (date_finished IS NULL OR date_finished = '0000-00-00' OR date_finished = '1970-01-01')");
				if (empty($book_entries)) { ?>
					<div class="user-message"><p>You're not currently reading any books? But I thought? Huh. Do you not...like books?</p></div>
			<?php } else { ?>
				<div class="slides-wrapper slider">
					<div class="slides">
						<ul class="slides-inner">
						<?php
							$i = 0;
							foreach ($book_entries as $book_entry) {
								$book_entry_published_date = strtotime($book_entry->published_date);
								$book_entry_published_date = date('Y',$book_entry_published_date);
								$book_entry_title = stripslashes($book_entry->title);
								$i++;
						?>
							<?php if ($i % 2 !== 0) { ?>
								<li class="row slide">
							<?php } ?>
								<a class="book-entry-edit-link" href="/update-book/?id=<?php echo $book_entry->id; ?>">
									<div class="slide-image">
										<img class="book_entry_img" src="<?php echo $book_entry->small_thumbnail_url;?>">
									</div>
									<div class="slide-text">
										<div id="book_entry_title" class="book-entry-title"><?php echo $book_entry_title ?></div>
										<div id="book_entry_author" class="book-entry-author"><?php echo $book_entry->author;?></div>
									</div>
								</a>
							<?php if ($i % 2 == 0) { ?>
								</li>
							<?php } ?>
						<?php } ?>
						</ul>
					</div> <!-- close slides -->

					<div id="slider_nav" class="slider-nav">
						<?php 
							$entries_number = count($book_entries);
							$slide_number = $entries_number / 2;
							$slide_number = round($slide_number);

							for ($x = 1; $x <= $slide_number; $x++) {
								echo "<div class='slider-nav-dot'>&nbsp;</div>";
							}
						?>
					</div>
				</div> <!-- close slides-wrapper -->
			<?php } ?>
		</div>


	</section>

</main>
</div> <!-- close wrapper -->

<?php get_template_part('templates/content', 'footer-nav'); ?>

<div class="loading-animation">
	<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><circle cx="4" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce0" attributeName="cy" begin="0;svgSpinners3DotsBounce1.end+0.25s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="12" cy="12" r="3" fill="currentColor"><animate attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.1s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="20" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce1" attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.2s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle></svg>
</div>

<script type="text/javascript">
	
	document.addEventListener('DOMContentLoaded', function () {
		// Touch Sliders
		if (document.querySelector('.slider').length != 0) {

			function createCarousel(carouselElement) {
			
				let slideWrapper = carouselElement.querySelector('.slides-inner');
				let slides = carouselElement.querySelector('.slides');
				let slideWidth = carouselElement.offsetWidth;
				let slideNumber = carouselElement.querySelectorAll('.slide').length;
				let slideWrapperWidth = slideWidth * slideNumber;
				let sliderNav = carouselElement.querySelector('#slider_nav');
				let currentSlide = 0;
				let firstDot = sliderNav.firstElementChild;

				firstDot.classList.add('active');

				slides.setAttribute("style", "width:" + slideWrapperWidth + "px");
			
				// Function to move to a specific slide
				function goToSlide(slideIndex) {
					currentSlide = slideIndex;
					slideWrapper.style.transform = `translateX(${currentSlide * slideWidth}px)`;
				}

				const touchStartHandler = function(event) {
					touchDistance = 0;
					startX = event.touches[0].clientX;
				}
				
				const touchSlideMover = function(event) {
					const moveX = event.touches[0].clientX;
					touchDistance = startX - moveX;

					// Update slider position based on touch movement (adjust logic as needed)
					slideWrapper.style.transform = `translateX(${currentSlide * slideWidth - touchDistance}px)`;
				}

				const touchEndHandler = function(event) {

					console.log('total distance = ' + touchDistance);
					// Logic to determine which slide to snap to based on touch movement distance
					const threshold = slideWidth / 5; // Adjust threshold for sensitivity

					if (Math.abs(touchDistance) > threshold) {
						if (touchDistance > 0) {
							if (currentSlide != (-slideNumber + 1)) {
								goToSlide(currentSlide - 1); // Move to next slide
								let activeSlideDot = carouselElement.querySelector('.slider-nav-dot.active');
								activeSlideDot.nextSibling.classList.add('active');
								activeSlideDot.classList.remove('active');
							} else {
								goToSlide(currentSlide); // Stay on the same slide
							}
						} else {
							if (currentSlide != 0) {
								goToSlide(currentSlide + 1); // Move to previous slide
								let activeSlideDot = carouselElement.querySelector('.slider-nav-dot.active');
								activeSlideDot.previousSibling.classList.add('active');
								activeSlideDot.classList.remove('active');
							} else {
								goToSlide(currentSlide); // Stay on the same slide
							}
						}
					} else {
						goToSlide(currentSlide); // Stay on the same slide
						console.log('sit, Ubu, sit. Good dog.');
					}
				};

				slideWrapper.addEventListener('touchstart', touchStartHandler);
				slideWrapper.addEventListener('touchmove', touchSlideMover);
				slideWrapper.addEventListener('touchend', touchEndHandler);
			}

			const carousels = document.querySelectorAll('.slider');
			carousels.forEach(createCarousel);

		}
	});
</script>

<?php get_footer(); ?>