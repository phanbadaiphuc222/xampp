<?php
/**
 * WooCommerce More Sorting - Rearrange Sorting Section Settings
 *
 * @version 3.2.5
 * @since   3.1.0
 * @author  Algoritmika Ltd.
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Alg_WC_More_Sorting_Settings_Rearrange_Sorting' ) ) :

class Alg_WC_More_Sorting_Settings_Rearrange_Sorting extends Alg_WC_More_Sorting_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function __construct() {
		$this->id   = 'rearrange_sorting';
		$this->desc = __( 'Rearrange Sorting', 'woocommerce-more-sorting' );
		parent::__construct();
		add_action( 'woocommerce_admin_field_alg_wc_more_sorting_values', array( $this, 'output_values' ) );
	}

	/**
	 * output_values.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function output_values( $value ) {
		$table_data = array();
		foreach ( alg_get_woocommerce_sortings_order() as $value ) {
			$table_data[] = array( $value );
		}
		$table_html = alg_get_table_html( $table_data, array(
			'table_class'        => 'widefat striped',
			'table_style'        => 'width:300px;',
			'table_heading_type' => 'none',
			'columns_styles'     => array( 'padding: 0;' ),
		) );
		echo '<tr valign="top">' .
				'<th scope="row" class="titledesc"><label>' . __( 'Available Values in Default Order', 'woocommerce-more-sorting' ) . '</label>' .
				'<td class="forminp">' . $table_html . '</td>' .
		'</tr>';
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
				'title'     => __( 'Rearrange Sorting', 'woocommerce-more-sorting' ),
				'desc'      => __( 'If you want to change the order of sorting options on frontend (in drop down box), you can do that in this section.', 'woocommerce-more-sorting' ),
				'type'      => 'title',
				'id'        => 'alg_wc_more_sorting_rearrange_options',
			),
			array(
				'title'     => __( 'Rearrange Sorting', 'woocommerce-more-sorting' ),
				'desc'      => '<strong>' . __( 'Enable Section', 'woocommerce-more-sorting' ) . '</strong>',
				'id'        => 'alg_wc_more_sorting_rearrange_enabled',
				'default'   => 'no',
				'type'      => 'checkbox',
			),
			array(
				'title'     => __( 'Rearrange Sorting', 'woocommerce-more-sorting' ),
				'id'        => 'alg_wc_more_sorting_rearrange_sorting',
				'default'   => implode( PHP_EOL, alg_get_woocommerce_sortings_order() ),
				'type'      => 'textarea',
				'css'       => 'min-height:500px;min-width:300px;',
			),
			array(
				'type'      => 'alg_wc_more_sorting_values',
			),
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_wc_more_sorting_rearrange_options',
			),
		);
		return $settings;
	}

}

endif;

return new Alg_WC_More_Sorting_Settings_Rearrange_Sorting();
