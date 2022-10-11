<?php

namespace Yay_Currency;

use Yay_Currency\WooCommerceCurrency;
use Yay_Currency\Settings;

defined( 'ABSPATH' ) || exit;

use WP_Widget;

class Widget extends WP_Widget {


	private static $instance = null;

	public $widget_ID;

	public $widget_name;

	public $widget_options = array();

	public $control_options = array();

	public $apply_currencies = array();

	public $all_currencies = array();

	public $selected_currency_ID = null;

	public $country_info;

	public $settings_data;

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
		$this->settings_data    = Settings::getInstance();
		$this->apply_currencies = $this->settings_data->yay_list_currencies;
		$this->all_currencies   = $this->settings_data->woo_list_currencies;

		if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
			$this->selected_currency_ID = sanitize_key( $_COOKIE['yay_currency_widget'] );
		}

		$this->widget_ID = 'yay_currency_widget';

		$this->widget_name = 'Currency Switcher - YayCurrency (Legacy)';

		$this->widget_options = array(
			'classname'                   => $this->widget_ID,
			'description'                 => $this->widget_name,
			'customize_selective_refresh' => true,
		);

		$this->control_options = array(
			'width'  => 300,
			'height' => 350,
		);
		parent::__construct( $this->widget_ID, $this->widget_name, $this->widget_options, $this->control_options );

		add_action( 'widgets_init', array( $this, 'widgetsInit' ) );
	}

	public function widgetsInit() {
		 register_widget( $this );
	}

	public function widget( $args, $instance ) {
		echo wp_kses_post( $args['before_widget'] );

		if ( isset( $_REQUEST['yay-currency-nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['yay-currency-nonce'] ), 'yay-currency-check-nonce' ) ) {

			if ( isset( $_POST['currency'] ) ) {
				$this->selected_currency_ID = sanitize_text_field( $_POST['currency'] );
			}
		}
		if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
			$this->selected_currency_ID = sanitize_key( $_COOKIE['yay_currency_widget'] );
		}
		$selected_currency_args         = array(
			'p'         => (int) $this->selected_currency_ID,
			'post_type' => 'yay-currency-manage',
		);
		$selected_currency_query_result = new \WP_Query( $selected_currency_args );
		$selected_currency_info         = $selected_currency_query_result->post;
		$yay_currency                   = WooCommerceCurrency::getInstance();

		$is_show_flag_in_widget            = get_option( 'yay_currency_show_flag_in_widget', 1 );
		$is_show_currency_name_in_widget   = get_option( 'yay_currency_show_currency_name_in_widget', 1 );
		$is_show_currency_symbol_in_widget = get_option( 'yay_currency_show_currency_symbol_in_widget', 1 );
		$is_show_currency_code_in_widget   = get_option( 'yay_currency_show_currency_code_in_widget', 1 );
		$widget_size                       = get_option( 'yay_currency_widget_size', 'small' );

		$no_currency_name_class                 = ! $is_show_currency_name_in_widget ? ' no-currency-name' : '';
		$only_currency_name_class               = $is_show_currency_name_in_widget && ! $is_show_flag_in_widget && ! $is_show_currency_symbol_in_widget && ! $is_show_currency_code_in_widget ? ' only-currency-name' : '';
		$only_currency_name_and_something_class = $is_show_currency_name_in_widget && 2 === Helper::count_display_elements_in_switcher( $is_show_flag_in_widget, $is_show_currency_name_in_widget, $is_show_currency_symbol_in_widget, $is_show_currency_code_in_widget ) ? ' only-currency-name-and-something' : '';

		$switcher_settings_info = array(
			'woo_currencies'                         => $this->all_currencies,
			'selected_currencies'                    => $this->apply_currencies,
			'selected_currency'                      => $selected_currency_info,
			'selected_currency_ID'                   => $this->selected_currency_ID,
			'is_show_flag'                           => $is_show_flag_in_widget,
			'is_show_currency_name'                  => $is_show_currency_name_in_widget,
			'is_show_currency_symbol'                => $is_show_currency_symbol_in_widget,
			'is_show_currency_code'                  => $is_show_currency_code_in_widget,
			'switcher_size'                          => $widget_size,
			'yay_currency'                           => $yay_currency,
			'no_currency_name_class'                 => $no_currency_name_class,
			'only_currency_name_class'               => $only_currency_name_class,
			'only_currency_name_and_something_class' => $only_currency_name_and_something_class,
		)
		?>
	<div class='yay-currency-widget-switcher'>
	  <h4><?php echo esc_html_e( 'Currency Switcher', 'yay-currency' ); ?></h4>
		<?php require YAY_CURRENCY_PLUGIN_DIR . 'includes/templates/switcherTemplate.php'; ?>
	</div>
		<?php
		echo wp_kses_post( $args['after_widget'] );
	}

	public function form( $instance ) {
		$is_show_flag_in_widget            = get_option( 'yay_currency_show_flag_in_widget', 1 );
		$is_show_currency_name_in_widget   = get_option( 'yay_currency_show_currency_name_in_widget', 1 );
		$is_show_currency_symbol_in_widget = get_option( 'yay_currency_show_currency_symbol_in_widget', 1 );
		$is_show_currency_code_in_widget   = get_option( 'yay_currency_show_currency_code_in_widget', 1 );

		$widget_size = get_option( 'yay_currency_widget_size', 'small' );
		wp_nonce_field( 'yay-currency-check-nonce', 'yay-currency-nonce' );
		// Widget admin form
		?>
	<div class="yay-currency-widget-custom-fields">
			<span class="yay-currency-widget-custom-fields__title">Switcher elements:</span>
			<div class="yay-currency-widget-custom-fields__field">
				<input class="yay-currency-widget-custom-fields__field--checkbox" type="checkbox" id="show-flag" name="show-flag" value="1" <?php echo $is_show_flag_in_widget ? 'checked' : null; ?> />
				<label for="show-flag">Show flag</label>
			</div>
			<div class="yay-currency-widget-custom-fields__field">
				<input class="yay-currency-widget-custom-fields__field--checkbox" type="checkbox" id="show-currency-name" name="show-currency-name" value="1" <?php echo $is_show_currency_name_in_widget ? 'checked' : null; ?> />
				<label for="show-currency-name">Show currency name</label>
			</div>
			<div class="yay-currency-widget-custom-fields__field">
				<input class="yay-currency-widget-custom-fields__field--checkbox" type="checkbox" id="show-currency-symbol" name="show-currency-symbol" value="1" <?php echo $is_show_currency_symbol_in_widget ? 'checked' : null; ?> />
				<label for="show-currency-symbol">Show currency symbol</label>
			</div>
			<div class="yay-currency-widget-custom-fields__field">
				<input class="yay-currency-widget-custom-fields__field--checkbox" type="checkbox" id="show-currency-code" name="show-currency-code" value="1" <?php echo $is_show_currency_code_in_widget ? 'checked' : null; ?> />
				<label for="show-currency-code">Show currency code</label>
			</div>
			<div class="yay-currency-widget-custom-fields">
				<span class="yay-currency-widget-custom-fields__title">Switcher size:</span>
				<div class="yay-currency-widget-custom-field__field-group">
					<div class="yay-currency-widget-custom-field__field">
						<input class="yay-currency-widget-custom-fields__field--radio" type="radio" id="widget-size-small" name="widget-size" value="small" <?php echo 'small' === $widget_size ? 'checked' : null; ?> />
						<label for="widget-size">Small</label>
					</div>
					<div class="yay-currency-widget-custom-field__field">
						<input class="yay-currency-widget-custom-fields__field--radio" type="radio" id="widget-size-medium" name="widget-size" value="medium" <?php echo 'medium' === $widget_size ? 'checked' : null; ?> />
						<label for="widget-size">Medium</label>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

		// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		if ( isset( $_REQUEST['yay-currency-nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['yay-currency-nonce'] ), 'yay-currency-check-nonce' ) ) {
			$is_show_flag_in_widget            = isset( $_POST['show-flag'] ) ? sanitize_text_field( $_POST['show-flag'] ) : 0;
			$is_show_currency_name_in_widget   = isset( $_POST['show-currency-name'] ) ? sanitize_text_field( $_POST['show-currency-name'] ) : 0;
			$is_show_currency_symbol_in_widget = isset( $_POST['show-currency-symbol'] ) ? sanitize_text_field( $_POST['show-currency-symbol'] ) : 0;
			$is_show_currency_code_in_widget   = isset( $_POST['show-currency-code'] ) ? sanitize_text_field( $_POST['show-currency-code'] ) : 0;
			$widget_size                       = isset( $_POST['widget-size'] ) ? sanitize_text_field( $_POST['widget-size'] ) : 'small';

			update_option( 'yay_currency_show_flag_in_widget', $is_show_flag_in_widget );
			update_option( 'yay_currency_show_currency_name_in_widget', $is_show_currency_name_in_widget );
			update_option( 'yay_currency_show_currency_symbol_in_widget', $is_show_currency_symbol_in_widget );
			update_option( 'yay_currency_show_currency_code_in_widget', $is_show_currency_code_in_widget );
			update_option( 'yay_currency_widget_size', $widget_size );
		}
		return $new_instance;
	}

}
