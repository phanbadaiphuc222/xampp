<?php
/**
 * WooCommerce More Sorting
 *
 * @version 3.2.5
 * @since   2.0.0
 * @author  Algoritmika Ltd.
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Alg_More_Sorting' ) ) :

class WC_Alg_More_Sorting {

	/**
	 * Constructor.
	 *
	 * @version 3.2.5
	 * @since   3.0.0
	 */
	function __construct() {

		if ( 'yes' === get_option( 'alg_wc_more_sorting_enabled', 'yes' ) ) {

			if ( 'yes' === get_option( 'alg_wc_more_sorting_restore_wc_default_enabled', 'no' ) ) {
				// Restore default WooCommerce sorting
				require_once( 'class-alg-wc-more-sorting-restore-default.php' );
			}

			// Remove All Sorting
			if ( 'yes' === apply_filters( 'alg_wc_more_sorting', 'no', 'remove_all' ) ) {
				add_action( 'wp_loaded',       array( $this, 'remove_sorting' ),          PHP_INT_MAX );
				add_filter( 'wc_get_template', array( $this, 'remove_sorting_template' ), PHP_INT_MAX, 5 );
			}

			// Hook into query args
			if (
				get_option( 'alg_wc_more_sorting_custom_sorting_enabled', 'yes' ) === 'yes' ||
				get_option( 'alg_wc_more_sorting_custom_meta_sorting_enabled', 'no' ) === 'yes'
			) {
				add_filter( 'woocommerce_get_catalog_ordering_args', array( $this, 'get_catalog_ordering_args' ), PHP_INT_MAX );
			}
			
			// Add Custom Sorting
			if ( get_option( 'alg_wc_more_sorting_custom_sorting_enabled', 'yes' ) === 'yes' ) {
				add_filter( 'woocommerce_catalog_orderby',                 array( $this, 'add_custom_sorting' ), PHP_INT_MAX );
				add_filter( 'woocommerce_default_catalog_orderby_options', array( $this, 'add_custom_sorting' ), PHP_INT_MAX );
			}
			
			// Add Custom Meta Sorting
			if ( get_option( 'alg_wc_more_sorting_custom_meta_sorting_enabled', 'no' ) === 'yes' ) {
				add_filter( 'woocommerce_catalog_orderby',                 array( $this, 'add_custom_meta_sorting' ), PHP_INT_MAX );
				add_filter( 'woocommerce_default_catalog_orderby_options', array( $this, 'add_custom_meta_sorting' ), PHP_INT_MAX );
			}

			// Remove or Rename Default Sorting
			if ( 'yes' === apply_filters( 'alg_wc_more_sorting', 'no', 'default_sorting' ) ) {
				add_filter( 'woocommerce_catalog_orderby',                 array( $this, 'remove_default_sortings' ), PHP_INT_MAX );
				add_filter( 'woocommerce_catalog_orderby',                 array( $this, 'rename_default_sortings' ), PHP_INT_MAX );
				add_filter( 'woocommerce_default_catalog_orderby_options', array( $this, 'remove_default_sortings' ), PHP_INT_MAX );
			}

			// Rearrange All Sorting
			if ( 'yes' === get_option( 'alg_wc_more_sorting_rearrange_enabled', 'no' ) ) {
				add_filter( 'woocommerce_catalog_orderby',                 array( $this, 'rearrange_sorting' ), PHP_INT_MAX );
				add_filter( 'woocommerce_default_catalog_orderby_options', array( $this, 'rearrange_sorting' ), PHP_INT_MAX );
			}

		}
	}
	
	/*
	 * Add Custom Sorting options to Front End and to Back End (in WooCommerce > Settings > Products > Default Product Sorting).
	 *
	 * @version 3.2.5
	 * @since   3.1.0
	 */
	function add_custom_sorting( $sortby ) {
		
		$custom_sorting_options = alg_wc_more_sorting_get_custom_sorting_options();
		foreach ( $custom_sorting_options as $custom_sorting_option_key => $custom_sorting_option_title ) {
			$sortby = $this->maybe_add_sorting( $sortby, $custom_sorting_option_key, $custom_sorting_option_title );
		}
		
		return $sortby;
	}
	
	/*
	 * Add Custom Meta Sorting options to Front End and to Back End (in WooCommerce > Settings > Products > Default Product Sorting).
	 *
	 * @version 3.2.5
	 * @since   3.2.5
	 */
	function add_custom_meta_sorting( $sortby ) {
		
		$custom_meta_sortings = alg_wc_more_sorting_get_custom_meta_sorting_options();
		foreach ( $custom_meta_sortings as $custom_meta_sorting_key => $custom_meta_sorting_values ) {
			$sortby[ $custom_meta_sorting_key ] = $custom_meta_sorting_values['title'];
		}
		
		return $sortby;
	}
	
	/*
	 * Add new sorting options to WooCommerce sorting.
	 *
	 * @version 3.1.1
	 * @todo    (maybe) order by custom `orderby`, `order` and `meta_key`
	 * @todo    (maybe) order by `relevance` - __( 'Order by search terms', 'woocommerce-more-sorting' ) - https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters
	 */
	function get_catalog_ordering_args( $args ) {

		// Get ordering from query string unless defined
		$orderby_value = isset( $_GET['orderby'] ) ?
			( version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '<' ) ? woocommerce_clean( $_GET['orderby'] ) : wc_clean( $_GET['orderby'] ) ) :
			apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
		// Get order + orderby args from string
		$orderby_value = explode( '-', $orderby_value );
		$orderby       = esc_attr( $orderby_value[0] );
		$order         = ! empty( $orderby_value[1] ) ? $orderby_value[1] : '';

		$orderby = strtolower( $orderby );
		$order   = strtoupper( $order );

		switch ( $orderby ) :
			case 'sku':
				$args['orderby']  = ( 'no' === apply_filters( 'alg_wc_more_sorting', 'no', 'by_sku_num' ) ) ? 'meta_value ID' : 'meta_value_num ID';
				$args['order']    = $order == 'DESC' ? 'DESC' : 'ASC';
				$args['meta_key'] = '_sku';
			break;
			case 'stock_quantity':
				$args['orderby']  = 'meta_value_num ID';
				$args['order']    = $order == 'DESC' ? 'DESC' : 'ASC';
				$args['meta_key'] = '_stock';
			break;
			case 'total_sales':
				$args['orderby']  = 'meta_value_num ID';
				$args['order']    = $order == 'DESC' ? 'DESC' : 'ASC';
				$args['meta_key'] = 'total_sales';
			break;
			case 'modified':
				$args['orderby']  = 'modified ID';
				$args['order']    = $order == 'DESC' ? 'DESC' : 'ASC';;
			break;
			case 'author':
				$args['orderby']  = 'author ID';
				$args['order']    = $order == 'DESC' ? 'DESC' : 'ASC';
			break;
			case 'product_id':
				$args['orderby']  = 'ID';
				$args['order']    = $order == 'DESC' ? 'DESC' : 'ASC';
			break;
			case 'name':
				$args['orderby']  = 'name ID';
				$args['order']    = $order == 'DESC' ? 'DESC' : 'ASC';
			break;
			case 'comment_count':
				$args['orderby']  = 'comment_count ID';
				$args['order']    = $order == 'DESC' ? 'DESC' : 'ASC';
			break;
			case 'none':
				$args['orderby']  = 'none';
			break;
		endswitch;

		// Custom Meta Sorting
		$custom_meta_sortings = alg_wc_more_sorting_get_custom_meta_sorting_options();
		foreach ( $custom_meta_sortings as $custom_meta_sorting_key => $custom_meta_sorting_values ) {
			if ( $orderby === $custom_meta_sorting_values['param'] && $order === strtoupper( $custom_meta_sorting_values['order'] ) ) {
				$args['orderby']  = $custom_meta_sorting_values['type'];
				if ( 'none' != $custom_meta_sorting_values['secondary'] ) {
					$args['orderby'] .= ' ' . $custom_meta_sorting_values['secondary'];
				}
				$args['order']    = $order == 'DESC' ? 'DESC' : 'ASC';
				$args['meta_key'] = $custom_meta_sorting_values['meta_key'];
				break;
			}
		}

		return $args;
	}

	/*
	 * maybe_add_sorting.
	 *
	 * @version 3.1.0
	 */
	function maybe_add_sorting( $sortby, $key, $default ) {
		$option_name = 'alg_wc_more_sorting_by_' . str_replace( '-', '_', $key ) . '_text';
		if ( '' != ( $value = get_option( $option_name, $default ) ) ) {
			$sortby[ $key ] = $value;
		}
		return $sortby;
	}

	/**
	 * remove_sorting_template.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function remove_sorting_template( $located, $template_name, $args, $template_path, $default_path ) {
		if ( 'loop/orderby.php' === $template_name ) {
			$located = untrailingslashit( realpath( plugin_dir_path( __FILE__ ) . '/..' ) ) . '/templates/alg-loop-orderby.php';
		}
		return $located;
	}

	/**
	 * remove_sorting.
	 *
	 * @version 3.1.0
	 */
	function remove_sorting() {
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
		remove_action( 'mpcth_before_shop_loop',       'woocommerce_catalog_ordering', 40 );       // Blaszok theme
		remove_action( 'woocommerce_after_shop_loop',  'woocommerce_catalog_ordering', 10 );       // Storefront
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 10 );       // Storefront
		remove_action( 'woocommerce_before_shop_loop', 'avada_woocommerce_catalog_ordering', 30 ); // Avada
		remove_action( 'woocommerce_before_shop_loop', 'revo_woocommerce_catalog_ordering', 30 );  // Revo
		remove_action( 'woocommerce_after_shop_loop',  'revo_woocommerce_catalog_ordering', 7 );   // Revo
	}

	/*
	 * rearrange_sorting.
	 *
	 * @version 3.1.2
	 * @since   3.0.0
	 */
	function rearrange_sorting( $sortby ) {
		$rearranged_sorting = get_option( 'alg_wc_more_sorting_rearrange_sorting', false );
		if ( false === $rearranged_sorting ) {
			$rearranged_sorting = alg_get_woocommerce_sortings_order();
		} else {
			$rearranged_sorting = array_map( 'trim', explode( PHP_EOL, $rearranged_sorting ) );
		}
		$rearranged_sortby = array();
		foreach ( $rearranged_sorting as $sorting ) {
			if ( isset( $sortby[ $sorting ] ) ) {
				$rearranged_sortby[ $sorting ] = $sortby[ $sorting ];
				unset( $sortby[ $sorting ] );
			}
		}
		return array_merge( $rearranged_sortby, $sortby );
	}

	/*
	 * remove_default_sortings.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function remove_default_sortings( $sortby ) {
		$default_sortings = alg_get_woocommerce_default_sortings();
		foreach ( $default_sortings as $sorting_key => $sorting_desc ) {
			$option_key = str_replace( '-', '_', $sorting_key );
			if ( 'yes' === apply_filters( 'alg_wc_more_sorting', 'no', 'default_sorting_disable', $option_key ) ) {
				unset( $sortby[ $sorting_key ] );
			}
		}
		return $sortby;
	}

	/*
	 * rename_default_sortings.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function rename_default_sortings( $sortby ) {
		$default_sortings = alg_get_woocommerce_default_sortings();
		foreach ( $default_sortings as $sorting_key => $sorting_desc ) {
			$option_key = str_replace( '-', '_', $sorting_key );
			if ( isset( $sortby[ $sorting_key ] ) ) {
				$sortby[ $sorting_key ] = apply_filters( 'alg_wc_more_sorting', $sorting_desc, 'default_sorting_text', $option_key );
			}
		}
		return $sortby;
	}

}

endif;

return new WC_Alg_More_Sorting();
