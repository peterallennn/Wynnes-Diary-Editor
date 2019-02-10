<?php
	function wdeditor_diary_page()
	{
		// If month GET variable does not exist, display the main diary page with the years/months
		if(!isset($_GET['period'])) {

			$years = get_terms([
				'taxonomy' => 'category',
				'hide_empty' => false,
				'exclude' => 1 // Exclude 'uncategorised'
			]);
			
			include PLUGIN_DIR_PATH . '/partials/_diary.php';

		} else { // If the month GET variable exists, display the month and it's posts

			$period = explode('-', $_GET['period']);
			$month_name = $period[0];
			$year_name = $period[1];

			// First double check that the month exists
			$month = get_term_by('slug', $_GET['period'], 'category');

			$year = get_term_by('name', $year_name, 'category');

			if(!$month) {
				// It doesn't so we need to create the month category
				wp_insert_term(ucfirst($month_name), 'category', ['parent' => $year->term_id, 'slug' => $month_name . '-' . $year_name]);
				$month = get_term_by('slug', $_GET['period'], 'category');
			} 
			
			$month_posts = get_posts(['posts_per_page' => -1, 'category' => $month->term_id, 'orderby' => 'order']);

			include PLUGIN_DIR_PATH . '/partials/_diary-month.php';

		}
	}
