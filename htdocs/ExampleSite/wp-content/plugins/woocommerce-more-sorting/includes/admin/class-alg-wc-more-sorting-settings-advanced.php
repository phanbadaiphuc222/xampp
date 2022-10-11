<?php
/**
 * WooCommerce More Sorting - Advanced Section Settings
 *
 * @version 3.2.5
 * @since   3.1.0
 * @author  Algoritmika Ltd.
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Alg_WC_More_Sorting_Settings_Advanced' ) ) :

class Alg_WC_More_Sorting_Settings_Advanced extends Alg_WC_More_Sorting_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function __construct() {
		$this->id   = 'advanced';
		$this->desc = __( 'Advanced', 'woocommerce-more-sorting' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 3.2.5
	 * @since   3.1.0
	 */
	public static function get_settings() {
		$settings = array(
			array(
				'title'     => __( 'Advanced: Restore Default WooCommerce Sorting', 'woocommerce-more-sorting' ),
				'desc'      => __( 'Some themes (e.g. Avada) replaces default WooCommerce sorting with theme\'s custom. With theme\'s custom sorting some (or all) plugin\'s option may not function. You can restore default WooCommerce sorting here.', 'woocommerce-more-sorting' ),
				'type'      => 'title',
				'id'        => 'alg_wc_more_sorting_advanced_restore_wc_default_options',
			),
			array(
				'title'     => __( 'Enable/Disable', 'woocommerce-more-sorting' ),
				'desc'      => '<strong>' . __( 'Enable Section', 'woocommerce-more-sorting' ) . '</strong>',
				'id'        => 'alg_wc_more_sorting_restore_wc_default_enabled',
				'default'   => 'no',
				'type'      => 'checkbox',
			),
			array(
				'title'     => __( 'Theme', 'woocommerce-more-sorting' ),
				'id'        => 'alg_wc_more_sorting_restore_wc_default_theme',
				'default'   => 'avada',
				'type'      => 'select',
				'options'   => array(
					'avada'        => __( 'Avada', 'woocommerce-more-sorting' ),
					'avada_no_css' => __( 'Avada (no CSS)', 'woocommerce-more-sorting' ),
					'revo'         => __( 'Revo', 'woocommerce-more-sorting' ),
					'revo_no_css'  => __( 'Revo (no CSS)', 'woocommerce-more-sorting' ),
					'other'        => __( 'Other', 'woocommerce-more-sorting' ),
				),
			),
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_wc_more_sorting_advanced_restore_wc_default_options',
			),
		);
		return $settings;
	}

}

endif;

return new Alg_WC_More_Sorting_Settings_Advanced();
