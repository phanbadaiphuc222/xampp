<?php

namespace Yay_Currency;

use Yay_Currency\WooCommerceCurrency;
use Yay_Currency\WooCommercePriceFormat;

defined( 'ABSPATH' ) || exit;

class WooCommerceOrderAdmin {
	protected static $instance        = null;
	public $yay_currency              = null;
	public $yay_currency_price_format = null;

	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	private function doHooks() {
		$this->yay_currency              = WooCommerceCurrency::getInstance();
		$this->yay_currency_price_format = WooCommercePriceFormat::getInstance();
		add_action( 'current_screen', array( $this, 'get_current_screen' ) );
	}

	public function get_current_screen() {
		$screen = get_current_screen();
		if ( 'shop_order' === $screen->id ) {
			$order_id = isset( $_GET['post'] ) ? sanitize_key( $_GET['post'] ) : null;

			if ( $order_id ) {
				$order_data                     = wc_get_order( $order_id );
				$yay_currency_checkout_currency = $order_data->get_currency();
				$convert_currency               = array();

				foreach ( $this->yay_currency->converted_currency as $key => $value ) {
					if ( $value['currency'] == $yay_currency_checkout_currency ) {
						$convert_currency = $value;
					}
				}
				if ( $convert_currency ) {
					$this->yay_currency->apply_currency = $convert_currency;
					add_filter( 'woocommerce_product_get_price', array( $this->yay_currency, 'custom_raw_price' ), 10, 2 );
					add_filter( 'woocommerce_product_get_sale_price', array( $this->yay_currency, 'custom_raw_price' ), 10, 2 );
					add_filter( 'woocommerce_product_get_regular_price', array( $this->yay_currency, 'custom_raw_price' ), 10, 2 );

					add_filter( 'woocommerce_product_variation_get_price', array( $this->yay_currency, 'custom_raw_price' ), 10, 2 );
					add_filter( 'woocommerce_product_variation_get_regular_price', array( $this->yay_currency, 'custom_raw_price' ), 10, 2 );
					add_filter( 'woocommerce_product_variation_get_sale_price', array( $this->yay_currency, 'custom_raw_price' ), 10, 2 );

					add_filter( 'woocommerce_variation_prices_price', array( $this->yay_currency, 'custom_raw_price' ), 10, 2 );
					add_filter( 'woocommerce_variation_prices_regular_price', array( $this->yay_currency, 'custom_raw_price' ), 10, 2 );
					add_filter( 'woocommerce_variation_prices_sale_price', array( $this->yay_currency, 'custom_raw_price' ), 10, 2 );

					add_filter( 'woocommerce_get_variation_prices_hash', array( $this->yay_currency, 'custom_variation_price_hash' ) );

					add_filter( 'woocommerce_currency_symbol', array( $this->yay_currency_price_format, 'change_existing_currency_symbol' ), 10, 2 );
					add_filter( 'pre_option_woocommerce_currency_pos', array( $this->yay_currency_price_format, 'change_currency_position' ) );
					add_filter( 'wc_get_price_thousand_separator', array( $this->yay_currency_price_format, 'change_thousand_separator' ) );
					add_filter( 'wc_get_price_decimal_separator', array( $this->yay_currency_price_format, 'change_decimal_separator' ) );
					add_filter( 'wc_get_price_decimals', array( $this->yay_currency_price_format, 'change_number_decimals' ) );
				}
			}
		}
	}

}
