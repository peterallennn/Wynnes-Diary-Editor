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
