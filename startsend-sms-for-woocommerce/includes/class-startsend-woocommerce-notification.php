<?php

class Startsend_WooCommerce_Notification {
	protected $log;

	/**
	 * Startsend_WooCommerce_Notification constructor.
	 * @param Startsend_WooCoommerce_Logger|null $log
	 */
	public function __construct( Startsend_WooCoommerce_Logger $log = null ) {
		if ( $log === null ) {
			$log = new Startsend_WooCoommerce_Logger();
		}
		$this->log = $log;
	}

	/**
	 * Send sms for order change status
	 * @param $order_id
	 */
	public function send_sms_woocommerce_order_status_pending( $order_id ) {
		$this->send_customer_notification( $order_id, "pending" );
		$this->send_admin_notification( $order_id, "pending" );
	}

	/**
	 * Send sms for order change status
	 * @param $order_id
	 */
	public function send_sms_woocommerce_order_status_failed( $order_id ) {
		$this->send_customer_notification( $order_id, "failed" );
		$this->send_admin_notification( $order_id, "pending" );
	}

	/**
	 * Send sms for order change status
	 * @param $order_id
	 */
	public function send_sms_woocommerce_order_status_on_hold( $order_id ) {
		$this->send_customer_notification( $order_id, "on-hold" );
		$this->send_admin_notification( $order_id, "on-hold" );
	}

	/**
	 * Send sms for order change status
	 * @param $order_id
	 */
	public function send_sms_woocommerce_order_status_processing( $order_id ) {
		$this->send_customer_notification( $order_id, "processing" );
		$this->send_admin_notification( $order_id, "processing" );
	}

	/**
	 * Send sms for order change status
	 * @param $order_id
	 */
	public function send_sms_woocommerce_order_status_completed( $order_id ) {
		$this->send_customer_notification( $order_id, "completed" );
		$this->send_admin_notification( $order_id, "completed" );
	}

	/**
	 * Send sms for order change status
	 * @param $order_id
	 */
	public function send_sms_woocommerce_order_status_refunded( $order_id ) {
		$this->send_customer_notification( $order_id, "refunded" );
		$this->send_admin_notification( $order_id, "refunded" );
	}

	/**
	 * Send sms for order change status
	 * @param $order_id
	 */
	public function send_sms_woocommerce_order_status_cancelled( $order_id ) {
		$this->send_customer_notification( $order_id, "cancelled" );
		$this->send_admin_notification( $order_id, "cancelled" );
	}

	/**
	 * Send sms for order change status
	 * @param $order_id
	 */
	public function send_sms_woocommerce_order_status_changed( $order_id, $old_status, $new_status ) {
		$this->log->add( 'StartSend', 'Order status changed": old status: ' . $old_status . ' , new status: ' . $new_status );
	}

	/**
	 * Send sms for order change status
	 * @param $order_id
	 */
	public function woocommerce_payment_complete( $order_id ) {
		$this->log->add( 'StartSend', 'Payment completed' );
	}

	/**
	 * Send sms for order change status
	 * @param $order_id
	 */
	public function woocommerce_payment_complete_order_status( $order_id ) {
		$this->log->add( 'StartSend', 'Completed order status' );
	}

	/**
	 * Send sms for customer notification
	 * @param $order_id
	 */
	public function send_customer_notification( $order_id, $status ) {
		if ( ! in_array( $status, Startsend_get_options( 'Startsend_woocommerce_send_sms', 'customer_setting', array() ) ) ) {
			return;
		}
		
        $send_sms_flag = true;
		$order_details = new WC_Order( $order_id );

		//Checking if multivendor is "wc_marketplace" 
		if (Startsend_Multivendor_Factory::$activatedPlugin == "wc_marketplace")
		{	
			//checking if it's having suborder
			$is_suborder = (get_wcmp_suborders( $order_id, false, false) ? false : true);
			if( $is_suborder ) {
				//Do not send sms when it's sub order
				$send_sms_flag = false; 
				if ( Startsend_get_options( 'Startsend_woocommerce_suborders_send_sms', 'customer_setting', 'off' ) == 'on' ) {
					$send_sms_flag = true; 
				}
			}
		}
		//Checking if multivendor is "dokan" 
		if (Startsend_Multivendor_Factory::$activatedPlugin == "dokan")
		{
			//checking if it's a suborder
			$dokan_suborders = dokan_is_sub_order($order_id); 
			if($dokan_suborders)
			{
				//Do not send sms when not sub order
				$send_sms_flag = false;
				if ( Startsend_get_options( 'Startsend_woocommerce_suborders_send_sms', 'customer_setting', 'off' ) == 'on' ) {
					$send_sms_flag = true; 
				}
			}	
		}

		//Checking if multivendor is "YITH" 
		if (Startsend_Multivendor_Factory::$activatedPlugin == "yith")
		{
			//checking if it's a suborder
			$yith_suborders =  wp_get_post_parent_id($order_id) ; 
			if($yith_suborders)
			{
				//Do not send sms when it's sub order
				$send_sms_flag = false;
				if ( Startsend_get_options( 'Startsend_woocommerce_suborders_send_sms', 'customer_setting', 'off' ) == 'on' ) {
					$send_sms_flag = true; 
				}
			}	
		}

		if($send_sms_flag)
		{
			$message = Startsend_get_options( 'Startsend_woocommerce_sms_template_' . $status, 'customer_setting', '' );
			if ( empty( $message ) ) {
				$message = Startsend_get_options( 'Startsend_woocommerce_sms_template_default', 'customer_setting', '' );
			}
			if ( empty( $message ) ) {
				return;
			}
			$message           = $this->replace_order_keyword( $message, $order_details, 'customer', $status );
			$customer_phone_no = $this->check_and_get_phone_number( $order_details->get_billing_phone(), $order_details->get_billing_country() );

			if ( $customer_phone_no !== false ) {
				$this->log->add( 'StartSend', 'Customer\'s billing phone number (' . $order_details->get_billing_phone() . ') in country (' . $order_details->get_billing_country() . ') converted to ' . $customer_phone_no );
			} else {
				$customer_phone_no = $order_details->get_billing_phone();
			}
			$this->send_sms( $customer_phone_no, $message );
		}
	}

