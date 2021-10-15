<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 4/10/2019
 * Time: 2:47 PM.
 */

class Startsend_Multivendor implements Startsend_Register_Interface {
	public function register() {
		$this->required_files();
		//create notification instance
		$Startsend_notification = new Startsend_Multivendor_Notification( 'Wordpress-Woocommerce-Multivendor-Extension-' . Startsend_Multivendor_Factory::$activatedPlugin );

		$registerInstance = new Startsend_WooCommerce_Register();
		$registerInstance->add( new Startsend_Multivendor_Hook( $Startsend_notification ) )
		                 ->add( new Startsend_Multivendor_Setting() )
		                 ->load();
	}

	protected function required_files() {
		require_once __DIR__ . '/admin/class-startsend-multivendor-setting.php';
		require_once __DIR__ . '/abstract/abstract-startsend-multivendor.php';
		require_once __DIR__ . '/contracts/class-startsend-multivendor-interface.php';
		require_once __DIR__ . '/class-startsend-multivendor-factory.php';
		require_once __DIR__ . '/class-startsend-multivendor-hook.php';
		require_once __DIR__ . '/class-startsend-multivendor-notification.php';
	}
}
