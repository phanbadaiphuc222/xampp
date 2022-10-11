<?php
/**
 * WooCommerce More Sorting - Remove Sorting Section Settings
 *
 * @version 3.2.5
 * @since   3.1.0
 * @author  Algoritmika Ltd.
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Alg_WC_More_Sorting_Settings_Remove_Sorting' ) ) :

class Alg_WC_More_Sorting_Settings_Remove_Sorting extends Alg_WC_More_Sorting_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function __construct() {
		$this->id   = 'remove_sorting';
		$this->desc = __( 'Remove Sorting', 'woocommerce-more-sorting' );
		parent::__construct();
		// Add 'Remove All Sorting' checkbox to WooCommerce > Settings > Products
		if ( 'yes' === get_option( 'alg_wc_more_sorting_enabled', 'yes' ) ) {
			add_filter( 'woocommerce_product_settings', array( $this, 'add_remove_sorting_checkbox' ), 100 );
		}
	}

	/*
	 * Add "Remove All Sorting" checkbox to WooCommerce > Settings > Products > Display.
	 *
	 * @version 3.2.8
	 */
	function add_remove_sorting_checkbox( $settings ) {
		$updated_settings = array();
		foreach ( $settings as $section ) {
			if ( isset( $section['id'] ) && 'woocommerce_cart_redirect_after_add' == $section['id'] ) {
				$updated_settings[] = array(
					'title'     => __( 'More Sorting: Remove All Sorting', 'woocommerce-more-sorting' ),
					'desc'      => __( 'Completely remove sorting from the shop front end', 'woocommerce-more-sorting' ),
					'id'        => 'alg_wc_more_sorting_remove_all_enabled',
					'type'      => 'checkbox',
					'default'   => 'no',
					'custom_attributes' => apply_filters( 'alg_wc_more_sorting', array( 'disabled' => 'disabled' ), 'settings' ),
					'desc_tip'  => apply_filters( 'alg_wc_more_sorting',
						sprintf( __( 'You will need %s plugin to enable this option.', 'woocommerce-more-sorting' ),
							'<a target="_blank" href="https://wpwham.com/products/more-sorting-options-for-woocommerce/?utm_source=settings_remove_sorting&utm_campaign=free&utm_medium=more_sorting">' .
								__( 'More Sorting Options for WooCommerce Pro', 'woocommerce-more-sorting' ) . '</a>'
						),
						'settings'
					),
				);
			}
			$updated_settings[] = $section;
		}
		return $updated_settings;
	}

	/**
	 * get_settings.
	 *
	 * @version 3.2.8
	 * @since   3.1.0
	 */
	public static function get_settings() {
		$settings = array(
			array(
				'title'     => __( 'Remove Sorting', 'woocommerce-more-sorting' ),
				'type'      => 'title',
				'id'        => 'alg_wc_more_sorting_remove_options',
			),
			array(
				'title'     => __( 'Remove All Sorting (Including WooCommerce Default)', 'woocommerce-more-sorting' ),
				'desc'      => '<strong>' . __( 'Enable Section', 'woocommerce-more-sorting' ) . '</strong>',
				'id'        => 'alg_wc_more_sorting_remove_all_enabled',
				'default'   => 'no',
				'type'      => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_more_sorting', array( 'disabled' => 'disabled' ), 'settings' ),
				'desc_tip'  => apply_filters( 'alg_wc_more_sorting',
					sprintf( __( 'You will need %s plugin to enable this section.', 'woocommerce-more-sorting' ),
						'<a target="_blank" href="https://wpwham.com/products/more-sorting-options-for-woocommerce/?utm_source=settings_remove_sorting&utm_campaign=free&utm_medium=more_sorting">' .
							__( 'More Sorting Options for WooCommerce Pro', 'woocommerce-more-sorting' ) . '</a>'
					),
					'settings'
				),
			),
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_wc_more_sorting_remove_options',
			),
		);
		return $settings;
	}

}

endif;

return new Alg_WC_More_Sorting_Settings_Remove_Sorting();
