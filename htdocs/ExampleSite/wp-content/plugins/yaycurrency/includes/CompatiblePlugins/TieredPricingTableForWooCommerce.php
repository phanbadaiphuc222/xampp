<?php

namespace Yay_Currency\CompatiblePlugins;

use Yay_Currency\WooCommerceCurrency;

defined( 'ABSPATH' ) || exit;

// Link plugin: https://woocommerce.com/products/tiered-pricing-table-for-woocommerce/

class TieredPricingTableForWooCommerce {
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

		add_filter( 'tier_pricing_table/price/product_price_rules', array( $this, 'custom_product_price_rules' ), 10, 4 );
	}

	public function custom_product_price_rules( $rules, $product_id, $type, $parent_id ) {
		if ( 'fixed' === $type ) {
			$converted_rules = array_map(
				function( $rule ) {
					$rule = $this->yay_currency->calculate_price_by_currency( $rule, true );
					return $rule;
				},
				$rules
			);
			return $converted_rules;
		}
		return $rules;
	}
}
