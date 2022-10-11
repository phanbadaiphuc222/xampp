<?php

namespace Yay_Currency\CompatiblePlugins;

use Yay_Currency\WooCommerceCurrency;

defined( 'ABSPATH' ) || exit;

// Link plugin: https://crocoblock.com/plugins/jetsmartfilters/

class JetSmartFilters {
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
		if ( ! class_exists( 'Jet_Smart_Filters' ) ) {
			return;
		}
		$this->yay_currency = WooCommerceCurrency::getInstance();
		add_filter( 'jet-smart-filters/filter-instance/args', array( $this, 'custom_query_args' ), 10, 2 );
		add_filter( 'jet-smart-filters/query/final-query', array( $this, 'custom_final_query' ) );
		add_filter( 'woocommerce_get_price_html', array( $this, 'custom_price_html_ajax' ), 10, 2 );
	}

	public function custom_query_args( $args ) {

		if ( '_price' === $args['query_var'] ) {
			$converted_args_min_price = $this->yay_currency->calculate_price_by_currency( $args['min'], true );
			$converted_args_max_price = $this->yay_currency->calculate_price_by_currency( $args['max'], true );
			$args['min']              = (float) number_format( $converted_args_min_price, (int) $this->yay_currency->apply_currency['numberDecimal'], null, '' );
			$args['max']              = (float) number_format( $converted_args_max_price, (int) $this->yay_currency->apply_currency['numberDecimal'], null, '' );
		}
		return $args;
	}

	public function custom_final_query( $args ) {

		$providers     = strtok( $args['jet_smart_filters'], '/' );
		$provider_list = array( 'jet-woo-products-grid', 'jet-woo-products-list', 'epro-products', 'epro-archive-products', 'woocommerce-shortcode', 'woocommerce-archive' );

		if ( in_array( $providers, $provider_list ) ) {

			if ( ! empty( $args['meta_query'] ) && is_array( $args['meta_query'] ) ) {

				foreach ( $args['meta_query'] as $index => $value ) {

					if ( '_price' === $args['meta_query'][ $index ]['key'] && ! empty( $args['meta_query'][ $index ]['value'] ) ) {

						if ( is_array( $args['meta_query'][ $index ]['value'] ) ) {
							$original_min_price = $this->yay_currency->reverse_calculate_price_by_currency( $args['meta_query'][ $index ]['value'][0] );
							$original_max_price = $this->yay_currency->reverse_calculate_price_by_currency( $args['meta_query'][ $index ]['value'][1] );

							$args['meta_query'][ $index ]['value'][0] = $original_min_price;
							$args['meta_query'][ $index ]['value'][1] = $original_max_price;
						}
					}
				}
			}
		}

		return $args;

	}

	public function custom_price_html_ajax( $price_html, $product ) {
		if ( wp_doing_ajax() ) {
			$price           = $product->get_price();
			$converted_price = $this->yay_currency->calculate_price_by_currency_cookie( $price );
			$price_html      = wc_price(
				$converted_price,
				$this->yay_currency->get_apply_currency_format_info()
			);
		}
		return $price_html;
	}
}
