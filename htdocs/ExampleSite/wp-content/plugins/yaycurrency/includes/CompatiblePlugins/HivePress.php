<?php

namespace Yay_Currency\CompatiblePlugins;

use Yay_Currency\WooCommerceCurrency;

defined( 'ABSPATH' ) || exit;

// Link plugin: https://hivepress.io/

class HivePress {
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
		add_filter( 'hivepress/v1/fields/currency/display_value', array( $this, 'custom_hivepress_price' ), 10, 2 );
	}

	public function custom_hivepress_price( $price, $data ) {
		$format                      = $this->yay_currency->format_currency_position( $this->yay_currency->apply_currency['currencyPosition'] );
		$converted_number_from_price = $this->yay_currency->calculate_price_by_currency( $data->get_value(), true );
		$formatted_number_from_price = wc_price(
			$converted_number_from_price,
			array(
				'currency'           => $this->yay_currency->apply_currency['currency'],
				'decimal_separator'  => $this->yay_currency->apply_currency['decimalSeparator'],
				'thousand_separator' => $this->yay_currency->apply_currency['thousandSeparator'],
				'decimals'           => (int) $this->yay_currency->apply_currency['numberDecimal'],
				'price_format'       => $format,
			)
		);
		return $formatted_number_from_price;
	}
}
