<?php
	/* Template Name: Notes Page Template */
	if (!is_user_logged_in()) {
		//auth_redirect();
		$url = get_site_url() . '/log-in/';
		wp_safe_redirect( $url );
	}
	get_header();
	get_template_part('templates/content', 'inner-header');
	
	global $post;
	$wp_current_user_id = get_current_user_id();

	$bookshelf_category = "notes";
	
	$book_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bookworm_books WHERE user_id_shelf = '$wp_current_user_id' AND (notes IS NOT NULL AND notes != '' AND notes != 'Enter your notes for this book.') ORDER BY date_finished DESC");
?>

<main id="main" role="main" class="bookshelf">

	<div class="bookshelf-heading-filter-wrapper flex justify-space-between align-items-center">
		<h3><?php echo get_the_title(); ?> (<?php echo count($book_entries); ?>)</h3>
		
		<?php get_template_part('templates/content', 'booklist-filters', $book_entries); ?>
		
	</div>

	<section class="bookshelf" id="bookshelf">
		<?php 
			
			if (empty($book_entries)) { ?>
				<div class="user-message"><p>You don't currently have any books here. Want to <a href="/add-book/">add one</a>?</p></div>
			<?php } else {
			foreach ($book_entries as $book_entry) {
				$book_entry_published_date = strtotime($book_entry->published_date);
				$book_entry_published_date = date('j F Y',$book_entry_published_date);
				$book_entry_title = stripslashes($book_entry->title);
				$book_entry_notes = stripslashes($book_entry->notes);
				$notes_word_count = str_word_count($book_entry_notes);
				if ($book_entry->description != '') {
					$book_entry_description = stripslashes($book_entry->description);
					if (preg_match('/^.{1,60}\b/s', $book_entry_description, $match)) {
					    $book_description_excerpt = $match[0] . "...";
						$description_word_count = str_word_count($book_entry_description);
					}
				}
				// echo "word count: " . $notes_word_count;
		?>
			
			<div class="book-entry flex wrap gap-10">
				<div class="book-entry-meta">
					<a class="book-entry-edit-link flex" href="/update-book/?id=<?php echo $book_entry->id; ?>">
						<div class="book-entry-thumb">
							<img class="book_entry_img" src="<?php echo $book_entry->small_thumbnail_url;?>">
						</div>
						<div class="book-entry-details">
							<div id="book_entry_title" class="book-entry-title"><?php echo $book_entry_title;?></div>
							<div id="book_entry_author" class="book-entry-author"><?php echo $book_entry->author;?></div>
							<!-- <div id="book_entry_pub_date" class="book-entry-publication-date">published <?php // echo $book_entry_published_date;?></div> -->					<?php if ($book_entry->description != '') { ?>
								<div id="book_entry_description" class="book_entry_description"><?php echo $book_description_excerpt; ?></div>
							<?php } ?>
							<?php if ($book_entry->fiction_or_non != NULL && $book_entry->fiction_or_non != '') { ?>
								<div id="book_entry_genre" class="book-entry-genre"><?php echo ucfirst($book_entry->fiction_or_non); ?></div>
							<?php } ?>
						</div>
					</a>
				</div>
				<?php // if ($book_entry->description != '') { ?>
					<!-- <div id="book_entry_description" class="book-entry-notes <?php if ($description_word_count > 40) { echo 'overage'; } ?>">
						<a class="popup-trigger" href="#popup_description_<?php echo $book_entry->id; ?>">
							<strong>Google Books Description:</strong> <?php echo $book_entry_description; ?>	
						</a>
					</div>
					<div id="popup_description_<?php echo $book_entry->id; ?>" class="popup-wrapper popup-description">
						<div class="popup popup-large">
							<a class="close-button popup-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
							<p><strong>Google Books Description:</strong> <?php echo $book_entry_description; ?></p>
						</div>
					</div> -->
				<?php // } ?>
				<div id="book_entry_notes" class="book-entry-notes <?php if ($notes_word_count > 40) { echo 'overage'; } ?>">
					<a class="popup-trigger" href="#popup_notes_<?php echo $book_entry->id; ?>">
						<strong>My Notes: </strong><?php echo $book_entry_notes; ?>	
					</a>
				</div>	
				<div id="popup_notes_<?php echo $book_entry->id; ?>" class="popup-wrapper popup-notes">
					<div class="popup popup-large">
						<a class="close-button popup-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
						<p><strong>My Notes: </strong><?php echo $book_entry_notes;?></p>
						<p class="margin-bottom-0"><a href="/update-book/?id=<?php echo $book_entry->id; ?>" class="popup-link">Edit</a></p>
					</div>
				</div>
			</div>
		<?php
				}
			}
		?>

	</section>
	<?php // get_template_part('templates/content', 'side-nav'); ?>

