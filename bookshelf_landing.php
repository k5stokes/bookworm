<?php
	/* Template Name: Bookshelf Landing Page Template */
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
	<h3>Bookshelf</h3>
	<section class="bookshelf">
		
		<?php 
			$currently_reading = new BookshelfCategory(
				'Currently Reading',
				'/img/iconb_nightstand.svg',
				"SELECT * FROM {$wpdb->prefix}bookworm_books WHERE (user_id_shelf = '$wp_current_user_id' AND date_started IS NOT NULL AND date_started != '0000-00-00' AND date_started != '1970-01-01') AND (date_finished IS NULL OR date_finished = '0000-00-00' OR date_finished = '1970-01-01') ORDER BY id DESC",
				'date_started',
				"You're not currently reading any books? Do you not...like books? If you want to give it a shot, <a href='/add-book/'>add one</a> you're currently reading.",
				'bg-yellow',
				'/nightstand/'
		);
			$recently_finished = new BookshelfCategory(
				'Recently Finished',
				'/img/icon_checkmark.svg',
				"SELECT * FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = '$wp_current_user_id' AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01' ORDER BY date_finished DESC",
				'date_finished',
				"Reading is good for the soul. <a href='/add-book/'>Add a book</a> you've made all the way through.",
				'bg-yellow',
				'/finished/'
			);
			$wishlist = new BookshelfCategory(
				'Wishlist',
				'/img/iconb_wishlist.svg',
				"SELECT * FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = '$wp_current_user_id' AND (date_started IS NULL OR date_started = '0000-00-00' OR date_started = '1970-01-01') AND (date_finished IS NULL OR date_finished = '0000-00-00' OR date_finished = '1970-01-01') ORDER BY RAND() DESC",
				'',
				"There are maybe 130 million books published. Would you like to <a href='/add-book/'>add one</a> of them to your wishlist?",
				'bg-yellow',
				'/wishlist/'
			);
			
			$bookshelf_categories = array($currently_reading, $recently_finished, $wishlist);
			
			foreach ($bookshelf_categories as $bookshelf_category) {
			
				$book_entries = $wpdb->get_results($bookshelf_category->queryString);

				// Temporary debug output: show a few `date_started` values when WP_DEBUG is enabled
				if (defined('WP_DEBUG') && WP_DEBUG) {
					echo '<div class="debug-dates" style="background:#fff7cc;padding:8px;margin:8px 0;border:1px solid #eee;font-size:13px;">';
					echo '<strong>Debug: date_started values (up to 5 entries)</strong><br/>';
					$debugCount = 0;
					foreach ($book_entries as $dbgEntry) {
						if ($debugCount++ >= 5) break;
						echo 'ID: ' . htmlspecialchars($dbgEntry->id ?? '') . ' — date_started: ' . htmlspecialchars($dbgEntry->date_started ?? '') . '<br/>';
					}
					echo '</div>';
				}
				// print_r($book_entries);

				$date_query_field = $bookshelf_category->ytdQueryString;
			?>
			<div class="bookshelf-category<?php // if (empty($book_entries)) { echo ' flex align-items-center'; } ?>">
				<div class="icon-wrapper icon-medium full-width <?php echo $bookshelf_category->iconBackground; ?>">
						<div class="icon-background">
							<a class="" href="<?php echo $bookshelf_category->link; ?>">
								<?php echo file_get_contents( get_stylesheet_directory() . $bookshelf_category->icon); ?>
							</a>
						</div>
						<h4><a class="" href="<?php echo $bookshelf_category->link; ?>"><?php echo $bookshelf_category->title; ?></a></h4>
				</div>
				<?php if (empty($book_entries)) { ?>
						<div class="user-message"><p><?php echo $bookshelf_category->userMessage; ?></p></div>
				<?php
					} else {
						// Apply Filters and Count
						if ($date_query_field != '' && $bookshelf_category->title == 'Recently Finished') {
							$filterClosure = create_year_filter($date_query_field);
							$countCurrentYear = count(array_filter($book_entries, $filterClosure));
							echo "<p>This Year: <strong>" . $countCurrentYear . "</strong></p>";
						}
				?>
					<div class="slides-wrapper slider">
						<div class="slides">
							<ul class="slides-inner">
							<?php
								$i = 0;
								foreach ($book_entries as $book_entry) {
									$book_entry_published_date = strtotime($book_entry->published_date);
									$book_entry_published_date = date('j F Y',$book_entry_published_date);
									$book_entry_title = stripslashes($book_entry->title);
									if ($book_entry->description != '') {
										$book_entry_description = stripslashes($book_entry->description);
										if (preg_match('/^.{1,60}\b/s', $book_entry_description, $match)) {
										    $book_description_excerpt = $match[0] . "...";
										}
									}
									$i++;
									
									if ($i <= 4) { 
							?>
								<?php if ($i % 2 !== 0) { ?>
									<li id="slide<?php echo $i; ?>" class="row slide<?php if (count($book_entries) <=2) { echo ' single-slide'; } ?>">
								<?php } ?>
									<a class="book-entry-edit-link" href="/update-book/?id=<?php echo $book_entry->id; ?>">
										<div class="slide-image">
											<img class="book_entry_img" src="<?php echo $book_entry->small_thumbnail_url;?>">
										</div>
										<div class="slide-text">
											<div id="book_entry_title" class="book-entry-title"><?php echo $book_entry_title ?></div>
											<div id="book_entry_author" class="book-entry-author"><?php echo $book_entry->author;?></div>
											<?php // if ($book_entry->description != '') { ?>
												<!-- <div id="book_entry_description" class="book_entry_description"><?php echo $book_description_excerpt; ?></div> -->
											<?php // } ?>
										</div>
									</a>
								<?php if ($i % 2 == 0) { ?>
									</li>
								<?php } ?>
							<?php
									} // close if we're less than 4
								} // close loop
							?>
							</ul>
						</div> <!-- close slides -->
	
						<div id="slider_nav" class="slider-nav">
							<?php 							
								$entries_number = count($book_entries);
								$slide_number = $entries_number / 2;
								$slide_number = round($slide_number);
								if ($slide_number >= 2) {
									$slide_number = 2;
								}
			
								for ($x = 1; $x <= $slide_number; $x++) {
									echo "<div class='slider-nav-dot'>&nbsp;</div>";
								}
							?>
						</div>
					</div> <!-- close slides-wrapper -->
				<?php } ?>
				<?php if (!empty($book_entries)) { ?>
					<a class="button button-primary bookshelf-landing-button" href="<?php echo $bookshelf_category->link; ?>">View All</a>
				<?php } ?>
			</div>
		<?php } // end foreach bookshelf category ?>

	</section>
	<?php // get_template_part('templates/content', 'side-nav'); ?>

</main>

<?php get_template_part('templates/content', 'footer-nav'); ?>

<div class="loading-animation">
	<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><circle cx="4" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce0" attributeName="cy" begin="0;svgSpinners3DotsBounce1.end+0.25s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="12" cy="12" r="3" fill="currentColor"><animate attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.1s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="20" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce1" attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.2s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle></svg>
</div>

<?php get_footer(); ?>