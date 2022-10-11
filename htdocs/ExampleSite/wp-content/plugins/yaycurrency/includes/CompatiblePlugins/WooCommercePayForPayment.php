<?php

namespace Yay_Currency\CompatiblePlugins;

use Yay_Currency\WooCommerceCurrency;

defined( 'ABSPATH' ) || exit;

// Link plugin: https://cs.wordpress.org/plugins/woocommerce-pay-for-payment/

class WooCommercePayForPayment {
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
		add_filter( 'woocommerce_pay4pay_charges_fixed', array( $this, 'custom_fee' ) );
		add_filter( 'woocommerce_pay4pay_charges_minimum', array( $this, 'custom_fee' ) );
		add_filter( 'woocommerce_pay4pay_charges_maximum', array( $this, 'custom_fee' ) );
	}

	public function custom_fee( $fee ) {
		if ( is_checkout() && ( 0 == $this->yay_currency->is_checkout_different_currency || 0 == $this->yay_currency->apply_currency['status'] ) ) {
			return $fee;
		}
		$fee = $this->yay_currency->calculate_price_by_currency( $fee, true );
		return $fee;
	}
}
