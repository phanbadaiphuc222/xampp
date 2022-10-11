<?php

namespace Yay_Currency;

use Yay_Currency\WooCommerceCurrency;
use Yay_Currency\Settings;
use Yay_Currency\Helper;

defined( 'ABSPATH' ) || exit;
class MenuShortcode {

	protected static $instance   = null;
	public $apply_currencies     = array();
	public $all_currencies       = array();
	public $selected_currency_ID = null;
	public $settings_data;

	public static function getInstance() {
		if ( null == self::$instance ) {
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
		$this->all_currencies   = $this->settings_data->woo_list_currencies;
		$this->apply_currencies = $this->settings_data->yay_list_currencies;

		if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
			$this->selected_currency_ID = sanitize_key( $_COOKIE['yay_currency_widget'] );
		}

		add_shortcode( 'yaycurrency-menu-item-switcher', array( $this, 'menu_item_switcher_shortcode' ) );
	}

	public function menu_item_switcher_shortcode( $content = null ) {
		if ( isset( $_REQUEST['yay-currency-nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['yay-currency-nonce'] ), 'yay-currency-check-nonce' ) ) {

			if ( isset( $_POST['currency'] ) ) {
				$this->selected_currency_ID = sanitize_key( wp_unslash( $_POST['currency'] ) );
			}
		}
		if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
			$this->selected_currency_ID = sanitize_key( $_COOKIE['yay_currency_widget'] );
		}
		$selected_currency_args         = array(
			'p'         => (int) $this->selected_currency_ID,
			'post_type' => 'yay-currency-manage',
		);
		$selected_currency_query_result = new \WP_Query( $selected_currency_args );
		$selected_currency_info         = $selected_currency_query_result->post;
		$yay_currency                   = WooCommerceCurrency::getInstance();

		$is_show_flag_in_menu_item            = get_option( 'yay_currency_show_flag_in_menu_item', 1 );
		$is_show_currency_name_in_menu_item   = get_option( 'yay_currency_show_currency_name_in_menu_item', 1 );
		$is_show_currency_symbol_in_menu_item = get_option( 'yay_currency_show_currency_symbol_in_menu_item', 1 );
		$is_show_currency_code_in_menu_item   = get_option( 'yay_currency_show_currency_code_in_menu_item', 1 );
		$menu_item_size                       = get_option( 'yay_currency_menu_item_size', 'small' );

		$no_currency_name_class                 = ! $is_show_currency_name_in_menu_item ? ' no-currency-name' : '';
		$only_currency_name_class               = $is_show_currency_name_in_menu_item && ! $is_show_flag_in_menu_item && ! $is_show_currency_symbol_in_menu_item && ! $is_show_currency_code_in_menu_item ? ' only-currency-name' : '';
		$only_currency_name_and_something_class = $is_show_currency_name_in_menu_item && 2 === Helper::count_display_elements_in_switcher( $is_show_flag_in_menu_item, $is_show_currency_name_in_menu_item, $is_show_currency_symbol_in_menu_item, $is_show_currency_code_in_menu_item ) ? ' only-currency-name-and-something' : '';

		$yay_currency_nonce     = wp_nonce_field( 'yay-currency-check-nonce', 'yay-currency-nonce', true, false );
		$switcher_settings_info = array(
			'woo_currencies'                         => $this->all_currencies,
			'selected_currencies'                    => $this->apply_currencies,
			'selected_currency'                      => $selected_currency_info,
			'selected_currency_ID'                   => $this->selected_currency_ID,
			'is_show_flag'                           => $is_show_flag_in_menu_item,
			'is_show_currency_name'                  => $is_show_currency_name_in_menu_item,
			'is_show_currency_symbol'                => $is_show_currency_symbol_in_menu_item,
			'is_show_currency_code'                  => $is_show_currency_code_in_menu_item,
			'switcher_size'                          => $menu_item_size,
			'yay_currency'                           => $yay_currency,
			'no_currency_name_class'                 => $no_currency_name_class,
			'only_currency_name_class'               => $only_currency_name_class,
			'only_currency_name_and_something_class' => $only_currency_name_and_something_class,
		);
		$content                = Helper::generate_currency_switcher_template( $switcher_settings_info );
		return $content;
	}
}
