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
		.analytics .icon-background {
			width: 80px;
			height: 80px;
		}
		.analytics .icon-background svg {
			width: 45px;
			height: 45px;
			stroke-width: 0;
		}
		h5 {
			font-size: 16px;
			font-weight: 500;
			margin-bottom: 10px;
		}
		.tags-wrapper {
			text-align: center;
		}
		li.tag-entry a.popup-trigger {
			text-decoration: none;
			color: #333;
		}
		.popup-book-entry a.book-entry-edit-link {
			display: flex;
		}
		.popup-book-entry .book-entry-thumb img {
			width: 85px;
    		height: auto;
		}
		.popup-book-entry .book-entry-details {
			margin-left: 0;
		}
		.popup-book-entry .book-entry-title, .popup-book-entry .book-entry-author {
			text-align: left;
		}
		.popup-book-entry .book-entry-author {
			font-weight: 400;
		}
		.loading-spinner {
			padding: 20px;
		}
		.loading-spinner svg {
			width: 40px;
			height: 40px;
		}
		.loading-spinner p {
			margin-top: 10px;
			color: #666;
			max-width: 100%;
		}
		.book-chart {
			position: relative;
		}
		.book-chart .chart-label {
			position: absolute;
			top: 75%;
			box-sizing: border-box;
			padding: 5px 7px;
			border: 1px solid #ccc;
			background: #fff;
		}
		@media all and (max-width: 630px) {
			.book-chart .chart-label {
				top: 60%;
			}
		}
		.book-chart .chart-label.label-left {
			left: 0;
		}
		.book-chart .chart-label.label-right {
			right: 0;
		}
		.book-chart canvas {
		}
		.date-input-wrapper {
			position: relative;
			width: 150px;
		}
		.date-input-wrapper .input-icon {
			position: absolute;
			right: 10px;
			bottom: -20px;
			transform: translateY(-50%);
			width: auto;
			height: 40px;
			pointer-events: none;
		}
		.date-input-wrapper label {
			font-weight: 500;
		}
		.analytics-heading h3 {
			margin-bottom: 0;
		}
		h4.analytics-date-range-label {
			font-style: italic;
			max-width: 200px;
		}
		.date-range-selector h5 {
			margin-bottom: 5px;
		}
		#timePeriodsList {
			margin-bottom: 20px;
		}
		#timePeriodsList h4 {
			display: inline-block;
		}
		#number_books_finished {
			display: inline-block;
			font-size: 20px;
			margin-bottom: 20px;
			margin-left: 10px;
    		font-weight: 600;
		}
		#book_analytics_form {
			margin-bottom: 0;
		}
		#book_analytics_form input, #book_analytics_form select {
			margin-bottom: 0;
		}
		.analytics .bookshelf-heading-filter-wrapper {
			margin-bottom: 10px;
		}
		.analytics #analytics_tags .tag-count-wrapper {
			margin-bottom: 20px;
		}
		#generate_gemini_summary {
			margin-bottom: 20px;
			display: inline-block;
		}
		.carousel-button {
			position: absolute;
			bottom: 10px;
			cursor: pointer;
		}
		.slides-wrapper:after {
			content: '';
			position: absolute;
			z-index: 1;
			background-image: linear-gradient(to right, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0) 90%, rgba(255, 255, 255, 1) 100%);
			width: 100%;
			height: 175px;
			top: 0;
		}
		.slider-nav {
			margin: 20px 0;
		}
		.carousel-prev {
			left: 0;
		}	
		.carousel-next {
			right: 0;
		}
		@media all and (max-width: 820px) {
			.analytics .bookshelf-heading-filter-wrapper {
				flex-direction: column;
				align-items: flex-start;
				gap: 15px;
			}
		}
		@media all and (max-width: 630px) {
			#book_analytics_form > .flex {
				flex-direction: column;
			}
			#book_analytics_form > .flex.align-items-end {
				align-items: flex-start;
			}
			#book_analytics_form #dateRangeButton {
				margin: 10px 0 20px;
			}
			.date-input-wrapper {
				width: 100%;
			}
			h4.analytics-date-range-label {
				max-width: 100%;
			}
		}
		@media all and (max-width: 480px) {
			#analytics_tags {
				flex-direction: column;
			}
			#analytics_tags li.tag-entry {
				display: inline-block;
			}
			#analytics_tags .book-count {
				display: block;
			}
		}
	</style>

	<div class="bookshelf-heading-filter-wrapper flex justify-space-between align-items-center">
		<div class="analytics-heading">
			<h3><?php echo get_the_title(); ?></h3>
			<h4 class="analytics-date-range-label">Past 12 Months</h4>
		</div>
		<div class="date-range-selector">
			<form id="book_analytics_form" class="">
				<!-- <div class="flex gap-10 align-items-baseline"> -->
					<div class="flex gap-10 align-items-end">
						<div class="date-input-wrapper">
							<label for="bookshelf_date_range">Date Range:</label>
							<select name="bookshelf_date_range">
								<option value="P12M">Past 12 Months</option>
								<option value="current">Year-to-Date</option>
								<option value="custom">Custom Date Range</option>
							</select>
						</div>
						<?php
							$bookshelf_end_date = new DateTimeImmutable();
							$bookshelf_start_date = $bookshelf_end_date->modify('-12 months');
						?>
						<script>
							// Put the date vars in JS
							let bookshelf_start_date = '<?php echo $bookshelf_start_date->format('Y-m-d'); ?>';
							let bookshelf_end_date = '<?php echo $bookshelf_end_date->format('Y-m-d'); ?>';
						</script>
						<div class="date-input-wrapper">
							<label for="start_date">Start:</label>
							<input id="bookshelf_start_date" class="calendar-ui" name="bookshelf_start_date" type="text" value="<?php echo $bookshelf_start_date->format('Y-m-d'); ?>" placeholder= "Start">
							<img class="input-icon" src="<?php echo get_stylesheet_directory_uri() . '/img/icon_calendar3.png'; ?>" alt="Calendar icon" />
						</div>
						<div class="date-input-wrapper">
							<label for="end_date">End:</label>
							<input id="bookshelf_end_date" class="calendar-ui" name="bookshelf_end_date" type="text" value="<?php echo $bookshelf_end_date->format('Y-m-d'); ?>" placeholder="End">
							<img class="input-icon" src="<?php echo get_stylesheet_directory_uri() . '/img/icon_calendar3.png'; ?>" alt="Calendar icon" />
						</div>
						<input id="dateRangeButton" type="submit" class="button button-primary" value="Apply" />
					</div>
					<input type="hidden" name="wp_current_user_id" value="<?php echo $wp_current_user_id; ?>" />
				<!-- </div> -->