</main>
</div> <!-- close wrapper -->

<?php get_template_part('templates/content', 'footer-nav'); ?>

<div class="loading-animation">
	<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><circle cx="4" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce0" attributeName="cy" begin="0;svgSpinners3DotsBounce1.end+0.25s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="12" cy="12" r="3" fill="currentColor"><animate attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.1s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="20" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce1" attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.2s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle></svg>
</div>

<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function () {
		let bookFilterForm = document.getElementById('book_filter_form');
		let bookFilterSelect = document.getElementById('bookshelf-filter-select');
		let bookshelf = document.getElementById('bookshelf');
		let loadingAnimation = document.querySelector('.loading-animation');
		let bookSortingButton = document.querySelector('.sort-button');
		let bookshelfSortingInput = document.querySelector('input[name="bookshelf_sorting"]');

		bookFilterSelect.addEventListener('change', function(event) {
			
			loadingAnimation.classList.add('active');
			var formData = new FormData(bookFilterForm);
			
			formData.append("action", "book_list");
			
			fetch([bookwormAjax.url], {
				method: "POST",
				credentials: "same-origin",
				body: formData
			})
			.then((response) => response.text())
			.then((text) => {
				console.log(text);
				bookshelf.innerHTML = text;
			})
			.then((data) => {
				if (data) {
					console.log(data);
					bookshelf.innerHTML = data;
				}
				loadingAnimation.classList.remove('active');
				window.scrollTo({top: 0, behavior: 'smooth'});
			})
			.catch((error) => {
				console.log("Error: ");
				console.error(error);
			});
		})
		
		bookSortingButton.addEventListener('click', function(e){
			//e.preventDefault();
			if (bookshelfSortingInput.value == 'ASC') {
				bookshelfSortingInput.value = 'DESC';
			} else {
				bookshelfSortingInput.value = 'ASC';
			}
			loadingAnimation.classList.add('active');
			var formData = new FormData(bookFilterForm);
			//formData.append("bookshelf_sorting", bookshelfSortingInput.value);
			formData.append("action", "book_list");
			
			fetch([bookwormAjax.url], {
				method: "POST",
				credentials: "same-origin",
				body: formData
			})
			.then((response) => response.text())
			.then((text) => {
				//console.log(text);
				bookshelf.innerHTML = text;
			})
			.then((data) => {
				if (data) {
					//console.log(data);
					bookshelf.innerHTML = data;
				}
				loadingAnimation.classList.remove('active');
				window.scrollTo({top: 0, behavior: 'smooth'});
			})
			.catch((error) => {
				console.log("Error: ");
				console.error(error);
			});
		})
		
		let popupClose = document.querySelectorAll('.popup-close');
		let popupNotesTriggers = document.querySelectorAll('.popup-trigger');
		
		popupNotesTriggers.forEach((popupNotesTrigger) => {
			popupNotesTrigger.addEventListener('click', function(e) {
				e.preventDefault();
				
				let popupId = e.target.getAttribute('href');
				console.log('click ' + popupId);
				document.querySelector(popupId).classList.add('active');
			})
		})

		popupClose.forEach((popupCloser) => {
			popupCloser.addEventListener('click', function(e){
				e.preventDefault();
				/*popupCloser.classList.remove('active');*/
				e.target.closest('.popup-wrapper').classList.remove('active');
			});
		})
	}, false);
</script>

<?php get_footer(); ?>