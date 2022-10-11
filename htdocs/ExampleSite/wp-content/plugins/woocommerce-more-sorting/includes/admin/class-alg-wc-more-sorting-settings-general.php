<?php
/**
 * WooCommerce More Sorting - General Section Settings
 *
 * @version 3.2.5
 * @since   2.0.0
 * @author  Algoritmika Ltd.
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Alg_WC_More_Sorting_Settings_General' ) ) :

class Alg_WC_More_Sorting_Settings_General extends Alg_WC_More_Sorting_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.1.0
	 */
	function __construct() {
		$this->id   = '';
		$this->desc = __( 'General', 'woocommerce-more-sorting' );
		parent::__construct();
		add_action( 'woocommerce_admin_field_alg_wc_more_sorting_dashboard', array( $this, 'output_dashboard' ) );
	}

	/**
	 * get_settings_data.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function get_settings_data() {
		return array(
			'custom_sorting' => array(
				'title'                  => __( 'Custom Sorting', 'woocommerce-more-sorting' ),
				'desc'                   => __( 'Custom sorting options: sort by title, sort by SKU, sort by stock quantity and more.', 'woocommerce-more-sorting' ),
				'enabled_option_id'      => 'alg_wc_more_sorting_custom_sorting_enabled',
				'enabled_option_default' => 'yes',
			),
			'custom_meta_sorting' => array(
				'title'                  => __( 'Custom Meta Sorting', 'woocommerce-more-sorting' ),
				'desc'                   => __( 'Sorting by custom meta fields.', 'woocommerce-more-sorting' ),
				'enabled_option_id'      => 'alg_wc_more_sorting_custom_meta_sorting_enabled',
				'enabled_option_default' => 'no',
			),
			'default_wc_sorting' => array(
				'title'                  => __( 'Default WooCommerce Sorting', 'woocommerce-more-sorting' ),
				'desc'                   => __( 'Customize default WooCommerce sorting options.', 'woocommerce-more-sorting' ),
				'enabled_option_id'      => 'alg_wc_more_sorting_default_sorting_enabled',
				'enabled_option_default' => 'no',
			),
			'rearrange_sorting' => array(
				'title'                  => __( 'Rearrange Sorting', 'woocommerce-more-sorting' ),
				'desc'                   => __( 'Rearrange sorting order on frontend.', 'woocommerce-more-sorting' ),
				'enabled_option_id'      => 'alg_wc_more_sorting_rearrange_enabled',
				'enabled_option_default' => 'no',
			),
			'remove_sorting' => array(
				'title'                  => __( 'Remove Sorting', 'woocommerce-more-sorting' ),
				'desc'                   => __( 'Remove all sorting (including WooCommerce default).', 'woocommerce-more-sorting' ),
				'enabled_option_id'      => 'alg_wc_more_sorting_remove_all_enabled',
				'enabled_option_default' => 'no',
			),
			'advanced' => array(
				'title'                  => __( 'Advanced', 'woocommerce-more-sorting' ),
				'desc'                   => __( 'Restore default WooCommerce sorting.', 'woocommerce-more-sorting' ),
				'enabled_option_id'      => 'alg_wc_more_sorting_restore_wc_default_enabled',
				'enabled_option_default' => 'no',
			),
		);
	}

	/**
	 * output_dashboard.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function output_dashboard( $value ) {
		$settings_data = $this->get_settings_data();
		$table_data = array(
			array(
				'<strong>' . __( 'Section', 'woocommerce-more-sorting' ) . '</strong>',
				'<strong>' . __( 'Description', 'woocommerce-more-sorting' ) . '</strong>',
				'<strong>' . __( 'Status', 'woocommerce-more-sorting' ) . '</strong>',
			),
		);
		foreach ( $settings_data as $settings_id => $settings_info ) {
			$table_data[] = array(
				'<strong>' . $settings_info['title'] . '</strong>' .
					'<div class="row-actions visible">' .
						'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_more_sorting&section=' . $settings_id ) . '">' .
							__( 'Settings', 'woocommerce-more-sorting' ) . '</a>' .
					'</div>',
				'<em>' . $settings_info['desc'] . '</em>',
				'<em>' . ( 'yes' === get_option( $settings_info['enabled_option_id'], $settings_info['enabled_option_default'] ) ?
					'<span style="color:green;">' . __( 'Enabled', 'woocommerce-more-sorting' ) . '</span>' : __( 'Disabled', 'woocommerce-more-sorting' ) ) . '</em>',
			);
		}
		$table_html = alg_get_table_html( $table_data, array( 'table_class' => 'widefat striped', 'table_heading_type' => 'horizontal' ) );
		echo '<h2>' . __( 'Dashboard', 'woocommerce-more-sorting' ) . '</h2>';
		echo '<tr valign="top"><td colspan="2">' . $table_html  . '</td></tr>';
	}

	/**
	 * get_settings.
	 *
	 * @version 3.2.8
	 */
	public static function get_settings() {
		$settings = array(
			array(
				'title'     => __( 'More Sorting Options', 'woocommerce-more-sorting' ),
				'type'      => 'title',
				'id'        => 'alg_wc_more_sorting_options',
			),
			array(
				'title'     => __( 'More Sorting for WooCommerce', 'woocommerce-more-sorting' ),
				'desc'      => '<strong>' . __( 'Enable Plugin', 'woocommerce-more-sorting' ) . '</strong>',
				'desc_tip'  => __( 'Add new custom, rearrange, remove or rename WooCommerce sorting options.', 'woocommerce-more-sorting' )
					. '<br /><br /><a href="https://wpwham.com/documentation/more-sorting-options-for-woocommerce/?utm_source=documentation_link&utm_campaign=free&utm_medium=more_sorting" target="_blank" class="button">'
					. __( 'Documentation', 'woocommerce-more-sorting' ) . '</a>',
				'id'        => 'alg_wc_more_sorting_enabled',
				'default'   => 'yes',
				'type'      => 'checkbox',
			),
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_wc_more_sorting_options',
			),
			array(
				'type'      => 'alg_wc_more_sorting_dashboard',
			),
		);
		return $settings;
	}

}

endif;

return new Alg_WC_More_Sorting_Settings_General();