<div class="validation-error-message" id="validation_error" style="display: none;"></div>
			</form>
		</div>
	</div>
	
	<section>
		<div class="icon-wrapper icon-medium full-width bg-lt-blue">
			<div class="icon-background">
				<?php echo file_get_contents( get_stylesheet_directory() . '/img/icon_graph-up-arrow.svg'); ?>
			</div>
			<h4>By the Numbers</h4>
		</div>
		<div id="timePeriodsList" class="time-periods-list">
			<?php
				$table_name = $wpdb->prefix . 'bookworm_books';
				$book_entries = $wpdb->get_results($wpdb->prepare(
					"SELECT * FROM $table_name WHERE user_id_shelf = %d AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01' AND date_finished BETWEEN %s AND %s",
					$wp_current_user_id,
					$bookshelf_start_date->format('Y-m-d'),
					$bookshelf_end_date->format('Y-m-d')
				));
			?>

			<h4 class='title'>Number of books you've finished:</h4> <span id="number_books_finished"><?php echo count($book_entries); ?></span>

			<?php
				// Book Covers Slider
				echo "<div class='slides-wrapper analytics-slider'>
						<div class='slides'>
							<ul class='slides-inner'>";
								$i = 0;
								foreach ($book_entries as $book_entry) {
									$i++;
									echo "<li id='slide" . $i . "' class='row slide'>";
									echo "<a class=\"book-entry-edit-link\" href=\"/update-book/?id=" . $book_entry->id . "\">
										<div class=\"slide-image\">
											<img class=\"book_entry_img\" src=\"" . $book_entry->small_thumbnail_url . "\">
										</div>
									</a>
									</li>";
								}
				echo "</ul>
					</div>
					<div class='carousel-prev carousel-button'>Prev</div>
					<div id='slider_nav' class=\"slider-nav\">";
						$slide_number  = count($book_entries);
						for ($x = 1; $x <= $slide_number; $x++) {
							echo "<div class='slider-nav-dot'>&nbsp;</div>";
						}
				echo "</div>
					<div class='carousel-next carousel-button'>Next</div>
				</div>";

				/*
				// Finished Books breakdown by time period
				$date_query_field = 'date_finished';
				$filterClosure = create_year_filter($date_query_field);
				$countCurrentYear = count(array_filter($book_entries, $filterClosure));
				echo "<div><h5>Year-to-Date</h5>
				<div class='text'>" . $countCurrentYear . "</div></div>";

				$filterClosure = create_date_filter($date_query_field, 3);
				$countThreeMonths = count(array_filter($book_entries, $filterClosure));
				echo "<div><h5>Past 3 Months</h5>
				<div class='text'>" . $countThreeMonths . "</div></div>";
				$filterClosure = create_date_filter($date_query_field, 6);
				$countSixMonths = count(array_filter($book_entries, $filterClosure));
				echo "<div><h5>Past 6 Months</h5>
				<div class='text'>" . $countSixMonths . "</div></div>";
				$filterClosure = create_date_filter($date_query_field, 12);
				$countTwelveMonths = count(array_filter($book_entries, $filterClosure));
				echo "<div><h5>Past Year</h5>
				<div class='text'>" . $countTwelveMonths . "</div></div>";
				*/
			?>
		</div>
		<div id="tagsList" class="tags-list">
			<h4 class='title'>Number of finished books in each category:</h4>
			<div class="tags-wrapper">
				<ul id="analytics_tags">
					<?php
						$all_book_tags = array();

						$table_name = $wpdb->prefix . 'bookworm_books';
						$book_entries = $wpdb->get_results($wpdb->prepare(
							"SELECT * FROM $table_name WHERE user_id_shelf = %d AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01' AND date_finished BETWEEN %s AND %s",
							$wp_current_user_id,
							$bookshelf_start_date->format('Y-m-d'),
							$bookshelf_end_date->format('Y-m-d')
						));

						foreach ($book_entries as $book_entry) {
							if ($book_entry->tags != NULL && $book_entry->tags != '' && !empty($book_entry->tags)) {
								$book_entry_tags = explode(',', $book_entry->tags);
								//$book_entry_tags = json_decode(stripslashes($book_entry->tags), true);
								$all_book_tags = array_merge($all_book_tags, $book_entry_tags);
							} 
						}

						if (!empty($all_book_tags)) {
							$all_book_tags = array_unique($all_book_tags);
							$output = '';

							foreach ($all_book_tags as $tagID) {
								$tag = get_tag($tagID);
								$book_entries = $wpdb->get_results($wpdb->prepare(
									"SELECT * FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = %d AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01' AND date_finished BETWEEN %s AND %s AND FIND_IN_SET(%d, tags)",
									$wp_current_user_id,
									$bookshelf_start_date->format('Y-m-d'),
									$bookshelf_end_date->format('Y-m-d'),
									$tagID
								));
								$book_count = count($book_entries);

								$output .= '<div class="tag-count-wrapper">';
								$output .= '<li class="tag-entry active ' . $tag->slug . '" data-id="' . $tag->slug . '">';
								$output .= '<a class="popup-trigger" href="#popup_tags_' . $tagID . '">';
								$output .= $tag->name;
								$output .= '</a>';
								$output .= '</li>';
								$output .= '<span class="book-count">' . $book_count . '</span>';
								$output .= '</div>';
								$output .= '<div id="popup_tags_' . $tagID . '" class="popup-wrapper popup-tags ' . $tagID . '">';
								$output .= '<div class="popup popup-medium">';
								$output .= '<a class="close-button popup-close" href="#"><img src="' . get_stylesheet_directory_uri() . '/img/icon_close.svg' . '" alt="Close button" /></a>';
								foreach ($book_entries as $book_entry) {
									$output .= '<div class="book-entry popup-book-entry">';
									$output .= '<a class="book-entry-edit-link" href="/update-book/?id=' . $book_entry->id . '">';
									$output .= '<div class="book-entry-thumb">
										<img class="book_entry_img" src="' . $book_entry->small_thumbnail_url . '">
									</div>';
									$output .= '<div class="book-entry-details">';
									$output .= '<div class="book-entry-title">' . stripslashes($book_entry->title) . '</div>';
									$output .= '<div class="book-entry-author">by ' . sanitizeInput($book_entry->author) . '</div>';
									$output .= '</div>';
									$output .= '</a>';
									$output .= '</div>';
								}
								$output .= '</div>';
								$output .= '</div>';
							}
							echo $output;
						}
					?>
				</ul>
			</div>
		</div> <!-- close tags-list -->
	</section>
	
	<section>
		<div class="icon-wrapper icon-medium full-width bg-lt-blue">
			<div class="icon-background">
				<?php echo file_get_contents( get_stylesheet_directory() . '/img/icon_journals.svg'); ?>
			</div>
			<h4>AI Summary of Notes</h4>
		</div>
		<div class="gemini-ai-summary-wrapper">
			<a href="#" id="generate_gemini_summary" class="button button-secondary">Generate AI Summary</a>
			<div id="gemini-summary-content">&nbsp;</div>
		</div>
	</section>

	<section>
		<div class="icon-wrapper icon-medium full-width bg-lt-blue">
			<div class="icon-background">
				<?php echo file_get_contents( get_stylesheet_directory() . '/img/icon_bar-chart.svg'); ?>
			</div>
			<h4>Book Characterizations</h4>
		</div>

		<h5>Mood</h5>
		<div class="book-chart flex">
			<div class="chart-label label-left">Funny</div>
			<canvas id="rating_mood"></canvas>
			<div class="chart-label label-right">Tragic</div>
		</div>
		<h5>Language</h5>
		<div class="book-chart flex">
			<div class="chart-label label-left">Dense</div>
			<canvas id="rating_language"></canvas>
			<div class="chart-label label-right">Accessible</div>
		</div>
		<h5>Romance</h5>
		<div class="book-chart flex">
			<div class="chart-label label-left">Not at All</div>
			<canvas id="rating_romance"></canvas>
			<div class="chart-label label-right">Chock Full</div>
		</div>
		<h5>Suspension of Disbelief</h5>
		<div class="book-chart flex">
			<div class="chart-label label-left">Entirely Speculative</div>
			<canvas id="rating_suspension_disbelief"></canvas>
			<div class="chart-label label-right">Entirely Realist</div>
		</div>
	</section>

</main>
</div> <!-- close wrapper -->

<?php get_template_part('templates/content', 'footer-nav'); ?>

<div class="loading-animation">
	<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><circle cx="4" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce0" attributeName="cy" begin="0;svgSpinners3DotsBounce1.end+0.25s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="12" cy="12" r="3" fill="currentColor"><animate attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.1s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="20" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce1" attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.2s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle></svg>
</div>

<?php get_footer(); ?>

<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function () {

	}, false); // end if DOM is loaded
</script>