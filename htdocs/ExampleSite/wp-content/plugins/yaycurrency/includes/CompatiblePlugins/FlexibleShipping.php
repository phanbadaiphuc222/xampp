<?php

namespace Yay_Currency\CompatiblePlugins;

use Yay_Currency\WooCommerceCurrency;

defined( 'ABSPATH' ) || exit;

// Link plugin: https://wordpress.org/plugins/flexible-shipping/

class FlexibleShipping {
	protected static $instance = null;
	public $yay_currency       = null;

	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	private function doHooks() {
		$this->yay_currency = WooCommerceCurrency::getInstance();
		add_filter( 'flexible_shipping_value_in_currency', array( $this, 'custom_shipping_fee' ), 1 );
	}

	public function custom_shipping_fee( $fee ) {
		$fee = $this->yay_currency->calculate_price_by_currency( $fee, true );
		return $fee;
	}
}
