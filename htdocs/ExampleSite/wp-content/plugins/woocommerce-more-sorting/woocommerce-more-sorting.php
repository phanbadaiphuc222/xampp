<?php
/*
Plugin Name: More Sorting Options for WooCommerce
Plugin URI: https://wpwham.com/products/more-sorting-options-for-woocommerce/
Description: Add new custom, rearrange, remove or rename WooCommerce sorting options.
Version: 3.2.9
Author: WP Wham
Author URI: https://wpwham.com
Text Domain: woocommerce-more-sorting
Domain Path: /langs
WC requires at least: 3.0.0
WC tested up to: 6.5
Copyright: © 2018-2022 WP Wham. All rights reserved.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'alg_is_plugin_active' ) ) {
	/**
	 * alg_is_plugin_active - Check if plugin is active.
	 *
	 * @return  bool
	 * @version 3.0.2
	 * @since   2.1.0
	 */
	function alg_is_plugin_active( $plugin_file ) {
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) );
		}
		foreach ( $active_plugins as $active_plugin ) {
			$active_plugin = explode( '/', $active_plugin );
			if ( isset( $active_plugin[1] ) && $plugin_file === $active_plugin[1] ) {
				return true;
			}
		}
		return false;
	}
}

if ( ! alg_is_plugin_active( 'woocommerce.php' ) ) {
	return;
}

// Check if Pro is active, if so then return
if ( basename( __FILE__ ) === 'woocommerce-more-sorting.php' && alg_is_plugin_active( 'woocommerce-more-sorting-pro.php' ) ) {
	return;
}

if ( ! class_exists( 'Alg_Woocommerce_More_Sorting' ) ) :

/**
 * Main Alg_Woocommerce_More_Sorting Class
 *
 * @class   Alg_Woocommerce_More_Sorting
 * @version 3.2.9
 * @since   1.0.0
 */
final class Alg_Woocommerce_More_Sorting {

	/**
	 * Plugin version
	 */
	public $version = '3.2.9';

