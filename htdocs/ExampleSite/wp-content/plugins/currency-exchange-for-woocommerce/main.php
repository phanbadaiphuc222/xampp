<?php
define( "BeRocket_CE_domain", 'currency-exchange-for-woocommerce'); 
define( "CE_TEMPLATE_PATH", plugin_dir_path( __FILE__ ) . "templates/" );
load_plugin_textdomain('currency-exchange-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
require_once(plugin_dir_path( __FILE__ ).'berocket/framework.php');
foreach (glob(__DIR__ . "/includes/*.php") as $filename)
{
    include_once($filename);
}
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

class BeRocket_CE extends BeRocket_Framework {
    public static $currency;
    public static $currency_symbol;
    public static $currency_modifier;

    protected $plugin_version_capability = 15;
    public static $settings_name = 'br_ce_options';
    protected static $instance;
    protected $disable_settings_for_admin = array(
        array('script', 'js_page_load'),
        array('script', 'js_before_set'),
        array('script', 'js_after_set'),
    );
    protected $check_init_array = array(
        array(
            'check' => 'woocommerce_version',
            'data' => array(
                'version' => '3.0',
                'operator' => '>=',
                'notice'   => 'Plugin Currency Exchange for WooCommerce required WooCommerce version 3.0 or higher'
            )
        ),
        array(
            'check' => 'framework_version',
            'data' => array(
                'version' => '2.1',
                'operator' => '>=',
                'notice'   => 'Please update all BeRocket plugins to the most recent version. Currency Exchange for WooCommerce is not working correctly with older versions.'
            )
        ),
    );
    function __construct () {
        $this->info = array(
            'id'          => 8,
            'lic_id'      => 62,
            'version'     => BeRocket_Curency_Exchange_version,
            'plugin'      => '',
            'slug'        => '',
            'key'         => '',
            'name'        => '',
            'plugin_name' => 'currency-exchange',
            'full_name'   => __('Currency Exchange for WooCommerce', 'currency-exchange-for-woocommerce'),
            'norm_name'   => __('Currency Exchange', 'currency-exchange-for-woocommerce'),
            'price'       => '',
            'domain'      => 'currency-exchange-for-woocommerce',
            'templates'   => CE_TEMPLATE_PATH,
            'plugin_file' => BeRocket_CE_file,
            'plugin_dir'  => __DIR__,
        );
        $this->defaults = array(
            'visual_only'       => '1',
            'currency_via_ip'   => '1',
            'user_profile'      => '',
            'use_currency'      => array(),
            'currency'          => array(),
            'currency_image'    => array(),
            'currency_custom'   => array(),
            'custom_css'        => '',
            'multiplier'        => '1',
            'use_open_exchange' => '0',
            'currency_site'     => 'oer',
            'open_exchange_api' => '',
            'currencylayer_api' => '',
            'fixerio_api'       => '',
            'last_oer_data'     => array('update' => 0, 'base' => 'USD'),
            'script'            => array(
                'js_page_load'      => '',
                'js_before_set'     => '',
                'js_after_set'      => 'location.reload();',
            ),
            'fontawesome_frontend_disable'    => '',
            'fontawesome_frontend_version'    => '',
        );
        $this->values = array(
            'settings_name' => 'br_ce_options',
            'option_page'   => 'br-currency-exchange',
            'premium_slug'  => 'woocommerce-currency-exchange',
            'free_slug'     => 'currency-exchange-for-woocommerce',
        );
        $this->feature_list = array();
        $this->framework_data['fontawesome_frontend'] = true;
        parent::__construct( $this );
        if( $this->check_framework_version() ) {
            if ( $this->init_validation() ) {
                $options = $this->get_option();
                add_action ( "widgets_init", array ( $this, 'widgets_init' ) );
                add_action( "wp_ajax_br_ce_settings_save", array ( $this, 'save_settings' ) );
                add_action( "wp_ajax_open_exchange_load", array ( $this, 'open_exchange_load' ) );
                add_shortcode( 'br_currency_exchange', array( $this, 'shortcode' ) );
                if($options['user_profile']) {
                    add_action( 'show_user_profile', array( $this, 'user_fields' ) );
                    add_action( 'edit_user_profile', array( $this, 'user_fields' ) );
                    add_action( 'personal_options_update', array( $this, 'save_user_fields' ) );
                    add_action( 'edit_user_profile_update', array( $this, 'save_user_fields' ) );
                    add_action('wp_login', array( $this, 'login_set' ), 10, 2);
                }
                add_filter('bapf_wc_price_args', array(__CLASS__, 'bapf_wc_price_args'), 1);
            
                if ( empty($options['visual_only']) && $this->request_apply_exchange() ) {
                    //add_action( 'raw_woocommerce_price', array( __CLASS__, 'return_custom_price_one' ) );
                    if ( br_woocommerce_version_check() ) {
                        add_filter('woocommerce_product_get_price', array( $this, 'return_custom_price' ), 10, 2);
                        add_filter('woocommerce_product_get_regular_price', array( $this, 'return_custom_price' ), 10, 2); 
                        add_filter('woocommerce_product_get_sale_price', array( $this, 'return_custom_price' ), 10, 2); 
                        add_filter('woocommerce_variation_prices', array( $this, 'return_custom_price_variable_array' ), 10);
                        add_filter('woocommerce_product_variation_get_price', array( $this, 'return_custom_price_variable' ), 10, 2); 
                        add_filter('woocommerce_product_variation_get_regular_price', array( $this, 'return_custom_price_variable' ), 10, 2); 
                        add_filter('woocommerce_product_variation_get_sale_price', array( $this, 'return_custom_price_variable' ), 10, 2);
                        add_filter('woocommerce_shipping_method_add_rate_args', array($this, 'return_add_rate_args'));
                    } else {
                        add_filter('woocommerce_get_price', array( $this, 'return_custom_price' ), 10, 2);
                        add_filter('woocommerce_get_regular_price', array( $this, 'return_custom_price' ), 10, 2); 
                        add_filter('woocommerce_get_sale_price', array( $this, 'return_custom_price' ), 10, 2); 
                        add_filter('woocommerce_get_variation_price', array( $this, 'return_custom_price_variable' ), 10, 2); 
                        add_filter('woocommerce_get_variation_regular_price', array( $this, 'return_custom_price_variable' ), 10, 2); 
                        add_filter('woocommerce_get_variation_sale_price', array( $this, 'return_custom_price_variable' ), 10, 2); 
                    }

                    add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'update_meta_data_with_new_currency' ) );
                    add_action( 'woocommerce_currency', array( $this, 'woocommerce_currency' ) );

                    add_filter('berocket_check_product_error_min_price', array( $this, 'return_custom_price_one' ));
                    add_filter('berocket_check_product_error_max_price', array( $this, 'return_custom_price_one' ));
                    add_filter('berocket_check_cart_notice_min_price', array( $this, 'return_custom_price_one' ));
                    add_filter('berocket_check_cart_notice_max_price', array( $this, 'return_custom_price_one' ));
                }
                add_action( 'woocommerce_price_filter_widget_min_amount', array( $this, 'return_custom_price_one' ) );
                add_action( 'woocommerce_price_filter_widget_max_amount', array( $this, 'return_custom_price_one' ) );
                add_filter('berocket_min_max_filter', array( $this, 'invert_custom_price_one' ) );
                add_filter( 'woocommerce_reports_get_order_report_query', array( $this, 'wc_report_query' ) );
            }
        } else {
            add_filter( 'berocket_display_additional_notices', array(
                $this,
                'old_framework_notice'
            ) );
        }
    }
    public function request_apply_exchange() {
        $post_action = ( empty($_POST['action']) ? ( empty($_GET['action']) ? '' : $_GET['action'] ) : $_POST['action'] );
        return apply_filters('brce_request_apply_exchange', (! is_admin() 
                || (
                    defined( 'DOING_AJAX' ) && DOING_AJAX 
                    && in_array($post_action, array('variation_label')) 
                ) 
            ));
    }
    public function widgets_init() {
        register_widget("berocket_ce_widget");
    }
    function init_validation() {
        return parent::init_validation() && $this->check_framework_version();
    }
    function check_framework_version() {
        return ( ! empty(BeRocket_Framework::$framework_version) && version_compare(BeRocket_Framework::$framework_version, 2.1, '>=') );
    }
    function old_framework_notice($notices) {
        $notices[] = array(
            'start'         => 0,
            'end'           => 0,
            'name'          => $this->info[ 'plugin_name' ].'_old_framework',
            'html'          => __('<strong>Please update all BeRocket plugins to the most recent version. Currency Exchange for WooCommerce is not working correctly with older versions.</strong>', 'currency-exchange-for-woocommerce'),
            'righthtml'     => '',
            'rightwidth'    => 0,
            'nothankswidth' => 0,
            'contentwidth'  => 1600,
            'subscribe'     => false,
            'priority'      => 10,
            'height'        => 50,
            'repeat'        => false,
            'repeatcount'   => 1,
            'image'         => array(
                'local'  => '',
                'width'  => 0,
                'height' => 0,
                'scale'  => 1,
            )
        );
        return $notices;
    }
    public function init () {
        parent::init();
        //$current_currency = BeRocket_CE::get_woocommerce_currency();
        $options = $this->get_option();
        global $pagenow;
        if( $this->request_apply_exchange() && ! in_array($pagenow, array('post.php', 'post-new.php')) ) {
            $currency_cur = false;
            if( isset($_COOKIE['br_ce_language']) ) {
                $currency_cur = $_COOKIE['br_ce_language'];
            } elseif($options['currency_via_ip']) {
                $currency_cur = br_ce_get_currency();
                if( !empty($currency_cur) && in_array($currency_cur, $options['use_currency']) ) {
                    setcookie('br_ce_language', $currency_cur, 0, '/', $_SERVER['HTTP_HOST']|$_SERVER['SERVER_NAME']);
                }
            }

            if( $currency_cur && in_array($currency_cur, $options['use_currency']) ) {
                self::$currency = $currency_cur;
                if( isset($options['currency'][$currency_cur]) ) {
                    self::$currency_modifier = floatval($options['currency'][$currency_cur]) * floatval($options['multiplier']);
                }
            }
        }
        $current_currency = $this->get_woocommerce_currency();
        if( isset(self::$currency) ) {
            self::$currency_symbol = get_woocommerce_currency_symbol(self::$currency);
        }

        wp_enqueue_script( 'berocket_jquery_cookie', plugins_url( 'js/jquery.cookie.js', __FILE__ ), array( 'jquery' ), BeRocket_Curency_Exchange_version );
        wp_enqueue_script( 'berocket_ce_currency_exchange', plugins_url( 'js/currency_exchange.js', __FILE__ ), array( 'jquery' ), BeRocket_Curency_Exchange_version );
        wp_register_style( 'berocket_ce_style', plugins_url( 'css/shop_ce.css', __FILE__ ), "", BeRocket_Curency_Exchange_version );
        wp_enqueue_style( 'berocket_ce_style' );
        $currency_rates = $options['currency'];
        if ( $options['visual_only'] ) {
            wp_enqueue_script( 'open_money', plugins_url( 'js/money.min.js', __FILE__ ), array( 'jquery' ) );
            wp_enqueue_script( 'open_accounting', plugins_url( 'js/accounting.min.js', __FILE__ ), array( 'jquery' ) );
            if( $options['multiplier'] != 1 ) {
                foreach($currency_rates as &$currency_rate) {
                    $currency_rate = $currency_rate * $options['multiplier'];
                }
            }
        }
        
        $currency_pos         = get_option( 'woocommerce_currency_pos' );
        $currency_thousand    = wc_get_price_thousand_separator();
        $currency_decimal     = wc_get_price_decimal_separator();
        $currency_decimal_num = wc_get_price_decimals();
        switch ( $currency_pos ) {
            case 'left' :
                $currency_pos = '%s%v';
                break;
            case 'right' :
                $currency_pos = '%v%s';
                break;
            case 'left_space' :
                $currency_pos = '%s %v';
                break;
            case 'right_space' :
                $currency_pos = '%v %s';
                break;
        }
        
        wp_localize_script(
            'berocket_ce_currency_exchange',
            'the_ce_js_data',
            array(
                'script'      => apply_filters( 'berocket_ce_user_func', $options['script'] ),
                'rates'       => $currency_rates,
                'base'        => $current_currency,
                'visual_only' => $options['visual_only'],
                'current'     => (isset(self::$currency) ? self::$currency : 'none'),
                'symbol'      => (isset(self::$currency_symbol) ? self::$currency_symbol : 'none'),
                'accounting'  => array(
                    'symbol'      => (isset(self::$currency_symbol) ? self::$currency_symbol : 'none'),
                    'decimal'     => $currency_decimal,
                    'thousand'    => $currency_thousand,
                    'precision'   => $currency_decimal_num,
                    'format'      => $currency_pos
                ),
            )
        );
    }


    public function get_open_exchange ($force = false) {
        $options = $this->get_option();
        $current_currency = $this->get_woocommerce_currency();
        if ( ( $options['use_open_exchange'] 
                && ( ( time() - strtotime('+24 hour', (int)$options['last_oer_data']['update']) ) > 0 
                || $current_currency != $options['last_oer_data']['base'] ) )
            || $force ) {
            $rates = apply_filters('berocket_currency_exchange_api_get', array(), $options);
            if( ! empty($rates) && is_array($rates) && count($rates) ) {
                if( $current_currency != 'USD' ) {
                    $current_rate = $rates[$current_currency];
                    
                    foreach( $rates as $rate_name => $rate ) {
                        $rates[$rate_name] = $rate / $current_rate;
                    }
                }
                $options['currency'] = array_merge($options['currency'], $rates);
                $options['last_oer_data']['update'] = time();
                $options['last_oer_data']['base'] = $current_currency;
                update_option( 'br_ce_options', $options );
            }
        }
    }

    public function open_exchange_load () {
        $this->get_open_exchange(true);
        wp_die();
    }

    public function shortcode( $atts = array() ) {
        if( ! is_array($atts) ) {
            $atts = array();
        }
        if( empty($atts['currency_text']) ) {
            $atts['currency_text'] = 'text';
        }
        $atts['currency_text'] = explode(',', $atts['currency_text']);
        $atts = apply_filters( 'berocket_ce_shortcode_options', $atts );
        ob_start();
        the_widget( 'berocket_ce_widget', $atts);
        return ob_get_clean();
    }

    public static function woocommerce_currency($currency) {
        if( isset(self::$currency) ) {
            $currency = self::$currency;
        }
        return $currency;
    }

    public static function bapf_wc_price_args($args) {
        $args['currency'] = self::$currency;
        return $args;
    }

    public static function return_custom_price($price, $product) {
        if( isset( self::$currency_modifier ) && self::$currency_modifier > 0 && $price ) {
            $price = $price * self::$currency_modifier;
        }
        return $price;
    }

    public static function return_custom_price_variable($price, $product) {
        if( isset( self::$currency_modifier ) && self::$currency_modifier > 0 && $price ) {
            $price = $price * self::$currency_modifier;
        }
        return $price;
    }

    public static function return_custom_price_one($price) {
        if( isset( self::$currency_modifier ) && self::$currency_modifier > 0 ) {
            $price = $price * self::$currency_modifier;
        }
        return $price;
    }

    public static function return_custom_price_variable_array($prices) {
        if( is_array($prices) ) {
            foreach($prices as &$price_type) {
                if( is_array($prices) ) {
                    foreach($price_type as &$price) {
                        if( isset( self::$currency_modifier ) && self::$currency_modifier > 0 && $price ) {
                            $price = $price * self::$currency_modifier;
                        }
                    }
                }
            }
        }
        return $prices;
    }

    public static function invert_custom_price_one($price) {
        if( isset( self::$currency_modifier ) && self::$currency_modifier > 0 ) {
            if( is_array($price) ) {
                foreach($price as &$prices) {
                    $prices = $prices / self::$currency_modifier;
                }
            } else {
                $price = $price / self::$currency_modifier;
            }
        }
        return $price;
    }
    public static function return_add_rate_args($args) {
        $args['cost'] = self::return_custom_price_one($args['cost']);
        return $args;
    }

    public function update_meta_data_with_new_currency( $order_id ) {
        if( isset(self::$currency) ) {
            update_post_meta( $order_id, 'currency_used', self::$currency );
            update_post_meta( $order_id, 'currency_default', $this->get_woocommerce_currency() );
            update_post_meta( $order_id, 'currency_modifier', self::$currency_modifier );
        }
    }

    public function get_woocommerce_currency() {
        $options = $this->get_option();
        if( !$options['visual_only'] ) {
            remove_action( 'woocommerce_currency', array( $this, 'woocommerce_currency' ) );
            $currency = get_woocommerce_currency();
            add_action( 'woocommerce_currency', array( $this, 'woocommerce_currency' ) );
        } else {
            $currency = get_woocommerce_currency();
        }
        return $currency;
    }

    public function user_fields( $user ) {
        $options = $this->get_option();
        $current_currency = $this->get_woocommerce_currency();
        $currency_text = array('symbol', 'space', 'text', 'flag');
        $currencies_text = br_get_currency_text_for_element($currency_text);
        $ce_user_currency = get_the_author_meta('ce_user_currency', $user->ID);
        ?>
        <table class="form-table"><tbody>
            <tr>
                <th><label><?php _e('Shop currency', 'currency-exchange-for-woocommerce'); ?></label></th>
                <td>
                    <div style="max-width: 400px;">
                    <select class="brjsf_ce" name="ce_user_currency">
                        <?php
                        echo '<option data-text="'.br_get_currency_text_for_element($currency_text, $current_currency).'" value="">'.br_get_currency_text_for_element($currency_text, $current_currency).'</option>';
                        foreach($currencies_text as $currency_slug => $currency)
                        {
                            if( $current_currency != $currency_slug ) {
                                echo '<option data-text="'.$currency.'" value="'.$currency_slug.'"'.( ( isset($ce_user_currency) && $ce_user_currency == $currency_slug ) ? ' selected' : '').'>'.$currency.'</option>';
                            }
                        }
                        ?>
                    </select>
                    </div>
                </td>
            </tr>
        </tbody></table>
        <?php
    }

    public function save_user_fields( $user_id ) {
        if ( current_user_can( 'edit_user', $user_id ) ) {
            if(isset($_POST['ce_user_currency'])){
                update_user_meta( $user_id, 'ce_user_currency', $_POST['ce_user_currency'] );
                setcookie('br_ce_language', $_POST['ce_user_currency'], 0, '/', $_SERVER['HTTP_HOST']|$_SERVER['SERVER_NAME']);
            }
        }
    }

    public function login_set( $user_name, $user ) {
        $options = $this->get_option();
        $ce_user_currency = get_the_author_meta('ce_user_currency', $user->ID);
        if( $ce_user_currency && in_array($ce_user_currency, $options['use_currency']) ) {
            setcookie('br_ce_language', $ce_user_currency, 0, '/', $_SERVER['HTTP_HOST']|$_SERVER['SERVER_NAME']);
        }
    }

    public function wc_report_query($query) {
/*
SUM( meta__order_total.meta_value / COALESCE(meta_currency_modifier.meta_value, 1)) as total_sales,
SUM( meta__order_shipping.meta_value / COALESCE(meta_currency_modifier.meta_value, 1)) as total_shipping,
SUM( meta__order_tax.meta_value / COALESCE(meta_currency_modifier.meta_value, 1)) as total_tax,
SUM( meta__order_shipping_tax.meta_value / COALESCE(meta_currency_modifier.meta_value, 1)) as total_shipping_tax, 

LEFT JOIN wp_postmeta AS meta_currency_modifier
ON ( posts.ID = meta_currency_modifier.post_id AND meta_currency_modifier.meta_key = 'currency_modifier' ) 
*/
        global $wpdb;
        $values_to_find_replace = array(
            'meta__order_total.meta_value',
            'meta__order_shipping.meta_value',
            'meta__order_tax.meta_value',
            'meta__order_shipping_tax.meta_value'
        );
        $use_replace = false;
        foreach($values_to_find_replace as $value_to_find_replace) {
            if( strpos($query['select'], $value_to_find_replace) !== FALSE ) {
                $use_replace = true;
                break;
            }
        }
        if( $use_replace ) {
            foreach($values_to_find_replace as $value_to_find_replace) {
                $query['select'] = str_replace( $value_to_find_replace, $value_to_find_replace . ' / COALESCE(meta_currency_modifier.meta_value, 1)', $query['select']);
            }
            $query['join'] .= " LEFT JOIN {$wpdb->postmeta} AS meta_currency_modifier ON ( posts.ID = meta_currency_modifier.post_id AND meta_currency_modifier.meta_key = 'currency_modifier' )";
        }
        return $query;
    }

    public function admin_init () {
        parent::admin_init();
        wp_register_style( 'berocket_ce_admin_style', plugins_url( 'css/admin_ce.css', __FILE__ ), "", BeRocket_Curency_Exchange_version );
        wp_enqueue_style( 'berocket_ce_admin_style' );
        wp_enqueue_script( 'berocket_ce_admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), BeRocket_Curency_Exchange_version );
    }
    public function set_styles () {
        $this->get_open_exchange();
        parent::set_styles();
    }
    public function admin_settings( $tabs_info = array(), $data = array() ) {
        parent::admin_settings(
            array(
                'General' => array(
                    'icon' => 'cog',
                    'name' => __('General', 'currency-exchange-for-woocommerce'),
                ),
                'Currencies' => array(
                    'icon' => 'money',
                    'name' => __('Currencies', 'currency-exchange-for-woocommerce'),
                ),
                'Custom CSS/JavaScript' => array(
                    'icon' => 'css3',
                    'name' => __('Custom CSS/JavaScript', 'currency-exchange-for-woocommerce'),
                ),
                'License' => array(
                    'icon' => 'unlock-alt',
                    'link' => admin_url( 'admin.php?page=berocket_account' ),
                    'name' => __('License', 'currency-exchange-for-woocommerce'),
                ),
            ),
            array(
            'General' => array(
                'visual_only' => array(
                    "label"     => __('Visual only', 'currency-exchange-for-woocommerce'),
                    "type"      => "checkbox",
                    "name"      => "visual_only",
                    "value"     => '1',
                ),
                'currency_via_ip' => array(
                    "label"     => __('Use IP to detect currency', 'currency-exchange-for-woocommerce'),
                    "type"      => "checkbox",
                    "name"      => "currency_via_ip",
                    "value"     => '1',
                ),
                'user_profile' => array(
                    "label"     => __('Currency in profile', 'currency-exchange-for-woocommerce'),
                    "type"      => "checkbox",
                    "name"      => "user_profile",
                    "value"     => '1',
                ),
                'use_open_exchange' => array(
                    "label"     => __('Exchange Rates', 'currency-exchange-for-woocommerce'),
                    "label_for" => __('Auto update each 24 hours and after WooCommerce currency changes', 'currency-exchange-for-woocommerce'),
                    "type"      => "checkbox",
                    "name"      => "use_open_exchange",
                    "value"     => '1',
                ),
                'exchange_apis' => array(
                    "label"     => "",
                    "section"   => 'exchange_apis'
                ),
                'multiplier' => array(
                    "label"     => __('Multiplier', 'currency-exchange-for-woocommerce'),
                    "label_be_for"=> __('Multiply all exchange rates by this value: ', 'currency-exchange-for-woocommerce'),
                    "type"      => "number",
                    "name"      => "multiplier",
                    "extra"     => "step='0.0001' min='0.0001'",
                    "value"     => '1',
                ),
                'shortcode' => array(
                    "label"     => "",
                    "section"   => 'shortcode'
                ),
            ),
            'Currencies' => array(
                'currency_list' => array(
                    "label"     => "",
                    "section"   => 'currency_list'
                ),
            ),
            'Custom CSS/JavaScript' => array(
                'global_font_awesome_disable' => array(
                    "label"     => __( 'Disable Font Awesome', "currency-exchange-for-woocommerce" ),
                    "type"      => "checkbox",
                    "name"      => "fontawesome_frontend_disable",
                    "value"     => '1',
                    'label_for' => __('Don\'t load Font Awesome css files on site front end. Use it only if you don\'t use Font Awesome icons in widgets or your theme has Font Awesome.', 'currency-exchange-for-woocommerce'),
                ),
                'global_fontawesome_version' => array(
                    "label"    => __( 'Font Awesome Version', "currency-exchange-for-woocommerce" ),
                    "name"     => "fontawesome_frontend_version",
                    "type"     => "selectbox",
                    "options"  => array(
                        array('value' => '', 'text' => __('Font Awesome 4', 'currency-exchange-for-woocommerce')),
                        array('value' => 'fontawesome5', 'text' => __('Font Awesome 5', 'currency-exchange-for-woocommerce')),
                    ),
                    "value"    => '',
                    "label_for" => __('Version of Font Awesome that will be used on front end. Please select version that you have in your theme', 'currency-exchange-for-woocommerce'),
                ),
                array(
                    "label"   => "Custom CSS",
                    "name"    => "custom_css",
                    "type"    => "textarea",
                    "value"   => "",
                ),
                array(
                    "label"   => __('JavaScript On Page Load', 'currency-exchange-for-woocommerce'),
                    "name"    => array("script", "js_page_load"),
                    "type"    => "textarea",
                    "value"   => "",
                ),
                array(
                    "label"   => __('JavaScript Before Language Set', 'currency-exchange-for-woocommerce'),
                    "name"    => array("script", "js_before_set"),
                    "type"    => "textarea",
                    "value"   => "",
                ),
                array(
                    "label"   => __('JavaScript After Language Set', 'currency-exchange-for-woocommerce'),
                    "name"    => array("script", "js_after_set"),
                    "type"    => "textarea",
                    "value"   => "",
                ),
            )
        ) );
    }
    public function section_currency_list($data, $options) {
        ob_start();
        include('templates/currency_list.php');
        return ob_get_clean();
    }
    public function section_exchange_apis($data, $options) {
        $apis_list = apply_filters('berocket_currency_exchange_api_list', array());
        $html = '<th>'.__('Site for currency load', 'currency-exchange-for-woocommerce').'</th><td><div>
            <input type="hidden" name="br_ce_options[last_oer_data][update]" value="' . $options['last_oer_data']['update'] . '">
            <input type="hidden" name="br_ce_options[last_oer_data][base]" value="' . $options['last_oer_data']['base'] . '">
            <select name="br_ce_options[currency_site]" class="br_ce_currency_site">';
        $set_initial = false;
        if( empty($options['currency_site']) || ! array_key_exists($options['currency_site'], $apis_list) ) {
            $set_initial = true;
        }
        foreach($apis_list as $api_slug => $api_option) {
            if( $set_initial ) {
                $options['currency_site'] = $api_slug;
                $set_initial = false;
            }
            $html .= '<option value="' . $api_slug . '"' . ($options['currency_site'] == $api_slug ? ' selected' : '') . '>' . $api_option['name'] . '</option>';
        }
        $html .= '</select></div>';
        foreach($apis_list as $api_slug => $api_option) {
            $html .= '<div class="' . $api_slug . '_data_block site_data_block"' . ($options['currency_site'] != $api_slug ? ' style="display:none;"' : '') . '>';
            if( ! empty($api_option['app_id_option']) ) {
                $html .= '<div>
                    <label>' . __('App ID: ', 'currency-exchange-for-woocommerce') . '</label><input class="app_id_option" size="50" name="br_ce_options[' . $api_option['app_id_option'] . ']" type="text" value="' . br_get_value_from_array($options, $api_option['app_id_option']) . '">
                </div>';
            }
            if( ! empty($api_option['app_id_required']) ) {
                $html .= '<div class="app_id_required">
                    <p class="notice notice-error">' . __('App ID is required for this currency rate site', 'currency-exchange-for-woocommerce') . '</p>
                </div>';
            }
            $html .='<div>
                    <button class="button update_open_exchange" type="button">' . __('Update Rates', 'currency-exchange-for-woocommerce') . '</button>
                    <a class="update_open_exchange_link" href="' . $api_option['link'] . '" target="_blank">' . $api_option['name'] . '</a>
                </div>
            </div>';
        }
        $html .= '</td>';
        return $html;
    }

    public function section_shortcode() {
        $html = '<th scope="row">' . __('Shortcode', 'currency-exchange-for-woocommerce') . '</th>
        <td>
            <ul>
            <li><strong>[br_currency_exchange]</strong></li>
            <li>
                <ul style="margin-left:2em;">
                    <li><i>title</i> - ' . __('title of widget', 'currency-exchange-for-woocommerce') . '</li>
                    <li><i>type</i> - ' . __('select, radio', 'currency-exchange-for-woocommerce') . '</li>
                    <li><i>currency_text</i> - ' . __('can be text, flag, symbol, image and space. You can use multiple elements separated by comma (text,space,image)', 'currency-exchange-for-woocommerce') . '</li>
                </ul>
            </li>
        </ul>
        </td>';
        return $html;
    }

    public function option_page_capability($capability = '') {
        return 'manage_berocket_currency_exchange';
    }
}

new BeRocket_CE;
