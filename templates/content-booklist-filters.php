<form id="book_filter_form" class="bookshelf-filter-form filters-wrapper flex justify-space-between align-items-center">	
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
		<label aria-hidden="false" style="display: none;" for="bookshelf-tag-filters">Filter by:</label>
		<select id="bookshelf_tag_filters" name="bookshelf_tag_filters">
			<option value="">Filter by:</option>
<?php
		foreach ($all_book_tags as $tagID) {
			$tag = get_tag($tagID);
			/* Checkboxes, if we go back to that */
			// echo '<label><input type="checkbox" name="bookshelf_tag_filters[]" value="' . $tag->term_id . '">' . $tag->name . '</label>';
			echo '<option value="' . $tag->term_id . '">' . $tag->name . '</option>';
		}
?>
			<option value="current">Year-to-Date</option>
			<option value="3">Past 3 Months</option>
			<option value="6">Past 6 Months</option>
			<option value="12">Past Year</option>
		</select>
	</div>
	<?php }	?>
	
	<div class="bookshelf-filter sorter">	
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
					<!-- <option value="published_date">Date published</option> -->
					<?php if ($args['bookshelf_category'] != 'wishlist') { ?> <option value="date_started">Date started</option><?php } ?>
					<!-- <option value="fiction_or_non">Fiction/Nonfiction</option> -->
				</select>
			</div>
			<div class="sort-button">
				<div class="sort-button-icon-wrapper flex align-items-center">
					<?php echo file_get_contents( get_stylesheet_directory() . '/img/iconb_arrows.svg'); ?>
				</div>
			</div>
			<input type="hidden" name="bookshelf_sorting" value="DESC" />
			<input type="hidden" name="bookshelf_category" value="<?php echo $args['bookshelf_category']; ?>" />
			<input type="hidden" name="wp_current_user_id" value="<?php echo $args['wp_current_user_id']; ?>" />
		</div>
	</div>
</form>