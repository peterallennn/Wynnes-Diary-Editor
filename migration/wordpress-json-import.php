<?php
	require '../../../../wp-load.php';
	require '../../../../wp-admin/includes/media.php';
	require '../../../../wp-admin/includes/file.php';
	require '../../../../wp-admin/includes/image.php';

	global $wpdb; 

	$json_file = file_get_contents('data.json');
	$data = json_decode($json_file, true);

	foreach ($data as $year_name => $years) {

		// if($year_name != '1897') {
		// 	continue;
		// }
	

		// First create year category if it doesn't already exist
		$year_exists = term_exists($year_name, 'category');

		if(!$year_exists) {
 			wp_insert_term($year_name, 'category');	
		}

		$year = get_term_by('name', $year_name, 'category');

		// Loop through the months within year
		foreach ($years as $month_name => $month_data) {		
			// Create the month as a category within the year
			// Assign the description for the month
			$month_slug = $month_name . '-' . $year_name;
			$month_description = isset($month_data['description']) ? $month_data['description'] : '';

			$month = get_term_by('slug', $month_slug, 'category');

			if(!$month) {
				wp_insert_term(ucfirst($month_name), 'category', ['parent' => $year->term_id, 'slug' => $month_slug, 'description' => $month_description]);
				$month = get_term_by('slug', $month_slug, 'category');
			}

			// Loop through the viewers within the month
			foreach($month_data['viewers'] as $viewer) {
				$description = preg_replace('/<p[^>]*><\\/p[^>]*>/', '', $viewer['description']);

				$slug = sanitize_text_field($year_name) . '-' . sanitize_text_field(strtolower($month_name)) . '-' . sanitize_title($viewer['title']);

				// Create each viewer as a post			
				$post_id = wp_insert_post([
					'post_title' => $viewer['title'],
					'post_name' => $slug,
					'post_excerpt' => (isset($viewer['excerpt']) ? $viewer['excerpt'] : ''),
					'post_content' => $description,
					'menu_order' => $viewer['order'],
					'post_status' => 'publish'
				], true);

				wp_set_post_categories($post_id, [$month->term_id]);

				if(isset($viewer['featured_image'])) {
					// Assign featured image to post
					$image_id = media_sideload_image(str_replace('//wynnesdiary.com', 'http://peterallen.me', $viewer['featured_image']), $post_id, null, 'id');

					set_post_thumbnail($post_id, $image_id);

				}

				$wpdb->query( "
		            UPDATE
		                `" . $wpdb->posts . "`
		            SET
		                `post_name` = '" . $slug . "'
		            WHERE
		                `ID` = '" . $post_id . "'
		            LIMIT 1
		        " );
			}

			if(isset($month_data['placeholder'])) {
				echo '<h1>' . $year_name . '</h1>';
				echo '<h3>' . ucwords($month_name) . '</h3>';

				echo '<h4>Errors</h4>';
				
				echo '<pre>';
				print_r($month_data['errors']);
				echo '</pre>';
			
				echo '<hr/>';
			}
		}

	}
?>