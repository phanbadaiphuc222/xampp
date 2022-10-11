<?php

namespace Yay_Currency;

use Yay_Currency\Settings;
use Yay_Currency\ExchangeRateAPI;

defined( 'ABSPATH' ) || exit;

class WooCommerceSettingGeneral {
	protected static $instance = null;
	public $yay_currency       = null;
	public $exchange_rate_api;

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
		$this->exchange_rate_api = new ExchangeRateAPI();
		add_filter( 'woocommerce_admin_settings_sanitize_option_woocommerce_currency', array( $this, 'update_currency_option' ), 10, 3 );
		add_filter( 'woocommerce_admin_settings_sanitize_option_woocommerce_currency_pos', array( $this, 'update_currency_meta_option' ), 10, 3 );
		add_filter( 'woocommerce_admin_settings_sanitize_option_woocommerce_price_thousand_sep', array( $this, 'update_currency_meta_option' ), 10, 3 );
		add_filter( 'woocommerce_admin_settings_sanitize_option_woocommerce_price_decimal_sep', array( $this, 'update_currency_meta_option' ), 10, 3 );
		add_filter( 'woocommerce_admin_settings_sanitize_option_woocommerce_price_num_decimals', array( $this, 'update_currency_meta_option' ), 10, 3 );
	}

	// Update Currency when save WooCommerce Setting General
	public function update_currency_option( $value, $option, $raw_value ) {
		$settings_data = Settings::getInstance();
		$currencies    = $settings_data->yay_list_currencies;

		if ( $currencies ) {
			$this->currency_update = $value;
			$currency_update       = get_page_by_title( $value, OBJECT, 'yay-currency-manage' );
			if ( empty( $currency_update ) ) {
				$new_currency    = array(
					'post_title'  => $value,
					'post_type'   => 'yay-currency-manage',
					'post_status' => 'publish',
					'menu_order'  => '0',
				);
				$new_currency_ID = wp_insert_post( $new_currency );
				if ( $new_currency_ID ) {
					update_post_meta( $new_currency_ID, 'rate', '1' );
					update_post_meta( $new_currency_ID, 'rate_type', 'auto' );
					update_post_meta(
						$new_currency_ID,
						'fee',
						array(
							'value' => '0',
							'type'  => 'fixed',
						)
					);
					update_post_meta( $new_currency_ID, 'status', '1' );
					update_post_meta( $new_currency_ID, 'payment_methods', array( 'all' ) );
					update_post_meta( $new_currency_ID, 'countries', array( 'default' ) );
					update_post_meta( $new_currency_ID, 'rounding_type', 'disabled' );
					update_post_meta( $new_currency_ID, 'rounding_value', 1 );
					update_post_meta( $new_currency_ID, 'subtract_amount', 0 );
				}

				// Remove last currency to make sure only limit to 3 currencies in lite version
				$last_currency = array_pop( $currencies );
				wp_delete_post( $last_currency->ID, true );
			} else {
				update_post_meta( $currency_update->ID, 'rate', '1' );
				update_post_meta(
					$currency_update->ID,
					'fee',
					array(
						'value' => '0',
						'type'  => get_post_meta(
							$currency_update->ID,
							'fee'
						)[0]['type'],
					)
				);
			}
			$this->update_exchange_rate_currency( $currencies, $value );
		}
		return $value;
	}

	public function update_currency_meta_option( $value, $option, $raw_value ) {
		if ( null != $this->currency_update ) {
			$currency_update = get_page_by_title( $this->currency_update, OBJECT, 'yay-currency-manage' );
			if ( $currency_update ) {
				if ( 'woocommerce_currency_pos' == $option['id'] ) {
					update_post_meta( $currency_update->ID, 'currency_position', $value );
				}
				if ( 'woocommerce_price_thousand_sep' == $option['id'] ) {
					update_post_meta( $currency_update->ID, 'thousand_separator', $value );
				}
				if ( 'woocommerce_price_decimal_sep' == $option['id'] ) {
					update_post_meta( $currency_update->ID, 'decimal_separator', $value );
				}
				if ( 'woocommerce_price_num_decimals' == $option['id'] ) {
					update_post_meta( $currency_update->ID, 'number_decimal', $value );
				}
			}
		}

		return $value;
	}

	public function update_exchange_rate_currency( $currencies, $value ) {
		if ( '' != $value ) {

			if ( $currencies ) {
				foreach ( $currencies as $currency ) {
					if ( $currency->post_title !== $value ) {
						$currency_params_template = array(
							'$src'  => $value,
							'$dest' => $currency->post_title,
						);
						$json_data                = $this->exchange_rate_api->get_exchange_rates( $currency_params_template );
						if ( 200 !== $json_data['response']['code'] ) {
							update_post_meta( $currency->ID, 'rate', 'N/A' );
							continue;
						}
						$decoded_json_data = json_decode( $json_data['body'] );
						$exchange_rate     = 1;
						if ( isset( $decoded_json_data->chart->result[0]->indicators->quote[0]->close ) ) {
							$exchange_rate = $decoded_json_data->chart->result[0]->indicators->quote[0]->close[0];
						} else {
							$exchange_rate = $decoded_json_data->chart->result[0]->meta->previousClose;
						}
						update_post_meta( $currency->ID, 'rate', $exchange_rate );
					} else {
						update_post_meta( $currency->ID, 'rate', 1 );
					}
				}
			}
		}
	}
}
