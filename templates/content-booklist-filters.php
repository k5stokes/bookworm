
<a class="button button-secondary filters-toggle-button" aria-expanded="false" aria-controls="book_filter_form" href="#book_filter_form">
	<span class="filters-toggle-text">Filter Books</span>
	<span class="filters-toggle-icon-wrapper">
		<?php echo file_get_contents( get_stylesheet_directory() . '/img/iconb_filters.svg'); ?>
	</span>
</a>
<div class="book-filters-panel">
	<a class="close-button filters-panel-close" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
	<form id="book_filter_form" class="bookshelf-filter-form filters-wrapper">	
		<?php 
			$all_book_tags = array();
			// Because this is a template part, we're passing $book_entries from the previous template as $args
			foreach ($args['book_entries'] as $book_entry) {
				if ($book_entry->tags != NULL && $book_entry->tags != '' && !empty($book_entry->tags)) {
					$book_entry_tags = explode(',', $book_entry->tags);
					//$book_entry_tags = json_decode(stripslashes($book_entry->tags), true);
					//print_r($book_entry_tags);
					$all_book_tags = array_merge($all_book_tags, $book_entry_tags);
				} 
			}
			if (!empty($all_book_tags)) {
				$all_book_tags = array_unique($all_book_tags);
		?>	
		<div class="bookshelf-filter tag-filter">
			<!--
				<div class="filter-button">
					<label id="filter_button" aria-hidden="false" for="bookshelf-tag-filters">Filter by:</label> 
				</div>
				<div id="filter_wrapper" class="filter-wrapper">
			-->

			<h4>Filter by:</h4>
			<!--
				<select id="bookshelf_tag_filters" name="bookshelf_tag_filters">
					<option value="">Filter by:</option>
			-->
			<div class="checkbox-filters-wrapper">
				<h5>Tags</h5>
	<?php
			foreach ($all_book_tags as $tagID) {
				$tag = get_tag($tagID);
				/* Checkboxes */
				echo '<label><input type="checkbox" name="bookshelf_tag_filters[]" value="' . $tag->term_id . '">' . $tag->name . '</label>';
				/* Select/Options, if we go back to that */
				//echo '<option value="' . $tag->term_id . '">' . $tag->name . '</option>';
			}
	?>
			</div>
	<?php
			if ($args['bookshelf_category'] != 'wishlist') {
	?>
			<div class="checkbox-filters-wrapper">
				<h5>Date Read</h5>
				<label><input type="checkbox" name="bookshelf_tag_filters[]" value="current">Year-to-Date</label>
				<label><input type="checkbox" name="bookshelf_tag_filters[]" value="P3M">Past 3 Months</label>
				<label><input type="checkbox" name="bookshelf_tag_filters[]" value="P6M">Past 6 Months</label>
				<label><input type="checkbox" name="bookshelf_tag_filters[]" value="P12M">Past Year</label>
			</div>
				<!-- 
					<option value="current">Year-to-Date</option>
					<option value="P3M">Past 3 Months</option>
					<option value="P6M">Past 6 Months</option>
					<option value="P12M">Past Year</option>
				-->
			<?php } ?>
			<!-- </select> -->
		</div>
		<?php }	?>
		
		<div class="bookshelf-filter sorter">
			<h4>Sort By:</h4>
			<div class="checkbox-filters-wrapper">
				<div class="flex align-items-end">	
					<div class="filter-button">
						<label aria-hidden="false" style="display: none;" for="bookshelf-sort-select">Sort by:</label> 
						<select id="bookshelf-sort-select" name="bookshelf-sort-select">
							<option value="">Sort by:</option>
							<?php if ($args['bookshelf_category'] == 'finished') { ?>
								<option value="date_finished">Date finished</option>
							<?php } else { ?>
								<option value="id">Date added</option>
							<?php } ?>
							<option value="author">Author</option>
							<option value="title">Title</option>
							<?php if ($args['bookshelf_category'] != 'wishlist') { ?> <option value="date_started">Date started</option><?php } ?>
						</select>
					</div>
					<div class="sort-button">
						<div class="sort-button-icon-wrapper flex align-items-center">
							<?php echo file_get_contents( get_stylesheet_directory() . '/img/iconb_arrows.svg'); ?>
						</div>
					</div>
					<input type="hidden" name="bookshelf_sorting" value="ASC" />
					<input type="hidden" name="bookshelf_category" value="<?php echo $args['bookshelf_category']; ?>" />
					<input type="hidden" name="wp_current_user_id" value="<?php echo $args['wp_current_user_id']; ?>" />
				</div>
			</div>
		</div>
		<input id="filterButton" type="submit" class="button button-primary apply-filters-button" value="View Books" />
	</form>
</div>