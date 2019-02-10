<?php

function wpeditor_load_style($hook) 
{
        // Load only on ?page=diary-editor
        if($hook != 'toplevel_page_diary-editor') {
                return;
        }
        wp_enqueue_style( 'wdeditor-diary-editor-style', PLUGIN_DIR_URL . 'diary.css' );
}