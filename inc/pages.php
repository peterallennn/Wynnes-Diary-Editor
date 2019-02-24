<?php
	function wdeditor_diary_page()
	{
		if(isset($_GET['period'])) {
			/**
			 * Display the 'diary-month' edit page 
			 */

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
			
			$month_posts = get_posts(['posts_per_page' => -1, 'category' => $month->term_id, 'orderby' => 'menu_order', 'order' => 'ASC']);
			//print_r($month_posts);

			include PLUGIN_DIR_PATH . '/partials/_diary-month.php';
			return true;

		}

		if(isset($_GET['action']) && $_GET['action'] == 'new-post' || isset($_GET['action']) && $_GET['action'] == 'edit-post') {
			/**
			 * Display the 'diary-post' edit page
			 */
			$editing = false;

			if(isset($_GET['post'])) {
				$post = get_post($_GET['post']);
				$editing = true;
			}

			include PLUGIN_DIR_PATH . '/partials/_diary-post.php';
			return true;
		}

		/**
		 * Otherwise, display the diary page with a grid of all years
		 */
		$years = get_terms([
			'taxonomy' => 'category',
			'hide_empty' => false,
			'exclude' => 1 // Exclude 'uncategorised'
		]);
		
		include PLUGIN_DIR_PATH . '/partials/_diary.php';
	}
