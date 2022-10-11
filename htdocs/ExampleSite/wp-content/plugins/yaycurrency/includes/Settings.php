<?php

namespace Yay_Currency;

defined( 'ABSPATH' ) || exit;
class Settings {


	protected static $instance = null;
	public $setting_hookfix    = null;
	public $countries;
	public $woo_list_currencies;
	public $currency_code_by_country_code;
	public $yay_list_currencies;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	private function doHooks() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		$this->woo_list_currencies           = get_woocommerce_currencies();
		$this->woo_list_currencies['USD']    = 'United States dollar'; // Remove (US) from default
		$this->yay_list_currencies           = $this->get_currencies_post_type();
		$this->currency_code_by_country_code = array(
			'AED' => 'ae',

			'AFN' => 'af',

			'ALL' => 'al',

			'AMD' => 'am',

			'ANG' => 'an',

			'AOA' => 'ao',

			'ARS' => 'ar',

			'AUD' => 'au',

			'AWG' => 'aw',

			'AZN' => 'az',

			'BAM' => 'ba',

			'BBD' => 'bb',

			'BDT' => 'bd',

			'BGN' => 'bg',

			'BHD' => 'bh',

			'BIF' => 'bi',

			'BMD' => 'bm',

			'BND' => 'bn',

			'BOB' => 'bo',

			'BRL' => 'br',

			'BSD' => 'bs',

			'BTN' => 'bt',

			'BTC' => 'btc',

			'BWP' => 'bw',

			'BYN' => 'by',

			'BYR' => 'byr',

			'BZD' => 'bz',

			'CAD' => 'ca',

			'CDF' => 'cd',

			'CHF' => 'ch',

			'CLP' => 'cl',

			'CNY' => 'cn',

			'COP' => 'co',

			'CRC' => 'cr',

			'CUP' => 'cu',

			'CUC' => 'cuc',

			'CVE' => 'cv',

			'CZK' => 'cz',

			'DJF' => 'dj',

			'DKK' => 'dk',

			'DOP' => 'do',

			'DZD' => 'dz',

			'EGP' => 'eg',

			'ERN' => 'er',

			'ETB' => 'et',

			'EUR' => 'eu',

			'FJD' => 'fj',

			'FKP' => 'fk',

			'GBP' => 'gb',

			'GEL' => 'ge',

			'GGP' => 'gg',

			'GHS' => 'gh',

			'GIP' => 'gi',

			'GMD' => 'gm',

			'GNF' => 'gn',

			'GTQ' => 'gt',

			'GYD' => 'gy',

			'HKD' => 'hk',

			'HNL' => 'hn',

			'HRK' => 'hr',

			'HTG' => 'ht',

			'HUF' => 'hu',

			'IDR' => 'id',

			'ILS' => 'il',

			'IMP' => 'im',

			'INR' => 'in',

			'IQD' => 'iq',

			'IRR' => 'ir',

			'IRT' => 'irt',

			'ISK' => 'is',

			'JEP' => 'je',

			'JMD' => 'jm',

			'JOD' => 'jo',

			'JPY' => 'jp',

			'KES' => 'ke',

			'KGS' => 'kg',

			'KHR' => 'kh',

			'KMF' => 'km',

			'KPW' => 'kp',

			'KRW' => 'kr',

			'KWD' => 'kw',

			'KYD' => 'ky',

			'KZT' => 'kz',

			'LAK' => 'la',

			'LBP' => 'lb',

			'LKR' => 'lk',

			'LRD' => 'lr',

			'LSL' => 'ls',

			'LYD' => 'ly',

			'MAD' => 'ma',

			'MDL' => 'md',

			'PRB' => 'mda',

			'MGA' => 'mg',

			'MKD' => 'mk',

			'MMK' => 'mm',

			'MNT' => 'mn',

			'MOP' => 'mo',

			'MRU' => 'mr',

			'MUR' => 'mu',

			'MVR' => 'mv',

			'MWK' => 'mw',

			'MXN' => 'mx',

			'MYR' => 'my',

			'MZN' => 'mz',

			'NAD' => 'na',

			'NGN' => 'ng',

			'NIO' => 'ni',

			'NOK' => 'no',

			'XOF' => 'none',

			'XPF' => 'none1',

			'XCD' => 'none2',

			'XAF' => 'none3',

			'NPR' => 'np',

			'NZD' => 'nz',

			'OMR' => 'om',

			'PAB' => 'pa',

			'PEN' => 'pe',

			'PGK' => 'pg',

			'PHP' => 'ph',

			'PKR' => 'pk',

			'PLN' => 'pl',

			'PYG' => 'py',

			'QAR' => 'qa',

			'RON' => 'ro',

			'RSD' => 'rs',

			'RUB' => 'ru',

			'RWF' => 'rw',

			'SAR' => 'sa',

			'SBD' => 'sb',

			'SCR' => 'sc',

			'SDG' => 'sd',

			'SEK' => 'se',

			'SGD' => 'sg',

			'SHP' => 'sh',

			'SLL' => 'sl',

			'SOS' => 'so',

			'SRD' => 'sr',

			'SSP' => 'ss',

			'STN' => 'st',

			'SYP' => 'sy',

			'SZL' => 'sz',

			'THB' => 'th',

			'TJS' => 'tj',

			'TMT' => 'tm',

			'TND' => 'tn',

			'TOP' => 'to',

			'TRY' => 'tr',

			'TTD' => 'tt',

			'TWD' => 'tw',

			'TZS' => 'tz',

			'UAH' => 'ua',

			'UGX' => 'ug',

			'USD' => 'us',

			'UYU' => 'uy',

			'UZS' => 'uz',

			'VES' => 've',

			'VEF' => 'vef',

			'VND' => 'vn',

			'VUV' => 'vu',

			'WST' => 'ws',

			'YER' => 'ye',

			'ZAR' => 'za',

			'ZMW' => 'zm',
		);

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_filter( 'plugin_action_links_' . YAY_CURRENCY_BASE_NAME, array( $this, 'addActionLinks' ) );
		add_filter( 'plugin_row_meta', array( $this, 'addDocumentLinks' ), 10, 2 );
		add_filter( 'woocommerce_general_settings', array( $this, 'add_multi_currencies_button' ), 10, 1 );
	}

	public function __construct() {     }

	public function add_multi_currencies_button( $sections ) {
		$update_sections = array();
		foreach ( $sections as $section ) {
			if ( array_key_exists( 'id', $section ) && 'pricing_options' === $section['id'] ) {
				$section['desc'] = '<a class="button" href="' . esc_url( admin_url( '/admin.php?page=yay_currency' ) ) . '">' . esc_html__( 'Configure multi-currency', 'yay-currency' ) . '</a><br>' . esc_html__( 'The following options affect how prices are displayed on the frontend', 'yay-currency' );
			}
			$update_sections[] = $section;
		}
		return $update_sections;
	}

	public function addDocumentLinks( $links, $file ) {
		if ( strpos( $file, YAY_CURRENCY_BASE_NAME ) !== false ) {
			$new_links = array(
				'doc'     => '<a href="https://yaycommerce.gitbook.io/yaycurrency/" target="_blank">' . __( 'Docs', 'yay-currency' ) . '</a>',
				'support' => '<a href="https://yaycommerce.com/support/" target="_blank" aria-label="' . esc_attr__( 'Visit community forums', 'yay-currency' ) . '">' . esc_html__( 'Support', 'yay-currency' ) . '</a>',
			);
			$links     = array_merge( $links, $new_links );
		}
		return $links;
	}

	public function addActionLinks( $links ) {
		$links[] = '<a target="_blank" href="https://yaycommerce.com/yaycurrency-woocommerce-multi-currency-switcher/" style="color: #43B854; font-weight: bold">' . __( 'Go Pro', 'yay-currency' ) . '</a>';
		$links   = array_merge(
			array(
				'<a href="' . esc_url( admin_url( '/admin.php?page=yay_currency' ) ) . '">' . __( 'Settings', 'yay-currency' ) . '</a>',
			),
			$links
		);
		return $links;
	}

	public function admin_menu() {
		$page_title            = __( 'YayCurrency', 'yay-currency' );
		$menu_title            = __( 'YayCurrency', 'yay-currency' );
		$this->setting_hookfix = add_submenu_page( 'woocommerce', $page_title, $menu_title, 'manage_woocommerce', 'yay_currency', array( $this, 'submenu_page_callback' ) );
	}

	public function admin_enqueue_scripts( $hook_suffix ) {
		$allow_hook_suffixes = array( 'woocommerce_page_yay_currency', 'nav-menus.php', 'widgets.php' );
		$is_set_fixed_price  = get_option( 'yay_currency_set_fixed_price', 0 );
		if ( $is_set_fixed_price ) {
			array_push( $allow_hook_suffixes, 'post.php' );
		}
		if ( ! in_array( $hook_suffix, $allow_hook_suffixes ) ) {
			return;
		}

		$converted_currencies_data = $this->convert_currencies_data();

		wp_register_script( 'yay-currency', YAY_CURRENCY_PLUGIN_URL . 'assets/dist/js/main.js', array(), '1.0', true );
		wp_localize_script(
			'yay-currency',
			'yayCurrency',
			array(
				'admin_url'                 => admin_url( 'admin.php?page=wc-settings' ),
				'plugin_url'                => YAY_CURRENCY_PLUGIN_URL,
				'ajaxurl'                   => admin_url( 'admin-ajax.php' ),
				'image_url'                 => YAY_CURRENCY_PLUGIN_URL . 'assets/images',
				'nonce'                     => wp_create_nonce( 'yay-currency-nonce' ),
				'currenciesData'            => $converted_currencies_data,
				'listCurrencies'            => $this->woo_list_currencies,
				'currencyCodeByCountryCode' => $this->currency_code_by_country_code,
			)
		);
		wp_enqueue_style(
			'yay-currency',
			YAY_CURRENCY_PLUGIN_URL . 'assets/dist/main.css',
			array(
				'woocommerce_admin_styles',
				'wp-components',
			),
			YAY_CURRENCY_VERSION
		);

		wp_enqueue_style(
			'yay-currency-admin-styles',
			YAY_CURRENCY_PLUGIN_URL . 'src/admin-styles.css',
			array(),
			YAY_CURRENCY_VERSION
		);

		wp_enqueue_script( 'yay-currency' );
	}

	public function get_currencies_post_type() {
		$post_type_args = array(
			'numberposts' => -1,
			'post_type'   => 'yay-currency-manage',
			'post_status' => 'publish',
			'order'       => 'ASC',
			'orderby'     => 'menu_order',
		);

		$currencies = get_posts( $post_type_args );

		return $currencies;
	}

	public function enqueue_scripts() {
		$converted_currency = array();
		if ( $this->yay_list_currencies ) {
			foreach ( $this->yay_list_currencies as $currency ) {
				$currency_meta = get_post_meta( $currency->ID, '', true );

				array_push(
					$converted_currency,
					array(
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
					)
				);
			}
		}
		wp_register_script( 'yay-currency', YAY_CURRENCY_PLUGIN_URL . 'src/script.js', array(), '1.0', true );

		wp_localize_script(
			'yay-currency',
			'yayCurrency',
			array(
				'admin_url'               => admin_url( 'admin.php?page=wc-settings' ),
				'ajaxurl'                 => admin_url( 'admin-ajax.php' ),
				'nonce'                   => wp_create_nonce( 'yay-currency-nonce' ),
				'isShowOnMenu'            => get_option( 'yay_currency_show_menu', 0 ),
				'shortCode'               => do_shortcode( '[yaycurrency-menu-item-switcher]' ),
				'isPolylangCompatible'    => get_option( 'yay_currency_polylang_compatible', 0 ),
				'isDisplayFlagInSwitcher' => get_option( 'yay_currency_show_flag_in_switcher', 1 ),
				'yayCurrencyPluginURL'    => YAY_CURRENCY_PLUGIN_URL,
				'converted_currency'      => $converted_currency,
			)
		);

		wp_enqueue_style(
			'yay-currency',
			YAY_CURRENCY_PLUGIN_URL . 'src/styles.css',
			array(),
			YAY_CURRENCY_VERSION
		);
		wp_enqueue_script( 'yay-currency', YAY_CURRENCY_PLUGIN_URL . 'src/script.js', array(), '1.0', true );
	}

	public function submenu_page_callback() {
		echo '<div id="yay-currency"></div>';
	}

	public function convert_currencies_data() {
		$most_traded_currencies_code           = array( 'USD', 'EUR', 'GBP', 'INR', 'AUD', 'CAD', 'SGD', 'CHF', 'MYR', 'JPY' );
		$most_traded_converted_currencies_data = array();
		$converted_currencies_data             = array();

		foreach ( $this->currency_code_by_country_code as $key => $value ) {
			$currency_data = array(
				'currency'      => html_entity_decode( $this->woo_list_currencies[ $key ] ),
				'currency_code' => $key,
				'country_code'  => $value,
			);
			if ( in_array( $key, $most_traded_currencies_code ) ) {
				array_push( $most_traded_converted_currencies_data, $currency_data );
			} else {
				array_push( $converted_currencies_data, $currency_data );
			}
		}
		usort(
			$most_traded_converted_currencies_data,
			function ( $a, $b ) use ( $most_traded_currencies_code ) {
				$pos_a = array_search( $a['currency_code'], $most_traded_currencies_code );
				$pos_b = array_search( $b['currency_code'], $most_traded_currencies_code );
				return $pos_a - $pos_b;
			}
		);
		$result = array_merge( $most_traded_converted_currencies_data, $converted_currencies_data );
		return $result;
	}

}
