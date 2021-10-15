<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 2/18/2019
 * Time: 3:25 PM.
 */

class Startsend_Multivendor_Notification extends Startsend_WooCommerce_Notification {
	/* @var Abstract_Startsend_Multivendor $Startsend_multivendor */
	private $Startsend_multivendor;
	private $medium;
	private $defaultHooks = array(
		'pending',
		'on-hold',
		'processing',
		'completed',
		'cancelled',
		'refunded',
		'failed'
	);

	public function __construct( $medium = 'wordpress_woocommerce_multivendor', $Startsend_multivendor = null, Startsend_WooCoommerce_Logger $log = null ) {
		parent::__construct( $log );
		if ( $Startsend_multivendor === null ) {
			$Startsend_multivendor = Startsend_Multivendor_Factory::make();
		}
		$this->Startsend_multivendor = $Startsend_multivendor;
		$this->medium                = $medium;
	}

	public function send_to_vendors( $order_id, $status ) {
		if ( Startsend_get_options( 'Startsend_multivendor_vendor_send_sms', 'multivendor_setting', 'off' ) === 'off' ) {
			return;
		}
		$send_sms_flag = true; 

		//Checking if multivendor is "wc_marketplace" but do not have suborder 
		if (Startsend_Multivendor_Factory::$activatedPlugin == "wc_marketplace")
		{
			// if order id is not vendor order
			$is_suborder = (get_wcmp_suborders( $order_id, false, false) ? true : false);
			if( $is_suborder ) {
				//Do not send sms when it's sub order
				$send_sms_flag = false; 
			}
		}
		
		if($send_sms_flag){
			// check for specific hook if sms should be send
			$activatedHooks = Startsend_get_options( 'Startsend_multivendor_vendor_send_sms_on', 'multivendor_setting', $this->defaultHooks );
			if ( ! in_array( $status, $activatedHooks ) ) {
				$this->log->add( 'Startsend_Multivendor', 'not sending, current hook: ["' . $status . '"] activated hooks: ' . json_encode( $activatedHooks ) );
				return;
			}

			$this->log->add( 'Startsend_Multivendor', '3rd party plugin setting: ' . Startsend_get_options( 'Startsend_multivendor_selected_plugin', 'multivendor_setting', 'auto' ) );
			if ( ! $this->Startsend_multivendor ) {
				$this->log->add( 'Startsend_Multivendor', 'error: no multivendor plugin detected' );
				return;
			}
			$this->log->add( 'Startsend_Multivendor', 'activated plugin: ' . Startsend_Multivendor_Factory::$activatedPlugin );

			$order_details = wc_get_order( $order_id );
			$message       = Startsend_get_options( 'Startsend_multivendor_vendor_sms_template', 'multivendor_setting', '' );
			//Get default country v1.1.17
			$default_country = Startsend_get_options('Startsend_woocommerce_api_gate', 'Startsend_setting', '' );

			$vendor_data_list = $this->Startsend_multivendor->get_vendor_data_list_from_order( $order_id );
			if ( ! $vendor_data_list ) {
				return;
			}
			foreach ( $vendor_data_list as $phone_number => $vendor_datas ) {
				$phone_number = $this->phone_number_processing( $phone_number );
				$this->log->add( 'StartSend', 'Original template: ' . $message );
				$processed_msg = $this->replace_vendor_order_keyword( $message, $order_details, $vendor_datas );
				//Country Code v1.1.17
				$vendor_country = $this->Startsend_multivendor->get_vendor_country_from_vendor_data( $vendor_datas );
				if(empty($vendor_country)){
					$vendor_country = $default_country;
				}
				$phone_with_country_code = $this->check_and_get_phone_number($phone_number, $vendor_country);			
				if ( $phone_with_country_code !== false ) {
					$this->log->add( 'StartSend', 'Vendor\'s phone number (' . $phone_number . ') in country (' . $vendor_country . ') converted to ' . $phone_with_country_code );
				}else {
					$phone_with_country_code = $phone_number;
				}
				$this->send_sms( $phone_with_country_code, $processed_msg, $this->medium );
			}
		}
	}

	public function replace_vendor_order_keyword( $message, WC_Order $order_details, $vendor_datas ) {
		$search  = array(
			'[shop_name]',
			'[shop_email]',
			'[shop_url]',
			'[vendor_shop_name]',
			'[order_id]',
			'[order_currency]',
			'[order_amount]',
			'[order_status]',
			'[order_product]',
			'[order_product_with_qty]',
			'[billing_first_name]',
			'[billing_last_name]',
			'[billing_phone]',
			'[billing_email]',
			'[billing_company]',
			'[billing_address]',
			'[billing_country]',
			'[billing_city]',
			'[billing_state]',
			'[billing_postcode]',
			'[payment_method]'
		);
		$replace = array(
			get_bloginfo( 'name' ),
			get_bloginfo( 'admin_email' ),
			get_bloginfo( 'url' ),
			$this->Startsend_multivendor->get_vendor_shop_name_from_vendor_data( $vendor_datas ),
			$order_details->get_order_number(),
			$order_details->get_currency(),
			$vendor_datas['total_amount_for_vendor'],
			ucfirst( $order_details->get_status() ),
			$vendor_datas['item'],
			$vendor_datas['product_with_qty'],
			$order_details->get_billing_first_name(),
			$order_details->get_billing_last_name(),
			$order_details->get_billing_phone(),
			$order_details->get_billing_email(),
			$order_details->get_billing_company(),
			$order_details->get_billing_address_1(),
			$order_details->get_billing_country(),
			$order_details->get_billing_city(),
			$order_details->get_billing_state(),
			$order_details->get_billing_postcode(),
			$order_details->get_payment_method()
		);
		$message = str_replace( $search, $replace, $message, $total_replaced );

		// 2020-07-04 - Support additional billing field for Multivendor
		$additional_billing_fields_array = $this->get_additional_billing_fields();
		foreach ( $additional_billing_fields_array as $field ) {
			$post_data = get_post_meta( $order_details->get_order_number(), $field, true );
			$message   = str_replace( '[' . $field . ']', $post_data, $message );
		}

		$this->log->add( 'StartSend', "Total replaced keyword: $total_replaced" );

		return $message;
	}

	// 2020-07-04 - Support additional billing field for Multivendor
	// Copied from class-startsend-woocommerce-notification.php
	protected function get_additional_billing_fields() {
		$default_billing_fields = array(
			'billing_first_name',
			'billing_last_name',
			'billing_company',
			'billing_address', // added specially for Multivendor
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_state',
			'billing_country',
			'billing_postcode',
			'billing_phone',
			'billing_email'
		);
		$additional_billing_field = array();
		$billing_fields           = array_filter( get_option( 'wc_fields_billing', array() ) );
		foreach ( $billing_fields as $field_key => $field_info ) {
			if ( ! in_array( $field_key, $default_billing_fields ) && $field_info['enabled'] ) {
				array_push( $additional_billing_field, $field_key );
			}
		}

		return $additional_billing_field;
	}
}
