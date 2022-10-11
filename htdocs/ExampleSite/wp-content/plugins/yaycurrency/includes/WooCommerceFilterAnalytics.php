<?php

namespace Yay_Currency;

defined( 'ABSPATH' ) || exit;

use Yay_Currency\WooCommerceCurrency;
use Yay_Currency\Settings;

class WooCommerceFilterAnalytics {


	private static $instance = null;
	public $default_currency;

	public function __construct() {     }

	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	public function doHooks() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		$this->default_currency = get_option( 'woocommerce_currency' );

		add_action( 'init', array( $this, 'add_currencies_dropdown_filter' ) );

		// args
		add_filter( 'woocommerce_analytics_orders_query_args', array( $this, 'filter_stats_by_currency' ) );
		add_filter( 'woocommerce_analytics_orders_stats_query_args', array( $this, 'filter_stats_by_currency' ) );

		add_filter( 'woocommerce_analytics_revenue_query_args', array( $this, 'filter_stats_by_currency' ) );

		add_filter( 'woocommerce_analytics_products_query_args', array( $this, 'filter_stats_by_currency' ) );
		add_filter( 'woocommerce_analytics_products_stats_query_args', array( $this, 'filter_stats_by_currency' ) );

		add_filter( 'woocommerce_analytics_coupons_query_args', array( $this, 'filter_stats_by_currency' ) );
		add_filter( 'woocommerce_analytics_coupons_stats_query_args', array( $this, 'filter_stats_by_currency' ) );

		add_filter( 'woocommerce_analytics_taxes_query_args', array( $this, 'filter_stats_by_currency' ) );
		add_filter( 'woocommerce_analytics_taxes_stats_query_args', array( $this, 'filter_stats_by_currency' ) );

