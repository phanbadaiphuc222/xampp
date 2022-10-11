<?php
/**
 * WooCommerce More Sorting - Restore Default
 *
 * @version 3.1.5
 * @since   3.1.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Alg_More_Sorting_Restore_Default' ) ) :

class WC_Alg_More_Sorting_Restore_Default {

	/**
	 * Constructor.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function __construct() {
		add_action( 'init',    array( $this, 'restore_default_woocommerce_sorting' ),       PHP_INT_MAX );
		//add_action( 'after_setup_theme',    array( $this, 'restore_default_woocommerce_sorting' ),       PHP_INT_MAX );
		add_action( 'wp_head', array( $this, 'restore_default_woocommerce_sorting_style' ), PHP_INT_MAX );
	}

	/**
	 * restore_default_woocommerce_sorting_style.
	 *
	 * @version 3.1.5
	 * @since   3.1.0
	 */
	function restore_default_woocommerce_sorting_style() {
		$theme = get_option( 'alg_wc_more_sorting_restore_wc_default_theme', 'avada' );
		switch ( $theme ) {
			case 'avada':
				// Avada theme
				echo '<style>
					form.woocommerce-ordering select.orderby {
						color: black !important;
					}
					form.woocommerce-ordering {
						display: inline !important;
						margin-bottom: 10px !important;
						margin-right: 10px !important;
					}
					select.orderby {
						font-family: \'PT Sans\', Arial, Helvetica, sans-serif !important;
						padding: 0 13px !important;
						height: 41px !important;
						background-color: #fbfaf9 !important;
					}
					div.orderby-order-container {
						display: none !important;
					}
					div.catalog-ordering {
						display: inline !important;
					}
					.catalog-ordering:before{
						content:none !important;
					}
				</style>';
				break;
			case 'avada_no_css':
				// Avada theme
				echo '<style>
					form.woocommerce-ordering {
						display: block !important;
						margin-bottom: 10px !important;
					}
				</style>';
				break;
			case 'revo':
				// Revo theme
				echo '<style>
					form.woocommerce-ordering {
						float: none !important;
						display: inline !important;
					}
					select.orderby {
						height: 41px !important;
						padding: 0 13px !important;
						border: 1px solid #e8e8e8 !important;
					}
					div.orderby-order-container ul.orderby,
					div.orderby-order-container ul.order {
						display: none !important;
					}
				</style>';
				break;
			default: // case 'other':
				// Unknown theme
				echo '<style>
					.orderby-order-container,
					.catalog-ordering {
						display: none !important;
					}
				</style>';
				break;
		}
	}

	/**
	 * restore_default_woocommerce_sorting.
	 *
	 * @version 3.1.5
	 * @since   3.1.0
	 * @todo    (maybe) try to integrate in custom Avada, Revo etc. sorting (instead of restoring default)
	 */
	function restore_default_woocommerce_sorting() {
		$theme = get_option( 'alg_wc_more_sorting_restore_wc_default_theme', 'avada' );
		switch ( $theme ) {
			case 'avada':
				// Avada theme
				add_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 29 );
				remove_action( 'woocommerce_get_catalog_ordering_args', 'avada_woocommerce_get_catalog_ordering_args', 20 );
				add_action( 'pre_get_posts', function () {
					global $avada_woocommerce;
					remove_filter( 'woocommerce_get_catalog_ordering_args', array( $avada_woocommerce, 'get_catalog_ordering_args' ), 20 );
				}, 6 );
			break;
			case 'avada_no_css':
				// Avada theme (no CSS)
				add_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
				remove_action( 'woocommerce_before_shop_loop', 'avada_woocommerce_catalog_ordering', 30 );
				remove_action( 'woocommerce_get_catalog_ordering_args', 'avada_woocommerce_get_catalog_ordering_args', 20 );
			break;
			case 'revo':
				// Revo theme
				add_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 29 );
				add_action( 'woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 6 );
			break;
			case 'revo_no_css':
				// Revo theme (no CSS)
				add_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
				add_action( 'woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 7 );
				remove_action( 'woocommerce_before_shop_loop', 'revo_woocommerce_catalog_ordering', 30 );
				remove_action( 'woocommerce_after_shop_loop', 'revo_woocommerce_catalog_ordering', 7 );
				remove_action( 'woocommerce_get_catalog_ordering_args', 'revo_woocommerce_get_catalog_ordering_args', 20 );
			break;
			default: // case 'other':
				// Unknown theme
				add_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
			break;
		}
	}

}

endif;

return new WC_Alg_More_Sorting_Restore_Default();
