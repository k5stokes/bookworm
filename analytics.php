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
		h5 {
			font-size: 16px;
			font-weight: 500;
			margin-bottom: 10px;
		}
		.tags-wrapper {
			text-align: center;
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
			top: 20px;
			transform: translateY(-50%);
			width: auto;
			height: 40px;
			pointer-events: none;
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
			#dateRangeButton {
				margin: 0 0 10px;
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
			<h5>Choose Date Range:</h5>
			<form id="book_analytics_form" class="">
				<div class="flex gap-10 align-items-baseline">
					<select name="bookshelf_date_range">
						<option value="P12M">Past 12 Months</option>
						<option value="current">Year-to-Date</option>
					</select>
					<div class="flex gap-10">
						<div class="date-input-wrapper">
							<label class="visually-hidden" for="start_date">Start:</label>
							<input id="bookshelf_start_date" class="calendar-ui" name="bookshelf_start_date" type="text" value="" placeholder= "Start">
							<img class="input-icon" src="<?php echo get_stylesheet_directory_uri() . '/img/icon_calendar3.png'; ?>" alt="Calendar icon" />
						</div>
						<div class="date-input-wrapper">
							<label class="visually-hidden" for="end_date">End:</label>
							<input id="bookshelf_end_date" class="calendar-ui" name="bookshelf_end_date" type="text" value="" placeholder="End">
							<img class="input-icon" src="<?php echo get_stylesheet_directory_uri() . '/img/icon_calendar3.png'; ?>" alt="Calendar icon" />
						</div>
					</div>
					<input type="hidden" name="wp_current_user_id" value="<?php echo $wp_current_user_id; ?>" />
					<input id="dateRangeButton" type="submit" class="button button-primary" value="Apply" />
				</div>
			</form>
		</div>
	</div>
	
	<section>
		<div class="icon-wrapper icon-medium full-width bg-lt-blue">
			<div class="icon-background">
				<?php echo file_get_contents( get_stylesheet_directory() . '/img/friends.svg'); ?>
			</div>
			<h4>By the Numbers</h4>
		</div>
		<div id="timePeriodsList" class="time-periods-list">
			<?php 
				$bookshelf_end_date = new DateTimeImmutable();
				$bookshelf_start_date = $bookshelf_end_date->modify('-12 months');

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
								$book_count = $wpdb->get_var($wpdb->prepare(
									"SELECT COUNT(*) FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = %d AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01' AND date_finished BETWEEN %s AND %s AND FIND_IN_SET(%d, tags)",
									$wp_current_user_id,
									$bookshelf_start_date->format('Y-m-d'),
									$bookshelf_end_date->format('Y-m-d'),
									$tagID
								));

								$output .= '<div class="tag-count-wrapper">';
								$output .= '<li class="tag-entry active ' . $tag->slug . '" data-id="' . $tag->slug . '">' . $tag->name . '</li>';
								$output .= '<span class="book-count">' . $book_count . '</span>';
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
				<?php echo file_get_contents( get_stylesheet_directory() . '/img/iconb_recommend.svg'); ?>
			</div>
			<h4>AI Summary of Notes</h4>
		</div>
		<div class="gemini-ai-summary-wrapper">
			<div id="gemini-summary-content">
				<div class="loading-spinner">
					<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><circle cx="4" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce0" attributeName="cy" begin="0;svgSpinners3DotsBounce1.end+0.25s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="12" cy="12" r="3" fill="currentColor"><animate attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.1s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="20" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce1" attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.2s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle></svg>
					<p>Generating AI summary...</p>
				</div>
			</div>
		</div>
	</section>

	<section>
		<div class="icon-wrapper icon-medium full-width bg-lt-blue">
			<div class="icon-background">
				<?php echo file_get_contents( get_stylesheet_directory() . '/img/iconb_recommend.svg'); ?>
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

<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function () {
		let validationError = document.getElementById('validation_error');
		let loadingAnimation = document.querySelector('.loading-animation');

		let startDate = document.getElementById('bookshelf_start_date');
		new AirDatepicker(startDate, {
			isMobile: true,
    		autoClose: true,
			buttons: ['clear'],
			onSelect: ({date}) => {
				const year = date.getFullYear();
				const month = String(date.getMonth() + 1).padStart(2, '0');
				const day = String(date.getDate()).padStart(2, '0');
				const formattedDate = `${year}-${month}-${day}`;
				console.log('start formattedDate:', formattedDate);
				startDate.value = formattedDate;
			}
		})

		let endDate = document.getElementById('bookshelf_end_date');
		new AirDatepicker(endDate, {
			isMobile: true,
    		autoClose: true,
			buttons: ['clear'],
			onSelect: ({date}) => {
				const year = date.getFullYear();
				const month = String(date.getMonth() + 1).padStart(2, '0');
				const day = String(date.getDate()).padStart(2, '0');
				const formattedDate = `${year}-${month}-${day}`;
				console.log('end formattedDate:', formattedDate);
				endDate.value = formattedDate;
			}
		})

		// Put the date vars in JS
		let bookshelf_start_date = '<?php echo $bookshelf_start_date->format('Y-m-d'); ?>';
		let bookshelf_end_date = '<?php echo $bookshelf_end_date->format('Y-m-d'); ?>';

		// Load Gemini summary
		fetchGeminiSummary(bookshelf_start_date, bookshelf_end_date);

		// Draw us some charts
		drawRatingsCharts(bookshelf_start_date, bookshelf_end_date);

		// Ratings Charts function
		function drawRatingsCharts(startVal, endVal) {
			let bookAnalyticsForm = document.querySelector('#book_analytics_form');
			var formData = new FormData(bookAnalyticsForm);

			if (startVal && endVal) {
				// Override the date values with passed parameters
				formData.set("bookshelf_start_date", startVal);
				formData.set("bookshelf_end_date", endVal);
			}

			formData.append("action", "book_analytics_ratings_query");

			fetch([bookwormAjax.url], {
				method: "POST",
				credentials: "same-origin",
				body: formData
			})
			.then(r => r.json())
			.then(data => {
				data.data.forEach(item => {
					console.log(item.label, item.rating, item.data, item.bg_color);

					var frequencyMap = item.data.reduce((accumulator, currentValue) => {
						// If the current number is already a key in the accumulator object, increment its count.
						// Otherwise, initialize its count to 1.
						accumulator[currentValue] = (accumulator[currentValue] || 0) + 1;
						return accumulator;
					}, {}); // The second argument {} is the initial value of the accumulator (an empty object).

					var data = {
						datasets: [{
							label: item.label,
							data: Object.entries(frequencyMap).map(([key, value]) => ({
								x: parseInt(key),
								y: 0,
								r: value
							})),
							backgroundColor: item.bg_color
						}]
					};

					// 1. Find the existing chart instance using the canvas ID
					const existingChart = Chart.getChart(item.rating);

					// 2. If it exists, destroy it
					if (existingChart) {
						existingChart.destroy();
					}

					var bookChart = new Chart(
						document.getElementById(item.rating),
						{
							type: 'bubble',
							data: data,
							options: {
								plugins: {
									tooltip: {
										callbacks: {
											label: function(context) {
												return 'Count: ' + context.raw.r + ' of ' + item.data.length;
											}
										}
									}
								},
								scales: {
									x: { // X-axis configuration
										type: 'linear', // Essential for numerical ranges
										position: 'bottom',
										suggestedMax: 5,
										suggestedMin: 1,
										ticks: {
											stepSize: 1, // Forces the ticks to be 1, 2, 3, etc.
											// or use precision: 0 as an alternative:
											// precision: 0 
										}
									},
									y: { // Y-axis configuration
										type: 'linear', // Essential for numerical ranges
										suggestedMax: 10,
										suggestedMin: -10,
										ticks: {
											display: false // This hides the numbers on the y-axis
										},
										grid: {
											display: false // This hides the grid lines for the y-axis
										}
									}
								}
							}
						}
					);
				});
			})
			.catch((error) => {
				console.log("Error: ");
				console.error(error);
			});
		} // End drawRatingsCharts

		// Gemini Summary fetch
		function fetchGeminiSummary(startVal, endVal, rangeVal) {
			fetch(bookwormAjax.url, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'get_gemini_summary',
					nonce: bookwormAjax.bookworm_thinking_nonce,
					bookshelf_start_date: startVal,
					bookshelf_end_date: endVal,
					//bookshelf_date_range: rangeVal
				})
			})
			.then(response => response.json())
			.then(data => {
				const summaryContent = document.getElementById('gemini-summary-content');
				if (data.success) {
					summaryContent.innerHTML = data.data + '</p><p class="small"><em>Summary generated by Google Gemini</em></p></div>';
					console.log(data.data);
				} else {
					summaryContent.innerHTML = '<div class="ai-summary"><p>Error: ' + data.data + '</p></div>';
				}
			})
			.catch(error => {
				const summaryContent = document.getElementById('gemini-summary-content');
				summaryContent.innerHTML = '<div class="ai-summary"><p>Error loading summary.</p></div>';
				console.error('Error:', error);
					console.log(data.data);
			});
		}

		// Book Analytics AJAX
		let bookAnalyticsForm = document.querySelector('#book_analytics_form');
		let dateRangeButton = document.querySelector('#dateRangeButton');
		let numberBooksFinished = document.querySelector('#number_books_finished');
		let analyticsTags = document.querySelector('#analytics_tags');
		let analyticsDateRangeLabel = document.querySelector('.analytics-date-range-label');
		let dateRangeSelect = document.querySelector('select[name="bookshelf_date_range"]');
		let selectedDateRange = dateRangeSelect.options[dateRangeSelect.selectedIndex].text;

		// If you choose the drop-down/select date range, fill in the input fields with the appropriate dates
		dateRangeSelect.addEventListener('change', function() {
			let selectedOptionValue = this.options[this.selectedIndex].value;
			console.log('Selected option value:', selectedOptionValue);
			if (selectedOptionValue === 'P12M') {
				// Past 12 months
				let endDate = new Date();
				let startDate = new Date();
				startDate.setFullYear(endDate.getFullYear() - 1);

				const formatDate = (date) => {
					const year = date.getFullYear();
					const month = String(date.getMonth() + 1).padStart(2, '0');
					const day = String(date.getDate()).padStart(2, '0');
					return `${year}-${month}-${day}`;
				};

				document.getElementById('bookshelf_start_date').value = formatDate(startDate);
				document.getElementById('bookshelf_end_date').value = formatDate(endDate);
			} else if (selectedOptionValue === 'current') {
				// Year-to-Date
				let endDate = new Date();
				let startDate = new Date(endDate.getFullYear(), 0, 1); // January 1st of current year

				const formatDate = (date) => {
					const year = date.getFullYear();
					const month = String(date.getMonth() + 1).padStart(2, '0');
					const day = String(date.getDate()).padStart(2, '0');
					return `${year}-${month}-${day}`;
				};

				document.getElementById('bookshelf_start_date').value = formatDate(startDate);
				document.getElementById('bookshelf_end_date').value = formatDate(endDate);
			}
		});

		if (dateRangeButton) {
			// Basic Analytics
			dateRangeButton.addEventListener("click", function(event) {
				event.preventDefault();
				loadingAnimation.classList.add('active');

				let bookshelfStartDate = document.querySelector('#bookshelf_start_date').value;
				let bookshelfEndDate = document.querySelector('#bookshelf_end_date').value;

				// Update the date range label
				if (bookshelfStartDate && bookshelfEndDate) {
					analyticsDateRangeLabel.innerText = `From ${bookshelfStartDate} to ${bookshelfEndDate}`;
				} else {
					analyticsDateRangeLabel.innerText = selectedDateRange;
				}
		
				var formData = new FormData(bookAnalyticsForm);
				formData.append("action", "book_analytics_query");
				
				fetch([bookwormAjax.url], {
					method: "POST",
					credentials: "same-origin",
					body: formData
				})
				.then((response) => response.text())
				.then((text) => {
					console.log(text);
					numberBooksFinished.innerHTML = text;
				})
				.then((data) => {
					if (data) {
						console.log(data);
						numberBooksFinished.innerHTML = data;
					}
					loadingAnimation.classList.remove('active');
					window.scrollTo({top: 0, behavior: 'smooth'});
				})
				.catch((error) => {
					console.log("Error: ");
					console.error(error);
				});
			});

			// Tags
			dateRangeButton.addEventListener("click", function(event) {
				event.preventDefault();
		
				var formData = new FormData(bookAnalyticsForm);
				formData.append("action", "book_analytics_tags_query");
				
				fetch([bookwormAjax.url], {
					method: "POST",
					credentials: "same-origin",
					body: formData
				})
				.then((response) => response.text())
				.then((text) => {
					console.log(text);
					analyticsTags.innerHTML = text;
				})
				.then((data) => {
					if (data) {
						console.log(data);
						analyticsTags.innerHTML = data;
					}
				})
				.catch((error) => {
					console.log("Error: ");
					console.error(error);
				});
			});

			// AI Notes Summary
			dateRangeButton.addEventListener("click", function(event) {
				event.preventDefault();
				let geminiSummaryContent = document.querySelector('#gemini-summary-content');
				geminiSummaryContent.innerHTML = `<div class="loading-spinner">
                     <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><circle cx="4" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce0" attributeName="cy" begin="0;svgSpinners3DotsBounce1.end+0.25s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="12" cy="12" r="3" fill="currentColor"><animate attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.1s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="20" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce1" attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.2s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle></svg>
                     <p>Generating AI summary...</p>
				</div>`;
		
				let bookshelf_start_date = document.querySelector('#bookshelf_start_date').value;
				let bookshelf_end_date = document.querySelector('#bookshelf_end_date').value;
				fetchGeminiSummary(bookshelf_start_date, bookshelf_end_date);
			});

			// Ratings
			dateRangeButton.addEventListener("click", function(event) {
				event.preventDefault();

				// Draw us some charts
				drawRatingsCharts();
			});
		}
	}, false);
</script>

<?php get_footer(); ?>