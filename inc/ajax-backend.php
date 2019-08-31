<?php
add_action( 'wp_ajax_wdeditor_ajax_update_category_description', 'wdeditor_ajax_update_category_description' );
function wdeditor_ajax_update_category_description() {
	global $wpdb;

	$description = stripslashes($_POST['description']);
	$category_id =  $_POST['category_id'];
	$update_category = $wpdb->update($wpdb->prefix . 'term_taxonomy', ['description' => $description], ['term_id' => $category_id], ['%s']);
	if($update_category === false) {
		echo 'The server encountered an error.';
	} else {
		echo 'The description has been updated.';
	}
	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action( 'wp_ajax_wdeditor_ajax_update_month_posts_order', 'wdeditor_ajax_update_month_posts_order' );
function wdeditor_ajax_update_month_posts_order() {
	global $wpdb;

	$posts = $_POST['posts'];
	$error = false;
	foreach ($posts as $id => $order) {
		$order += 1;
		$post = get_post($id);
		$data = [
			'ID' => $id,
			'menu_order' => $order
		];
		//if(strpos($post->post_title, '') !== false) {
			$new_title = 'Viewer ' . $order;
			$new_slug = str_replace('viewer-' . $post->menu_order, 'viewer-' . $order, $post->post_name);
			$data['post_title'] = $new_title;
			$data['post_name'] = $new_slug;
		//}
		//$update = wp_update_post($new, true);
		//$update = $wpdb->update('wd_posts', $data, ['ID' => $id]);
		$wpdb->query($wpdb->prepare('UPDATE ' . $wpdb->prefix . 'posts SET menu_order = %d, post_title = %s, post_name = %s WHERE id = %d', $order, $new_title, $new_slug, $id));
		if(is_wp_error($update)) {
			$error = true;
		}
	}
	if(!$error) {
		echo 'The order has been successfully updated. Note: Posts with \'Viewer\' in the title will also be renamed based on the order within the strip. After clicking OK, you will see this change.';
	} else {
		echo 'The server encountered an error in saving the order.';
	}
	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action( 'wp_ajax_wdeditor_ajax_add_year', 'wdeditor_ajax_add_year' );
function wdeditor_ajax_add_year() {
	global $wpdb;

	$year = $_POST['year'];
	$year_exists = term_exists($year, 'category');
	if($year_exists) {
		echo 'The year already exists.';
	} else {
		$add_year = wp_insert_term($year, 'category');	
		if(is_wp_error($add_year)) {
			echo 'The server encountered an error: ' . $add_year->get_error_message();
		} else {
			echo 'The year has been added.';
		}
	}
	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action( 'wp_ajax_wdeditor_ajax_delete_year', 'wdeditor_ajax_delete_year' );
function wdeditor_ajax_delete_year() {
	global $wpdb;

	$year_id = $_POST['year'];
	// Get the year
	$year = get_term_by('id', $year_id, 'category');
	$year_name = $year->name;
	// Get the child months
	$child_months = get_categories(['hide_empty' => 0, 'parent' => $year_id]);
	// Loop through the months and check whether each month has a post
	foreach ($child_months as $month) {
		// If a month has posts, then delete the posts
		if($month->count > 0) {
			$posts = get_posts(['post_per_page' => -1, 'category' => $month->term_id]);
			foreach ($posts as $post) {
				wp_delete_post($post->ID);
			}
		}
		// Also delete this month
		wp_delete_category($month->term_id);
	}
	// Finally, delete the year
	wp_delete_category($year_id);
	echo 'The year "' . $year_name . '" has been deleted.';
	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action( 'wp_ajax_wdeditor_ajax_delete_post', 'wdeditor_ajax_delete_post' );
function wdeditor_ajax_delete_post() {
	global $wpdb;

	$post_id = $_POST['post'];
	$post = get_post($post_id);
	$post_title = $post->post_title;
	if($post) {
		wp_delete_post($post_id);
	}
	echo 'The post "' . $post_title . '" has been deleted.';
	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_wdeditor_ajax_hide_year', 'wdeditor_ajax_hide_year' );
function wdeditor_ajax_hide_year() {
	global $wpdb;

	$year = $_POST['year'];

	$hidden_years = get_option('wdeditor_hidden_years');

	if(!$hidden_years) {
		$hidden_years_arr = array($year);
		$add = add_option('wdeditor_hidden_years', json_encode($hidden_years_arr));

		if($add) {
			echo 'The year has been hidden from the public.';
		} else {
			echo 'The server encountered an error whilst attempting to hide the year. Please try again.';
		}

		wp_die();
	}

	// Get existing years
	$hidden_years_arr = json_decode($hidden_years, true);

	// Add to existing years
	if(!in_array($year, $hidden_years_arr)) {
		$hidden_years_arr[] = $year;

		$update = update_option('wdeditor_hidden_years', json_encode($hidden_years_arr));

		if($update) {
			echo 'The year has been hidden from the public. Note; Whilst logged in as an admin, the month will still be visible within the diary. This will only affect public users or in other words, users that are not logged in to the WordPress admin.';
		} else {
			echo 'The server encountered an error whilst attempting to hide the year. Please try again.';
		}
	} else {
		echo 'This year is already hidden from the public.';
	}

	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_wdeditor_ajax_show_year', 'wdeditor_ajax_show_year' );
function wdeditor_ajax_show_year() {
	global $wpdb;

	$year = $_POST['year'];

	$hidden_years = json_decode(get_option('wdeditor_hidden_years'), true);

	if(in_array($year, $hidden_years)) {
		// Remove year from the array
		foreach (array_keys($hidden_years, $year, true) as $key) {
		    unset($hidden_years[$key]);
		}

		$update = update_option('wdeditor_hidden_years', json_encode($hidden_years));

		if($update) {
			echo 'The year is now being shown to the public.';
		} else {
			echo 'The server encountered an error whilst attempting to hide the year. Please try again.';
		}
	} else {
		echo 'The year is already being shown to the public.';
	}

	wp_die();
}

add_action( 'wp_ajax_wdeditor_ajax_hide_month', 'wdeditor_ajax_hide_month' );
function wdeditor_ajax_hide_month() {
	global $wpdb;

	$month = $_POST['month'];

	$hidden_months = get_option('wdeditor_hidden_months');

	if(!$hidden_months) {
		$hidden_months_arr = array($month);
		$add = add_option('wdeditor_hidden_months', json_encode($hidden_months_arr));

		if($add) {
			echo 'The month has been hidden from the public.';
		} else {
			echo 'The server encountered an error whilst attempting to hide the month. Please try again.';
		}

		wp_die();
	}

	// Get existing months
	$hidden_months_arr = json_decode($hidden_months, true);

	// Add to existing months
	if(!in_array($month, $hidden_months_arr)) {
		$hidden_months_arr[] = $month;

		$update = update_option('wdeditor_hidden_months', json_encode($hidden_months_arr));

		if($update) {
			echo 'The month has been hidden from the public.';
		} else {
			echo 'The server encountered an error whilst attempting to hide the month. Please try again.';
		}
	} else {
		echo 'This month is already hidden from the public.';
	}

	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_wdeditor_ajax_show_month', 'wdeditor_ajax_show_month' );
function wdeditor_ajax_show_month() {
	global $wpdb;

	$month = $_POST['month'];

	$hidden_months = json_decode(get_option('wdeditor_hidden_months'), true);

	if(in_array($month, $hidden_months)) {
		// Remove month from the array
		foreach (array_keys($hidden_months, $month, true) as $key) {
		    unset($hidden_months[$key]);
		}

		$update = update_option('wdeditor_hidden_months', json_encode($hidden_months));

		if($update) {
			echo 'The month is now being shown to the public.';
		} else {
			echo 'The server encountered an error whilst attempting to hide the month. Please try again.';
		}
	} else {
		echo 'The month is already being shown to the public.';
	}

	wp_die();
}