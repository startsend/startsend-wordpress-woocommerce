<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 4/10/2019
 * Time: 2:15 PM.
 */

class Startsend_WooCommerce_Frontend_Scripts implements Startsend_Register_Interface {
	public function register() {
		add_action( 'admin_enqueue_scripts', array( $this, 'msmswc_admin_enqueue_scripts' ) );
	}

	public function msmswc_admin_enqueue_scripts() {
		wp_enqueue_script( 'admin-startsend-scripts', plugins_url( 'js/admin.js?v=202012071500', __DIR__ ), array( 'jquery' ), '1.1.5', true );

		//jquery modal
		wp_enqueue_style( 'admin-startsend-css', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css', array(), '0.9.1' );
		wp_enqueue_script( 'Jquery Modal', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js', array( 'jquery' ), '0.9.1', true );
	}
}
