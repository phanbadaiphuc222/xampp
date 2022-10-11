<?php
/**
 * WooCommerce More Sorting Functions
 *
 * @version 3.1.4
 * @since   3.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'alg_get_woocommerce_default_sortings' ) ) {
	/**
	 * alg_get_woocommerce_default_sortings.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function alg_get_woocommerce_default_sortings() {
		return array(
			'menu_order' => __( 'Default sorting', 'woocommerce' ),
			'popularity' => __( 'Sort by popularity', 'woocommerce' ),
			'rating'     => __( 'Sort by average rating', 'woocommerce' ),
			'date'       => __( 'Sort by newness', 'woocommerce' ),
			'price'      => __( 'Sort by price: low to high', 'woocommerce' ),
			'price-desc' => __( 'Sort by price: high to low', 'woocommerce' ),
		);
	}
}

if ( ! function_exists( 'alg_wc_more_sorting_get_custom_meta_sorting_options' ) ) {
	/**
	 * alg_wc_more_sorting_get_custom_meta_sorting_options.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function alg_wc_more_sorting_get_custom_meta_sorting_options() {
		$custom_meta_sortings = array();
		if ( 'yes' === get_option( 'alg_wc_more_sorting_custom_meta_sorting_enabled', 'no' ) ) {
			$total_number = apply_filters( 'alg_wc_more_sorting', 1, 'custom_meta_sorting' );
			for ( $i = 1; $i <= $total_number; $i++ ) {
				if ( 'yes' === get_option( 'alg_wc_more_sorting_custom_meta_sorting_enabled_' . $i, 'yes' ) ) {
					$meta_key = get_option( 'alg_wc_more_sorting_custom_meta_sorting_meta_key_' . $i, '' );
					$param    = get_option( 'alg_wc_more_sorting_custom_meta_sorting_param_' . $i, 'custom_sorting_' . $i );
					$order    = get_option( 'alg_wc_more_sorting_custom_meta_sorting_order_' . $i, 'asc' );
					$title    = get_option( 'alg_wc_more_sorting_custom_meta_sorting_title_' . $i, __( 'Custom Meta Sorting', 'woocommerce-more-sorting' ) . ' #' . $i );
					if ( '' != $meta_key && '' != $param && '' != $title ) {
						$custom_meta_sortings[ $param . '-' . $order ] = array(
							'type'      => get_option( 'alg_wc_more_sorting_custom_meta_sorting_type_' . $i, 'meta_value'),
							'meta_key'  => $meta_key,
							'secondary' => get_option( 'alg_wc_more_sorting_custom_meta_sorting_secondary_' . $i, 'none'),
							'order'     => $order,
							'param'     => $param,
							'title'     => $title,
						);
					}
				}
			}
		}
		return $custom_meta_sortings;
	}
}

if ( ! function_exists( 'alg_wc_more_sorting_get_custom_sorting_options' ) ) {
	/**
	 * alg_wc_more_sorting_get_custom_sorting_options.
	 *
	 * @version 3.1.4
	 * @since   3.1.0
	 */
	function alg_wc_more_sorting_get_custom_sorting_options() {
		$options = array(
			'date-asc'            => __( 'Sort by date', 'woocommerce-more-sorting' ) . ' (' . __( 'ascending', 'woocommerce-more-sorting' ) . ')',
			'date-desc'           => __( 'Sort by date', 'woocommerce-more-sorting' ) . ' (' . __( 'descending', 'woocommerce-more-sorting' ) . ')',
			'title-asc'           => __( 'Sort by title', 'woocommerce-more-sorting' ) . ': ' . __( 'A to Z', 'woocommerce-more-sorting' ),
			'title-desc'          => __( 'Sort by title', 'woocommerce-more-sorting' ) . ': ' . __( 'Z to A', 'woocommerce-more-sorting' ),
			'name-asc'            => __( 'Sort by slug', 'woocommerce-more-sorting' ) . ': ' . __( 'A to Z', 'woocommerce-more-sorting' ),
			'name-desc'           => __( 'Sort by slug', 'woocommerce-more-sorting' ) . ': ' . __( 'Z to A', 'woocommerce-more-sorting' ),
			'sku-asc'             => __( 'Sort by SKU', 'woocommerce-more-sorting' ) . ': ' . __( 'low to high', 'woocommerce-more-sorting' ),
			'sku-desc'            => __( 'Sort by SKU', 'woocommerce-more-sorting' ) . ': ' . __( 'high to low', 'woocommerce-more-sorting' ),
			'stock_quantity-asc'  => __( 'Sort by stock quantity', 'woocommerce-more-sorting' ) . ': ' . __( 'low to high', 'woocommerce-more-sorting' ),
			'stock_quantity-desc' => __( 'Sort by stock quantity', 'woocommerce-more-sorting' ) . ': ' . __( 'high to low', 'woocommerce-more-sorting' ),
			'total_sales-asc'     => __( 'Sort by total sales', 'woocommerce-more-sorting' ) . ': ' . __( 'low to high', 'woocommerce-more-sorting' ),
			'total_sales-desc'    => __( 'Sort by total sales', 'woocommerce-more-sorting' ) . ': ' . __( 'high to low', 'woocommerce-more-sorting' ),
			'modified-asc'        => __( 'Sort by last modified date', 'woocommerce-more-sorting' ) . ': ' . __( 'oldest to newest', 'woocommerce-more-sorting' ),
			'modified-desc'       => __( 'Sort by last modified date', 'woocommerce-more-sorting' ) . ': ' . __( 'newest to oldest', 'woocommerce-more-sorting' ),
			'author-asc'          => __( 'Sort by author ID', 'woocommerce-more-sorting' ) . ' (' . __( 'ascending', 'woocommerce-more-sorting' ) . ')',
			'author-desc'         => __( 'Sort by author ID', 'woocommerce-more-sorting' ) . ' (' . __( 'descending', 'woocommerce-more-sorting' ) . ')',
			'product_id-asc'      => __( 'Sort by product ID', 'woocommerce-more-sorting' ) . ' (' . __( 'ascending', 'woocommerce-more-sorting' ) . ')',
			'product_id-desc'     => __( 'Sort by product ID', 'woocommerce-more-sorting' ) . ' (' . __( 'descending', 'woocommerce-more-sorting' ) . ')',
			'comment_count-asc'   => __( 'Sort by number of comments', 'woocommerce-more-sorting' ) . ' (' . __( 'ascending', 'woocommerce-more-sorting' ) . ')',
			'comment_count-desc'  => __( 'Sort by number of comments', 'woocommerce-more-sorting' ) . ' (' . __( 'descending', 'woocommerce-more-sorting' ) . ')',
			'rand'                => __( 'Random sorting', 'woocommerce-more-sorting' ),
			'none'                => __( 'No sorting', 'woocommerce-more-sorting' ),
		);

		return apply_filters( 'alg_wcmso_sorting_options', $options );
	}
}

