<?php
/**
 * WooCommerce More Sorting - Custom Sorting Section Settings
 *
 * @version 3.2.5
 * @since   3.1.0
 * @author  Algoritmika Ltd.
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Alg_WC_More_Sorting_Settings_Custom_Sorting' ) ) :

class Alg_WC_More_Sorting_Settings_Custom_Sorting extends Alg_WC_More_Sorting_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function __construct() {
		$this->id   = 'custom_sorting';
		$this->desc = __( 'Custom Sorting', 'woocommerce-more-sorting' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 3.2.8
	 * @since   3.1.0
	 * @todo    (maybe) add enable/disable checkboxes (instead of disabling by setting option blank)
	 */
	public static function get_settings() {
		$settings = array(
			array(
				'title'     => __( 'Custom Sorting', 'woocommerce-more-sorting' ),
				'desc'      => __( 'Text to show on frontend. Fill option with some value to enable. Set option blank to disable.', 'woocommerce-more-sorting' ),
				'type'      => 'title',
				'id'        => 'alg_wc_more_sorting_custom_sorting_options',
			),
			array(
				'title'     => __( 'Custom Sorting', 'woocommerce-more-sorting' ),
				'desc'      => '<strong>' . __( 'Enable Section', 'woocommerce-more-sorting' ) . '</strong>',
				'id'        => 'alg_wc_more_sorting_custom_sorting_enabled',
				'default'   => 'yes',
				'type'      => 'checkbox',
			),
		);
		$enabled_by_default = array( 'title-asc', 'title-desc', 'sku-asc', 'sku-desc', 'stock_quantity-asc', 'stock_quantity-desc' );
		$custom_sorting_options = alg_wc_more_sorting_get_custom_sorting_options();
		foreach ( $custom_sorting_options as $custom_sorting_option_key => $custom_sorting_option_title ) {
			$settings[] = array(
				'title'     => $custom_sorting_option_title,
				'id'        => 'alg_wc_more_sorting_by_' . str_replace( '-', '_', $custom_sorting_option_key ) . '_text',
				'default'   => ( in_array( $custom_sorting_option_key, $enabled_by_default ) ? $custom_sorting_option_title : '' ),
				'type'      => 'text',
				'css'       => 'min-width:300px;',
			);
			if ( 'sku-desc' === $custom_sorting_option_key ) {
				$settings[] = array(
					'title'     => __( 'Sort by SKU', 'woocommerce-more-sorting' ),
					'desc'      => __( 'Sort SKUs as numbers instead of as text', 'woocommerce-more-sorting' ),
					'id'        => 'alg_wc_more_sorting_by_sku_num_enabled',
					'default'   => 'no',
					'type'      => 'checkbox',
					'custom_attributes' => apply_filters( 'alg_wc_more_sorting', array( 'disabled' => 'disabled' ), 'settings' ),
					'desc_tip'  => apply_filters( 'alg_wc_more_sorting',
						sprintf( __( 'You will need %s plugin to enable this option.', 'woocommerce-more-sorting' ),
							'<a target="_blank" href="https://wpwham.com/products/more-sorting-options-for-woocommerce/?utm_source=settings_custom_sorting&utm_campaign=free&utm_medium=more_sorting">' .
								__( 'More Sorting Options for WooCommerce Pro', 'woocommerce-more-sorting' ) . '</a>'
						),
						'settings'
					),
				);
			}
		}
		$settings = array_merge( $settings, array(
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_wc_more_sorting_custom_sorting_options',
			),
		) );
		return $settings;
	}

}

endif;

return new Alg_WC_More_Sorting_Settings_Custom_Sorting();
