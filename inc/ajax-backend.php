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