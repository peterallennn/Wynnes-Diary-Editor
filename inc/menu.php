<?php

function wdeditor_admin_menu()
{
  $page_title = 'Diary Editor';
  $menu_title = 'Diary';
  $capability = 'manage_options';
  $menu_slug  = 'diary-editor';
  $function   = 'wdeditor_diary_page';
  $icon_url   = 'dashicons-media-code';
  $position   = 4;

  add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
}

function wpeditor_remove_menus() 
{
	remove_menu_page( 'edit.php' );                   //Posts
	remove_menu_page( 'edit-comments.php' );          //Comments
	remove_menu_page( 'themes.php' );                 //Appearance
}