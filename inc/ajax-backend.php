<?php
add_action( 'wp_ajax_wdeditor_ajax_update_month_description', 'wdeditor_ajax_update_month_description' );

function wdeditor_ajax_update_month_description() {
	global $wpdb; // this is how you get access to the database

	$description = $_POST['description'];
	$month_id =  $_POST['month_id'];

	$update_month = $wpdb->update($wpdb->prefix . 'term_taxonomy', ['description' => $description], ['term_id' => $month_id], ['%s']);

	if($update_month === false) {
		echo 'The server encountered an error.';
	} else {
		echo 'The description has been updated.';
	}

	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_wdeditor_ajax_update_month_posts_order', 'wdeditor_ajax_update_month_posts_order' );

function wdeditor_ajax_update_month_posts_order() {
	global $wpdb; // this is how you get access to the database

	$posts = $_POST['posts'];
	$error = false;

	foreach ($posts as $id => $order) {

		$update = wp_update_post([
			'ID' => $id,
			'menu_order' => $order
		], true);

		if(is_wp_error($update)) {
			$error = true;
		}
	}

	if(!$error) {
		echo 'The order has been successfully updated.';
	} else {
		echo 'The server encountered an error in saving the order.';
	}

	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_wdeditor_ajax_add_year', 'wdeditor_ajax_add_year' );

function wdeditor_ajax_add_year() {
	global $wpdb; // this is how you get access to the database

	$year = $_POST['year'];

	$year_exists = term_exists($year, 'category');

	if($year_exists) {
		echo 'The year already exists.';
	} else {
		$add_year = wp_insert_term($year, 'category');	

		if($add_year === false) {
			echo 'The server encountered an error.';
		} else {
			echo 'The year has been added.';
		}
	}

	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_wdeditor_ajax_delete_year', 'wdeditor_ajax_delete_year' );

function wdeditor_ajax_delete_year() {
	global $wpdb; // this is how you get access to the database

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
	global $wpdb; // this is how you get access to the database

	$post_id = $_POST['post'];
	$post = get_post($post_id);
	$post_title = $post->post_title;

	if($post) {
		wp_delete_post($post_id);
	}
	
	echo 'The post "' . $post_title . '" has been deleted.';
	wp_die(); // this is required to terminate immediately and return a proper response
}