	/**
	 * @var Alg_Woocommerce_More_Sorting The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_Woocommerce_More_Sorting Instance
	 *
	 * Ensures only one instance of Alg_Woocommerce_More_Sorting is loaded or can be loaded.
	 *
	 * @static
	 * @return Alg_Woocommerce_More_Sorting - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_Woocommerce_More_Sorting Constructor.
	 *
	 * @access  public
	 * @version 3.2.5
	 * @since   3.0.0
	 */
	function __construct() {

		// Set up localisation
		load_plugin_textdomain( 'woocommerce-more-sorting', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

		// Include required files
		$this->includes();

		// Settings
		if ( is_admin() ) {
			add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
			add_action( 'woocommerce_system_status_report', array( $this, 'add_settings_to_status_report' ) );
		}
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param   mixed $links
	 * @return  array
	 * @version 3.2.8
	 * @since   3.0.0
	 */
	function action_links( $links ) {
		$custom_links = array();
		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_more_sorting' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
		if (
			basename( __FILE__ ) === 'woocommerce-more-sorting.php' &&
			! class_exists( 'Alg_Woocommerce_More_Sorting_Pro' )
		) {
			$custom_links[] = '<a href="https://wpwham.com/products/more-sorting-options-for-woocommerce/?utm_source=plugins_page&utm_campaign=free&utm_medium=more_sorting">' .
				__( 'Unlock all', 'woocommerce-more-sorting' ) . '</a>';
		}
		return array_merge( $custom_links, $links );
	}

	/**
	 * add settings to WC status report
	 *
	 * @version 3.2.5
	 * @since   3.2.5
	 * @author  WP Wham
	 */
	public static function add_settings_to_status_report() {
		#region add_settings_to_status_report
		$protected_settings        = array( 'wpwham_more_sorting_license' );
		$settings_general          = Alg_WC_More_Sorting_Settings_General::get_settings();
		$settings_custom_sort      = Alg_WC_More_Sorting_Settings_Custom_Sorting::get_settings();
		$settings_custom_meta_sort = Alg_WC_More_Sorting_Settings_Custom_Meta_Sorting::get_settings();
		$settings_default_wc_sort  = Alg_WC_More_Sorting_Settings_Default_WC_Sorting::get_settings();
		$settings_rearrange_sort   = Alg_WC_More_Sorting_Settings_Rearrange_Sorting::get_settings();
		$settings_remove_sort      = Alg_WC_More_Sorting_Settings_Remove_Sorting::get_settings();
		$settings_advanced         = Alg_WC_More_Sorting_Settings_Advanced::get_settings();
		if ( class_exists( 'Alg_WCMSO_Pro_Settings_General' ) ) {
			$settings_general = array_merge( $settings_general, Alg_WCMSO_Pro_Settings_General::get_settings( array() ) );
		}
		if ( class_exists( 'Alg_WCMSO_Pro_Settings_Custom_Sorting' ) ) {
			$settings_custom_sort = array_merge( $settings_custom_sort, Alg_WCMSO_Pro_Settings_Custom_Sorting::get_settings( array() ) );
		}
		$settings = array_merge(
			$settings_general, $settings_custom_sort, $settings_custom_meta_sort, $settings_default_wc_sort,
			$settings_rearrange_sort, $settings_remove_sort, $settings_advanced
		);
		?>
		<table class="wc_status_table widefat" cellspacing="0">
			<thead>
				<tr>
					<th colspan="3" data-export-label="More Sorting Settings"><h2><?php esc_html_e( 'More Sorting Settings', 'woocommerce-more-sorting' ); ?></h2></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $settings as $setting ): ?>
				<?php 
				if ( in_array( $setting['type'], array( 'title', 'sectionend', 'alg_wc_more_sorting_values', 'alg_wc_more_sorting_dashboard' ) ) ) { 
					continue;
				}
				if ( isset( $setting['title'] ) ) {
					$title = $setting['title'];
				} elseif ( isset( $setting['desc'] ) ) {
					$title = $setting['desc'];
				} else {
					$title = $setting['id'];
				}
				$value = get_option( $setting['id'] ); 
				if ( in_array( $setting['id'], $protected_settings ) ) {
					$value = $value > '' ? '(set)' : 'not set';
				}
				?>
				<tr>
					<td data-export-label="<?php echo esc_attr( $title ); ?>"><?php esc_html_e( $title, 'woocommerce-more-sorting' ); ?>:</td>
					<td class="help">&nbsp;</td>
					<td><?php echo is_array( $value ) ? print_r( $value, true ) : $value; ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		#endregion add_settings_to_status_report
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 3.1.2
	 */
	function includes() {

		// Functions
		require_once( 'includes/alg-wc-more-sorting-functions.php' );

		// Settings
		require_once( 'includes/admin/class-alg-wc-more-sorting-settings-section.php' );
		$this->settings = array();
		$this->settings['general']             = require_once( 'includes/admin/class-alg-wc-more-sorting-settings-general.php' );
		$this->settings['custom-sorting']      = require_once( 'includes/admin/class-alg-wc-more-sorting-settings-custom-sorting.php' );
		$this->settings['custom-meta-sorting'] = require_once( 'includes/admin/class-alg-wc-more-sorting-settings-custom-meta-sorting.php' );
		$this->settings['default-wc-sorting']  = require_once( 'includes/admin/class-alg-wc-more-sorting-settings-default-wc-sorting.php' );
		$this->settings['rearrange-sorting']   = require_once( 'includes/admin/class-alg-wc-more-sorting-settings-rearrange-sorting.php' );
		$this->settings['remove-sorting']      = require_once( 'includes/admin/class-alg-wc-more-sorting-settings-remove-sorting.php' );
		$this->settings['advanced']            = require_once( 'includes/admin/class-alg-wc-more-sorting-settings-advanced.php' );
		if ( is_admin() && $this->version != get_option( 'alg_wc_more_sorting_version', '' ) ) {
			foreach ( $this->settings as $section ) {
				foreach ( $section->get_settings() as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? ( bool ) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
			$this->handle_deprecated_options();
			update_option( 'alg_wc_more_sorting_version', $this->version );
		}

		// Core
		require_once( 'includes/class-alg-wc-more-sorting.php' );
	}

	/**
	 * handle_deprecated_options.
	 *
	 * @version 3.1.2
	 * @since   3.0.0
	 */
	function handle_deprecated_options() {
		$deprecated_settings = array(
			// v3.0.0
			'woocommerce_more_sorting_enabled'                         => 'alg_wc_more_sorting_enabled',
			'woocommerce_more_sorting_by_name_asc_text'                => 'alg_wc_more_sorting_by_title_asc_text',
			'woocommerce_more_sorting_by_name_desc_text'               => 'alg_wc_more_sorting_by_title_desc_text',
			'woocommerce_more_sorting_by_sku_asc_text'                 => 'alg_wc_more_sorting_by_sku_asc_text',
			'woocommerce_more_sorting_by_sku_desc_text'                => 'alg_wc_more_sorting_by_sku_desc_text',
			'woocommerce_more_sorting_by_sku_num_enabled'              => 'alg_wc_more_sorting_by_sku_num_enabled',
			'woocommerce_more_sorting_by_stock_quantity_asc_text'      => 'alg_wc_more_sorting_by_stock_quantity_asc_text',
			'woocommerce_more_sorting_by_stock_quantity_desc_text'     => 'alg_wc_more_sorting_by_stock_quantity_desc_text',
			'woocommerce_more_sorting_remove_all_enabled'              => 'alg_wc_more_sorting_remove_all_enabled',
			'woocommerce_more_sorting_pro_enabled'                     => 'alg_wc_more_sorting_enabled',
			'woocommerce_more_sorting_pro_by_name_asc_text'            => 'alg_wc_more_sorting_by_title_asc_text',
			'woocommerce_more_sorting_pro_by_name_desc_text'           => 'alg_wc_more_sorting_by_title_desc_text',
			'woocommerce_more_sorting_pro_by_sku_asc_text'             => 'alg_wc_more_sorting_by_sku_asc_text',
			'woocommerce_more_sorting_pro_by_sku_desc_text'            => 'alg_wc_more_sorting_by_sku_desc_text',
			'woocommerce_more_sorting_pro_by_sku_num_enabled'          => 'alg_wc_more_sorting_by_sku_num_enabled',
			'woocommerce_more_sorting_pro_by_stock_quantity_asc_text'  => 'alg_wc_more_sorting_by_stock_quantity_asc_text',
			'woocommerce_more_sorting_pro_by_stock_quantity_desc_text' => 'alg_wc_more_sorting_by_stock_quantity_desc_text',
			'woocommerce_more_sorting_pro_remove_all_enabled'          => 'alg_wc_more_sorting_remove_all_enabled',
		);
		foreach ( $deprecated_settings as $old => $new ) {
			if ( false !== ( $old_value = get_option( $old ) ) ) {
				update_option( $new, $old_value );
				delete_option( $old );
			}
		}
		// v3.1.0
		$rearranged_sorting = get_option( 'alg_wc_more_sorting_rearrange', false );
		if ( false !== $rearranged_sorting ) {
			$replacement_values = array(
				'title_asc'           => 'title-asc',
				'title_desc'          => 'title-desc',
				'sku_asc'             => 'sku-asc',
				'sku_desc'            => 'sku-desc',
				'stock_quantity_asc'  => 'stock_quantity-asc',
				'stock_quantity_desc' => 'stock_quantity-desc',
			);
			$rearranged_sorting = array_map( 'trim', explode( PHP_EOL, $rearranged_sorting ) );
			$rearranged_sorting_modified = array();
			foreach ( $rearranged_sorting as $sorting ) {
				$rearranged_sorting_modified[] = str_replace( array_keys( $replacement_values ), array_values( $replacement_values ), $sorting );
			}
			$rearranged_sorting_modified = implode( PHP_EOL, $rearranged_sorting_modified );
			update_option( 'alg_wc_more_sorting_rearrange_sorting', $rearranged_sorting_modified );
			delete_option( 'alg_wc_more_sorting_rearrange' );
		}
	}

	/**
	 * Add Woocommerce settings tab to WooCommerce settings.
	 *
	 * @version 3.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = include( 'includes/admin/class-alg-wc-settings-more-sorting.php' );
		return $settings;
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}
}

endif;

if ( ! function_exists( 'alg_woocommerce_more_sorting' ) ) {
	/**
	 * Returns the main instance of Alg_Woocommerce_More_Sorting to prevent the need to use globals.
	 *
	 * @return  Alg_Woocommerce_More_Sorting
	 * @version 3.0.0
	 */
	function alg_woocommerce_more_sorting() {
		return Alg_Woocommerce_More_Sorting::instance();
	}
}

alg_woocommerce_more_sorting();