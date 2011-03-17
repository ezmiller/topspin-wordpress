<?php
/*
Plugin Name: Topspin Store Plugin
Plugin URI: http://www.topspinmedia.com
Description: Plugin for displaying products using the TopSpin API
Author: Topspin Media
Version: 3.0
Author URI: http://www.topspinmedia.com
*/

### This File
define('TOPSPIN_PLUGIN_FILE',__FILE__);

### Require Global Configs
require('topspin_global.php');

### Settings Page Functions
function topspin_page_settings_general() {
	include('page/settings_general.php');
}
function topspin_page_settings_viewstores() {
	include('page/settings_viewstores.php');
}
function topspin_page_settings_edit() {
	include('page/settings_edit.php');
}

### Add Menus
function topspin_add_menus() {
	add_menu_page('Topspin','Topspin',6,'topspin/page/settings_general','topspin_page_settings_general');
		add_submenu_page('topspin/page/settings_general','Settings','Settings',6,'topspin/page/settings_general','topspin_page_settings_general');
		add_submenu_page('topspin/page/settings_general','View Stores','View Stores',6,'topspin/page/settings_viewstores','topspin_page_settings_viewstores');
		add_submenu_page('topspin/page/settings_general','Store Setup','Add Store',6,'topspin/page/settings_edit','topspin_page_settings_edit');
}

### Global CSS/JS
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-sortable');

### Administrative Hooks
if(is_admin()) {
	### Add Menus
	add_action('admin_menu','topspin_add_menus');
	### CSS/JS
	wp_enqueue_style('topspin-admin',TOPSPIN_PLUGIN_URL.'/resources/css/admin.css');
}
### Frontend Hooks
else {
	### CSS/JS
	wp_enqueue_style('topspin-default',TOPSPIN_PLUGIN_URL.'/css/topspin.css');
	if(file_exists(TOPSPIN_CURRENT_THEME_PATH.'/topspin.css')) { wp_enqueue_style('topspin-theme',TOPSPIN_CURRENT_THEME_URL.'/topspin.css'); }
		### IE7 CSS
		if(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 7.0')) {
			wp_enqueue_style('topspin-default-ie7',TOPSPIN_PLUGIN_URL.'/css/topspin-ie7.css');
			if(file_exists(TOPSPIN_CURRENT_THEME_PATH.'/topspin-ie7.css')) { wp_enqueue_style('topspin-theme',TOPSPIN_CURRENT_THEME_URL.'/topspin-ie7.css'); }
		}
	wp_enqueue_style('topspin-colorbox',TOPSPIN_PLUGIN_URL.'/resources/js/colorbox/colorbox.css');
	wp_enqueue_script('topspin-core','http://cdn.topspin.net/javascripts/topspin_core.js?aId='.TOPSPIN_ARTIST_ID);
	wp_enqueue_script('topspin-colorbox',TOPSPIN_PLUGIN_URL.'/resources/js/colorbox/jquery.colorbox-min.js');
	wp_enqueue_script('topspin-ready',TOPSPIN_PLUGIN_URL.'/resources/js/topspin.ready.js');
}

?>