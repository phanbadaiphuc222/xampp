<?php

namespace Yay_Currency;

use WP_Query;
use Yay_Currency\WooCommerceCurrency;
use Yay_Currency\Settings;

defined( 'ABSPATH' ) || exit;

class SingleProductDropdown {

	private static $instance = null;

	public $apply_currencies = array();

	public $all_currencies = array();

	public $selected_currency_ID = null;

	public $settings_data;

	public function __construct() {     }

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
		$this->settings_data    = Settings::getInstance();
		$this->apply_currencies = $this->settings_data->yay_list_currencies;
		$this->all_currencies   = $this->settings_data->woo_list_currencies;

		if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
			$this->selected_currency_ID = sanitize_key( $_COOKIE['yay_currency_widget'] );
		}

		$is_show_on_single_product_page = get_option( 'yay_currency_show_single_product_page', 1 );

		if ( $is_show_on_single_product_page ) {
			$switcherPositionOnSingleProductPage = get_option( 'yay_currency_switcher_position_on_single_product_page', 'after_description' );
			if ( 'after_description' === $switcherPositionOnSingleProductPage ) {
				add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'dropdown_price_in_different_currency' ) );
			} else {
				add_action( 'woocommerce_single_product_summary', array( $this, 'dropdown_price_in_different_currency' ) );
			}
		}
	}

	public function dropdown_price_in_different_currency() {
		if ( isset( $_REQUEST['yay-currency-nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['yay-currency-nonce'] ), 'yay-currency-check-nonce' ) ) {
			if ( isset( $_POST['currency'] ) ) {
				$this->selected_currency_ID = sanitize_text_field( $_POST['currency'] );
			}
		}
		if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
			$this->selected_currency_ID = sanitize_key( $_COOKIE['yay_currency_widget'] );
		}
		$selected_currency_args              = array(
			'p'         => (int) $this->selected_currency_ID,
			'post_type' => 'yay-currency-manage',
		);
		$selected_currency_query_result      = new WP_Query( $selected_currency_args );
		$selected_currency_info              = $selected_currency_query_result->post;
		$yay_currency                        = WooCommerceCurrency::getInstance();
		$is_show_flag_in_switcher            = get_option( 'yay_currency_show_flag_in_switcher', 1 );
		$is_show_currency_name_in_switcher   = get_option( 'yay_currency_show_currency_name_in_switcher', 1 );
		$is_show_currency_symbol_in_switcher = get_option( 'yay_currency_show_currency_symbol_in_switcher', 1 );
		$is_show_currency_code_in_switcher   = get_option( 'yay_currency_show_currency_code_in_switcher', 1 );
		$switcher_size                       = get_option( 'yay_currency_switcher_size', 'medium' );

		$no_currency_name_class                 = ! $is_show_currency_name_in_switcher ? ' no-currency-name' : '';
		$only_currency_name_class               = $is_show_currency_name_in_switcher && ! $is_show_flag_in_switcher && ! $is_show_currency_symbol_in_switcher && ! $is_show_currency_code_in_switcher ? ' only-currency-name' : '';
		$only_currency_name_and_something_class = $is_show_currency_name_in_switcher && 2 === Helper::count_display_elements_in_switcher( $is_show_flag_in_switcher, $is_show_currency_name_in_switcher, $is_show_currency_symbol_in_switcher, $is_show_currency_code_in_switcher ) ? ' only-currency-name-and-something' : '';

		$switcher_settings_info = array(
			'woo_currencies'                         => $this->all_currencies,
			'selected_currencies'                    => $this->apply_currencies,
			'selected_currency'                      => $selected_currency_info,
			'selected_currency_ID'                   => $this->selected_currency_ID,
			'is_show_flag'                           => $is_show_flag_in_switcher,
			'is_show_currency_name'                  => $is_show_currency_name_in_switcher,
			'is_show_currency_symbol'                => $is_show_currency_symbol_in_switcher,
			'is_show_currency_code'                  => $is_show_currency_code_in_switcher,
			'switcher_size'                          => $switcher_size,
			'yay_currency'                           => $yay_currency,
			'no_currency_name_class'                 => $no_currency_name_class,
			'only_currency_name_class'               => $only_currency_name_class,
			'only_currency_name_and_something_class' => $only_currency_name_and_something_class,
		);
		require YAY_CURRENCY_PLUGIN_DIR . 'includes/templates/switcherTemplate.php';
	}
}
