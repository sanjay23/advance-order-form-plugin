<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Setting Class
 * 
 * Manage Setting Class
 *
 * @package Advance order form
 * @since 1.0.0
 */
if( !class_exists( 'Advance_Order_Form_Settings' ) ) {

	class Advance_Order_Form_Settings {

		public $options = '';
		function __construct() {
			add_action( 'admin_menu', array($this,'advance_order_form_admin_setting_page') );
			add_action( 'admin_init', array($this,'advance_order_form_register_settings') );	
				
		}

		/**
		 * Create advance order form setting in backend
		 */
		public function advance_order_form_admin_setting_page()
		{
			add_menu_page( 
				__( 'Advance Order Form Setting', 'advance-order-form' ),
				'Advance Order Form Settings',
				'manage_options',
				'advance_order_form_setting',
				array($this,'advance_order_form_setting_page'),	
			); 		
		}

		/**
		 * advance order form page display from here
		 */
		public function advance_order_form_setting_page()
		{
			?>
			<div class='wrap'> 
				<h2><?php esc_html_e('Advance Order Form Settings','advance-order-form');?></h2> 
				<?php settings_errors();?>
				<form method='POST' action='options.php'>
					<?php 
					settings_fields( 'advance_order_form_options' );
					do_settings_sections( 'advance_order_form_general_settings' );              
					submit_button("Save");  
					?>
				</form> 
			</div>
			<?php
		}

		/**
		 * Register and add setting from here
		 */
		public function advance_order_form_register_settings() 
		{
			// General Setting
			register_setting( 'advance_order_form_options', 'advance_order_form_options_mapping','');
			add_settings_section( 'api_settings', '', '', 'advance_order_form_general_settings' );
			add_settings_field( 'advance_order_form_setting_mapping', "<label for='advance_order_form_setting_mapping'>".esc_html('Role', 'advance-order-form' )."</label>", array($this,'advance_order_form_setting_mapping'), 'advance_order_form_general_settings', 'api_settings' );
		}

		/**
		 * Display and add setting for role
		 */
		public function advance_order_form_setting_mapping() 
		{
			$mapping = get_option('advance_order_form_options_mapping','');
			$roleSelected = '';
			if(isset($mapping['role'])){
				$roleSelected = $mapping['role'];
			}
			global $wp_roles;
			?>
			<div class='mapping_wrap setting-wrap'>
				<select name="advance_order_form_options_mapping[role][]" multiple id="role">
					<?php 
					foreach($wp_roles->roles as $rkey => $rval){
						?>
						<option value="<?php echo esc_html($rkey); ?>" <?php echo !empty($roleSelected) && in_array($rkey,$roleSelected) ? "selected":'';?>><?php echo esc_html($rval['name']); ?></option>
						<?php
					}
					?>
				</select>
				<p><?php esc_html_e( 'This role can see show advance order form.', 'advance-order-form' )?></p>
			</div>		
			<?php
		}
	} 
}