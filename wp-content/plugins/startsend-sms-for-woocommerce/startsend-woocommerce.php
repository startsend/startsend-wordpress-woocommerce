<?php

/*
Plugin Name: StartsendAPI WooCommerce
Plugin URI:  https://app.startsend.ru/
Description: StartsendAPI Order SMS Notification for WooCommerce
Version:     1.0.3
Author:      Startsend SMS sending service
Author URI:  https://startsend.ru
License:     GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: startsend-woocommerce
*/


if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'plugins_loaded', 'Startsend_woocommerce_init', PHP_INT_MAX );

function Startsend_woocommerce_init() {
	require_once ABSPATH . '/wp-admin/includes/plugin.php';
	require_once ABSPATH . '/wp-includes/pluggable.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/contracts/class-startsend-register-interface.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-startsend-helper.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-startsend-woocommerce-frontend-scripts.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-startsend-woocommerce-hook.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-startsend-woocommerce-register.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-startsend-woocommerce-logger.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-startsend-woocommerce-notification.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-startsend-woocommerce-widget.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-startsend-download-log.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/multivendor/class-startsend-multivendor.php';
	require_once plugin_dir_path( __FILE__ ) . 'admin/class-startsend-woocommerce-setting.php';
	require_once plugin_dir_path( __FILE__ ) . 'lib/class.settings-api.php';
	require_once plugin_dir_path( __FILE__ ) . 'lib/StartSend.php'; // add StartSend class
	require_once plugin_dir_path( __FILE__ ) . 'lib/CountSmsParts.php'; // add StartSend class - devide SMS

	//create notification instance
	$Startsend_notification = new Startsend_WooCommerce_Notification();

	//register hooks and settings
	$registerInstance = new Startsend_WooCommerce_Register();
	$registerInstance->add( new Startsend_WooCommerce_Hook( $Startsend_notification ) )
	                 ->add( new Startsend_WooCommerce_Setting() )
	                 ->add( new Startsend_WooCommerce_Widget() )
	                 ->add( new Startsend_WooCommerce_Frontend_Scripts() )
	                 ->add( new Startsend_Multivendor() )
	                 ->add( new Startsend_Download_log() )
	                 ->load();
}

?>
