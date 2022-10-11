<?php

namespace Yay_Currency;

use Yay_Currency\WooCommerceCurrency;

defined( 'ABSPATH' ) || exit;

class WooCommercePriceFormat {
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
		if ( ! function_exists( 'WC' ) ) {
			return;
		}
		$this->yay_currency = WooCommerceCurrency::getInstance();

		if ( ! is_admin() ) {
			add_filter( 'woocommerce_currency', array( $this, 'change_woocommerce_currency' ), 10, 1 );
			add_filter( 'woocommerce_currency_symbol', array( $this, 'change_existing_currency_symbol' ), 10, 2 );
			add_filter( 'pre_option_woocommerce_currency_pos', array( $this, 'change_currency_position' ) );
			add_filter( 'wc_get_price_thousand_separator', array( $this, 'change_thousand_separator' ) );
			add_filter( 'wc_get_price_decimal_separator', array( $this, 'change_decimal_separator' ) );
			add_filter( 'wc_get_price_decimals', array( $this, 'change_number_decimals' ) );
		}
	}

	public function change_woocommerce_currency( $currency ) {

		if ( is_checkout() && ( 0 == $this->yay_currency->is_checkout_different_currency || 0 == $this->yay_currency->apply_currency['status'] ) ) {
			return $currency;
		}

		$currency = $this->yay_currency->apply_currency['currency'];
		return $currency;
	}

	public function change_existing_currency_symbol( $currency_symbol, $currency ) {
		$currency_unit_type = get_option( 'yay_currency_currency_unit_type', 'symbol' );

		if ( 'symbol' === $currency_unit_type ) {
			if ( '&#36;' === $currency_symbol ) { // if symbol is '$', concat it with currency code
				$currency_symbol = $currency . $currency_symbol;
			}
		} else {
			$currency_symbol = $currency;
		}

		if ( is_checkout() && ( 0 == $this->yay_currency->is_checkout_different_currency || 0 == $this->yay_currency->apply_currency['status'] ) ) {
			return $currency_symbol;
		}

		if ( is_null( $this->yay_currency->apply_currency ) ) {
			return $currency_symbol;
		}

		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			return $currency_symbol;
		}

		if ( 'symbol' === $currency_unit_type ) {
			$currency_symbol = $currency === $this->yay_currency->apply_currency['currency'] ? $this->yay_currency->apply_currency['symbol'] : $this->yay_currency->get_symbol_by_currency( $currency );
			if ( '&#36;' === $currency_symbol ) { // if symbol is '$', concat it with currency code
				$currency_symbol = $currency . $currency_symbol;
			}
			return wp_kses_post( html_entity_decode( $currency_symbol ) );
		}
		$currency_symbol = $this->yay_currency->apply_currency['currency'];
		return wp_kses_post( html_entity_decode( $currency_symbol ) );
	}

	public function change_currency_position() {
		if ( is_null( $this->yay_currency->apply_currency ) ) {
			return false;
		}
		return $this->yay_currency->apply_currency['currencyPosition'];
	}

	public function change_thousand_separator() {
		if ( is_null( $this->yay_currency->apply_currency ) ) {
			return;
		}
		return wp_kses_post( html_entity_decode( $this->yay_currency->apply_currency['thousandSeparator'] ) );
	}

	public function change_decimal_separator() {
		if ( is_null( $this->yay_currency->apply_currency ) ) {
			return;
		}
		return wp_kses_post( html_entity_decode( $this->yay_currency->apply_currency['decimalSeparator'] ) );
	}

	public function change_number_decimals() {
		if ( is_null( $this->yay_currency->apply_currency ) ) {
			return;
		}
		return wp_kses_post( html_entity_decode( $this->yay_currency->apply_currency['numberDecimal'] ) );
	}

}
