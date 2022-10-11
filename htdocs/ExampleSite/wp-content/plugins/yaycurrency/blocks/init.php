<?php

use Yay_Currency\WooCommerceCurrency;
use Yay_Currency\Settings;
use Yay_Currency\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * Passes translations to JavaScript.
 */
function yaycurrency_currency_switcher_register_block() {

	// automatically load dependencies and version
	$asset_file = include plugin_dir_path( __FILE__ ) . 'build/index.asset.php';

	wp_register_script(
		'yaycurrency-currency-switcher-block-editor-script',
		plugins_url( 'build/index.js', __FILE__ ),
		$asset_file['dependencies'],
		$asset_file['version'],
		true
	);

	wp_localize_script(
		'yaycurrency-currency-switcher-block-editor-script',
		'yayCurrencyGutenberg',
		array(
			'nonce'                => wp_create_nonce( 'yay-currency-gutenberg-nonce' ),
			'yayCurrencyPluginURL' => YAY_CURRENCY_PLUGIN_URL,
		)
	);

	wp_register_style(
		'yaycurrency-currency-switcher-block-editor-style',
		plugins_url( 'style.css', __FILE__ ),
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . 'style.css' )
	);

	register_block_type(
		'yay-currency/currency-switcher',
		array(
			'attributes'      => array(
				'currencyName'         => array(
					'type'    => 'string',
					'default' => 'United States dollar',
				),
				'currencySymbol'       => array(
					'type'    => 'string',
					'default' => '($)',
				),
				'hyphen'               => array(
					'type'    => 'string',
					'default' => ' - ',
				),
				'currencyCode'         => array(
					'type'    => 'string',
					'default' => 'USD',
				),
				'isShowFlag'           => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'isShowCurrencyName'   => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'isShowCurrencySymbol' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'isShowCurrencyCode'   => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'widgetSize'           => array(
					'type'    => 'string',
					'default' => 'small',
				),
			),
			'style'           => 'yaycurrency-currency-switcher-block-editor-style',
			'editor_script'   => 'yaycurrency-currency-switcher-block-editor-script',
			'render_callback' => 'yaycurrency_switcher_render_html',
		)
	);
}

function yaycurrency_switcher_render_html( $attributes ) {

	$yay_currency  = WooCommerceCurrency::getInstance();
	$settings_data = Settings::getInstance();

	$apply_currencies = $settings_data->yay_list_currencies;
	$all_currencies   = $settings_data->woo_list_currencies;

	$selected_currency_ID = null;

	if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
		$selected_currency_ID = sanitize_key( $_COOKIE['yay_currency_widget'] );
	}

	if ( isset( $_REQUEST['yay-currency-nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['yay-currency-nonce'] ), 'yay-currency-check-nonce' ) ) {

		if ( isset( $_POST['currency'] ) ) {
			$selected_currency_ID = sanitize_key( wp_unslash( $_POST['currency'] ) );
		}
	}
	if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
		$selected_currency_ID = sanitize_key( $_COOKIE['yay_currency_widget'] );
	}

	$selected_currency_args            = array(
		'p'         => (int) $selected_currency_ID,
		'post_type' => 'yay-currency-manage',
	);
	$selected_currency_query_result    = new WP_Query( $selected_currency_args );
	$selected_currency_info            = $selected_currency_query_result->post;
	$is_show_flag_in_widget            = $attributes['isShowFlag'];
	$is_show_currency_name_in_widget   = $attributes['isShowCurrencyName'];
	$is_show_currency_symbol_in_widget = $attributes['isShowCurrencySymbol'];
	$hyphen                            = $attributes['hyphen'];
	$is_show_currency_code_in_widget   = $attributes['isShowCurrencyCode'];
	$widget_size                       = $attributes['widgetSize'];

	$no_currency_name_class                 = ! $is_show_currency_name_in_widget ? ' no-currency-name' : '';
	$only_currency_name_class               = $is_show_currency_name_in_widget && ! $is_show_flag_in_widget && ! $is_show_currency_symbol_in_widget && ! $is_show_currency_code_in_widget ? ' only-currency-name' : '';
	$only_currency_name_and_something_class = $is_show_currency_name_in_widget && 2 === Helper::count_display_elements_in_switcher( $is_show_flag_in_widget, $is_show_currency_name_in_widget, $is_show_currency_symbol_in_widget, $is_show_currency_code_in_widget ) ? ' only-currency-name-and-something' : '';

	$yay_currency_nonce     = wp_nonce_field( 'yay-currency-check-nonce', 'yay-currency-nonce', true, false );
	$switcher_settings_info = array(
		'woo_currencies'                         => $all_currencies,
		'selected_currencies'                    => $apply_currencies,
		'selected_currency'                      => $selected_currency_info,
		'selected_currency_ID'                   => $selected_currency_ID,
		'is_show_flag'                           => $is_show_flag_in_widget,
		'is_show_currency_name'                  => $is_show_currency_name_in_widget,
		'is_show_currency_symbol'                => $is_show_currency_symbol_in_widget,
		'is_show_currency_code'                  => $is_show_currency_code_in_widget,
		'switcher_size'                          => $widget_size,
		'yay_currency'                           => $yay_currency,
		'no_currency_name_class'                 => $no_currency_name_class,
		'only_currency_name_class'               => $only_currency_name_class,
		'only_currency_name_and_something_class' => $only_currency_name_and_something_class,
	);
	require YAY_CURRENCY_PLUGIN_DIR . 'includes/templates/switcherTemplate.php';
}
add_action( 'init', 'yaycurrency_currency_switcher_register_block' );
