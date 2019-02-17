<?php

function wpeditor_load_style($hook) 
{
        // Load only on ?page=diary-editor
        if($hook != 'toplevel_page_diary-editor') {
                return;
        }
        wp_enqueue_style( 'wdeditor-diary-editor-style', PLUGIN_DIR_URL . 'diary.css' );
}

function wdedtior_load_scripts($hook)
{
	wp_enqueue_script( 'wdeditor-plugins', PLUGIN_DIR_URL. '/js/plugins.js', ['jquery'] );

    wp_enqueue_script( 'wdeditor-scripts', PLUGIN_DIR_URL. '/js/scripts.js', ['jquery'] );
    wp_localize_script( 'wdeditor-scripts', 'ajax_object', array('ajax_url' => admin_url( 'admin-ajax.php')) );
}