<?php

namespace Yay_Currency;

defined( 'ABSPATH' ) || exit;

class Helper {

	protected static $instance = null;
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {     }

	public static function sanitize_array( $var ) {
		if ( is_array( $var ) ) {
			return array_map( 'self::sanitize_array', $var );
		} else {
			return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		}
	}

	public static function sanitize( $var ) {
		return wp_kses_post_deep( $var['data'] );
	}

	public static function count_display_elements_in_switcher( $is_show_flag, $is_show_currency_name, $is_show_currency_symbol, $is_show_currency_code ) {
		$display_elements_array = array();
		$is_show_flag ? array_push( $display_elements_array, $is_show_flag ) : null;
		$is_show_currency_name ? array_push( $display_elements_array, $is_show_currency_name ) : null;
		$is_show_currency_symbol ? array_push( $display_elements_array, $is_show_currency_symbol ) : null;
		$is_show_currency_code ? array_push( $display_elements_array, $is_show_currency_code ) : null;
		return count( $display_elements_array );
	}

	public static function get_flag_by_country_code( $country_code ) {
		$flag = $country_code;
		switch ( $country_code ) {
			case 'byr':
				$flag = 'by';
				return YAY_CURRENCY_PLUGIN_URL . 'assets/dist/flags/' . $flag . '.svg';
			case 'cuc':
				$flag = 'cu';
				return YAY_CURRENCY_PLUGIN_URL . 'assets/dist/flags/' . $flag . '.svg';
			case 'irt':
				$flag = 'ir';
				return YAY_CURRENCY_PLUGIN_URL . 'assets/dist/flags/' . $flag . '.svg';
			case 'vef':
				$flag = 've';
				return YAY_CURRENCY_PLUGIN_URL . 'assets/dist/flags/' . $flag . '.svg';
			default:
				return YAY_CURRENCY_PLUGIN_URL . 'assets/dist/flags/' . $flag . '.svg';
		}
	}

	public static function generate_currency_switcher_template( $switcher_settings_info ) {
		$file = YAY_CURRENCY_PLUGIN_DIR . 'includes/templates/switcherTemplate.php';

		ob_start();
		require $file;
		$content = ob_get_clean();

		return $content;
	}
}
