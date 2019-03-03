<?php
	/**
	 * Add a 'back' link to the post editor page (post.php)
	 * Wasn't sure on the best way to do this so I'm doing it this way. Add the URL into the html and then dynamically move it with JS. I realise this could (and probably should) be better!
	 */
	$wdeditor_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	
	if (strpos($wdeditor_url, 'post.php') !== false) {
		$current_category_month = wp_get_post_categories($_GET['post'], ['fields' => 'all']);
	
		if(isset($current_category_month[0])) {
			$current_category_year = get_term_by('id', $current_category_month[0]->parent, 'category');

			$post_month = $current_category_month[0]->name;
			$post_year = $current_category_year->name;
		}

	    echo '<div class="wdeditor-back-to-link wdeditor-post-editor-back-link" style="display: none;"><a href="/wp-admin/admin.php?page=diary-editor&period=' . $post_month . '-' . strtolower($post_year) . '">Back to ' . $post_month . ', ' . $post_year . '</a></div>';
	}

	if (strpos($wdeditor_url, 'post-new.php') !== false) {
		$period = $_GET['period'];
		$period_arr = explode('-', $_GET['period']);

	    echo '<div class="wdeditor-back-to-link wdeditor-post-editor-back-link" style="display: none;"><a href="/wp-admin/admin.php?page=diary-editor&period=' . $period . '">Back to ' . ucwords($period_arr[0]) . ', ' . $period_arr[1] . '</a></div>';
	}
?>