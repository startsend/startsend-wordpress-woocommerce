<?php

class Startsend_Multivendor_Setting implements Startsend_Register_Interface {
	public function register() {
		add_filter( 'Startsend_setting_section', array( $this, 'set_multivendor_setting_section' ) );
		add_filter( 'Startsend_setting_fields', array( $this, 'set_multivendor_setting_field' ) );
	}

	public function set_multivendor_setting_section( $sections ) {
		$sections[] = array(
			'id'    => 'multivendor_setting',
			'title' => __( 'Multivendor Settings', 'startsend-woocoommerce' )
		);

		return $sections;
	}

	public function set_multivendor_setting_field( $setting_fields ) {
		$setting_fields['multivendor_setting'] = array(
			array(
				'name'    => 'Startsend_multivendor_vendor_send_sms',
				'label'   => __( 'Enable Vendor SMS Notifications', 'startsend-woocoommerce' ),
				'desc'    => 'Enable',
				'type'    => 'checkbox',
				'default' => 'off',
			),
			array(
				'name'    => 'Startsend_multivendor_vendor_send_sms_on',
				'label'   => __( 'Send notification on', 'startsend-woocoommerce' ),
				'desc'    => __( 'Choose when to send a status notification message to your vendors', 'startsend-woocoommerce' ),
				'type'    => 'multicheck',
				'default' => array(
					'pending'    => 'pending',
					'on-hold'    => 'on-hold',
					'processing' => 'processing',
					'completed'  => 'completed',
					'cancelled'  => 'cancelled',
					'refunded'   => 'refunded',
					'failed'     => 'failed'
				),
				'options' => array(
					'pending'    => ' Pending',
					'on-hold'    => ' On-hold',
					'processing' => ' Processing',
					'completed'  => ' Completed',
					'cancelled'  => ' Cancelled',
					'refunded'   => ' Refunded',
					'failed'     => ' Failed'
				)
			),
			array(
				'name'    => 'Startsend_multivendor_selected_plugin',
				'label'   => __( 'Third Party Plugin', 'startsend-woocoommerce' ),
				'desc'    => 'Change this when auto detect multivendor plugin not working<br /><span id="multivendor_setting[multivendor_helper_desc]"></span>',
				'type'    => 'select',
				'default' => 'auto',
				'options' => array(
					'auto'             => 'Auto Detect',
					'product_vendors'  => 'Woocommerce Product Vendors',
					'wc_marketplace'   => 'WC Marketplace',
					'wc_vendors'       => 'WC Vendors Marketplace',
					'wcfm_marketplace' => 'WooCommerce Multivendor Marketplace',
					'dokan'            => 'Dokan',
					'yith'             => 'YITH WooCommerce Multi Vendor'
				)
			),
			array(
				'name'    => 'Startsend_multivendor_vendor_sms_template',
				'label'   => __( 'Vendor SMS Message', 'startsend-woocoommerce' ),
				'desc'    => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="multivendor" data-attr-target="multivendor_setting[Startsend_multivendor_vendor_sms_template]" class="button button-secondary">Keywords</button>',
				'type'    => 'textarea',
				'rows'    => '8',
				'cols'    => '500',
				'css'     => 'min-width:350px;',
				'default' => __( '[shop_name] : You have a new order with order ID [order_id] and order amount [order_currency] [order_amount]. The order is now [order_status].', 'startsend-woocoommerce' )
			),
			array(
				'name'  => 'export_multivendor_log',
				'label' => 'Export Log',
				'desc'  => '<a href="' . admin_url( 'admin.php?page=startsend-download-file&file=Startsend_Multivendor' ) . '" class="button button-secondary">Export</a>',
				'type'  => 'html'
			),
		);

		return $setting_fields;
	}
}

?>
