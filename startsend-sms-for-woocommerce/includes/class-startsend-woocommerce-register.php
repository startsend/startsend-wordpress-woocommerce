<?php

class Startsend_WooCommerce_Register {
	/* @var Startsend_Register_Interface[] $instances_to_be_register */
	protected $instances_to_be_register = array();

	public function add( Startsend_Register_Interface $instance ) {
		$this->instances_to_be_register[] = $instance;

		return $this;
	}

	public function load() {
		foreach ( $this->instances_to_be_register as $instance ) {
			$instance->register();
		}
	}
}

?>