	/**
	 * Send admin notification
	 * @param $order_id
	 * @param $status
	 */
	public function send_admin_notification( $order_id, $status ) {
		//v1.1.18 add selection for sending admin notification on which status
		if ( ! in_array( $status, Startsend_get_options( 'Startsend_woocommerce_admin_send_sms_on', 'admin_setting', array() ) ) ) {
			return;
		}

		$order_details = new WC_Order( $order_id );
		$send_sms_flag = true;

		if (Startsend_Multivendor_Factory::$activatedPlugin == "wc_marketplace")
		{	
			$is_suborder = (get_wcmp_suborders( $order_id, false, false) ? false : true);
			if( $is_suborder ) {
				$send_sms_flag = false; 
				if ( Startsend_get_options( 'Startsend_woocommerce_admin_suborders_send_sms', 'admin_setting', 'off' ) == 'on' ) {
					$send_sms_flag = true; 
				}
			}
		}
		if (Startsend_Multivendor_Factory::$activatedPlugin == "dokan")
		{
			$dokan_suborders = dokan_is_sub_order($order_id); 
			if($dokan_suborders)
			{
				$send_sms_flag = false;
				if ( Startsend_get_options( 'Startsend_woocommerce_admin_suborders_send_sms', 'admin_setting', 'off' ) == 'on' ) {
					$send_sms_flag = true; 
				}
			}	
		}
		if (Startsend_Multivendor_Factory::$activatedPlugin == "yith")
		{
			$yith_suborders =  wp_get_post_parent_id($order_id) ; 
			if($yith_suborders)
			{
				$send_sms_flag = false;
				if ( Startsend_get_options( 'Startsend_woocommerce_admin_suborders_send_sms', 'admin_setting', 'off' ) == 'on' ) {
					$send_sms_flag = true; 
				}
			}	
		}

		if($send_sms_flag){
			$message        = Startsend_get_options( 'Startsend_woocommerce_admin_sms_template', 'admin_setting', '' );
			$message        = $this->replace_order_keyword( $message, $order_details, 'admin', $status );
			$admin_phone = trim( Startsend_get_options( 'Startsend_woocommerce_admin_sms_recipients', 'admin_setting', '' ) );
			//Get default country v1.1.17
			$admin_country = Startsend_get_options('Startsend_woocommerce_api_gate', 'Startsend_setting', '' );

			//If multiple number, need to call check_and_get_phone_number multiple time
			if ( $admin_phone != '' ) {
				$phone_no_array = explode( ",", $admin_phone );
				foreach ( $phone_no_array as $number ) {
					if ( $number != '' ) {
						//Get default country v1.1.17
						$phone_with_country_code = $this->check_and_get_phone_number($number, $admin_country);			
						if ( $phone_with_country_code !== false ) {
							$this->log->add( 'StartSend', 'Admin\'s phone number (' . $number . ') in country (' . $admin_country . ') converted to ' . $phone_with_country_code );
						} else {
							$phone_with_country_code = $number;
						}
						$admin_phone_no = $this->phone_number_processing( $phone_with_country_code );
						$admin_phone_no = str_replace( ',', ' ', $admin_phone_no );
						if ( $admin_phone_no == '' || $message == '' ) {
							return;
						}
						$this->send_sms( $admin_phone_no, $message );
					}
				}
			}
		}
	}