		// join
		add_filter( 'woocommerce_analytics_clauses_join_orders_subquery', array( $this, 'concat_join_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_join_orders_stats_total', array( $this, 'concat_join_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_join_orders_stats_interval', array( $this, 'concat_join_subquery' ) );

		add_filter( 'woocommerce_analytics_clauses_join_products_subquery', array( $this, 'concat_join_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_join_products_stats_total', array( $this, 'concat_join_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_join_products_stats_interval', array( $this, 'concat_join_subquery' ) );

		add_filter( 'woocommerce_analytics_clauses_join_coupons_subquery', array( $this, 'concat_join_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_join_coupons_stats_total', array( $this, 'concat_join_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_join_coupons_stats_interval', array( $this, 'concat_join_subquery' ) );

		add_filter( 'woocommerce_analytics_clauses_join_taxes_subquery', array( $this, 'concat_join_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_join_taxes_stats_total', array( $this, 'concat_join_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_join_taxes_stats_interval', array( $this, 'concat_join_subquery' ) );

		// where
		add_filter( 'woocommerce_analytics_clauses_where_orders_subquery', array( $this, 'concat_where_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_where_orders_stats_total', array( $this, 'concat_where_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_where_orders_stats_interval', array( $this, 'concat_where_subquery' ) );

		add_filter( 'woocommerce_analytics_clauses_where_products_subquery', array( $this, 'concat_where_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_where_products_stats_total', array( $this, 'concat_where_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_where_products_stats_interval', array( $this, 'concat_where_subquery' ) );

		add_filter( 'woocommerce_analytics_clauses_where_coupons_subquery', array( $this, 'concat_where_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_where_coupons_stats_total', array( $this, 'concat_where_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_where_coupons_stats_interval', array( $this, 'concat_where_subquery' ) );

		add_filter( 'woocommerce_analytics_clauses_where_taxes_subquery', array( $this, 'concat_where_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_where_taxes_stats_total', array( $this, 'concat_where_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_where_taxes_stats_interval', array( $this, 'concat_where_subquery' ) );

		// select
		add_filter( 'woocommerce_analytics_clauses_select_orders_subquery', array( $this, 'concat_select_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_select_orders_stats_total', array( $this, 'concat_select_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_select_orders_stats_interval', array( $this, 'concat_select_subquery' ) );

		add_filter( 'woocommerce_analytics_clauses_select_products_subquery', array( $this, 'concat_select_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_select_products_stats_total', array( $this, 'concat_select_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_select_products_stats_interval', array( $this, 'concat_select_subquery' ) );

		add_filter( 'woocommerce_analytics_clauses_select_coupons_subquery', array( $this, 'concat_select_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_select_coupons_stats_total', array( $this, 'concat_select_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_select_coupons_stats_interval', array( $this, 'concat_select_subquery' ) );

		add_filter( 'woocommerce_analytics_clauses_select_taxes_subquery', array( $this, 'concat_select_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_select_taxes_stats_total', array( $this, 'concat_select_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_select_taxes_stats_interval', array( $this, 'concat_select_subquery' ) );
	}

	public function get_apply_currencies_info() {
		$settings_data         = Settings::getInstance();
		$all_apply_currencies  = $settings_data->yay_list_currencies;
		$currencies_meta_value = array();

		foreach ( $all_apply_currencies as $currency ) {
			$currency_meta                                  = get_post_meta( $currency->ID, '', true );
			$currencies_meta_value[ $currency->post_title ] = array(
				'rate' => $currency_meta['rate'][0],
				'fee'  => maybe_unserialize( $currency_meta['fee'][0] ),
			);
		}
		return array(
			'all_apply_currencies'  => $all_apply_currencies,
			'currencies_meta_value' => $currencies_meta_value,
		);
	}

	public function filter_stats_by_currency( $args ) {
		$currency = $this->default_currency;

		if ( isset( $_GET['currency'] ) ) {
			$currency = sanitize_text_field( wp_unslash( $_GET['currency'] ) );
		}
		$args['currency'] = $currency;

		return $args;
	}

	public function get_categories_leaderboard( $per_page, $after, $before, $persisted_query ) {
		global $wpdb;

		$categories_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$wpdb->prefix}wc_category_lookup.category_tree_id as category_id, SUM(product_qty) as items_sold, SUM(product_qty) * meta_value AS net_revenue, name as category_name
		FROM {$wpdb->prefix}wc_order_product_lookup
		JOIN {$wpdb->prefix}wc_order_stats ON {$wpdb->prefix}wc_order_product_lookup.order_id = {$wpdb->prefix}wc_order_stats.order_id
		LEFT JOIN {$wpdb->prefix}term_relationships ON {$wpdb->prefix}wc_order_product_lookup.product_id = {$wpdb->prefix}term_relationships.object_id
		JOIN {$wpdb->prefix}term_taxonomy ON {$wpdb->prefix}term_taxonomy.term_taxonomy_id = {$wpdb->prefix}term_relationships.term_taxonomy_id
		LEFT JOIN {$wpdb->prefix}wc_category_lookup ON {$wpdb->prefix}term_taxonomy.term_id = {$wpdb->prefix}wc_category_lookup.category_id
		JOIN {$wpdb->prefix}postmeta as product_meta ON {$wpdb->prefix}wc_order_product_lookup.product_id = product_meta.post_ID
		JOIN {$wpdb->prefix}terms ON {$wpdb->prefix}terms.term_id = {$wpdb->prefix}wc_category_lookup.category_tree_id
		WHERE 1=1 
		AND ( {$wpdb->prefix}wc_order_stats.status NOT IN ( 'wc-trash','wc-pending','wc-failed','wc-cancelled' ) ) AND {$wpdb->prefix}wc_order_product_lookup.date_created <= '' 
		AND {$wpdb->prefix}wc_order_product_lookup.date_created <= %s AND {$wpdb->prefix}wc_order_product_lookup.date_created >= %s
		AND {$wpdb->prefix}wc_category_lookup.category_tree_id IS NOT NULL
		AND product_meta.meta_key = '_price'
		GROUP BY category_id
		ORDER BY items_sold DESC
		LIMIT %d",
				$before,
				$after,
				$per_page
			),
			ARRAY_A
		);

		$rows            = array();
		$woo_currency    = WooCommerceCurrency::getInstance();
		$currency_symbol = $woo_currency->get_symbol_by_currency( $this->default_currency );

		foreach ( $categories_data as $category ) {
			$url_query    = wp_parse_args(
				array(
					'filter'     => 'single_category',
					'categories' => $category['category_id'],
				),
				$persisted_query
			);
			$category_url = wc_admin_url( '/analytics/categories', $url_query );
			if ( isset( $_GET['currency'] ) ) {
				$currency                  = sanitize_text_field( wp_unslash( $_GET['currency'] ) );
				$all_apply_currencies_info = $this->get_apply_currencies_info();
				$rate                      = $all_apply_currencies_info['currencies_meta_value'][ $currency ]['rate'];
				$fee                       = 'percentage' === $all_apply_currencies_info['currencies_meta_value'][ $currency ]['fee']['type'] ? ( $category['net_revenue'] ) / ( $all_apply_currencies_info['currencies_meta_value'][ $currency ]['fee']['value'] / 100 ) : ( $all_apply_currencies_info['currencies_meta_value'][ $currency ]['fee']['value'] * $category['items_sold'] );
				$currency_symbol           = $woo_currency->get_symbol_by_currency( $currency );
				$category['net_revenue']   = ( $category['net_revenue'] * $rate ) + $fee;
			};

			$rows[] = array(
				array(
					'display' => '<a href="' . esc_attr( $category_url ) . '">' . esc_html( $category['category_name'] ) . '</a>',
					'value'   => $category['category_name'],
				),
				array(
					'display' => wc_admin_number_format( $category['items_sold'] ),
					'value'   => $category['items_sold'],
				),
				array(
					'display' => wp_kses_post( html_entity_decode( '~' . $currency_symbol . number_format( $category['net_revenue'], 2, '.', ',' ) ) ),
					'value'   => $category['net_revenue'],
				),
			);
		}

		return array(
			'id'      => 'categories',
			'label'   => __( 'Top Categories - Items Sold', 'woocommerce' ),
			'headers' => array(
				array(
					'label' => __( 'Category', 'woocommerce' ),
				),
				array(
					'label' => __( 'Items Sold', 'woocommerce' ),
				),
				array(
					'label' => __( 'Net Sales', 'woocommerce' ),
				),
			),
			'rows'    => $rows,
		);
	}

	public function get_products_leaderboard( $per_page, $after, $before, $persisted_query ) {
		global $wpdb;

		$products_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID as product_id, post_title as product_name, COUNT(product_id) as items_sold, SUM(meta_value) as net_revenue
		FROM {$wpdb->prefix}wc_order_stats 
		JOIN {$wpdb->prefix}postmeta currency_postmeta ON {$wpdb->prefix}wc_order_stats.order_id = currency_postmeta.post_id
		JOIN {$wpdb->prefix}wc_order_product_lookup product_order_meta ON {$wpdb->prefix}wc_order_stats.order_id = product_order_meta.order_id
		JOIN {$wpdb->prefix}posts products_meta ON product_order_meta.product_id = products_meta.ID
		WHERE 1=1 
		AND ( {$wpdb->prefix}wc_order_stats.status NOT IN ( 'wc-trash','wc-pending','wc-failed','wc-cancelled' ) ) 
		AND {$wpdb->prefix}wc_order_stats.date_created <= %s AND {$wpdb->prefix}wc_order_stats.date_created >= %s
		AND currency_postmeta.meta_key = 'yay_currency_checkout_original_total' 
		GROUP BY product_id ORDER BY items_sold DESC LIMIT %d",
				$before,
				$after,
				$per_page
			),
			ARRAY_A
		);

		$rows            = array();
		$woo_currency    = WooCommerceCurrency::getInstance();
		$currency_symbol = $woo_currency->get_symbol_by_currency( $this->default_currency );

		foreach ( $products_data as $product ) {
			$url_query   = wp_parse_args(
				array(
					'filter'   => 'single_product',
					'products' => $product['product_id'],
				),
				$persisted_query
			);
			$product_url = wc_admin_url( '/analytics/products', $url_query );
			if ( isset( $_GET['currency'] ) ) {
				$currency                  = sanitize_text_field( wp_unslash( $_GET['currency'] ) );
				$all_apply_currencies_info = $this->get_apply_currencies_info();
				$rate                      = $all_apply_currencies_info['currencies_meta_value'][ $currency ]['rate'];
				$fee                       = 'percentage' === $all_apply_currencies_info['currencies_meta_value'][ $currency ]['fee']['type'] ? ( $product['net_revenue'] ) / ( $all_apply_currencies_info['currencies_meta_value'][ $currency ]['fee']['value'] / 100 ) : ( $all_apply_currencies_info['currencies_meta_value'][ $currency ]['fee']['value'] * $product['items_sold'] );
				$currency_symbol           = $woo_currency->get_symbol_by_currency( $currency );
				$product['net_revenue']    = ( $product['net_revenue'] * $rate ) + $fee;
			}
			$rows[] = array(
				array(
					'display' => '<a href="' . esc_attr( $product_url ) . '">' . esc_html( $product['product_name'] ) . '</a>',
					'value'   => $product['product_name'],
				),
				array(
					'display' => wc_admin_number_format( $product['items_sold'] ),
					'value'   => $product['items_sold'],
				),
				array(
					'display' => wp_kses_post( html_entity_decode( '~' . $currency_symbol . number_format( $product['net_revenue'], 2, '.', ',' ) ) ),
					'value'   => $product['net_revenue'],
				),
			);
		}

		return array(
			'id'      => 'products',
			'label'   => __( 'Top Products - Items Sold', 'woocommerce' ),
			'headers' => array(
				array(
					'label' => __( 'Product', 'woocommerce' ),
				),
				array(
					'label' => __( 'Items Sold', 'woocommerce' ),
				),
				array(
					'label' => __( 'Net Sales', 'woocommerce' ),
				),
			),
			'rows'    => $rows,
		);
	}

	public function get_coupons_leaderboard( $per_page, $after, $before, $persisted_query ) {
		global $wpdb;

		$coupons_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$wpdb->prefix}wc_order_coupon_lookup.coupon_id as coupon_id, post_name as coupon_code, SUM(original_price_meta.meta_value) as original_price, coupon_amount_meta.meta_value as coupon_amount, coupon_type_meta.meta_value as coupon_type, COUNT(DISTINCT {$wpdb->prefix}wc_order_stats.order_id) as orders_count, IF (coupon_type_meta.meta_value = 'percent', SUM(original_price_meta.meta_value)/(1 - coupon_amount_meta.meta_value/100) - SUM(original_price_meta.meta_value), coupon_amount_meta.meta_value * COUNT(DISTINCT {$wpdb->prefix}wc_order_stats.order_id)) as amount
		FROM {$wpdb->prefix}wc_order_coupon_lookup
		JOIN {$wpdb->prefix}wc_order_stats ON {$wpdb->prefix}wc_order_coupon_lookup.order_id = {$wpdb->prefix}wc_order_stats.order_id
		JOIN {$wpdb->prefix}posts coupon_name_meta ON {$wpdb->prefix}wc_order_coupon_lookup.coupon_id = coupon_name_meta.ID
		JOIN {$wpdb->prefix}postmeta original_price_meta ON {$wpdb->prefix}wc_order_coupon_lookup.order_id = original_price_meta.post_ID
		JOIN {$wpdb->prefix}postmeta coupon_amount_meta ON {$wpdb->prefix}wc_order_coupon_lookup.coupon_id = coupon_amount_meta.post_ID
		JOIN {$wpdb->prefix}postmeta coupon_type_meta ON {$wpdb->prefix}wc_order_coupon_lookup.coupon_id = coupon_type_meta.post_ID
	WHERE 1=1 
	AND ( {$wpdb->prefix}wc_order_stats.status NOT IN ( 'wc-trash','wc-pending','wc-failed','wc-cancelled' ) ) AND {$wpdb->prefix}wc_order_coupon_lookup.date_created <= %s AND {$wpdb->prefix}wc_order_coupon_lookup.date_created >= %s AND original_price_meta.meta_key = 'yay_currency_checkout_original_total' AND coupon_amount_meta.meta_key = 'coupon_amount' AND coupon_type_meta.meta_key = 'discount_type' GROUP BY coupon_id ORDER BY orders_count DESC LIMIT %d",
				$before,
				$after,
				$per_page
			),
			ARRAY_A
		);

		$rows            = array();
		$woo_currency    = WooCommerceCurrency::getInstance();
		$currency_symbol = $woo_currency->get_symbol_by_currency( $this->default_currency );

		foreach ( $coupons_data as $coupon ) {
			$url_query  = wp_parse_args(
				array(
					'filter'  => 'single_coupon',
					'coupons' => $coupon['coupon_id'],
				),
				$persisted_query
			);
			$coupon_url = wc_admin_url( '/analytics/coupons', $url_query );
			if ( isset( $_GET['currency'] ) ) {
				$currency                  = sanitize_text_field( wp_unslash( $_GET['currency'] ) );
				$all_apply_currencies_info = $this->get_apply_currencies_info();
				$rate                      = $all_apply_currencies_info['currencies_meta_value'][ $currency ]['rate'];
				$fee                       = 'percentage' === $all_apply_currencies_info['currencies_meta_value'][ $currency ]['fee']['type'] ? ( $coupon['amount'] ) / ( $all_apply_currencies_info['currencies_meta_value'][ $currency ]['fee']['value'] / 100 ) : ( $all_apply_currencies_info['currencies_meta_value'][ $currency ]['fee']['value'] * $coupon['amount'] );
				$currency_symbol           = $woo_currency->get_symbol_by_currency( $currency );
				$coupon['amount']          = ( $coupon['amount'] * $rate ) + $fee;
			}
			$rows[] = array(
				array(
					'display' => '<a href="' . esc_attr( $coupon_url ) . '">' . esc_html( $coupon['coupon_code'] ) . '</a>',
					'value'   => $coupon['coupon_code'],
				),
				array(
					'display' => wc_admin_number_format( $coupon['orders_count'] ),
					'value'   => $coupon['orders_count'],
				),
				array(
					'display' => wp_kses_post( html_entity_decode( '~' . $currency_symbol . number_format( $coupon['amount'], 2, '.', ',' ) ) ),
					'value'   => $coupon['amount'],
				),
			);
		}

		return array(
			'id'      => 'coupons',
			'label'   => __( 'Top Coupons - Number of Orders', 'woocommerce' ),
			'headers' => array(
				array(
					'label' => __( 'Coupon Code', 'woocommerce' ),
				),
				array(
					'label' => __( 'Orders', 'woocommerce' ),
				),
				array(
					'label' => __( 'Amount Discounted', 'woocommerce' ),
				),
			),
			'rows'    => $rows,
		);
	}

	public function get_customers_leaderboard( $per_page, $after, $before, $persisted_query ) {
		$currency = $this->default_currency;
		global $wpdb;

		$customers_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$wpdb->prefix}wc_order_stats.customer_id as id, CONCAT_WS(' ', `first_name`, `last_name`) AS 'name', COUNT({$wpdb->prefix}wc_order_stats.customer_id) as orders_count, SUM(meta_value) as total_spend
		FROM {$wpdb->prefix}wc_order_stats 
		JOIN {$wpdb->prefix}postmeta currency_postmeta ON {$wpdb->prefix}wc_order_stats.order_id = currency_postmeta.post_id
		JOIN {$wpdb->prefix}wc_customer_lookup customer_meta ON {$wpdb->prefix}wc_order_stats.customer_id = customer_meta.customer_id
		WHERE 1=1 
		AND ( {$wpdb->prefix}wc_order_stats.status NOT IN ( 'wc-trash','wc-pending','wc-failed','wc-cancelled' ) ) AND {$wpdb->prefix}wc_order_stats.date_created <= %s AND {$wpdb->prefix}wc_order_stats.date_created >= %s AND currency_postmeta.meta_key = 'yay_currency_checkout_original_total' GROUP BY id LIMIT %d",
				$before,
				$after,
				$per_page
			),
			ARRAY_A
		);

		$rows            = array();
		$woo_currency    = WooCommerceCurrency::getInstance();
		$currency_symbol = $woo_currency->get_symbol_by_currency( $this->default_currency );

		foreach ( $customers_data as $customer ) {
			$url_query    = wp_parse_args(
				array(
					'filter'    => 'single_customer',
					'customers' => $customer['id'],
				),
				$persisted_query
			);
			$customer_url = wc_admin_url( '/analytics/customers', $url_query );
			if ( isset( $_GET['currency'] ) ) {
				$currency                  = sanitize_text_field( wp_unslash( $_GET['currency'] ) );
				$all_apply_currencies_info = $this->get_apply_currencies_info();
				$rate                      = $all_apply_currencies_info['currencies_meta_value'][ $currency ]['rate'];
				$fee                       = 'percentage' === $all_apply_currencies_info['currencies_meta_value'][ $currency ]['fee']['type'] ? ( $customer['total_spend'] ) / ( $all_apply_currencies_info['currencies_meta_value'][ $currency ]['fee']['value'] / 100 ) : ( $all_apply_currencies_info['currencies_meta_value'][ $currency ]['fee']['value'] * $customer['orders_count'] );
				$currency_symbol           = $woo_currency->get_symbol_by_currency( $currency );
				$customer['total_spend']   = ( $customer['total_spend'] * $rate ) + $fee;
			}
			$rows[] = array(
				array(
					'display' => '<a href="' . esc_attr( $customer_url ) . '">' . esc_html( $customer['name'] ) . '</a>',
					'value'   => $customer['name'],
				),
				array(
					'display' => $customer['orders_count'],
					'value'   => $customer['orders_count'],
				),
				array(
					'display' => wp_kses_post( html_entity_decode( '~' . $currency_symbol . number_format( $customer['total_spend'], 2, '.', ',' ) ) ),
					'value'   => $customer['total_spend'],
				),
			);
		}

		return array(
			'id'      => 'customers',
			'label'   => __( 'Top Customers - Total Spend', 'woocommerce' ),
			'headers' => array(
				array(
					'label' => __( 'Customer Name', 'woocommerce' ),
				),
				array(
					'label' => __( 'Orders', 'woocommerce' ),
				),
				array(
					'label' => __( 'Total Spend', 'woocommerce' ),
				),
			),
			'rows'    => $rows,
		);
	}

	public function custom_leaderboards_analytics( $leaderboards, $per_page, $after, $before, $persisted_query ) {
		$leaderboards = array(
			$this->get_categories_leaderboard( $per_page, $after, $before, $persisted_query ),
			$this->get_products_leaderboard( $per_page, $after, $before, $persisted_query ),
			$this->get_coupons_leaderboard( $per_page, $after, $before, $persisted_query ),
			$this->get_customers_leaderboard( $per_page, $after, $before, $persisted_query ),
		);
		return $leaderboards;
	}

	public function add_currencies_dropdown_filter() {
		add_filter( 'woocommerce_leaderboards', array( $this, 'custom_leaderboards_analytics' ), 10, 5 );
		$woo_currency         = WooCommerceCurrency::getInstance();
		$settings_data        = Settings::getInstance();
		$currencies           = $settings_data->yay_list_currencies;
		$converted_currencies = array();
		foreach ( $currencies as $currency ) {
			$currency_meta = get_post_meta( $currency->ID, '', true );

			array_push(
				$converted_currencies,
				array(
					$currency->post_title => array(
						'code'              => $currency->post_title,
						'symbol'            => html_entity_decode( $woo_currency->get_symbol_by_currency( $currency->post_title ) ),
						'symbolPosition'    => $currency_meta['currency_position'][0],
						'thousandSeparator' => $currency_meta['thousand_separator'][0],
						'decimalSeparator'  => $currency_meta['decimal_separator'][0],
						'precision'         => $currency_meta['number_decimal'][0],
					),
				)
			);
		};
		if ( is_admin() ) {
			wp_enqueue_script( 'yay-currency-analytics', YAY_CURRENCY_PLUGIN_URL . 'src/analytics.js', array(), '1.0', true );
		}
		wp_localize_script(
			'yay-currency-analytics',
			'yayCurrencyAnalytics',
			array(
				'defaultCurrency' => $this->default_currency,
				'currencies'      => $converted_currencies,
			)
		);

		$dropdown_currencies = array();
		$list_currencies     = $settings_data->woo_list_currencies;

		foreach ( $converted_currencies as $key => $value ) {
			if ( ! isset( $list_currencies[ reset( $value )['code'] ] ) ) {
				continue;
			};
			$decoded_currency_name       = wp_kses_post( html_entity_decode( $list_currencies[ reset( $value )['code'] ] ) );
			$currency_symbol             = wp_kses_post( html_entity_decode( reset( $value )['symbol'] ) );
			$currency_code               = wp_kses_post( html_entity_decode( reset( $value )['code'] ) );
			$dropdown_converted_currency = array(
				'label' => __( $decoded_currency_name . '(' . $currency_symbol . ') - ' . $currency_code, 'yay-currency' ),
				'value' => $currency_code,
			);
			array_push( $dropdown_currencies, $dropdown_converted_currency );
		}

		$data_registry = \Automattic\WooCommerce\Blocks\Package::container()->get( \Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::class );

		$data_registry->add( 'multiCurrency', $dropdown_currencies );
	}

	public function concat_join_subquery( $clauses ) {
		global $wpdb;

		$clauses[] = "JOIN {$wpdb->postmeta} currency_postmeta ON {$wpdb->prefix}wc_order_stats.order_id = currency_postmeta.post_id";

		return $clauses;
	}

	public function concat_where_subquery( $clauses ) {
		global $wpdb;
		$currency = $this->default_currency;
		$pattern  = '/^[a-zA-Z]{3}+$/';

		if ( isset( $_GET['currency'] ) ) {
			$currency = sanitize_text_field( wp_unslash( $_GET['currency'] ) );
		}
		if ( preg_match( $pattern, $currency ) ) {
			$clauses[] = $wpdb->prepare( "AND currency_postmeta.meta_key = '_order_currency' AND currency_postmeta.meta_value = %s", $currency );
		}

		return $clauses;
	}

	public function concat_select_subquery( $clauses ) {
		$currency = $this->default_currency;
		if ( isset( $_GET['currency'] ) ) {
			$currency = sanitize_text_field( wp_unslash( $_GET['currency'] ) );
		}
		$clauses[] = ', currency_postmeta.meta_value AS currency';
		return $clauses;
	}
}
