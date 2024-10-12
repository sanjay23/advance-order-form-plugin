<?php
/**
 * Plugin Name: Advance Order Form
 * Description: Admin or any user can place quick order without checkout
 * Version: 1.0.0
 * Text Domain: advance-order-form
 * Author: msanjay23
 * Author URI: https://profiles.wordpress.org/msanjay23/
 * License: GPLv3
 * 
 * @package 
 * @category Core 
 * @author 
 */

// Exit if accessed directly 
if( !defined( 'ABSPATH' ) ) exit; 

/**
 * Basic plugin definitions 
 * 
 * @package 
 * @since 1.0.0
 */
if( !defined( 'ADVANCE_ORDER_FORM_VERSION' ) ) {
	define( 'ADVANCE_ORDER_FORM_VERSION', '1.0.0' ); // version of plugin
}
if( !defined( 'ADVANCE_ORDER_FORM_DIR' ) ) {
	define( 'ADVANCE_ORDER_FORM_DIR', dirname(__FILE__) ); // plugin dir
}
if( !defined( 'ADVANCE_ORDER_FORM_PLUGIN_BASENAME' ) ) {
	define( 'ADVANCE_ORDER_FORM_PLUGIN_BASENAME', basename( ADVANCE_ORDER_FORM_DIR ) ); //Plugin base name
}
if( !defined( 'ADVANCE_ORDER_FORM_URL' ) ) {
	define( 'ADVANCE_ORDER_FORM_URL', plugin_dir_url(__FILE__) ); // plugin url
}
if( !defined( 'ADVANCE_ORDER_FORM_INCLUDE_DIR' ) ) {
	define( 'ADVANCE_ORDER_FORM_INCLUDE_DIR', ADVANCE_ORDER_FORM_DIR . '/includes/' ); 
}
if( !defined( 'ADVANCE_ORDER_FORM_INCLUDE_URL' ) ) {
	define( 'ADVANCE_ORDER_FORM_INCLUDE_URL', ADVANCE_ORDER_FORM_URL . 'includes/' ); // plugin include url
}
if( !defined( 'ADVANCE_ORDER_FORM_ADMIN_DIR' ) ) {
	define( 'ADVANCE_ORDER_FORM_ADMIN_DIR', ADVANCE_ORDER_FORM_DIR . '/includes/admin' ); // plugin admin dir 
}

/**
 * Load Text Domain
 * 
 * This gets the plugin ready for translation.
 */
function advance_order_form_load_textdomain() {
	
	// Set filter for plugin's languages directory
	$lang_dir	= dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$lang_dir	= apply_filters( 'advance_order_form_languages_directory', $lang_dir );
	
	// Traditional WordPress plugin locale filter
	$locale	= apply_filters( 'plugin_locale',  get_locale(), 'advance-order-form' );
	$mofile	= sprintf( '%1$s-%2$s.mo', 'advance-order-form', $locale );
	
	// Setup paths to current locale file
	$mofile_local	= $lang_dir . $mofile;
	$mofile_global	= WP_LANG_DIR . '/' . ADVANCE_ORDER_FORM_PLUGIN_BASENAME . '/' . $mofile;
	
	if ( file_exists( $mofile_global ) ) { // Look in global /wp-content/languages/advance-order-form folder
		load_textdomain( 'advance-order-form', $mofile_global );
	} elseif ( file_exists( $mofile_local ) ) { // Look in local /wp-content/plugins/advance-order-form/languages/ folder
		load_textdomain( 'advance-order-form', $mofile_local );
	} else { // Load the default language files
		load_plugin_textdomain( 'advance-order-form', false, $lang_dir );
	}	
}

/**
 * Load Plugin
 */
function advance_order_form_plugin_loaded() {
	advance_order_form_load_textdomain();
}
add_action( 'plugins_loaded', 'advance_order_form_plugin_loaded' );


/**
 * Declaration of global variable
 */ 
global $advance_order_form_public;

include_once( ADVANCE_ORDER_FORM_INCLUDE_DIR . '/class-advance-order-form.php' );
$advance_order_form_public = new Advance_Order_Form_Public();

include_once( ADVANCE_ORDER_FORM_ADMIN_DIR . '/class-advance-order-form-settings.php' );
$advance_order_form_settings = new Advance_Order_Form_Settings();

