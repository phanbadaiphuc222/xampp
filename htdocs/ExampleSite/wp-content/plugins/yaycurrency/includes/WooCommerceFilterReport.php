<?php

namespace Yay_Currency;

use Yay_Currency\WooCommerceCurrency;
use Yay_Currency\Settings;

defined( 'ABSPATH' ) || exit;

class WooCommerceFilterReport {

	private static $instance = null;
	public $default_currency;
	public $all_currencies;
	public $apply_currencies;
	public $converted_currencies;
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

		$settings_data          = Settings::getInstance();
		$this->apply_currencies = $settings_data->yay_list_currencies;
		$this->all_currencies   = $settings_data->woo_list_currencies;

		$this->default_currency = get_option( 'woocommerce_currency' );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_report_script' ) );
		add_action( 'wc_reports_tabs', array( $this, 'wc_reports_currencies_dropdown' ) );
		add_filter( 'woocommerce_reports_get_order_report_query', array( $this, 'custom_admin_report_query' ) );
	}

	public function enqueue_report_script() {
		$current_screen = get_current_screen();
		if ( 'woocommerce_page_wc-reports' === $current_screen->id ) {
			wp_enqueue_script( 'yay-currency-report', YAY_CURRENCY_PLUGIN_URL . 'src/report.js', array(), '1.0', true );
		}
	}

	public function wc_reports_currencies_dropdown() {
		$current_url  = wc_get_current_admin_url();
		$woo_currency = WooCommerceCurrency::getInstance();

		remove_filter( 'woocommerce_currency_symbol', array( $this, 'custom_admin_report_currency_symbol' ), 10, 2 );
		?>
	<div id="yay-currency-dropdown-reports">
		<span><?php echo esc_html_e( 'Sales by currency:', 'yay-currency' ); ?></span>
		<select class="widget-currencies-dropdown" name='currency'>
		  <?php foreach ( $this->apply_currencies as $currency ) { ?>
			<option data-url="<?php echo esc_url( add_query_arg( array( 'currency' => $currency->post_title ), $current_url ) ); ?>" value='<?php echo esc_attr( $currency->post_title ); ?>'>
				<?php echo wp_kses_post( html_entity_decode( esc_html__( $this->all_currencies[ $currency->post_title ], 'yay-currency' ) . ' (' . esc_html__( $woo_currency->get_symbol_by_currency( esc_html__( $currency->post_title, 'yay-currency' ) ), 'yay-currency' ) . ') - ' . esc_html__( $currency->post_title, 'yay-currency' ) ) ); ?>
			</option>
		  <?php } ?>
		</select>
	</div>
		<?php
		add_filter( 'woocommerce_currency_symbol', array( $this, 'custom_admin_report_currency_symbol' ), 10, 2 );
	}

	public function custom_admin_report_query( $query ) {
		global $wpdb;
		$currency = isset( $_GET['currency'] ) ? sanitize_text_field( $_GET['currency'] ) : $this->default_currency;
		$pattern  = '/^[a-zA-Z]{3}+$/';
		if ( preg_match( $pattern, $currency ) ) {

			$query['join']  .= " LEFT JOIN {$wpdb->postmeta} AS meta_checkout_currency ON meta_checkout_currency.post_id = posts.ID";
			$query['where'] .= $wpdb->prepare( " AND meta_checkout_currency.meta_key='_order_currency' AND meta_checkout_currency.meta_value = %s", $currency );

		}

		return $query;
	}

	public function custom_admin_report_currency_symbol( $currency_symbol, $currency ) {
		$selected_currency = isset( $_GET['currency'] ) ? sanitize_text_field( $_GET['currency'] ) : $this->default_currency;
		$woo_currency      = WooCommerceCurrency::getInstance();
		$currency_symbol   = $woo_currency->get_symbol_by_currency( $selected_currency );
		return $currency_symbol;
	}
}