if ( ! function_exists( 'alg_get_woocommerce_sortings_order' ) ) {
	/**
	 * alg_get_woocommerce_sortings_order.
	 *
	 * @version 3.1.0
	 * @since   3.0.0
	 */
	function alg_get_woocommerce_sortings_order() {
		return array_merge(
			array_keys( alg_get_woocommerce_default_sortings() ),
			array_keys( alg_wc_more_sorting_get_custom_sorting_options() ),
			array_keys( alg_wc_more_sorting_get_custom_meta_sorting_options() )
		);
	}
}

if ( ! function_exists( 'alg_get_table_html' ) ) {
	/**
	 * alg_get_table_html.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function alg_get_table_html( $data, $args = array() ) {
		$defaults = array(
			'table_class'        => '',
			'table_style'        => '',
			'row_styles'         => '',
			'table_heading_type' => 'horizontal',
			'columns_classes'    => array(),
			'columns_styles'     => array(),
		);
		$args = array_merge( $defaults, $args );
		extract( $args );
		$table_class = ( '' == $table_class ) ? '' : ' class="' . $table_class . '"';
		$table_style = ( '' == $table_style ) ? '' : ' style="' . $table_style . '"';
		$row_styles  = ( '' == $row_styles )  ? '' : ' style="' . $row_styles  . '"';
		$html = '';
		$html .= '<table' . $table_class . $table_style . '>';
		$html .= '<tbody>';
		foreach( $data as $row_number => $row ) {
			$html .= '<tr' . $row_styles . '>';
			foreach( $row as $column_number => $value ) {
				$th_or_td = ( ( 0 === $row_number && 'horizontal' === $table_heading_type ) || ( 0 === $column_number && 'vertical' === $table_heading_type ) ) ? 'th' : 'td';
				$column_class = ( ! empty( $columns_classes ) && isset( $columns_classes[ $column_number ] ) ) ? ' class="' . $columns_classes[ $column_number ] . '"' : '';
				$column_style = ( ! empty( $columns_styles ) && isset( $columns_styles[ $column_number ] ) ) ? ' style="' . $columns_styles[ $column_number ] . '"' : '';
				$html .= '<' . $th_or_td . $column_class . $column_style . '>';
				$html .= $value;
				$html .= '</' . $th_or_td . '>';
			}
			$html .= '</tr>';
		}
		$html .= '</tbody>';
		$html .= '</table>';
		return $html;
	}
}