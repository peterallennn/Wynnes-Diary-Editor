<?php
/** 
 *
 * The following logic adds modifications to the 'post-new.php' form
 *
 */

//If the post content, title and excerpt are empty WordPress will prevent the insertion of the post. You can trick WordPress by first filtering the input array so empty values are set to something else, and then later resetting these values back to empty strings. This will bypass the standard check.
add_filter('pre_post_title', 'wpse28021_mask_empty');
add_filter('pre_post_content', 'wpse28021_mask_empty');
function wpse28021_mask_empty($value)
{
    if ( empty($value) ) {
        return ' ';
    }
    return $value;
}

add_filter('wp_insert_post_data', 'wpse28021_unmask_empty');
function wpse28021_unmask_empty($data)
{
    if ( ' ' == $data['post_title'] ) {
        $data['post_title'] = '';
    }
    if ( ' ' == $data['post_content'] ) {
        $data['post_content'] = '';
    }
    return $data;
}

// Remove fields from the form
// - Thumbnail
function posteditor_handle_post_form_fields()
{
	$type = 'post';
	add_post_type_support('post', 'post-formats');
}
add_action('init', 'posteditor_handle_post_form_fields');
/**
 * Add custom date field
 */
// Add a meta box containing the field above the editor
function posteditor_date_categories_add_metabox()
{
	if(isset($_GET['post'])) {
		$category = wp_get_post_categories($_GET['post'], ['fields' => 'all']);
	}

	if(isset($_GET['sidebar']) || isset($category[0]) && $category[0]->parent == 259) {	// Sidebar, don't add date metabox
		return true;
	}

	if(isset($_GET['period']) || isset($_GET['post'])) {
		add_meta_box('posteditor_date_categories_metabox', 'Date', 'posteditor_date_categories_metabox_content', 'post', 'side', 'high');
	}
}
function posteditor_date_categories_metabox_content()
{
	global $post;

	if(isset($_GET['period']) || isset($_GET['post'])) {
		echo wp_nonce_field( 'posteditor_date_categories_metabox', 'posteditor_date_categories_metabox_nonce' );
		// Default month/year
		$timestamp = new DateTime();
		$post_month =$timestamp->format('F');
		$post_year = $timestamp->format('Y');
		if(isset($_GET['period'])) {
			$period = explode('-', $_GET['period']);
			$post_month = ucwords($period[0]);
			$post_year = $period[1];
		}
		$current_category_month = wp_get_post_categories($post->ID, ['fields' => 'all']);
		if(isset($current_category_month[0])) {
			$current_category_year = get_term_by('id', $current_category_month[0]->parent, 'category');
			$post_month = $current_category_month[0]->name;
			$post_year = $current_category_year->name;
		}
		ob_start();
		require(PLUGIN_DIR_PATH . 'partials/fields/category.php');
		$date_metabox_content = ob_get_clean();
		echo $date_metabox_content;
	}
}
add_action( 'add_meta_boxes', 'posteditor_date_categories_add_metabox' );
// Functionality to save the post to the specified year/month categories
add_action('save_post', 'posteditor_save_post');
function posteditor_save_post($post_id)
{
	global $wpdb;
	// Checks save status
	$is_autosave = wp_is_post_autosave($post_id);
	$is_revision = wp_is_post_revision($post_id);
	$is_valid_nonce = (isset($_POST[ 'posteditor_date_categories_metabox_nonce' ]) && wp_verify_nonce($_POST[ 'posteditor_date_categories_metabox_nonce' ], basename(__FILE__ ))) ? 'true' : 'false';
	// Exits script depending on save status
	// if ($is_autosave || $is_revision || !$is_valid_nonce) {
	// 	return;
	// }
	// Checks for input and sanitizes/saves if needed
	if(isset($_POST['post_month']) && isset($_POST['post_year'])) {
		$post_year = sanitize_text_field($_POST['post_year']);
		$post_month = sanitize_text_field($_POST['post_month']);
		$post_title = sanitize_text_field($_POST['post_title']);

		// Remove any existing categories assigned to the post (this is for when updating a post and reassigning to a different year/month)
		$wpdb->delete('term_relationships', ['object_id' => $post_id]);

		// Check whether the year category has been created
		$year_exists = term_exists($post_year, 'category');

		if($year_exists) {
			// It does, so get the ID 
			$year = get_term_by('name', $post_year, 'category', ARRAY_A);
		} else {
			// It does not, so create the year category
			$year = wp_insert_term($post_year, 'category');	
		}

		// Check whether the month category exists within the year
		$month_exists = term_exists($post_month, 'category', $year['term_id']);

		if($month_exists) {
			// It does, get the ID
			$month = get_term_by('id', $month_exists['term_id'], 'category', ARRAY_A);
		} else {
			$month = wp_insert_term($post_month, 'category', ['parent' => $year['term_id'], 'slug' => $post_month . '-' . $post_year]);
		}

		// Now assign the post to the month category.
		wp_set_post_categories($post_id, [$month['term_id']]);

		// Get current posts within the month category
		$posts = get_posts(['posts_per_page' => -1, 'category' => $month['term_id'], 'orderby' => 'menu_order', 'order' => 'ASC']);

		/**
	     * Check whether post title is blank, if it is then create one
	     */
		if(empty($post_title)) {
			// We'll set the title to be 'Viewer X', where X is the count of posts (note that WP has already added the current post already, so it's actually already counted)
			$post_title = 'Viewer ' . count($posts);
			$menu_order = count($posts);
		}

		// Next update the slug on post saving to insert the month, this just keeps things unique and avoids the random generation of additional numbers

		// use title, since $post->post_name might have unique numbers added
	    $new_slug = sanitize_title( $post_title, $post_id );
	    $post_month = sanitize_text_field($_POST['post_month']);
	    $post_year = sanitize_text_field($_POST['post_year']);
	    $new_slug = $post_year . '-' . strtolower($post_month) . '-' . $new_slug;

	    if ($new_slug == $post->post_name)
	        return; // already set

	    // unhook this function to prevent infinite looping
	    remove_action( 'save_post', 'posteditor_save_post', 10, 3 );

	    $data = array(
	        'ID' => $post_id,
	        'post_name' => $new_slug,
	        'post_title' => $post_title,
	    );

	    if(isset($menu_order)) {
	    	$data['menu_order'] = $menu_order;
	    }

	    // update the post slug (WP handles unique post slug)
	    wp_update_post($data);
	}

	if(isset($_GET['post'])) {
		$category = wp_get_post_categories($_GET['post'], ['fields' => 'all']);
	}

	if(isset($_GET['sidebar']) || isset($category[0]) && $category[0]->parent == 259) {	// Sidebar, don't add date metabox
		$category = get_term_by('id', $_GET['sidebar'], 'category');
		// Assign post to the sidebar category
		wp_set_post_categories($post_id, [$category->term_id]);
	}

	// re-hook this function
	add_action( 'save_post', 'posteditor_save_post', 10, 3 );
}