<?php
	/**
	 * Add a 'back' link to the post editor page (post.php)
	 * Wasn't sure on the best way to do this so I'm doing it this way. Add the URL into the html and then dynamically move it with JS. I realise this could (and probably should) be better!
	 */
	$wdeditor_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	if (strpos($wdeditor_url, 'post.php') !== false && isset($_GET['post'])) {
		$category = wp_get_post_categories($_GET['post'], ['fields' => 'all']);
		if(isset($category[0]) && $category[0]->parent == 259) { // Sidebar
			echo '<span class="sidebar-post-category" style="display:none;">' . $category[0]->term_id . '</span>'; 
			echo '<div class="wdeditor-back-to-link wdeditor-post-editor-back-link" style="display: none;"><a href="/wp-admin/admin.php?page=diary-editor&sidebar=' . $category[0]->term_id . '">Back to ' . $category[0]->name . '</a></div>';
		} elseif(!empty($category)) {
			$current_category_year = get_term_by('id', $category[0]->parent, 'category');
			if($current_category_year) {
				$post_month = $category[0]->name;
				$post_year = $current_category_year->name;
				echo '<span class="post-month" style="display:none;">' . $post_month . '</span><span class="post-year" style="display:none;">' . $post_year . '</span>';
		    	echo '<div class="wdeditor-back-to-link wdeditor-post-editor-back-link" style="display: none;"><a href="/wp-admin/admin.php?page=diary-editor&period=' . $post_month . '-' . strtolower($post_year) . '">Back to ' . $post_month . ', ' . $post_year . '</a></div>';
		   	}
	    }
	}
	if (strpos($wdeditor_url, 'post-new.php') !== false) {
		if(isset($_GET['period'])) {
			$period = $_GET['period'];
			$period_arr = explode('-', $_GET['period']);
		    echo '<div class="wdeditor-back-to-link wdeditor-post-editor-back-link" style="display: none;"><a href="/wp-admin/admin.php?page=diary-editor&period=' . $period . '">Back to ' . ucwords($period_arr[0]) . ', ' . $period_arr[1] . '</a></div>';
		}
		if(isset($_GET['sidebar'])) {
			$category = get_term_by('id', $_GET['sidebar'], 'category');
			echo '<div class="wdeditor-back-to-link wdeditor-post-editor-back-link" style="display: none;"><a href="/wp-admin/admin.php?page=diary-editor&sidebar=' . $category->term_id . '">Back to ' . $category->name . '</a></div>';
		}
	}
?>