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
		}
		.date-range-selector h5 {
			margin-bottom: 5px;
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
				<div class="flex gap-10 align-end">
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
			<div class="chart-label label-right">Easily Realist</div>
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
		console.log('test');
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

		console.log('test');
		// Draw us some charts
		<?php
			$ratingArray = array('rating_mood' => 'Mood', 'rating_language' => 'Language', 'rating_romance' => 'Romance', 'rating_suspension_disbelief' => 'Suspension of Disbelief');

			foreach ($ratingArray as $rating => $label) {
				
				$rating_data = $wpdb->get_col($wpdb->prepare(
					"SELECT $rating FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = %d AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01'",
					$wp_current_user_id
				));

				if ($rating == 'rating_mood') {
					$bg_color = 'rgba(219, 99, 255, 1)';
				} else if ($rating == 'rating_language') {
					$bg_color = 'rgba(99, 255, 187, 1)';
				} else if ($rating == 'rating_romance') {
					$bg_color = 'rgb(255, 99, 132)';
				} else if ($rating == 'rating_suspension_disbelief') {
					$bg_color = 'rgba(99, 167, 255, 1)';
				}
		?>
				var rating_data = <?php echo json_encode($rating_data); ?>;

				var frequencyMap = rating_data.reduce((accumulator, currentValue) => {
					// If the current number is already a key in the accumulator object, increment its count.
					// Otherwise, initialize its count to 1.
					accumulator[currentValue] = (accumulator[currentValue] || 0) + 1;
					return accumulator;
				}, {}); // The second argument {} is the initial value of the accumulator (an empty object).

				var data = {
					datasets: [{
						label: '<?php echo $label; ?>',
						data: Object.entries(frequencyMap).map(([key, value]) => ({
							x: parseInt(key),
							y: 0,
							r: value
						})),
						backgroundColor: '<?php echo $bg_color; ?>'
					}]
				};

				var bookChart = new Chart(
					document.getElementById('<?php echo $rating; ?>'),
					{
						type: 'bubble',
						data: data,
						options: {
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
									max: 0.25,
									min: -0.25,
									ticks: {
										display: false // This hides the numbers on the y-axis
									},
									// Optionally, you can also hide the grid lines or the axis line itself
									grid: {
										display: false // This hides the grid lines for the y-axis
									}
								}
							}
						}
					}
				);
		<?php
			} // end foreach
		?>

		// Load Gemini summary
		fetchGeminiSummary($bookshelf_start_date, $bookshelf_end_date);

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

		if (dateRangeButton) {
			dateRangeButton.addEventListener("click", function(event) {
				event.preventDefault();
				loadingAnimation.classList.add('active');
		
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
		}
	}, false);
</script>

<?php get_footer(); ?>