<?php


namespace Yay_Currency;

use Yay_Currency\Settings;

defined( 'ABSPATH' ) || exit;
class WooCommerceCurrency {

	private static $instance   = null;
	public $converted_currency = array();
	public $apply_currency     = null;
	public $is_checkout_different_currency;
	private $price_filters_priority = 10;

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

		// Compatible with B2B Wholesale Suite, Price by Country, B2BKing
		if ( class_exists( 'B2bwhs' ) || class_exists( 'CBP_Country_Based_Price' ) || class_exists( 'B2bkingcore' ) ) {
			$this->price_filters_priority = 100000;
		}

		$currencies = $this->get_currencies_post_type();
		if ( $currencies ) {
			foreach ( $currencies as $currency ) {
				$currency_meta = get_post_meta( $currency->ID, '', true );
				$currency_info = array(
					'ID'                => $currency->ID,
					'currency'          => $currency->post_title,
					'currencyPosition'  => $currency_meta['currency_position'][0],
					'thousandSeparator' => $currency_meta['thousand_separator'][0],
					'decimalSeparator'  => $currency_meta['decimal_separator'][0],
					'numberDecimal'     => $currency_meta['number_decimal'][0],
					'rate'              => $currency_meta['rate'][0],
					'fee'               => maybe_unserialize( $currency_meta['fee'][0] ),
					'status'            => $currency_meta['status'][0],
					'paymentMethods'    => maybe_unserialize( $currency_meta['payment_methods'][0] ),
					'countries'         => maybe_unserialize( $currency_meta['countries'][0] ),
					'symbol'            => get_woocommerce_currency_symbol( $currency->post_title ),
					'roundingType'      => $currency_meta['rounding_type'][0],
					'roundingValue'     => $currency_meta['rounding_value'][0],
					'subtractAmount'    => $currency_meta['subtract_amount'][0],
				);

				array_push(
					$this->converted_currency,
					$currency_info
				);

				if ( $currency->post_title === get_option( 'woocommerce_currency' ) ) {
					$this->apply_currency = $currency_info;
				}
			}
			$this->is_checkout_different_currency = get_option( 'yay_currency_checkout_different_currency', 0 );
			add_action(
				'init',
				function () {
					$this->add_woocommerce_filters();
				}
			);
		}
	}

	public function add_woocommerce_filters( $currency_ID = null ) {
		if ( ! is_admin() ) {
			if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
				$currency_ID          = sanitize_key( $_COOKIE['yay_currency_widget'] );
				$this->apply_currency = $this->get_currency_by_ID( $currency_ID ) ? $this->get_currency_by_ID( $currency_ID ) : reset( $this->converted_currency );
			}

			if ( isset( $_REQUEST['yay-currency-nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['yay-currency-nonce'] ), 'yay-currency-check-nonce' ) ) {
				if ( isset( $_POST['currency'] ) ) {
					$currency_ID = sanitize_text_field( $_POST['currency'] );

					$this->apply_currency = $this->get_currency_by_ID( $currency_ID );
				}
			}
			$this->set_cookies();

			add_filter( 'woocommerce_product_get_price', array( $this, 'custom_raw_price' ), $this->price_filters_priority, 2 );
			add_filter( 'woocommerce_product_get_regular_price', array( $this, 'custom_raw_price' ), $this->price_filters_priority, 2 );
			add_filter( 'woocommerce_product_get_sale_price', array( $this, 'custom_raw_price' ), $this->price_filters_priority, 2 );

			add_filter( 'woocommerce_product_variation_get_price', array( $this, 'custom_raw_price' ), $this->price_filters_priority, 2 );
			add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'custom_raw_price' ), $this->price_filters_priority, 2 );
			add_filter( 'woocommerce_product_variation_get_sale_price', array( $this, 'custom_raw_price' ), $this->price_filters_priority, 2 );

			add_filter( 'woocommerce_variation_prices_price', array( $this, 'custom_raw_price' ), $this->price_filters_priority, 2 );
			add_filter( 'woocommerce_variation_prices_regular_price', array( $this, 'custom_raw_price' ), $this->price_filters_priority, 2 );
			add_filter( 'woocommerce_variation_prices_sale_price', array( $this, 'custom_raw_price' ), $this->price_filters_priority, 2 );

			add_filter( 'woocommerce_get_variation_prices_hash', array( $this, 'custom_variation_price_hash' ) );

			add_filter( 'woocommerce_subscriptions_product_sign_up_fee', array( $this, 'custom_subscription_sign_up_fee' ), 10, 2 );

			add_filter( 'woocommerce_subscriptions_product_price_string', array( $this, 'custom_subscription_price_string' ), 10, 3 );

			add_filter( 'woocommerce_subscriptions_price_string', array( $this, 'custom_subscription_price_string' ), 10, 3 );

			add_filter( 'woocommerce_available_payment_gateways', array( $this, 'conditional_payment_gateways' ), 10, 1 );
			add_action( 'woocommerce_before_mini_cart', array( $this, 'custom_mini_cart_price' ), 10 );

			if ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) {
				add_filter( 'woocommerce_cart_product_subtotal', array( $this, 'custom_checkout_product_subtotal' ), 10, 4 );
				add_action( 'woocommerce_checkout_before_order_review', array( $this, 'add_notice_checkout_payment_methods' ), 1000 );
				add_filter( 'woocommerce_cart_subtotal', array( $this, 'custom_checkout_order_subtotal' ), 10, 3 );
				add_filter( 'woocommerce_cart_total', array( $this, 'custom_checkout_order_total' ) );
				add_filter( 'woocommerce_cart_shipping_method_full_label', array( $this, 'custom_shipping_fee' ), 10, 2 );
				add_filter( 'woocommerce_cart_totals_coupon_html', array( $this, 'custom_discount_coupon' ), 10, 3 );
				add_filter( 'woocommerce_cart_tax_totals', array( $this, 'custom_total_tax' ), 10, 2 );
			}
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'add_order_currency_meta' ), 10, 2 );
			add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'custom_cart_item_subtotal' ), 10, 3 );
		}
		add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'change_format_order_line_subtotal' ), 10, 3 );
		add_filter( 'woocommerce_get_order_item_totals', array( $this, 'change_format_order_item_totals' ), 10, 3 );

		add_filter( 'woocommerce_get_formatted_order_total', array( $this, 'get_formatted_order_total' ), 10, 2 );
		add_filter( 'woocommerce_order_subtotal_to_display', array( $this, 'get_formatted_order_subtotal' ), 10, 3 );
		add_filter( 'woocommerce_order_shipping_to_display', array( $this, 'get_formatted_order_shipping' ), 10, 3 );
		add_filter( 'woocommerce_order_discount_to_display', array( $this, 'get_formatted_order_discount' ), 10, 2 );
		add_filter( 'woocommerce_package_rates', array( $this, 'change_shipping_cost' ), 10, 2 );
		add_filter( 'woocommerce_coupon_get_amount', array( $this, 'change_coupon_amount' ), 10, 2 );

		add_filter( 'woocommerce_stripe_request_body', array( $this, 'custom_stripe_request_total_amount' ), 10, 2 );
		add_filter( 'woocommerce_paypal_args', array( $this, 'custom_request_paypal' ), 10, 2 );

		// Custom price for Woocommerce Product Addon plugin
		add_filter( 'woocommerce_product_addons_option_price_raw', array( $this, 'custom_product_addons_option_price' ), 10, 2 );
		add_filter( 'woocommerce_product_addons_get_item_data', array( $this, 'custom_cart_item_addon_data' ), 10, 3 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'custom_order_meta_fee' ), 10, 3 );

		// Compatible with Table Rate Shipping plugin
		add_filter( 'woocommerce_table_rate_package_row_base_price', array( $this, 'custom_table_rate_shipping_plugin_row_base_price' ), 10, 3 );

		// Display an friendly error message for WooCommerce PayPal Checkout Gateway && WooCommerce PayPal Payments plugin error when turn of Checkout in different currency
		add_filter( 'woocommerce_after_checkout_validation', array( $this, 'handle_woocommerce_paypal_payments_plugin_error' ), 10, 1 );

		// Compatible with YITH Woocommerce Gift Cards plugin
		add_filter( 'yith_ywgc_gift_card_amounts', array( $this, 'custom_gift_cards_price_in_product_page' ), 10, 2 );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'reverse_gift_card_amount_before_add_to_cart' ), 10, 3 );

		// Compatible with YITH Points and Rewards plugin
		add_filter( 'ywpar_get_point_earned_price', array( $this, 'prevent_convert_points_by_price' ), 10, 3 );
		add_filter( 'ywpar_calculate_product_discount', array( $this, 'custom_price_value_of_points' ), 10, 3 );
		add_filter( 'ywpar_rewards_conversion_rate', array( $this, 'set_rewards_conversion_rate' ), 10, 1 );
		add_filter( 'ywpar_rewards_percentual_conversion_rate', array( $this, 'set_rewards_percentual_conversion_rate' ), 10, 1 );
		add_filter( 'ywpar_conversion_points_rate', array( $this, 'set_conversion_points_rate' ), 10, 1 );
		add_filter( 'ywpar_calculate_rewards_discount_max_discount', array( $this, 'custom_rewards_discount_max_discount' ), 10, 3 );
		add_filter( 'woocommerce_available_variation', array( $this, 'format_variation_price_discount_fixed_conversion' ), 11, 3 );

		add_filter( 'woocommerce_cart_item_price', array( $this, 'recalculate_mini_cart' ), 10000, 3 );

		// Compatible with Cartflows plugin && betheme theme
		if ( class_exists( 'Cartflows_Checkout' ) || 'betheme' === wp_get_theme()->template && wp_doing_ajax() ) {
			add_filter( 'woocommerce_cart_product_subtotal', array( $this, 'custom_product_subtotal' ), 10, 4 );
			add_filter( 'woocommerce_cart_subtotal', array( $this, 'custom_cart_subtotal' ), 10, 3 );
			add_filter( 'woocommerce_cart_total', array( $this, 'custom_cart_total' ) );
		}

		// Free shipping with minimum amount
		add_filter( 'woocommerce_shipping_free_shipping_instance_option', array( $this, 'custom_free_shipping_min_amount' ), 10, 3 );
		add_filter( 'woocommerce_shipping_free_shipping_option', array( $this, 'custom_free_shipping_min_amount' ), 10, 3 );

		// Compatible with Advanced Product Fields Pro for WooCommerce plugin
		add_filter( 'wapf/pricing/addon', array( $this, 'wapf_recalculate_price_option' ), 10, 4 );

		// Custom price fees
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'recalculate_cart_fees' ), 10, 1 );

	}

	public function recalculate_cart_fees( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		} else {
			foreach ( $cart->get_fees() as $fee ) {
				$amount      = $this->calculate_price_by_currency( $fee->amount );
				$fee->amount = $amount;
			}
		}
	}

	public function wapf_recalculate_price_option( $amount, $product, $type, $for ) {
		$amount = $this->calculate_price_by_currency( $amount );
		return $amount;
	}

	public function custom_free_shipping_min_amount( $option, $key, $method ) {

		if ( 'min_amount' !== $key || ! is_numeric( $option ) ) {
			return $option;
		}

		$converted_min_amount = $this->calculate_price_by_currency( $option, true );
		return $converted_min_amount;
	}

	public function custom_product_subtotal( $product_subtotal, $product, $quantity, $cart ) {
		if ( is_checkout() ) {
			return $product_subtotal;
		}

		if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
			$currency_ID          = sanitize_key( $_COOKIE['yay_currency_widget'] );
			$this->apply_currency = $this->get_currency_by_ID( $currency_ID );
			$price                = $this->calculate_price_by_currency( $product->get_price() * $quantity, true );
			$price                = wc_price(
				$price,
				$this->get_apply_currency_format_info()
			);
			return $price;
		}
		return $product_subtotal;
	}

	public function custom_cart_subtotal( $price, $compound, $cart ) {
		if ( is_checkout() ) {
			return $price;
		}
		if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
			$currency_ID          = sanitize_key( $_COOKIE['yay_currency_widget'] );
			$this->apply_currency = $this->get_currency_by_ID( $currency_ID );
			$price                = $this->calculate_price_by_currency( WC()->cart->get_displayed_subtotal(), true );
			$price                = wc_price(
				$price,
				$this->get_apply_currency_format_info()
			);
			return $price;
		}
		return $price;
	}

	public function custom_cart_total( $price ) {
		if ( is_checkout() ) {
			return $price;
		}
		if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
			$currency_ID          = sanitize_key( $_COOKIE['yay_currency_widget'] );
			$this->apply_currency = $this->get_currency_by_ID( $currency_ID );
			$price                = $this->calculate_price_by_currency( WC()->cart->total, true );
			$price                = wc_price(
				$price,
				$this->get_apply_currency_format_info()
			);
			return $price;
		}
		return $price;
	}

	public function recalculate_mini_cart( $price, $cart_item, $cart_item_key ) {
		if ( 'betheme' === wp_get_theme()->template && wp_doing_ajax() && isset( $_COOKIE['yay_currency_widget'] ) ) {
			if ( is_cart() ) {
				return $price;
			}
			$currency_ID          = sanitize_key( $_COOKIE['yay_currency_widget'] );
			$this->apply_currency = $this->get_currency_by_ID( $currency_ID );
			$product_price        = wc_get_product( $cart_item['product_id'] )->get_price();
			$price                = $this->calculate_price_by_currency( $product_price, true );
			$price                = wc_price(
				$cart_item['line_total'],
				$this->get_apply_currency_format_info()
			);
			return $price;
		}
		// Compatible with Measurement_Price_Calculator for WooCommerce plugin
		if ( class_exists( 'WC_Measurement_Price_Calculator' ) ) {
			$custom_price  = $cart_item['custom_price'];
			$convert_price = $this->calculate_price_by_currency( $custom_price );
			$price         = wc_price(
				$convert_price,
				$this->get_apply_currency_format_info()
			);
			return $price;
		}

		// Compatible with Bookly plugin
		if ( class_exists( 'BooklyPro\Lib\Plugin' ) && isset( $cart_item['bookly'] ) ) {
			$userData = new \Bookly\Lib\UserBookingData( null );
			$userData->fillData( $cart_item['bookly'] );
			$userData->cart->setItemsData( $cart_item['bookly']['items'] );
			$cart_info = $userData->cart->getInfo();
			if ( 'excl' === get_option( 'woocommerce_tax_display_cart' ) && \Bookly\Lib\Config::taxesActive() ) {
					$product_price = $cart_info->getPayNow() - $cart_info->getPayTax();
			} else {
					$product_price = $cart_info->getPayNow();
			}
			$price = $this->calculate_price_by_currency( $product_price );
			$price = wc_price(
				$price,
				$this->get_apply_currency_format_info()
			);
			return $price;
		}

		// Compatible with B2B Wholesale Suite plugin
		if ( class_exists( 'B2bwhs' ) ) {
			WC()->cart->calculate_totals();
		}

		return $price;
	}

	public function prevent_convert_points_by_price( $price, $currency, $object ) {
		$price = $object->get_data()['price'];
		return $price;
	}

	public function format_variation_price_discount_fixed_conversion( $args, $product, $variation ) {

		if ( isset( $args['variation_price_discount_fixed_conversion'] ) ) {
			$args['variation_price_discount_fixed_conversion'] = wc_price(
				$args['variation_price_discount_fixed_conversion'],
				$this->get_apply_currency_format_info()
			);
		}

		return $args;
	}

	public function custom_price_value_of_points( $discount, $product_id, $not_formatted_discount ) {
		$product_type       = wc_get_product( $product_id )->get_type();
		$converted_discount = $this->calculate_price_by_currency( $not_formatted_discount, true );

		if ( 'variation' === $product_type || 'subscription_variation' === $product_type ) {
			return $converted_discount;
		}

		$discount = wc_price(
			$converted_discount,
			$this->get_apply_currency_format_info()
		);
		return $discount;
	}

	public function set_rewards_conversion_rate( $conversion ) {
		$rewards_conversion_rate = get_option( 'ywpar_rewards_conversion_rate' );
		$conversion              = reset( $rewards_conversion_rate );
		return $conversion;
	}

	public function set_rewards_percentual_conversion_rate( $conversion ) {
		$percentual_conversion_rate = get_option( 'ywpar_rewards_percentual_conversion_rate' );
		$conversion                 = reset( $percentual_conversion_rate );
		return $conversion;
	}

	public function set_conversion_points_rate( $conversion ) {
		$earn_points_conversion_rate = get_option( 'ywpar_earn_points_conversion_rate' );
		$conversion                  = reset( $earn_points_conversion_rate );
		return $conversion;
	}


	public function custom_rewards_discount_max_discount( $max_discount, $data, $conversion ) {
		$type = $data->get_conversion_method();
		if ( 'fixed' === $type ) {
			$converted_max_discount = $this->calculate_price_by_currency( $max_discount, true );
			return $converted_max_discount;
		}
		return $max_discount;
	}

	public function reverse_gift_card_amount_before_add_to_cart( $cart_item_data, $product_id, $variation_id ) {
		if ( isset( $cart_item_data['ywgc_amount'] ) ) {
			$cart_item_data['ywgc_amount'] = $this->reverse_calculate_price_by_currency( $cart_item_data['ywgc_amount'] );
		}
		// Compatible with Measurement_Price_Calculator for WooCommerce plugin
		if ( class_exists( 'WC_Measurement_Price_Calculator' ) ) {
			if ( isset( $cart_item_data['custom_price'] ) ) {
				$cart_item_data['custom_price'] = $this->reverse_calculate_price_by_currency( $cart_item_data['custom_price'] );
			}
			if ( isset( $cart_item_data['extra_pack'] ) ) {
				$cart_item_data['extra_pack'] = $this->reverse_calculate_price_by_currency( $cart_item_data['extra_pack'] );
			}
		}
		return $cart_item_data;
	}

	public function custom_gift_cards_price_in_product_page( $amount ) {

		$converted_amount = array_map(
			function( $amount_item ) {

				return $this->calculate_price_by_currency( $amount_item, true );

			},
			$amount
		);

		return $converted_amount;
	}

	public function handle_woocommerce_paypal_payments_plugin_error( $data ) {
		$default_currency = get_option( 'woocommerce_currency' );
		if ( is_checkout() && ( ( 'ppcp-gateway' === $data['payment_method'] ) || ( 'ppec_paypal' === $data['payment_method'] ) ) && ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) && $this->apply_currency['currency'] !== $default_currency ) {
			wc_add_notice( __( 'Sorry! This Paypal payment method for ' . $this->apply_currency['currency'] . ' is not supported in your location. Please cancel and start payment again with ' . $default_currency . '.', 'yay-currency' ), 'error' );
		}
		return $data;
	}

	public function custom_table_rate_shipping_plugin_row_base_price( $row_base_price, $_product, $qty ) {
		$row_base_price = $_product->get_data()['price'] * $qty;
		return $row_base_price;
	}

	public function custom_order_meta_fee( $item, $cart_item_key, $values ) {
		if ( ! empty( $values['addons'] ) ) {
			foreach ( $values['addons'] as $index => $addon ) {
				$key = $addon['name'];
				if ( 'percentage_based' !== $addon['price_type'] ) {
					$item_fee             = $addon['price'];
					$converted_item_fee   = $this->calculate_price_by_currency( $item_fee, true );
					$formatted_item_fee   = wc_price(
						$converted_item_fee,
						$this->get_apply_currency_format_info()
					);
					$item_meta_value      = $item->get_meta_data( $addon['value'] )[ $index ];
					$item_meta_value->key = $key . ' (' . $formatted_item_fee . ')';
				}
			}
		}
	}

	public function custom_cart_item_addon_data( $addon_data, $addon, $cart_item ) {
		if ( 'percentage_based' !== $addon['price_type'] ) {
			$item_fee = $addon['price'];
			if ( 0 == $item_fee ) {
				return $addon_data;
			}
			$converted_item_fee = $this->calculate_price_by_currency( $item_fee, true );
			$formatted_item_fee = wc_price(
				$converted_item_fee,
				$this->get_apply_currency_format_info()
			);
			$addon_data['name'] = $addon['name'] . ' (' . $formatted_item_fee . ')';
		}
		return $addon_data;
	}

	public function custom_cart_item_subtotal( $subtotal, $cart_item, $cart_item_key ) {
		if ( 'betheme' === wp_get_theme()->template || ( ! is_cart() && ! is_checkout() ) ) {
			return $subtotal;
		}
		$tax_display                     = get_option( 'woocommerce_tax_display_cart' );
		$included_tax_cart_item_subtotal = 'incl' === $tax_display ? $cart_item['line_subtotal'] + $cart_item['line_subtotal_tax'] : $cart_item['line_subtotal'];

		// Compatible with Measurement_Price_Calculator for WooCommerce plugin
		if ( class_exists( 'WC_Measurement_Price_Calculator' ) ) {
			$extra_pack                      = $cart_item['extra_pack'];
			$convert_price                   = $this->calculate_price_by_currency( $extra_pack );
			$price                           = $convert_price * $cart_item['quantity'];
			$included_tax_cart_item_subtotal = 'incl' === $tax_display ? $price + $cart_item['line_subtotal_tax'] : $price;
		}

		$formmatted_cart_item_subtotal = wc_price(
			$included_tax_cart_item_subtotal,
			$this->get_apply_currency_format_info()
		);
		return $formmatted_cart_item_subtotal;
	}

	public function custom_product_addons_option_price( $price, $option ) {
		if ( 'percentage_based' !== $option['price_type'] ) {
			$price = $this->calculate_price_by_currency( $price );
		}
		return $price;
	}

	public function custom_request_paypal( $args, $order ) {
		if ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) {
			return $args;
		}
		$args['currency_code'] = $this->apply_currency['currency'];
		return $args;
	}

	public function custom_stripe_request_total_amount( $request, $api ) {

		global $wpdb;
		if ( isset( $request['currency'] ) && isset( $request['metadata'] ) && isset( $request['metadata']['order_id'] ) ) {
			$array_zero_decimal_currencies = array(
				'BIF',
				'CLP',
				'DJF',
				'GNF',
				'JPY',
				'KMF',
				'KRW',
				'MGA',
				'PYG',
				'RWF',
				'UGX',
				'VND',
				'VUV',
				'XAF',
				'XOF',
				'XPF',
			);
			if ( in_array( strtoupper( $request['currency'] ), $array_zero_decimal_currencies ) ) {
				$orderID = $request['metadata']['order_id'];
				$result  = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT meta_value FROM {$wpdb->postmeta} WHERE (post_id = %d AND meta_key = '_order_total')",
						$orderID
					)
				);

				if ( empty( $result ) ) {
					return $request;
				}

				$order_total = $result;

				$request['amount'] = (int) $order_total;
			}
		}
			return $request;
	}

	public function custom_subscription_sign_up_fee( $sign_up_fee ) {
		$converted_sign_up_fee = $this->calculate_price_by_currency( $sign_up_fee, true );
		return $converted_sign_up_fee;
	}

	public function custom_subscription_price_string( $price_string, $product, $args ) {

		if ( is_checkout() ) {
			return $price_string;
		}

		$quantity = 1;

		if ( is_cart() ) {
			foreach ( WC()->cart->get_cart() as $cart_item ) {

				$item = $cart_item['data'];

				if ( ! empty( $item ) ) {
						$quantity = $cart_item['quantity'];
				}
			}
		}

		$signup_fee_original = get_post_meta( $product->get_id(), '_subscription_sign_up_fee', true );

		$converted_signup_fee = $this->calculate_price_by_currency( $signup_fee_original, true ) * $quantity;

		$formatted_signup_fee = wc_price(
			$converted_signup_fee,
			$this->get_apply_currency_format_info()
		);

		$custom_sign_up_fee = ( isset( $args['sign_up_fee'] ) && 0 != $signup_fee_original ) ? __( ' and a ' . wp_kses_post( $formatted_signup_fee ) . ' sign-up fee', 'woocommerce' ) : '';

		if ( in_array( $product->get_type(), array( 'variable-subscription' ) ) ) {
			$formatted_price            = wc_price(
				$product->get_price(),
				$this->get_apply_currency_format_info()
			);
			$price_string_no_html       = strip_tags( $price_string );
			$price_string_no_fee_string = substr( $price_string_no_html, 0, strpos( $price_string_no_html, 'and' ) ); // remove default sign-up fee string
			$start_index_to_cut_string  = strpos( $price_string_no_html, ' /' ) ? strpos( $price_string_no_html, ' /' ) : ( strpos( $price_string_no_html, ' every' ) ? strpos( $price_string_no_html, ' every' ) : strpos( $price_string_no_html, ' for' ) );
			$interval_subscrition       = substr( empty( $price_string_no_fee_string ) ? $price_string_no_html : $price_string_no_fee_string, $start_index_to_cut_string ); // get default interval subscrition (ex: /month or every x days...)
			$price_string               = __( 'From: ', 'woocommerce' ) . $formatted_price . $interval_subscrition . $custom_sign_up_fee;
		}

		return $price_string;
	}

	public function custom_variation_price_hash( $hash ) {
		if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
			$hash[] = sanitize_key( $_COOKIE['yay_currency_widget'] );
		}
		return $hash;
	}

	public function change_format_order_item_totals( $total_rows, $order, $tax_display ) {
		if ( isset( $_GET['action'] ) && 'generate_wpo_wcpdf' === $_GET['action'] ) {
			return $total_rows;
		}
		$yay_currency_checkout_currency = get_post_meta( $order->get_id(), '_order_currency', true );
		if ( ! empty( $yay_currency_checkout_currency ) ) {
			$convert_currency = $this->apply_currency;
			foreach ( $this->converted_currency as $key => $value ) {
				if ( $value['currency'] == $yay_currency_checkout_currency ) {
					$convert_currency = $value;
				}
			}
			$format = $this->format_currency_position( $convert_currency['currencyPosition'] );

			$fees = $order->get_fees();
			if ( $fees ) {
				foreach ( $fees as $id => $fee ) {
					if ( apply_filters( 'woocommerce_get_order_item_totals_excl_free_fees', empty( $fee['line_total'] ) && empty( $fee['line_tax'] ), $id ) ) {
						continue;
					}
					$total_rows[ 'fee_' . $fee->get_id() ] = array(
						'label' => $fee->get_name() . ':',
						'value' => wc_price(
							'excl' === $tax_display ? $fee->get_total() : $fee->get_total() + $fee->get_total_tax(),
							array(
								'currency'           => $yay_currency_checkout_currency,
								'decimal_separator'  => $convert_currency['decimalSeparator'],
								'thousand_separator' => $convert_currency['thousandSeparator'],
								'decimals'           => (int) $convert_currency['numberDecimal'],
								'price_format'       => $format,
							)
						),
					);
				}
			}

			if ( 'excl' === $tax_display && wc_tax_enabled() ) {
				if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
					foreach ( $order->get_tax_totals() as $code => $tax ) {
						$total_rows[ sanitize_title( $code ) ] = array(
							'label' => $tax->label . ':',
							'value' => $tax->formatted_amount,
						);
					}
				} else {
					$total_rows['tax'] = array(
						'label' => WC()->countries->tax_or_vat() . ':',
						'value' => wc_price(
							$order->get_total_tax(),
							array(
								'currency'           => $yay_currency_checkout_currency,
								'decimal_separator'  => $convert_currency['decimalSeparator'],
								'thousand_separator' => $convert_currency['thousandSeparator'],
								'decimals'           => (int) $convert_currency['numberDecimal'],
								'price_format'       => $format,
							)
						),
					);
				}
			}

			$refunds = $order->get_refunds();
			if ( $refunds ) {
				foreach ( $refunds as $id => $refund ) {
					$total_rows[ 'refund_' . $id ] = array(
						'label' => $refund->get_reason() ? $refund->get_reason() : __( 'Refund', 'woocommerce' ) . ':',
						'value' => wc_price(
							'-' . $refund->get_amount(),
							array(
								'currency'           => $yay_currency_checkout_currency,
								'decimal_separator'  => $convert_currency['decimalSeparator'],
								'thousand_separator' => $convert_currency['thousandSeparator'],
								'decimals'           => (int) $convert_currency['numberDecimal'],
								'price_format'       => $format,
							)
						),
					);
				}
			}
		}
		return $total_rows;
	}

	public function get_formatted_order_total( $formatted_total, $order ) {
		if ( ( isset( $_GET['action'] ) && 'generate_wpo_wcpdf' === $_GET['action'] ) || ( isset( $_GET['_fs_blog_admin'] ) && 'true' === $_GET['_fs_blog_admin'] ) ) {
			return $formatted_total;
		}
		$yay_currency_checkout_currency = get_post_meta( $order->get_id(), '_order_currency', true );
		if ( ! empty( $yay_currency_checkout_currency ) ) {
			$total            = get_post_meta( $order->get_id(), '_order_total', true );
			$convert_currency = $this->apply_currency;
			foreach ( $this->converted_currency as $key => $value ) {
				if ( $value['currency'] == $yay_currency_checkout_currency ) {
					$convert_currency = $value;
				}
			}
			$format          = $this->format_currency_position( $convert_currency['currencyPosition'] );
			$formatted_total = wc_price(
				$total,
				array(
					'currency'           => $yay_currency_checkout_currency,
					'decimal_separator'  => $convert_currency['decimalSeparator'],
					'thousand_separator' => $convert_currency['thousandSeparator'],
					'decimals'           => (int) $convert_currency['numberDecimal'],
					'price_format'       => $format,
				)
			);
		}
		return $formatted_total;
	}

	public function change_format_order_line_subtotal( $subtotal, $item, $order ) {
		$yay_currency_checkout_currency = get_post_meta( $order->get_id(), '_order_currency', true );
		if ( ! empty( $yay_currency_checkout_currency ) ) {
			$convert_currency = $this->apply_currency;
			foreach ( $this->converted_currency as $key => $value ) {
				if ( $value['currency'] == $yay_currency_checkout_currency ) {
					$convert_currency = $value;
				}
			}
			$format      = $this->format_currency_position( $convert_currency['currencyPosition'] );
			$tax_display = get_option( 'woocommerce_tax_display_cart' );
			if ( 'excl' === $tax_display ) {
				$ex_tax_label = $order->get_prices_include_tax() ? 1 : 0;

				$subtotal = wc_price(
					$order->get_line_subtotal( $item ),
					array(
						'ex_tax_label'       => $ex_tax_label,
						'currency'           => $yay_currency_checkout_currency,
						'decimal_separator'  => $convert_currency['decimalSeparator'],
						'thousand_separator' => $convert_currency['thousandSeparator'],
						'decimals'           => (int) $convert_currency['numberDecimal'],
						'price_format'       => $format,
					)
				);
			} else {
				$subtotal = wc_price(
					$order->get_line_subtotal( $item, true ),
					array(
						'currency'           => $yay_currency_checkout_currency,
						'decimal_separator'  => $convert_currency['decimalSeparator'],
						'thousand_separator' => $convert_currency['thousandSeparator'],
						'decimals'           => (int) $convert_currency['numberDecimal'],
						'price_format'       => $format,
					)
				);
			}
		}
		return $subtotal;
	}

	protected function get_cart_subtotal_for_order( $order ) {
		return wc_remove_number_precision(
			$order->get_rounded_items_total(
				$this->get_values_for_total( 'subtotal', $order )
			)
		);
	}

	protected function get_values_for_total( $field, $order ) {
		$items = array_map(
			function ( $item ) use ( $field ) {
				return wc_add_number_precision( $item[ $field ], false );
			},
			array_values( $order->get_items() )
		);
		return $items;
	}

	protected static function round_at_subtotal() {
		return 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' );
	}

	protected static function round_line_tax( $value, $in_cents = true ) {
		if ( ! self::round_at_subtotal() ) {
			$value = wc_round_tax_total( $value, $in_cents ? 0 : null );
		}
		return $value;
	}

	public function get_formatted_order_subtotal( $subtotal, $compound, $order ) {
		$yay_currency_checkout_currency = get_post_meta( $order->get_id(), '_order_currency', true );
		if ( ! empty( $yay_currency_checkout_currency ) ) {
			$convert_currency = $this->apply_currency;
			foreach ( $this->converted_currency as $key => $value ) {
				if ( $value['currency'] == $yay_currency_checkout_currency ) {
					$convert_currency = $value;
				}
			}
			$format      = $this->format_currency_position( $convert_currency['currencyPosition'] );
			$tax_display = get_option( 'woocommerce_tax_display_cart' );
			$subtotal    = $this->get_cart_subtotal_for_order( $order );

			if ( ! $compound ) {

				if ( 'incl' === $tax_display ) {
					$subtotal_taxes = 0;
					foreach ( $order->get_items() as $item ) {
						$subtotal_taxes += self::round_line_tax( $item->get_subtotal_tax(), false );
					}
					$subtotal += wc_round_tax_total( $subtotal_taxes );
				}

				$subtotal = wc_price(
					$subtotal,
					array(
						'currency'           => $yay_currency_checkout_currency,
						'decimal_separator'  => $convert_currency['decimalSeparator'],
						'thousand_separator' => $convert_currency['thousandSeparator'],
						'decimals'           => (int) $convert_currency['numberDecimal'],
						'price_format'       => $format,
					)
				);

				if ( 'excl' === $tax_display && $order->get_prices_include_tax() && wc_tax_enabled() ) {
					$subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
				}
			} else {
				if ( 'incl' === $tax_display ) {
					return '';
				}

				$subtotal += $order->get_shipping_total();

				foreach ( $order->get_taxes() as $tax ) {
					if ( $tax->is_compound() ) {
						continue;
					}
					$subtotal = $subtotal + $tax->get_tax_total() + $tax->get_shipping_tax_total();
				}

				$subtotal = $subtotal - $order->get_total_discount();
				$subtotal = wc_price(
					$subtotal,
					array(
						'currency'           => $yay_currency_checkout_currency,
						'decimal_separator'  => $convert_currency['decimalSeparator'],
						'thousand_separator' => $convert_currency['thousandSeparator'],
						'decimals'           => (int) $convert_currency['numberDecimal'],
						'price_format'       => $format,
					)
				);
			}
		}
		return $subtotal;
	}

	public function get_formatted_order_shipping( $shipping, $order, $tax_display ) {
		$yay_currency_checkout_currency = get_post_meta( $order->get_id(), '_order_currency', true );
		if ( ! empty( $yay_currency_checkout_currency ) ) {
			$convert_currency = $this->apply_currency;
			foreach ( $this->converted_currency as $key => $value ) {
				if ( $value['currency'] == $yay_currency_checkout_currency ) {
					$convert_currency = $value;
				}
			}
			$format      = $this->format_currency_position( $convert_currency['currencyPosition'] );
			$tax_display = $tax_display ? $tax_display : get_option( 'woocommerce_tax_display_cart' );
			if ( 0 < abs( (float) $order->get_shipping_total() ) ) {

				if ( 'excl' === $tax_display ) {
					$shipping = wc_price(
						$order->get_shipping_total(),
						array(
							'currency'           => $yay_currency_checkout_currency,
							'decimal_separator'  => $convert_currency['decimalSeparator'],
							'thousand_separator' => $convert_currency['thousandSeparator'],
							'decimals'           => (int) $convert_currency['numberDecimal'],
							'price_format'       => $format,
						)
					);

					if ( (float) $order->get_shipping_tax() > 0 && $order->get_prices_include_tax() ) {
						$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_tax_label', '&nbsp;<small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>', $order, $tax_display );
					}
				} else {
					$shipping = wc_price(
						$order->get_shipping_total() + $order->get_shipping_tax(),
						array(
							'currency'           => $yay_currency_checkout_currency,
							'decimal_separator'  => $convert_currency['decimalSeparator'],
							'thousand_separator' => $convert_currency['thousandSeparator'],
							'decimals'           => (int) $convert_currency['numberDecimal'],
							'price_format'       => $format,
						)
					);

					if ( (float) $order->get_shipping_tax() > 0 && ! $order->get_prices_include_tax() ) {
						$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_tax_label', '&nbsp;<small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>', $order, $tax_display );
					}
				}

				/* translators: %s: method */
				$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_shipped_via', '&nbsp;<small class="shipped_via">' . sprintf( __( 'via %s', 'woocommerce' ), $order->get_shipping_method() ) . '</small>', $order );

			} elseif ( $order->get_shipping_method() ) {
				$shipping = $order->get_shipping_method();
			} else {
				$shipping = __( 'Free!', 'woocommerce' );
			}
		}
		return $shipping;
	}

	public function get_formatted_order_discount( $tax_display, $order ) {
		$yay_currency_checkout_currency = get_post_meta( $order->get_id(), '_order_currency', true );
		if ( ! empty( $yay_currency_checkout_currency ) ) {
			$convert_currency = $this->apply_currency;
			foreach ( $this->converted_currency as $key => $value ) {
				if ( $value['currency'] == $yay_currency_checkout_currency ) {
					$convert_currency = $value;
				}
			}
			$format      = $this->format_currency_position( $convert_currency['currencyPosition'] );
			$tax_display = wc_price(
				$order->get_total_discount( 'excl' === $tax_display && 'excl' === get_option( 'woocommerce_tax_display_cart' ) ),
				array(
					'currency'           => $yay_currency_checkout_currency,
					'decimal_separator'  => $convert_currency['decimalSeparator'],
					'thousand_separator' => $convert_currency['thousandSeparator'],
					'decimals'           => (int) $convert_currency['numberDecimal'],
					'price_format'       => $format,
				)
			);
		}
		return $tax_display;
	}

	protected function evaluate_cost( $sum, $args = array() ) {
		if ( ! is_array( $args ) || ! array_key_exists( 'qty', $args ) || ! array_key_exists( 'cost', $args ) ) {
			wc_doing_it_wrong( __FUNCTION__, '$args must contain `cost` and `qty` keys.', '4.0.1' );
		}

		include_once WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php';
		$args           = apply_filters( 'woocommerce_evaluate_shipping_cost_args', $args, $sum, $this );
		$locale         = localeconv();
		$decimals       = array( wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'], ',' );
		$this->fee_cost = $args['cost'];
		add_shortcode( 'fee', array( $this, 'fee' ) );

		$sum = do_shortcode(
			str_replace(
				array(
					'[qty]',
					'[cost]',
				),
				array(
					$args['qty'],
					$args['cost'],
				),
				$sum
			)
		);
		remove_shortcode( 'fee', array( $this, 'fee' ) );
		$sum = preg_replace( '/\s+/', '', $sum );
		$sum = str_replace( $decimals, '.', $sum );
		$sum = rtrim( ltrim( $sum, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );
		return $sum ? \WC_Eval_Math::evaluate( $sum ) : 0;
	}

	public function change_shipping_cost( $methods, $package ) {
		if ( count( array_filter( $methods ) ) ) {
			foreach ( $methods as $key => $method ) {
				if ( 'betrs_shipping' == $method->method_id || 'printful_shipping' == $method->method_id || 'easyship' == $method->method_id ) {
					continue;
				}
				if ( 'flat_rate' == $method->method_id ) {
					$shipping  = new \WC_Shipping_Flat_Rate( $method->instance_id );
					$has_costs = false;
					$cost      = $shipping->get_option( 'cost' );

					if ( '' !== $cost ) {
						$has_costs    = true;
						$rate['cost'] = $this->evaluate_cost(
							$cost,
							array(
								'qty'  => $shipping->get_package_item_qty( $package ),
								'cost' => $package['contents_cost'],
							)
						);
					}

					$shipping_classes = WC()->shipping->get_shipping_classes();

					if ( ! empty( $shipping_classes ) ) {
						$product_shipping_classes = $shipping->find_shipping_classes( $package );
						$shipping_classes_cost    = 0;

						foreach ( $product_shipping_classes as $shipping_class => $products ) {
							$shipping_class_term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
							$class_cost_string   = $shipping_class_term && $shipping_class_term->term_id ? $shipping->get_option( 'class_cost_' . $shipping_class_term->term_id, $shipping->get_option( 'class_cost_' . $shipping_class, '' ) ) : $shipping->get_option( 'no_class_cost', '' );

							if ( '' === $class_cost_string ) {
								continue;
							}

							$has_costs  = true;
							$class_cost = $this->evaluate_cost(
								$class_cost_string,
								array(
									'qty'  => array_sum( wp_list_pluck( $products, 'quantity' ) ),
									'cost' => array_sum( wp_list_pluck( $products, 'line_total' ) ),
								)
							);

							if ( 'class' === $shipping->type ) {
								$rate['cost'] += $class_cost;
							} else {
								$shipping_classes_cost = $class_cost > $shipping_classes_cost ? $class_cost : $shipping_classes_cost;
							}
						}

						if ( 'order' === $shipping->type && $shipping_classes_cost ) {
							$rate['cost'] += $shipping_classes_cost;
						}
					}
					if ( $has_costs ) {
						if ( is_checkout() && 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) {
							$method->set_cost( $rate['cost'] );
						} else {
							$method->set_cost( $this->calculate_price_by_currency( $rate['cost'], true ) );
						}
					}
				} elseif ( 'printful_shipping_STANDARD' === $method->method_id ) {
					continue;
				} else {
					$special_shipping_methods = array( 'table_rate', 'per_product', 'tree_table_rate', 'wf_fedex_woocommerce_shipping' );

					if ( in_array( $method->method_id, $special_shipping_methods ) ) {
						if ( ( is_checkout() ) && 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) {
							return $methods;
						}

						$method->cost = $this->calculate_price_by_currency( $method->cost, true );
						return $methods;
					}

					$data = get_option( 'woocommerce_' . $method->method_id . '_' . $method->instance_id . '_settings' );
					$method->set_cost( isset( $data['cost'] ) ? $this->calculate_price_by_currency( $data['cost'], true ) : $this->calculate_price_by_currency( $method->get_cost(), true ) );
				}

				// Set tax for shipping method
				if ( count( $method->get_taxes() ) ) {
					$tax_new = array();
					foreach ( $method->get_taxes() as $key => $tax ) {
						$tax_new[ $key ] = $this->calculate_price_by_currency( $tax, true );
					}
					$method->set_taxes( $tax_new );
				}
			}
		}

		return $methods;
	}

	public function change_coupon_amount( $price, $coupon ) {
		if ( $coupon->is_type( array( 'percent' ) ) ) {
			return $price;
		}
		if ( is_checkout() && ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) ) {
			return $price;
		}

		// Compatible with YITH Points and Rewards plugin
		if ( class_exists( 'YITH_WC_Points_Rewards' ) ) {
			if ( \YITH_WC_Points_Rewards_Redemption()->check_coupon_is_ywpar( $coupon ) ) {
				// Fix for change currency after apply points
				$conversion_rate_method = \YITH_WC_Points_Rewards()->get_option( 'conversion_rate_method' );
				if ( 'percentage' === $conversion_rate_method ) {
					$percentual_conversion_rate = get_option( 'ywpar_rewards_percentual_conversion_rate' );
					$cart_total                 = WC()->cart->subtotal;
					$point                      = WC()->session->get( 'ywpar_coupon_code_points' );
					$percent                    = ( $point / reset( $percentual_conversion_rate )['points'] ) * reset( $percentual_conversion_rate )['discount'];
					$original_coupon_price      = $cart_total * $percent / 100;
					return $original_coupon_price;
				}
			}
		}

		// Coupon type != 'percent' calculate price
		$converted_coupon_price = $this->calculate_price_by_currency( $price, true );
		return $converted_coupon_price;
	}

	public function get_symbol_by_currency( $currency_name ) {
		foreach ( $this->converted_currency as $key => $currency ) {
			if ( $currency['currency'] == $currency_name ) {
				return $currency['symbol'];
			}
		}
	}

	public function show_notice_notification() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'You\'re using the maximum number of currencies in the YayCurrency lite version. Please delete one of them so that you can add another.', 'yay-currency' ); ?></p>
		</div>
		<?php
	}

	public function custom_raw_price( $price, $product ) {

		if ( ( is_checkout() ) && ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) ) {
			return $price;
		}

		if ( is_null( $this->apply_currency ) ) {
			return $price;
		}

		if ( empty( $price ) || ! is_numeric( $price ) ) {
			return $price;
		}

		// Fix for manual renewal subscription product and still keep old code works well
		if ( is_checkout() || is_cart() || wp_doing_ajax() ) {

			$is_yaye_adjust_price = false;
			$is_ydp_adjust_price  = false;

			if ( class_exists( '\YayExtra\Classes\ProductPage' ) ) {
				$is_yaye_adjust_price = apply_filters( 'yaye_check_adjust_price', false );
			}

			if ( class_exists( '\YayPricing\FrontEnd\ProductPricing' ) ) {
				$is_ydp_adjust_price = apply_filters( 'ydp_check_adjust_price', false );
			}

			if ( in_array( $product->get_type(), array( 'subscription', 'variable-subscription', 'subscription_variation' ) ) ) {
				remove_filter( 'woocommerce_cart_item_subtotal', array( $this, 'custom_cart_item_subtotal' ) );
			}

			$except_class_plugins = array(
				'WC_Product_Addons',
				'B2bwhs',
				'WC_Measurement_Price_Calculator',
				'Cartflows_Checkout',
				'\WP_Grid_Builder\Includes\Plugin',
				'B2bkingcore',
				'WCPA', // Woocommerce Custom Product Addons
				'AWCPA', // Woocommerce Custom Product Addons
				'WoonpCore', // Name Your Price for WooCommerce
			);

			foreach ( $except_class_plugins as $class ) {
				if ( class_exists( $class ) ) {
					$price = $this->calculate_price_by_currency( $price );
					return $price;
				}
			}

			if ( class_exists( '\YayPricing\FrontEnd\ProductPricing' ) && $is_ydp_adjust_price ) {
				if ( class_exists( '\YayExtra\Classes\ProductPage' ) && $is_yaye_adjust_price ) {
					return $price;
				} else {
					$price = $this->calculate_price_by_currency( $price );
					return $price;
				}
			}

			if ( class_exists( '\YayExtra\Classes\ProductPage' ) && $is_yaye_adjust_price ) {
				return $price;
			}

			// Compatible with Tiered Pricing Table for WooCommerce plugin
			if ( class_exists( 'TierPricingTable\TierPricingTablePlugin' ) ) {
				if ( isset( $product->get_changes()['price'] ) ) {
					return $product->get_changes()['price'];
				}
			}

			$product_price = $product->get_data()['price'] ? $product->get_data()['price'] : $price;
			$price         = $this->calculate_price_by_currency( $product_price );

			return $price;
		}

		if ( class_exists( 'HivePress\Core' ) ) {
			return $price;
		}

		$price = $this->calculate_price_by_currency( $price );
		return $price;
	}

	public function get_currency_by_ID( $currency_ID ) {
		$currency = get_post( $currency_ID );

		if ( empty( $currency ) || 'yay-currency-manage' !== $currency->post_type ) {
			$currencies_data = $this->get_current_and_default_currency();
			return $currencies_data['default_currency'];
		}

		$currency_meta = get_post_meta( $currency_ID, '', true );

		$converted_currency = array(
			'ID'                => $currency->ID,
			'currency'          => $currency->post_title,
			'currencyPosition'  => $currency_meta['currency_position'][0],
			'thousandSeparator' => $currency_meta['thousand_separator'][0],
			'decimalSeparator'  => $currency_meta['decimal_separator'][0],
			'numberDecimal'     => $currency_meta['number_decimal'][0],
			'roundingType'      => $currency_meta['rounding_type'][0],
			'roundingValue'     => $currency_meta['rounding_value'][0],
			'subtractAmount'    => $currency_meta['subtract_amount'][0],
			'rate'              => $currency_meta['rate'][0],
			'fee'               => maybe_unserialize( $currency_meta['fee'][0] ),
			'status'            => $currency_meta['status'][0],
			'paymentMethods'    => maybe_unserialize( $currency_meta['payment_methods'][0] ),
			'countries'         => maybe_unserialize( $currency_meta['countries'][0] ),
			'symbol'            => get_woocommerce_currency_symbol( $currency->post_title ),
		);
		return $converted_currency;
	}

	public function get_currencies_post_type() {
		$settings_data = Settings::getInstance();
		$currencies    = $settings_data->yay_list_currencies;

		return $currencies;
	}

	public function get_current_and_default_currency() {
		$current_currency_ID   = $this->apply_currency['ID'];
		$current_currency      = $this->get_currency_by_ID( $current_currency_ID );
		$default_currency_code = get_option( 'woocommerce_currency' );
		$default_currency      = null;

		foreach ( $this->converted_currency as $currency ) {
			if ( $currency['currency'] === $default_currency_code ) {
				$default_currency = $currency;
				break;
			}
		}
		return array(
			'current_currency' => $current_currency,
			'default_currency' => $default_currency,
		);
	}

	public function add_notice_checkout_payment_methods() {
		$currencies_data = $this->get_current_and_default_currency();
		if ( $currencies_data['current_currency']['currency'] == $currencies_data['default_currency']['currency'] ) {
			return;
		}
		if ( current_user_can( 'manage_options' ) ) {
			// only for admin
			echo "<div class='yay-currency-checkout-notice'><span>" . esc_html__( 'The current payment method for ', 'yay-currency' ) . '<strong>' . wp_kses_post( html_entity_decode( esc_html__( $currencies_data['current_currency']['currency'], 'yay-currency' ) ) ) . '</strong></span><span>' . esc_html__( ' is not supported in your location. ', 'yay-currency' ) . '</span><span>' . esc_html__( 'So your payment will be recorded in ', 'yay-currency' ) . '</span><strong>' . wp_kses_post( html_entity_decode( esc_html__( $currencies_data['default_currency']['currency'], 'yay-currency' ) ) ) . '.</strong></span></div>';
			echo "<div class='yay-currency-checkout-notice-admin'><span>" . esc_html__( 'Are you the admin? You can change the checkout options for payment methods ', 'yay-currency' ) . '<a href=' . esc_url( admin_url( '/admin.php?page=yay_currency&tabID=1' ) ) . '>' . esc_html__( 'here', 'yay-currency' ) . '</a>.</span><br><span><i>' . esc_html__( '(Only logged in admin can see this.)', 'yay-currency' ) . '</i></span></div>';
		} else {
			echo "<div class='yay-currency-checkout-notice user'><span>" . esc_html__( 'The current payment method for ', 'yay-currency' ) . '<strong>' . wp_kses_post( html_entity_decode( esc_html__( $currencies_data['current_currency']['currency'], 'yay-currency' ) ) ) . '</strong></span><span>' . esc_html__( ' is not supported in your location. ', 'yay-currency' ) . '</span><span>' . esc_html__( 'So your payment will be recorded in ', 'yay-currency' ) . '</span><strong>' . wp_kses_post( html_entity_decode( esc_html__( $currencies_data['default_currency']['currency'], 'yay-currency' ) ) ) . '.</strong></span></div>';
		}
	}

	public function calculate_price_by_currency( $price, $exclude = false ) {
		if ( 'percentage' === $this->apply_currency['fee']['type'] ) {
			$rate_after_fee = (float) $this->apply_currency['rate'] + ( (float) $this->apply_currency['rate'] * ( (float) $this->apply_currency['fee']['value'] / 100 ) );
		} else {
			$rate_after_fee = (float) $this->apply_currency['rate'] + (float) $this->apply_currency['fee']['value'];
		}
		$price = ( (float) $price * $rate_after_fee );

		if ( $exclude ) {
			return $price;
		}

		if ( 'disabled' !== $this->apply_currency['roundingType'] ) {

			$rounding_type   = $this->apply_currency['roundingType'];
			$rounding_value  = $this->apply_currency['roundingValue'];
			$subtract_amount = $this->apply_currency['subtractAmount'];

			switch ( $rounding_type ) {
				case 'up':
					$price = ceil( $price / $rounding_value ) * $rounding_value - $subtract_amount;
					return $price;
				case 'down':
					$price = floor( $price / $rounding_value ) * $rounding_value - $subtract_amount;
					return $price;
				case 'nearest':
					$price = round( $price / $rounding_value ) * $rounding_value - $subtract_amount;
					return $price;
				default:
					return;
			}
		}
		return $price;
	}

	public function calculate_price_by_currency_html( $currency, $original_price, $quantity = 1 ) {
		$price = $original_price * $quantity * $currency['rate'];

		if ( 'disabled' !== $currency['roundingType'] ) {

			$rounding_type   = $currency['roundingType'];
			$rounding_value  = $currency['roundingValue'];
			$subtract_amount = $currency['subtractAmount'];

			switch ( $rounding_type ) {
				case 'up':
					$price = ceil( $price / $rounding_value ) * $rounding_value - $subtract_amount;
					break;
				case 'down':
					$price = floor( $price / $rounding_value ) * $rounding_value - $subtract_amount;
					break;
				case 'nearest':
					$price = round( $price / $rounding_value ) * $rounding_value - $subtract_amount;
					break;
				default:
					return;
			}
		}

		$format          = $this->format_currency_position( $currency['currencyPosition'] );
		$formatted_price = wc_price(
			$price,
			array(
				'currency'           => $currency['currency'],
				'decimal_separator'  => $currency['decimalSeparator'],
				'thousand_separator' => $currency['thousandSeparator'],
				'decimals'           => (int) $currency['numberDecimal'],
				'price_format'       => $format,
			)
		);
		return $formatted_price;
	}

	public function custom_checkout_product_subtotal( $product_subtotal, $product, $quantity, $cart ) {
		$currencies_data = $this->get_current_and_default_currency();
		$product_price   = $product->get_price();
		if ( class_exists( '\YayExtra\Classes\ProductPage' ) ) {
			$product_price = $this->reverse_calculate_price_by_currency( $product_price );
		}

		if ( $currencies_data['current_currency']['currency'] == $currencies_data['default_currency']['currency'] || is_cart() ) {
			$format           = $this->format_currency_position( $currencies_data['current_currency']['currencyPosition'] );
			$product_subtotal = wc_price(
				$product_price,
				array(
					'currency'           => $currencies_data['current_currency']['currency'],
					'decimal_separator'  => $currencies_data['current_currency']['decimalSeparator'],
					'thousand_separator' => $currencies_data['current_currency']['thousandSeparator'],
					'decimals'           => (int) $currencies_data['current_currency']['numberDecimal'],
					'price_format'       => $format,
				)
			);
			return $product_subtotal;
		}
		if ( is_checkout() ) {
			remove_filter( 'woocommerce_cart_item_subtotal', array( $this, 'custom_cart_item_subtotal' ) );
			$original_product_subtotal  = $this->calculate_price_by_currency_html( $currencies_data['default_currency'], $product_price, $quantity );
			$converted_product_subtotal = $this->calculate_price_by_currency_html( $currencies_data['current_currency'], $product_price, $quantity );
			$product_subtotal           = $original_product_subtotal . ' (~' . $converted_product_subtotal . ')';
			return $product_subtotal;
		}

		return $product_subtotal;
	}

	public function custom_checkout_order_subtotal( $price ) {
		$currencies_data = $this->get_current_and_default_currency();
		$subtotal_price  = WC()->cart->get_displayed_subtotal();
		if ( class_exists( '\YayExtra\Classes\ProductPage' ) ) {
			$subtotal_price = $this->reverse_calculate_price_by_currency( $subtotal_price );
		}

		if ( $currencies_data['current_currency']['currency'] == $currencies_data['default_currency']['currency'] ) {
			$format   = $this->format_currency_position( $currencies_data['current_currency']['currencyPosition'] );
			$subtotal = wc_price(
				$subtotal_price,
				array(
					'currency'           => $currencies_data['current_currency']['currency'],
					'decimal_separator'  => $currencies_data['current_currency']['decimalSeparator'],
					'thousand_separator' => $currencies_data['current_currency']['thousandSeparator'],
					'decimals'           => (int) $currencies_data['current_currency']['numberDecimal'],
					'price_format'       => $format,
				)
			);
			return $subtotal;
		}
		if ( is_checkout() ) {
			$original_subtotal  = $this->calculate_price_by_currency_html( $currencies_data['default_currency'], $subtotal_price );
			$converted_subtotal = $this->calculate_price_by_currency_html( $currencies_data['current_currency'], $subtotal_price );
			$subtotal           = $original_subtotal . ' (~' . $converted_subtotal . ')';
			return $subtotal;
		}
		return $price;
	}

	public function custom_checkout_order_total( $price ) {
		$currencies_data = $this->get_current_and_default_currency();
		$total_price     = WC()->cart->total;
		if ( class_exists( '\YayExtra\Classes\ProductPage' ) ) {
			$total_price = $this->reverse_calculate_price_by_currency( $total_price );
		}
		$format = $this->format_currency_position( $currencies_data['current_currency']['currencyPosition'] );

		if ( $currencies_data['current_currency']['currency'] == $currencies_data['default_currency']['currency'] ) {
			$total = wc_price(
				$total_price,
				array(
					'currency'           => $currencies_data['current_currency']['currency'],
					'decimal_separator'  => $currencies_data['current_currency']['decimalSeparator'],
					'thousand_separator' => $currencies_data['current_currency']['thousandSeparator'],
					'decimals'           => (int) $currencies_data['current_currency']['numberDecimal'],
					'price_format'       => $format,
				)
			);
			return $total;
		}

		if ( is_checkout() ) {
			$original_total  = $this->calculate_price_by_currency_html( $currencies_data['default_currency'], $total_price );
			$converted_total = $this->calculate_price_by_currency_html( $currencies_data['current_currency'], $total_price );
			$total           = $original_total . ' (~' . $converted_total . ')';
			return $total;
		}
		return $price;
	}

	public function custom_shipping_fee( $label, $method ) {
		if ( is_checkout() ) {
			if ( 'Free shipping' === $label ) {
				return $label;
			}
			$currencies_data = $this->get_current_and_default_currency();
			if ( $currencies_data['current_currency']['currency'] == $currencies_data['default_currency']['currency'] ) {
				return $label;
			}
			$shipping_fee           = (float) $method->cost;
			$converted_shipping_fee = $this->calculate_price_by_currency( $shipping_fee, true );
			$formatted_shipping_fee = wc_price(
				$converted_shipping_fee,
				$this->get_apply_currency_format_info()
			);
				$label             .= ' (~' . $formatted_shipping_fee . ')';
				return $label;
		}
		return $label;
	}

	public function custom_discount_coupon( $coupon_html, $coupon, $discount_amount_html ) {

		if ( is_checkout() ) {

			$currencies_data = $this->get_current_and_default_currency();

			if ( $currencies_data['current_currency']['currency'] == $currencies_data['default_currency']['currency'] ) {
				return $coupon_html;
			}

			$discount_type   = $coupon->get_discount_type();
			$discount_amount = (float) $coupon->get_amount();

			if ( 'percent' !== $discount_type ) {
				$converted_discount_amount = $this->calculate_price_by_currency( $discount_amount, true );
				$formatted_discount_amount = wc_price(
					$converted_discount_amount,
					$this->get_apply_currency_format_info()
				);

				$custom_discount_amount_html = $discount_amount_html . ' (~' . $formatted_discount_amount . ')';
				$coupon_html                 = str_replace( $discount_amount_html, $custom_discount_amount_html, $coupon_html );

				return $coupon_html;
			}

			$cart_subtotal            = WC()->cart->subtotal;
			$converted_cart_subtotal  = $this->calculate_price_by_currency( $cart_subtotal, true );
			$discount_price           = ( $converted_cart_subtotal * $discount_amount ) / 100;
			$formatted_discount_price = wc_price(
				$discount_price,
				$this->get_apply_currency_format_info()
			);

			$custom_discount_amount_html = $discount_amount_html . ' (~' . $formatted_discount_price . ')';
			$coupon_html                 = str_replace( $discount_amount_html, $custom_discount_amount_html, $coupon_html );
			return $coupon_html;
		}
		return $coupon_html;
	}

	public function custom_total_tax( $tax_display ) {

		if ( count( $tax_display ) > 0 ) {
			$tax_info                       = reset( $tax_display );
			$converted_tax_amount           = $this->calculate_price_by_currency( $tax_info->amount );
			$formatted_converted_tax_amount = wc_price(
				$converted_tax_amount,
				$this->get_apply_currency_format_info()
			);
			$tax_info->formatted_amount    .= " (~$formatted_converted_tax_amount )";
		}

		return $tax_display;
	}

	public function custom_mini_cart_price() {
		if ( is_checkout() && ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) ) {
			return false;
		}
		if ( is_cart() || is_checkout() ) {
			return false;
		}
		WC()->cart->calculate_totals();
	}

	public function conditional_payment_gateways( $available_gateways ) {
		$total_price = WC()->cart ? WC()->cart->total : 0;
		apply_filters( 'order_in_default_price', $total_price );

		if ( is_checkout() && ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) ) {
			$currencies_data = $this->get_current_and_default_currency();

			$available_gateways = $this->filter_payment_methods_by_currency( $currencies_data['default_currency'], $available_gateways );

			return $available_gateways;
		}
		if ( is_null( $this->apply_currency ) ) {
			return;
		} else {

			$available_gateways = $this->filter_payment_methods_by_currency( $this->apply_currency, $available_gateways );

			return $available_gateways;
		}
	}

	public function filter_payment_methods_by_currency( $currency, $available_gateways ) {
		if ( array( 'all' ) === $currency['paymentMethods'] ) {
			return $available_gateways;
		}
		$allowed_payment_methods = $currency['paymentMethods'];
		$filtered                = array_filter(
			$available_gateways,
			function ( $key ) use ( $allowed_payment_methods ) {
				return in_array( $key, $allowed_payment_methods );
			},
			ARRAY_FILTER_USE_KEY
		);
		$available_gateways      = $filtered;
		return $available_gateways;
	}

	public function add_order_currency_meta( $order_id, $data ) {
		if ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) {
			return;
		}
		$order_data            = wc_get_order( $order_id );
		$order_total           = $order_data->get_total();
		$converted_order_total = $this->reverse_calculate_price_by_currency( $order_total );
		update_post_meta( $order_id, '_order_currency', $this->apply_currency['currency'] );
		update_post_meta( $order_id, 'yay_currency_checkout_original_total', $converted_order_total );
	}
	public function format_currency_position( $currency_position ) {
		$format = '%1$s%2$s';
		switch ( $currency_position ) {
			case 'left':
				$format = '%1$s%2$s';
				break;
			case 'right':
				$format = '%2$s%1$s';
				break;
			case 'left_space':
				$format = '%1$s&nbsp;%2$s';
				break;
			case 'right_space':
				$format = '%2$s&nbsp;%1$s';
				break;
		}
		return $format;
	}

	// To support YayExtra
	public function set_cookies() {
		$cookie_name  = 'yay_currency_widget';
		$cookie_value = $this->apply_currency['ID'];
		setcookie( $cookie_name, $cookie_value, time() + ( 86400 * 30 ), '/' );
		$_COOKIE[ $cookie_name ] = $cookie_value;
	}

	public function reverse_calculate_price_by_currency( $price ) {
		if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
			$currency_ID          = sanitize_key( $_COOKIE['yay_currency_widget'] );
			$this->apply_currency = $this->get_currency_by_ID( $currency_ID );
		}
		$currency_rate  = $this->apply_currency['rate'];
		$currency_fee   = 'percentage' === $this->apply_currency['fee']['type'] ? ( $price ) / ( $this->apply_currency['fee']['value'] / 100 ) : $this->apply_currency['fee']['value'];
		$reversed_price = ( ( $price - $currency_fee ) / $currency_rate );
		return $reversed_price;
	}

	public function format_price( $price ) {
		$currency_ID     = sanitize_key( $_COOKIE['yay_currency_widget'] );
		$formatted_price = wc_price( $price );
		if ( ! empty( $currency_ID ) ) {
			$this->apply_currency = $this->get_currency_by_ID( $currency_ID );
			$formatted_price      = wc_price(
				$price,
				$this->get_apply_currency_format_info()
			);
		}

		return $formatted_price;
	}

	public function calculate_price_by_currency_cookie( $price, $exclude = false ) {

		$currency_ID = sanitize_key( $_COOKIE['yay_currency_widget'] );

		$this->apply_currency = $this->get_currency_by_ID( $currency_ID );

			$price = $this->calculate_price_by_currency( $price, $exclude );

			return $price;
	}

	public function get_apply_currency_format_info() {
		$format                     = $this->format_currency_position( $this->apply_currency['currencyPosition'] );
		$apply_currency_format_info = array(
			'currency'           => $this->apply_currency['currency'],
			'decimal_separator'  => $this->apply_currency['decimalSeparator'],
			'thousand_separator' => $this->apply_currency['thousandSeparator'],
			'decimals'           => (int) $this->apply_currency['numberDecimal'],
			'price_format'       => $format,
		);
		return $apply_currency_format_info;
	}
}
