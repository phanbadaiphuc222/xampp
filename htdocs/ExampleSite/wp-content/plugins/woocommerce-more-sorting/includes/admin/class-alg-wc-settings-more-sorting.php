<?php
/**
 * WooCommerce More Sorting - Settings
 *
 * @version 3.1.2
 * @since   2.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Alg_WC_Settings_More_Sorting' ) ) :

class Alg_WC_Settings_More_Sorting extends WC_Settings_Page {

	/**
	 * Constructor.
	 *
	 * @version 3.0.0
	 * @since   2.0.0
	 */
	function __construct() {
		$this->id    = 'alg_more_sorting';
		$this->label = __( 'More Sorting', 'woocommerce-more-sorting' );
		parent::__construct();
		add_action( 'admin_notices', array( $this, 'settings_saved_admin_notice' ) );
	}

	/**
	 * settings_saved_admin_notice.
	 *
	 * @since   3.2.0
	 */
	function settings_saved_admin_notice() {
		if ( ! empty( $_GET['alg_wc_more_sorting_settings_saved'] ) ) {
			WC_Admin_Settings::add_message( __( 'Your settings have been saved.', 'woocommerce' ) );
		}
	}

	/**
	 * get_settings.
	 *
	 * @version 3.1.2
	 * @since   2.0.0
	 */
	function get_settings() {
		global $current_section;
		// Section settings
		$settings = apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() );
		// "Reset section" settings
		$settings = array_merge( $settings, array(
			array(
				'title'     => __( 'Reset Section Settings', 'woocommerce-more-sorting' ),
				'type'      => 'title',
				'id'        => 'alg_more_sorting' . '_' . $current_section . '_reset_options',
			),
			array(
				'title'     => __( 'Reset Settings', 'woocommerce-more-sorting' ),
				'desc'      => '<strong>' . __( 'Reset', 'woocommerce-more-sorting' ) . '</strong>',
				'id'        => 'alg_more_sorting' . '_' . $current_section . '_reset',
				'default'   => 'no',
				'type'      => 'checkbox',
			),
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_more_sorting' . '_' . $current_section . '_reset_options',
			),
		) );
		// WooCommerce v3.2.0 compatibility
		if ( ! isset( $this->is_wc_version_below_3_2_0 ) ) {
			$this->is_wc_version_below_3_2_0 = version_compare( get_option( 'woocommerce_version', null ), '3.2.0', '<' );
		}
		if ( ! $this->is_wc_version_below_3_2_0 ) {
			foreach ( $settings as &$setting ) {
				if ( isset( $setting['type'] ) && 'select' === $setting['type'] ) {
					if ( ! isset( $setting['class'] ) || '' === $setting['class'] ) {
						$setting['class'] = 'wc-enhanced-select';
					} else {
						$setting['class'] .= ' ' . 'wc-enhanced-select';
					}
				}
			}
		}
		return $settings;
	}

	/**
	 * maybe_reset_settings.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 * @todo    (maybe) reset `alg_wc_more_sorting_custom_meta_sorting_total_number` to 100 (max)
	 */
	function maybe_reset_settings() {
		global $current_section;
		if ( 'yes' === get_option( $this->id . '_' . $current_section . '_reset', 'no' ) ) {
			foreach ( $this->get_settings() as $value ) {
				if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
					delete_option( $value['id'] );
					$autoload = isset( $value['autoload'] ) ? ( bool ) $value['autoload'] : true;
					add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
				}
			}
		}
	}

	/**
	 * Save settings.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function save() {
		parent::save();
		$this->maybe_reset_settings();
		wp_safe_redirect( add_query_arg( 'alg_wc_more_sorting_settings_saved', true ) );
		exit;
	}
}

endif;

return new Alg_WC_Settings_More_Sorting();