	/**
	 * Send SMS function
	 * @param $phone_no
	 * @param $message
	 * @param string $medium
	 */
	public function send_sms( $phone_no, $message, $medium = 'wordpress_order_notification' ) {

		require_once plugin_dir_path( __DIR__ ) . 'lib/StartSend.php';

		$api_token    = Startsend_get_options( "Startsend_woocommerce_api_token", 'Startsend_setting', '' );
		$api_gate    = Startsend_get_options( "Startsend_woocommerce_api_gate", 'Startsend_setting', '' );
		$alphaname_id = Startsend_get_options( "Startsend_woocommerce_sms_from", 'Startsend_setting', '' );

		if ( $api_token == '' || $api_gate == '' ) {
			$this->log->add( 'StartSend', "Failed sent SMS, required field empty: api_token = ($api_token)" );
			return;
		}

		$this->log->add( 'StartSend', 'Sending SMS to ' . $phone_no . ', message: ' . $message );

		try {
			$Startsend_rest = new StartSend( $api_token, $api_gate );
			$rest_response  = $Startsend_rest->sendQuickSMS( $message, $phone_no, $alphaname_id);
			$this->log->add( 'StartSend', 'SMS response from SMS gateway: ' . json_encode( $rest_response ) );
		} catch ( Exception $e ) {
			$this->log->add( 'StartSend', 'Failed sent SMS: ' . $e->getMessage() );
		}
	}

	/**
	 * Check phone number
	 */
	protected function check_and_get_phone_number( $phone_number, $country ) {
		if ($phone_number) {
			return $phone_number;
		}
		return false;
	}

	/**
	 * Replace keywords
	 * @param $message
	 * @param $order_details
	 * @param $user_type
	 * @param $order_status
	 * @return string|string[]
	 */
	protected function replace_order_keyword( $message, $order_details, $user_type, $order_status ) {
		/** @var WC_Order $order_details */
		$items            = $order_details->get_items();
		$product_name     = '';
		$product_with_qty = '';
		foreach ( $items as $item ) {
			$product_name     .= ', ' . $item->get_name();
			$product_with_qty .= ', ' . $item->get_name() . ' X ' . $item->get_quantity();
		}
		if ( $product_name ) {
			$product_name     = substr( $product_name, 2 );
			$product_with_qty = substr( $product_with_qty, 2 );
		}

		$search  = array(
			'[shop_name]',
			'[shop_email]',
			'[shop_url]',
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
			$order_details->get_order_number(),
			$order_details->get_currency(),
			$order_details->get_total(),
			ucfirst( $order_details->get_status() ),
			$product_name,
			$product_with_qty,
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
		$message = str_replace( $search, $replace, $message );

		$additional_billing_fields_array = $this->get_additional_billing_fields();
		foreach ( $additional_billing_fields_array as $field ) {
			$post_data = get_post_meta( $order_details->get_order_number(), $field, true );
			$message   = str_replace( '[' . $field . ']', $post_data, $message );
		}

		$status_for_basc = array( 'on-hold', 'pending', 'processing' );
		if ( $user_type == 'customer' && in_array( $order_status, $status_for_basc ) && strpos( $message, '[bank_details]' ) !== false ) {
			$bank_message          = '';
			$bank_message_template = '[bank_name] - [account_name] (Acc No.: [account_number], Sort code: [sort_code], IBAN: [iban], BIC: [bic])';
			$bank_details          = new WC_Gateway_BACS();
			if ( $order_details->payment_method == 'bacs' ) {
				foreach ( $bank_details->account_details as $details ) {
					if ( $details['bank_name'] != '' && $details['account_name'] != '' && $details['account_number'] != '' ) {
						$search       = array(
							'[bank_name]',
							'[account_name]',
							'[account_number]',
							'[sort_code]',
							'[iban]',
							'[bic]'
						);
						$replace      = array(
							$details['bank_name'],
							$details['account_name'],
							$details['account_number'],
							$details['sort_code'],
							$details['iban'],
							$details['bic']
						);
						$bank_message .= ', ' . str_replace( $search, $replace, $bank_message_template );
					}
				}
				$bank_message = str_replace( ' Sort code: ,', '', $bank_message );
				$bank_message = str_replace( ' IBAN: ,', '', $bank_message );
				$bank_message = str_replace( ', BIC: )', ')', $bank_message );

				if ( $bank_message ) {
					$bank_message = 'Bank details: ' . substr( $bank_message, 2 );
				}
			}
			$message = TRIM( str_replace( '[bank_details]', $bank_message, $message ) );
		}

		return $message;
	}

	/**
	 * Phone number processing
	 * @param $phone_no
	 * @return false|string
	 */
	protected function phone_number_processing( $phone_no ) {
		$updated_phone_no = '';
		if ( $phone_no != '' ) {
			$phone_no_array = explode( ",", $phone_no );
			foreach ( $phone_no_array as $number ) {
				if ( $number != '' ) {
					$number           = preg_replace( "/[^0-9,.]/", "", $number );
					$updated_phone_no .= ',' . $number;
				}
			}
			$updated_phone_no = substr( $updated_phone_no, 1 );
		}

		return $updated_phone_no;
	}

	protected function get_additional_billing_fields() {
		$default_billing_fields   = array(
			'billing_first_name',
			'billing_last_name',
			'billing_company',
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

?>
