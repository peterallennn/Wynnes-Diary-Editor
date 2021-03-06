<?php
// Register Custom Post Type
function wdeditor_image_mouseovers() {
	$labels = array(
		'name'                  => _x( 'Image Mouseovers', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'Image Mouseover', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Image Mouseovers', 'text_domain' ),
		'name_admin_bar'        => __( 'image_mouseovers', 'text_domain' ),
		'archives'              => __( 'Item Archives', 'text_domain' ),
		'attributes'            => __( 'Item Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
		'all_items'             => __( 'All Items', 'text_domain' ),
		'add_new_item'          => __( 'Add New Item', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Item', 'text_domain' ),
		'edit_item'             => __( 'Edit Item', 'text_domain' ),
		'update_item'           => __( 'Update Item', 'text_domain' ),
		'view_item'             => __( 'View Item', 'text_domain' ),
		'view_items'            => __( 'View Items', 'text_domain' ),
		'search_items'          => __( 'Search Item', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Featured Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
		'items_list'            => __( 'Items list', 'text_domain' ),
		'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'Image Mouseover', 'text_domain' ),
		'description'           => __( 'GUI to create simple image mouseover effects', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => true,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'image_mouseovers', $args );
}
add_action( 'init', 'wdeditor_image_mouseovers', 0 );

function wdeditor_image_mouseovers_display_mouseover($atts) {
    extract( shortcode_atts( array (
        'id' => null // The image mouseover ID to display
    ), $atts ) );

    $default_image_id = get_field('default_image', (int)$id);
    $default_image = wp_get_attachment_url($default_image_id);
    $default_image_meta = wp_get_attachment_metadata($default_image_id);

    $hover_image_id = get_field('hover_image', (int)$id);
    $hover_image = wp_get_attachment_url($hover_image_id);

    $alt_text = get_field('alt_text', (int)$id);
    return '<img src="' . $default_image . '" onmouseover="this.src=\'' . $hover_image . '\'" onmouseout="this.src=\'' . $default_image . '\'" alt="' . $alt_text . '" height="' . $default_image_meta['height'] . '"></a>';
}
add_shortcode( 'image_mouseover', 'wdeditor_image_mouseovers_display_mouseover' );