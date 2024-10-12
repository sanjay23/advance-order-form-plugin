<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'Advance_Order_Form_Public' ) ) {
	class Advance_Order_Form_Public {

		function __construct() {
			add_shortcode('advance_order_form',array($this,'advance_order_form_order_form_callback'));
			add_action('wp_ajax_get_custom_ajax_data',array($this,'advance_order_form_customer_search'));
			add_action('wp_ajax_get_product_search_data', array($this,'advance_order_form_product_search'));
			add_action('wp_ajax_check_email_exist',array($this,'advance_order_form_check_email_exist'));
			add_action('wp_ajax_add_custom_order_data',array($this,'advance_order_form_place_quick_order'));
			add_filter('wp_send_new_user_notification_to_user',array($this,'wp_send_new_user_notification_to_user'),10,2);
			add_filter('woocommerce_mail_callback_params',array($this,'disabled_woo_email'),10,2);
			add_filter('wp_mail',array($this,'disabling_emails'), 10,1);
			add_action( 'wp_ajax_get_product_cart_data', array($this,'get_product_cart_data'));
			add_action('wp_enqueue_scripts', array($this,'advance_order_form_script'));
		}

		public function advance_order_form_script()
		{   
			wp_enqueue_script('advance-order-form-js',ADVANCE_ORDER_FORM_INCLUDE_URL.'/js/advance-order-form.js',array('jquery'),ADVANCE_ORDER_FORM_VERSION);
	
			$localize = array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('ajax-nonce')
			);
			wp_localize_script('advance-order-form-js', 'orderObj', $localize);
			wp_enqueue_style('advance-order-form-css', ADVANCE_ORDER_FORM_INCLUDE_URL.'/css/advance-order-form.css',array(),ADVANCE_ORDER_FORM_VERSION);
			wp_enqueue_style('advance-order-form-select2-css',ADVANCE_ORDER_FORM_INCLUDE_URL.'/css/select2.min.css',array(),ADVANCE_ORDER_FORM_VERSION);
			wp_enqueue_script('advance-order-form-select2-js',ADVANCE_ORDER_FORM_INCLUDE_URL.'/js/select2.min.js',array(),ADVANCE_ORDER_FORM_VERSION);
		}

		public function advance_order_form_allowed_role()
		{
			$mapping = get_option('advance_order_form_options_mapping','');
			$exist = false;
			if( is_user_logged_in() ) {
				$user = wp_get_current_user();
				$roles = $user->roles;
				if(!empty($roles) && !empty($mapping['role'])){
					foreach($mapping['role'] as $mval){
						if(in_array($mval,$roles) || in_array('administrator',$roles)){
							$exist = true;
							break;
						}
					}
				}
			}
			return $exist;
		}

		/**
		 * Shortcode for add order form into any page
		 */
		public function advance_order_form_order_form_callback()
		{
			if ( class_exists( 'WooCommerce' ) ) {
				ob_start();
				$allowed = $this->advance_order_form_allowed_role();
				?>
				<section class="section-form">
					<div class="container">
						<?php 
						if ($allowed) {
						?>
							<form class="custom_order_form" method="post" id="custom_order_form" name="custom_order_form">
							<div class="error">
							</div>	
							<div class="success">
								<?php 
								$msg = get_option('advance_order_form_success_msg','');
								if(!empty($msg)){
									?>
									<div class="success_msg"><?php esc_html_e( 'Order Created Successfully!', 'advance-order-form' ); ?></div>
									<?php 
									delete_option('advance_order_form_success_msg');
								}
								?>
								<p></p>
							</div>
							<input type="hidden" name="customer_exist" class="exist_customer" value="1">
							<div class="order_form">
								<div class="custom_section order_form-section">
									<h4 class="section-title"><?php esc_html_e( 'Customer', 'advance-order-form' );?></h4>
									<div class="customer_data ">
										<div>
											<input type="radio" name="existing_customer" id="existing_customer" value="1"  class="customer_exist_check">
											<label for="existing_customer"><?php esc_html_e( 'Existing Customer', 'advance-order-form' );?></label>
										</div>
										<div>
											<input type="radio" name="existing_customer" id="new_customer" value="0" checked="checked" class="customer_exist_check">
											<label for="new_customer"><?php esc_html_e( 'New Customer', 'advance-order-form' );?></label>
										</div>
									</div>
									<div class="customer-select">
										<select name="customer_user" style="width:30%" id="customer_user" class="wc-customer-search">
											<option value=""><?php esc_html_e( '-- Select customer --', 'advance-order-form' );?></option>
										</select>
									</div>
									<div class="not_exist_customer">
										<div class="field__wrapper">
											<div class="field-left">
												<label class="field-label" for="Fname"><?php esc_html_e( 'First Name :', 'advance-order-form' );?></label>
												<input type="text" name="fname" class="orderform fname" id="Fname">
											</div>
											<div class="field-right">
												<label class="field-label" for="Lname"><?php esc_html_e( 'Last Name :', 'advance-order-form' );?></label>
												<input type="text" name="lname" class="orderform lname" id="Lname">
											</div>
										</div>
										<div class="field__wrapper">
											<div class="field-left">
												<label class="field-label" for="phone"><?php esc_html_e( 'Phone :', 'advance-order-form' );?></label>
												<input type="text" name="phone" class="orderform phone" id="phone">
											</div>
											<div class="field-right">
												<label class="field-label" for="address"><?php esc_html_e( 'Email :', 'advance-order-form' );?></label>
												<input type="tel" name="email" class="orderform email_address email_add" id="address">
												<input type="hidden" name="user_exist_id" class="user_exist_id" id="user_exist_id">
												
												<p class="user_exist_error"></p>
											</div>
										</div>
										
									</div>
								</div>
								
								<div class="product_section order_form-section">
									<h4 class="section-title"><?php esc_html_e( 'Product', 'advance-order-form' );?></h4>
									<div class="mainclone-div">
										<div class="field-left">
												<label class="field-label" for="product"><?php esc_html_e( 'Product', 'advance-order-form' );?></label>
												<select class="wc-product-search product-search product-field">
													<option value=""><?php esc_html_e( '-- Select product --', 'advance-order-form' );?></option>
												</select>
										</div>
										<div class="product-total">
											<div class="qty-total-row">
												<div class="new-product">
													<button name="sync_data" type="button" class="qty-btn sync_data"><?php esc_html_e( 'Update Cart', 'advance-order-form' );?></button>
												</div>
												<div class="qty-total-wrap">
													<label class="field-label" for="qty-subtotal"><?php esc_html_e( 'SubTotal :', 'advance-order-form' );?></label>
													<div class="qty-wrapper">
														<input type="text" name="qty-subtotal" id="qty-subtotal" class="product-field-qty qty-subtotal" value="0" readonly>
													</div>
												</div>
												<div class="fees_main_wrap">
												</div>
												
												<div class="qty-total-wrap">
													<label class="field-label" for="qty-total"><?php esc_html_e( 'Total :', 'advance-order-form' );?></label>
													<div class="qty-wrapper">
														<input type="text" name="qty-total" value="0" id="qty-total"
															class="product-field-qty qty-field qty-total" readonly>
													</div>
												</div>
											</div>
										</div>
										
									</div>
								</div>
								
								<div class="delivery_section order_form-section ">
									<h4 class="section-title"><?php esc_html_e( 'Delivery', 'advance-order-form' );?></h4>
									
									<div class="delivery_fields">
										
										
										<div class="field__wrapper delivery_shipping">
											<div class="field-left">
												<label class="field-label" for="delivery_country"><?php esc_html_e( 'Country :', 'advance-order-form' );?></label>
												<select name="billing_country" id="delivery_country" class="delivery-select" autocomplete="country" data-placeholder="Select a country / region…" data-label="Country / Region">
													<?php 
													$countries = WC()->countries->get_allowed_countries();
													$value = "US";
													foreach ( $countries as $ckey => $cvalue ) {
														echo '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . esc_html( $cvalue ) . '</option>';
													}
													?>
												</select>
											</div>
											<div class="field-right">
												<label class="field-label" for="billing_city"><?php esc_html_e( 'Town / City*', 'advance-order-form' );?></label>
												<input type="text" class="input-text orderform billing_city" name="billing_city" id="billing_city" placeholder="" value="" autocomplete="address-level2">
											</div>
										</div>
										
										<div class="field__wrapper delivery_shipping ">
											<div class="field-left">
												<label class="field-label" for="billing_address_1"><?php esc_html_e( 'Street address 1*', 'advance-order-form' );?></label>
												<input type="text" name="billing_address_1" class="orderform billing_address_1" id="billing_address_1">
											</div>
											<div class="field-right">
												<label class="field-label" for="billing_address_2"><?php esc_html_e( 'Street address 2', 'advance-order-form' );?></label>
												<input type="text" name="billing_address_2" class="orderform billing_address_2" id="billing_address_2">
											</div>
										</div>
										
										<div class="field__wrapper delivery_shipping ">
											<div class="field-left">
												<label class="field-label" for="billing_state"><?php esc_html_e( 'State*', 'advance-order-form' );?></label>
												<input type="text" name="billing_state" class="input-text orderform billing_state" id="billing_state">
											</div>
											<div class="field-right">
												<label class="field-label" for="billing_postcode"><?php esc_html_e( 'Postcode / ZIP*', 'advance-order-form' );?></label>
												<input type="text" class="input-text orderform billing_postcode" name="billing_postcode" id="billing_postcode" placeholder="" value="" autocomplete="postal-code">
											</div>
										</div>
									</div>
								</div>
								
								
								
								<div class="btn-wrap">
									<button type="button" name="place_order" class="form-submit order-submit-btn order-form-btn"><?php esc_html_e( 'Place order', 'advance-order-form' );?></button>
									
								</div>
							</div>
							<div class="loader_main_wrap">
								<div class="internal_loader">
									<img src="<?php echo esc_url(ADVANCE_ORDER_FORM_INCLUDE_URL.'/images/loader.gif')?>" alt="loader-img"/>
								</div>
							</div>
							</form>
						<?php 
						} else {
							?>
							<p style='color:red;'><?php esc_html_e( 'You are not authorized to access this page', 'advance-order-form' )?></p>
							<?php 
						}
						?>
				</div>
				</section>
				<?php
				return ob_get_clean();
			} else {
				ob_start();
				?>
				<section class="section-form">
					<?php esc_html_e( 'Please activate WooCommerce Plugin.', 'advance-order-form' );?>
				</section>
				<?php
				return ob_get_clean();
			}
		}

		/**
		 * Search customer data
		 */
		public function advance_order_form_customer_search()
		{
			$term  = isset( $_GET['term'] ) ? (string) wc_clean( wp_unslash( $_GET['term'] ) ) : '';
			$limit = 0;
			
			if ( empty( $term ) ) {
				wp_die();
			}
		
			$ids = array();
			// Search by ID.
			if ( is_numeric( $term ) ) {
				$customer = new WC_Customer( intval( $term ) );
		
				// Customer does not exists.
				if ( 0 !== $customer->get_id() ) {
					$ids = array( $customer->get_id() );
				}
			}
		
			if ( empty( $ids ) ) {
				$data_store = WC_Data_Store::load( 'customer' );
				if ( 3 > strlen( $term ) ) {
					$limit = 20;
				}
				$ids = $data_store->search_customers( $term, $limit );
			}
			
			$found_customers = array();
		
			if ( ! empty( $_GET['exclude'] ) ) {
				$ids = array_diff( $ids, array_map( 'absint', (array) wp_unslash( $_GET['exclude'] ) ) );
			}
			
			foreach ( $ids as $id ) {
				$customer = new WC_Customer( $id );
				$last_order_id = $this->get_last_order_id($id);
				$last_order = wc_get_order( $last_order_id );
				$user_country_code = get_user_meta($id,'country_code',true);
				$found_customers[ $id ] = array(
					'fname' => !empty($last_order) ? get_post_meta( $last_order_id, '_billing_first_name',true) : '',
					'lname' => !empty($last_order) ? get_post_meta( $last_order_id, '_billing_last_name',true) : '',
					'id' => $customer->get_id(),
					'email' => $customer->get_email(),
					'username' => $customer->get_first_name() . ' ' . $customer->get_last_name(),
					'address1' => !empty($last_order) ? $last_order->get_billing_address_1() : '',
					'address2' => !empty($last_order) ? $last_order->get_billing_address_2() : '',
					'city' => !empty($last_order) ? $last_order->get_billing_city() : '',
					'state' => !empty($last_order) ? $last_order->get_billing_state() : '',
					'postcode' => !empty($last_order) ? $last_order->get_billing_postcode() : '',
					'country' => !empty($last_order) ? $last_order->get_billing_country() : '',
					'dob' => get_user_meta($id,'date_of_birth',true),
					'phone' => !empty($last_order) ? $last_order->get_billing_phone() : '',
				);
			}
			wp_send_json( $found_customers );
		}

		/**
		 * Get customer last place order
		 */
		public function get_last_order_id($id)
		{
			$customer = new WC_Customer( $id );
			$last_order = $customer->get_last_order();
			if(!empty($last_order)){
				return $last_order->get_id();
			}
			return '';
		}

		/**
		 * Search product by name
		 */
		public function advance_order_form_product_search()
		{
			global $woocommerce;
			$include_variations = true;
			if ( empty( $term ) && isset( $_GET['term'] ) ) {
				$term = (string) wc_clean( wp_unslash( $_GET['term'] ) );
			}
		
			if ( empty( $term ) ) {
				wp_die();
			}
		
			if ( ! empty( $_GET['limit'] ) ) {
				$limit = absint( $_GET['limit'] );
			} else {
				$limit = absint( 500 );
			}
		
			$include_ids = ! empty( $_GET['include'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['include'] ) ) : array();
			$exclude_ids = ! empty( $_GET['exclude'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['exclude'] ) ) : array();
		
			$exclude_types = array();
			if ( ! empty( $_GET['exclude_type'] ) ) {
				// Support both comma-delimited and array format inputs.
				$exclude_types = sanitize_text_field(wp_unslash( $_GET['exclude_type'] ));
				if ( ! is_array( $exclude_types ) ) {
					$exclude_types = explode( ',', $exclude_types );
				}
		
				// Sanitize the excluded types against valid product types.
				foreach ( $exclude_types as &$exclude_type ) {
					$exclude_type = strtolower( trim( $exclude_type ) );
				}
				$exclude_types = array_intersect(
					array_merge( array( 'variation' ), array_keys( wc_get_product_types() ) ),
					$exclude_types
				);
			}
		
			$data_store = WC_Data_Store::load( 'product' );
			$ids        = $data_store->search_products( $term, '', (bool) $include_variations, false, $limit, $include_ids, $exclude_ids );
			$products = array();
			foreach ( $ids as $id ) {
				if(empty($id)){
					continue;
				}
				$product_object = wc_get_product( $id );
		
				$formatted_name = $product_object->get_formatted_name();
				$managing_stock = $product_object->managing_stock();
				$price = $product_object->get_regular_price();
				$sell_price = $product_object->get_sale_price();
				if(!empty($sell_price)){
					$price = $sell_price;
				}
				if ( in_array( $product_object->get_type(), $exclude_types, true ) ) {
					continue;
				}
				
				$stock_amount = $product_object->is_in_stock();
				if($stock_amount == 0){
					continue;
				}
				
				$products[ $product_object->get_id() ] = rawurldecode( wp_strip_all_tags( $formatted_name ) )."||".$price;
			}
		
			wp_send_json( $products );
		}

		/**
		 * Check email is exist or not for new customer
		 */
		public function advance_order_form_check_email_exist()
		{
			if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
				return;
			}
			$email = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
			$response = array();
			$response['success'] = false;
			$response['message'] = "";
			if(!empty($email)){
				$user = get_user_by( 'email', $email );
				if(!empty($user)){
					$response['success'] = true;
					$response['message'] = esc_html( __( "Email already exist!", 'advance-order-form' ) );
					$response['user_id'] = $user->ID;
				}
			}
			wp_send_json($response);
		}

		public function get_three_digit(){
			return mt_rand(100,999);
		}

		/**
		 * Quick order place
		 */
		public function advance_order_form_place_quick_order()
		{
			if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
				return;
			}
			$params = array();
			if(isset($_POST['formdata'])){
				parse_str($_POST['formdata'], $params);
			}
			$response = array();
			$error = array(); 
			if(isset($params['productId'][0]) && empty($params['productId'][0])){
				$error[] = esc_html( __( "Please select at least one product", 'advance-order-form' ) ); 
			}
		
			if(!isset($params['productId'])){
				$error[] = esc_html( __( "Please select at least one product", 'advance-order-form' ) ); 
			}
			
			if(empty($params['billing_country'])){
				$error[] = esc_html( __( "Please select country", 'advance-order-form' ) ); 
			}
			if(empty($params['billing_city'])){
				$error[] = esc_html( __( "Please enter city", 'advance-order-form' ) ); 
			}
			if(empty($params['billing_address_1'])){
				$error[] = esc_html( __( "Please enter street address", 'advance-order-form' ) ); 
			}
			if(empty($params['billing_postcode'])){
				$error[] = esc_html( __( "Please enter postal code", 'advance-order-form' ) ); 
			}
			if(empty($params['billing_state'])){
				$error[] = esc_html( __( "Please enter state", 'advance-order-form' ) ); 
			}
			
			if(empty($error)){
				if(!empty($params['existing_customer']) && empty($params['customer_user'])){
					$allEmpty = true;
				}
				if(empty($params['existing_customer'])){
					$allEmpty = true;
				}
				if($allEmpty){
					$autoLastName = "";
					if(empty($params['fname'])){
						$params['fname'] = 'auto-generated';
					}
					if(empty($params['lname'])){
						$params['lname'] = $autoLastName;
					}
					if(empty($params['email'])){
						$fname = $params['fname'];
						if($params['fname'] == 'auto-generated'){
							$fname = 'auto generate';
						}
						$params['email'] = $params['fname'].'.'.$params['lname'].'.'.$this->get_three_digit().'@gmail.com';
						$params['fname'] = $fname;
					}
				}
				if(!empty($params['email'])){   // for new customer
					$email = $params['email'];
					$user = get_user_by( 'email', $email );
					if ( $user ) {
						$user_id = $user->ID;
						update_user_meta( $user_id, 'billing_first_name',$params['fname']);
						update_user_meta( $user_id, 'billing_last_name',$params['lname']);
						update_user_meta( $user_id, 'billing_phone', $params['phone']);
						update_user_meta( $user_id, 'country_code',$params['code']);
						update_user_meta( $user_id, 'date_of_birth', $params['dob']);
					} else {
						$pass = wp_generate_password( 20, true, true );
						$user_id = wp_create_user($email, $pass, $email);
						update_user_meta($user_id,'user_pass',$pass);
						
						add_user_meta( $user_id, 'billing_first_name', $params['fname']);
						add_user_meta( $user_id, 'billing_last_name', $params['lname']);
						add_user_meta( $user_id, 'country_code',$params['code']);
						add_user_meta( $user_id, 'billing_phone', $params['phone']);
						add_user_meta( $user_id, 'date_of_birth', $params['dob']);
						wp_update_user([
							'ID' => $user_id, // this is the ID of the user you want to update.
							'first_name' => $params['fname'],
							'last_name' => $params['lname'],
						]);
						do_action( 'register_new_user',$user_id,'both');
					}
					$params['customer_user'] = $user_id;
				}
				
				$order = wc_create_order();
				$user_id = $params['customer_user'];
				$order->set_customer_id( $user_id );
				$has_term = false;
				$count = 0;
				foreach($params['productId'] as $pkey => $pval){
					
					$qty = isset($params['qty'][$pkey]) ? $params['qty'][$pkey] : 1; 
					$item_id = $order->add_product( wc_get_product($pval), $qty);
					$line_total = isset($params['price'][$count]) ? $params['price'][$count] : '0';   // update discounted price 
					
					wc_update_order_item_meta( $item_id, '_line_subtotal', $line_total); // price per item
					wc_update_order_item_meta( $item_id, '_line_total', $line_total); // total price	
					$count++;
				}
				update_option('advance_order_form_success_msg',1);
				
				
				$first_name = get_user_meta( $user_id, 'billing_first_name',true);
				$last_name = get_user_meta( $user_id, 'billing_last_name',true);
				$billing_phone = get_user_meta( $user_id, 'billing_phone',true);
				$billing_country = get_user_meta( $user_id, 'billing_country',true);
				$billing_city = get_user_meta( $user_id, 'billing_city',true);
				$billing_address_1 = get_user_meta( $user_id, 'billing_address_1',true);
				$billing_postcode = get_user_meta( $user_id, 'billing_postcode',true);
				$billing_state = get_user_meta( $user_id, 'billing_state',true);
				$phone_country_code = get_user_meta( $user_id, 'country_code',true);
				$address = array(
					'country' => $params['billing_country'] ?? $billing_country,
					'city' => $params['billing_city'] ?? $billing_city,
					'address_1' => $params['billing_address_1'] ?? $billing_address_1,
					'address_2' => $params['billing_address_2'],
					'postcode' => $params['billing_postcode'] ?? $billing_postcode,
					'state' => $params['billing_state'] ?? $billing_state,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'phone' => $billing_phone,
					'phone_country_code' => $phone_country_code,
				);
	
				$order->set_address( $address, 'billing' );
				
				$order->calculate_totals();
				
				$order->update_status( 'processing', '', TRUE); 
		
				// $order->add_order_note( $note );
				$payment_gateways = WC()->payment_gateways->payment_gateways();
				$order->set_payment_method($payment_gateways['cod']);
				if (str_contains($params['email'], 'auto-generated')) { 
					$note = esc_html( __( " This is a COD type of order ", 'advance-order-form' ) );
					$order->add_order_note( $note );
				}
				$login_user_ID = get_current_user_id();
				if(!empty($login_user_ID)){
					$first_name = get_user_meta( $login_user_ID, 'first_name',true);
					$last_name = get_user_meta( $login_user_ID, 'last_name',true);
				}
				
				$note = esc_html( __( " This order is placed from Advance Order Form.", 'advance-order-form' ) );
				$order->add_order_note( $note );
				$order->save();
				$order_id = $order->get_id(); 
				
				
				if(isset($params['qty-fees']) && !empty($params['qty-fees'])){
					foreach($params['qty-fees'] as $feeKey => $fees){
						$feeLabel = isset($params['qty-fees-label'][$feeKey]) ? $params['qty-fees-label'][$feeKey] : 'Fee';
						$order_item_id = wc_add_order_item($order_id,array('order_item_name' => $feeLabel,'order_item_type' => 'fee'));
						if( $order_item_id ) {
							// provide its meta information
							wc_add_order_item_meta( $order_item_id, '_fee_amount',  $fees, true ); // fee
							wc_add_order_item_meta( $order_item_id, '_line_total',  $fees, true ); // fee
							// you can also add "_variation_id" meta
							wc_add_order_item_meta( $order_item_id, '_line_tax', 0, true ); 
							wc_add_order_item_meta( $order_item_id, '_tax_status', 'taxable', true ); 
						}
					}
				}
				
				update_post_meta( $order_id, '_order_total', $params['qty-total']); // total price
				update_post_meta( $order_id, 'is_advance_order_form', 1); // intenal order
				
				$orderMeta = array(
					'first_name' => $params['fname'],
					'last_name' => $params['lname'],
					
				);
				
				$orderMeta['billing_country'] = $params['billing_country'];
				$orderMeta['billing_city'] = $params['billing_city'];
				$orderMeta['billing_address_1'] = $params['billing_address_1'];
				$orderMeta['billing_postcode'] = $params['billing_postcode'];
				$orderMeta['billing_state'] = $params['billing_state'];
				
				$this->add_extra_fields($order_id,$orderMeta);
				$subject = 'Your order has been received! #'.$order_id;
		
				// Get WooCommerce email objects
				$mailer = WC()->mailer()->get_emails();
	
				$mailer['WC_Email_New_Order']->settings['subject'] = $subject;
				
				// Send the email with custom heading & subject
				$mailer['WC_Email_New_Order']->trigger( $order_id );
				
				$response['success'] = true;
			} else {
				$response['success'] = false;
			}
			$response['error'] = $error;
			wp_send_json($response);
		}

		public function add_extra_fields($order_id,$posted)
		{
			if(!empty($posted['first_name'])){
				update_post_meta( $order_id, '_billing_first_name', $posted['first_name'] );
			}
			if(!empty($posted['last_name'] )){
				update_post_meta( $order_id, '_billing_last_name', $posted['last_name'] );
			}
			update_post_meta( $order_id, '_billing_address_1', $posted['billing_address_1'] );
			update_post_meta( $order_id, '_billing_country', $posted['billing_country']);
			update_post_meta( $order_id, '_billing_state', $posted['billing_state'] );
			update_post_meta( $order_id, '_billing_city', $posted['billing_city'] );
			update_post_meta( $order_id, '_billing_postcode', $posted['billing_postcode'] );
		}
		
		
		public function wp_send_new_user_notification_to_user($send, $user)
		{
			if (str_contains($user->user_email, 'auto-generated')) { 
				return false;
			}
			return $send;
		}

		public function disabled_woo_email( $args, $obj )
		{
			if (isset($args[0]) && str_contains($args[0], 'auto-generated')) { 
				unset ( $args[0] );
			}
			return $args;
		}

		public function disabling_emails( $args )
		{
			if (str_contains($args['to'], 'auto-generated')) { 
				unset ( $args['to'] );
			}
			return $args;
		}

		/**
		 * Add data into cart using api
		 */
		public function get_product_cart_data($postArr = '')
		{
			if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
				return;
			}
			$productArr = isset($_POST['productArr']) ? $_POST['productArr'] : $postArr;
			
			$response = array();
			$response['success'] = false;
			if(!empty($productArr)){
			
				$nonce = get_transient('advance_order_form_api_nonce');
				if(empty($nonce)){
					$nonce = wp_create_nonce( 'wc_store_api' );
				}
				
				$url = site_url().'/wp-json/wc/store/v1/batch';
				$nutArr = array('1');
				$newArr = [];
				$otherArr = [];
				$overrideArr = [];
				foreach($productArr as $pval){
					if(in_array($pval['productId'],$nutArr)){
						$newArr[] = array(
							'productId' => $pval['productId'],
							'qty' => $pval['qty'] 
						);
					} else {
						$otherArr[] = array(
							'productId' => $pval['productId'],
							'qty' => $pval['qty']
						);
					}
					$overrideArr[$pval['productId']] = array(
						'productId' => $pval['productId'],
						'qty' => $pval['qty'],
						'overridePrice' => isset($pval['overridePrice']) ? $pval['overridePrice'] : '0', 
						'originPrice' => isset($pval['originPrice']) ? $pval['originPrice'] : '0', 
					);
				}
				$finalArr = array_merge($newArr,$otherArr);
				$productArr = $finalArr;
				$data = array();
				foreach($productArr as $pkey => $product){
					if(!empty($product['productId'])){
						$qty = isset($productArr[$pkey]['qty']) ? $productArr[$pkey]['qty'] : '1';
						$productId = $product['productId'];
						$productObj = wc_get_product( $productId );
						$variationData['variation'] = [];
		
						if($productObj->get_type() == 'variation'){
							if(!empty($productObj->attributes)){
								$attributeArr = [];
								foreach($productObj->attributes as $attributeKey => $attribute){
									$attributeArr[] = array(
										"attribute" => $attributeKey,
										"value" => $attribute
									);
								}
								if(!empty($attributeArr)){
									$variationData['variation'] = $attributeArr;
								}
							}
							$data[] = array(
								'path' => "/wc/store/v1/cart/add-item",
								"method" => "POST",
								"cache" =>  "no-store",
								"headers" => [
									"Nonce" => $nonce,
								],
								"body" => array(
									"id" => $productId,
									"quantity" => $qty,
									"variation" => $attributeArr,
								),
							); 
						} else {
							$data[] = array(
								'path' => "/wc/store/v1/cart/add-item",
								"method" => "POST",
								"cache" =>  "no-store",
								"headers" => [
									"Nonce" => $nonce,
								],
								"body" => array(
									"id" => $productId,
									"quantity" => $qty
								),
							); 
						}
					}
				}
				$jsonData["requests"] = $data;
			
				$arguments = array(
					'headers' => array(
						'content-type' => 'application/json',
						"Nonce" => $nonce,
					),
					"cache" =>  "no-store",
					'body' => json_encode($jsonData),
				);  
					
				$responseBody = wp_remote_retrieve_body( wp_remote_post( $url,$arguments ) );
				$obj =  json_decode( $responseBody,true );
				$errorMsg = [];
				if(isset($obj['responses']) && !empty($obj['responses'])){
					$lastArr = end($obj['responses']);    // get last array due to it show wrong array value in all array so.
					
					if(isset($lastArr['status']) && $lastArr['status'] == '403'){
						$nonce = isset($lastArr['headers']['Nonce']) ? $lastArr['headers']['Nonce'] : '';
						set_transient( 'advance_order_form_api_nonce', $nonce, 24 * HOUR_IN_SECONDS );
						$this->get_product_cart_data($productArr);
					}
					
					foreach($obj['responses'] as $responseVal){
						if($responseVal['status'] == '400'){
							$errorMsg[] = isset($responseVal['body']['message']) ? $responseVal['body']['message'] : '';
						}
		
					}
					if(isset($lastArr['body']) && !empty($lastArr['body'])){
						
						$productData = $lastArr['body'];
						$itemData = array();
						foreach($productData['items'] as $item){
							$itemId = $item['id'];
		
							$_product = wc_get_product( $itemId );
							$overRide = '0';
							if(isset($overrideArr[$itemId])){
								$overRide = $overrideArr[$itemId]['overridePrice'];
							}
							if(!empty($overRide)){
								$originPrice = $overrideArr[$itemId]['originPrice'];
								$total = $originPrice * $item['quantity'];
							} else {
								$regular_price = ($item['prices']['sale_price'] / 100);
								$total =  $regular_price * $item['quantity'];
							}
							$itemData[] = array(
								'key' =>  $item['key'],
								'name' =>  $item['name'],
								'qty' =>  $item['quantity'],
								'id' =>  $item['id'],
								'line_total' => $total,
							);
						}
						
						$label = '';
						if(!empty($productData['fees'])){
							foreach($productData['fees'] as $fee){
								$label = $fee['name'];
							}
						}
		
						$response['items'] = $itemData;
						$response['total'] = ($productData['totals']['total_price'] / 100);
						$response['fees'] = ($productData['totals']['total_fees'] / 100);
						$response['fees_name'] = $label;
						$response['feesData'] = $productData['fees'];
						$response['sub_total'] = ($productData['totals']['total_items'] / 100);
						$response['success'] = true;
						$response['error'] = $errorMsg;
					}
				}
			}
			wp_send_json($response);
		}
	}
}