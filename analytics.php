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
	</style>

	<div class="bookshelf-heading-filter-wrapper flex justify-space-between align-items-center">
		<h3><?php echo get_the_title(); ?></h3>
	</div>
	
	<section>
		<div class="icon-wrapper icon-medium full-width bg-lt-blue">
			<div class="icon-background">
				<?php echo file_get_contents( get_stylesheet_directory() . '/img/friends.svg'); ?>
			</div>
			<h4>By the Numbers</h4>
		</div>
		<div id="tagsList" class="tags-list">
			<h4 class='title'>Number of finished books in each category:</h4>
			<?php
				$all_book_tags = array();
				$book_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = '$wp_current_user_id' AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01' ORDER BY date_finished DESC");
				foreach ($book_entries as $book_entry) {
					if ($book_entry->tags != NULL && $book_entry->tags != '' && !empty($book_entry->tags)) {
						$book_entry_tags = explode(',', $book_entry->tags);
						//$book_entry_tags = json_decode(stripslashes($book_entry->tags), true);
						$all_book_tags = array_merge($all_book_tags, $book_entry_tags);
					} 
				}
				if (!empty($all_book_tags)) {
					$all_book_tags = array_unique($all_book_tags);
			?>
				<div class="tags-wrapper">
					<ul>
			<?php
					foreach ($all_book_tags as $tagID) {
						$tag = get_tag($tagID);
						$book_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = %d AND date_finished IS NOT NULL AND date_finished != '0000-00-00' AND date_finished != '1970-01-01' AND FIND_IN_SET(%d, tags)", $wp_current_user_id, $tagID));

			?>
					<div class="tag-count-wrapper">
						<li class="tag-entry active <?php echo $tag->slug; ?>" data-id="<?php echo $tag->slug; ?>"><?php echo $tag->name; ?></li>
						<span class="book-count"><?php echo $book_count; ?></span>
					</div>
			<?php
					}
			?>
					</ul>
				</div>
			<?php
				}
			?>
		</div> <!-- close tags-list -->

		<div id="timePeriodsList" class="time-periods-list">
			<h4 class='title'>Number of finished books in each time period:</h4>
			<div class="flex gap-20 text-center">
			<?php
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
			?>
		</div>
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
	
	<!--
	<section>
		<h2>Book Clubs</h2>
		<p>Coming soon!</p>
	</section>
	-->
	
	<?php // get_template_part('templates/content', 'side-nav'); ?>

</main>
</div> <!-- close wrapper -->

<?php get_template_part('templates/content', 'footer-nav'); ?>

<div id="popup" class="popup-wrapper">
	<div class="popup popup-large">
		<a class="close-button popup-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
		<div id="popupContainer"></div>
		<p><a id="close-popup" href="#" class="popup-link popup-close">Add Another Friend?</a></p>
	</div>
</div>

<div class="loading-animation">
	<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><circle cx="4" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce0" attributeName="cy" begin="0;svgSpinners3DotsBounce1.end+0.25s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="12" cy="12" r="3" fill="currentColor"><animate attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.1s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="20" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce1" attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.2s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle></svg>
</div>

<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function () {
		let loadingAnimation = document.querySelector('.loading-animation');
		let popupNextStep = document.querySelector('#popup');
		let validationError = document.getElementById('validation_error');

		// Load Gemini summary
		fetchGeminiSummary();

		function fetchGeminiSummary() {
			fetch(bookwormAjax.url, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'get_gemini_summary',
					nonce: bookwormAjax.bookworm_thinking_nonce
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
	}, false);
</script>

<?php get_footer(); ?>