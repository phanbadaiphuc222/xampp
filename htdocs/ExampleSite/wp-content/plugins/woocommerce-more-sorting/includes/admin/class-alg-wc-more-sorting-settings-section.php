<?php
/**
 * More Sorting for WooCommerce - Section Settings
 *
 * @version 3.1.2
 * @since   3.1.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Alg_WC_More_Sorting_Settings_Section' ) ) :

class Alg_WC_More_Sorting_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.1.2
	 * @since   3.1.0
	 */
	function __construct() {
		add_filter( 'woocommerce_get_sections_alg_more_sorting',                   array( $this, 'settings_section' ) );
		add_filter( 'woocommerce_get_settings_alg_more_sorting' . '_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
	}

	/**
	 * settings_section.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function settings_section( $sections ) {
		$sections[ $this->id ] = $this->desc;
		return $sections;
	}

}

endif;
