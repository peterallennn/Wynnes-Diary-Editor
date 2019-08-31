<?php
/*
Plugin Name: Wynne's Diary Editor
Plugin URI: https://wynnesdiary.com
Description: Custom functionality developed within the Wordpress Admin, required to make the diary simple to edit.
Version: 1.0
Author: Peter Allen
Author URI: https://peterallen.me
*/

define('PLUGIN_DIR_PATH', plugin_dir_path(__FILE__ ));
define('PLUGIN_DIR_URL', plugin_dir_url(__FILE__ ));
define('CURRENT_ADMIN_URL', $current_url = "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

require 'inc/pages.php';
require 'inc/menu.php';
require 'inc/assets.php';
require 'inc/post-editor.php';
require 'inc/custom-post-types/image-mouseovers.php';

require 'inc/ajax-backend.php';

/**
 * Add modification to 'posts-new.php' form
 */
require 'inc/posts-form.php';

// Register the admin menu
add_action( 'admin_menu', 'wdeditor_admin_menu' );

// Register 'diary.css' style
add_action( 'admin_enqueue_scripts', 'wpeditor_load_style' );

// Register 'scripts.js' script
add_action( 'admin_enqueue_scripts', 'wdedtior_load_scripts' );

remove_filter( 'pre_term_description', 'wp_filter_kses' );
remove_filter( 'pre_link_description', 'wp_filter_kses' );
remove_filter( 'pre_link_notes', 'wp_filter_kses' );
remove_filter( 'term_description', 'wp_kses_data' );

