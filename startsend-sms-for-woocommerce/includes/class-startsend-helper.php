<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 4/10/2019
 * Time: 12:16 PM.
 */

if ( ! function_exists( 'Startsend_get_options' ) ) {
	/**
	 * @param $option
	 * @param $section
	 * @param array|string $default
	 *
	 * @return mixed
	 */
	function Startsend_get_options( $option, $section, $default = '' ) {
		$options = get_option( $section );

		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}

		return $default;
	}
}

if ( ! function_exists( 'Startsend_add_actions' ) ) {
	/**
	 * @param array $hook_actions
	 */
	function Startsend_add_actions( $hook_actions ) {
		foreach ( $hook_actions as $hook ) {
			add_action( $hook['hook'], $hook['function_to_be_called'] );
		}
	}
}
