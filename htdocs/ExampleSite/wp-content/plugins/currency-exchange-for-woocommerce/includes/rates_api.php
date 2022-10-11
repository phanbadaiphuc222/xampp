<?php
class BeRocket_CE_apis {
    function __construct() {
        add_filter('berocket_currency_exchange_api_list', array(__CLASS__, 'exchange_api_list'));
        add_filter('berocket_currency_exchange_api_get', array(__CLASS__, 'get_currencies_from_site'), 10, 2);
        $apis_list = apply_filters('berocket_currency_exchange_api_list', array());
        foreach($apis_list as $api_slug => $api_option) {
            if( ! empty($api_option['sanitize_callback']) ) {
                add_filter('berocket_ce_apis_sanitize_' . $api_slug, $api_option['sanitize_callback'], 10, 2 );
            }
        }
    }
    static function exchange_api_list($apis_list) {
        $apis_list['oer'] = array(
            'name'              => __('Open Exchange Rates', 'currency-exchange-for-woocommerce'),
            'app_id_option'     => 'open_exchange_api',
            'link'              => 'https://openexchangerates.org/',
            'api_link'          => 'https://openexchangerates.org/api/latest.json?app_id=%app_id%',
            'json_decode'       => true,
            'app_id_required'   => true,
            'sanitize_callback' => array(__CLASS__, 'sanitize_oer'),
        );
        $apis_list['currencylayer'] = array(
            'name'              => __('CurrencyLayer', 'currency-exchange-for-woocommerce'),
            'app_id_option'     => 'currencylayer_api',
            'link'              => 'https://currencylayer.com/',
            'api_link'          => 'http://apilayer.net/api/live?access_key=%app_id%',
            'json_decode'       => true,
            'app_id_required'   => true,
            'sanitize_callback' => array(__CLASS__, 'sanitize_currencylayer'),
        );
        $apis_list['fixerio'] = array(
            'name'              => __('Fixer.io', 'currency-exchange-for-woocommerce'),
            'app_id_option'     => 'fixerio_api',
            'link'              => 'https://fixer.io/',
            'api_link'          => 'http://data.fixer.io/api/latest?access_key=%app_id%',
            'json_decode'       => true,
            'app_id_required'   => true,
            'sanitize_callback' => array(__CLASS__, 'sanitize_fixerio'),
        );
        $apis_list['floatrates'] = array(
            'name'              => __('FloatRates', 'currency-exchange-for-woocommerce'),
            'link'              => 'http://www.floatrates.com/',
            'api_link_noid'     => 'http://www.floatrates.com/daily/usd.json',
            'json_decode'       => true,
            'sanitize_callback' => array(__CLASS__, 'sanitize_floatrates'),
        );
        return $apis_list;
    }
    static function get_currencies_from_site($rates, $options) {
        $apis_list = apply_filters('berocket_currency_exchange_api_list', array());
        $rates = array();
        if( empty($options['currency_site']) || ! array_key_exists($options['currency_site'], $apis_list) ) {
            foreach($apis_list as $api_slug => $api_option) {
                $options['currency_site'] = $api_slug;
                $set_initial = false;
                break;
            }
        }
        $api_slug           = $options['currency_site'];
        $api_option         = $apis_list[$api_slug];

        $api_link           = br_get_value_from_array($api_option,  'api_link');
        $api_link_noid      = br_get_value_from_array($api_option,  'api_link_noid');
        $app_id_option      = br_get_value_from_array($api_option,  'app_id_option');
        $app_id_required    = br_get_value_from_array($api_option,  'app_id_required');
        $app_id             = br_get_value_from_array($options,     $app_id_option);
        $url = '';
        if( $app_id_required ) {
            if( $app_id ) {
                $url = $api_link;
            }
        } else {
            if( $app_id && $api_link ) {
                $url = $api_link;
            } else {
                $url = $api_link_noid;
            }
        }
        $url = str_replace('%app_id%', $app_id, $url);
        if( ! empty($url) ) {
            $response = self::api_request($url);
            $rates = apply_filters('berocket_ce_apis_sanitize_' . $api_slug, $rates, $response);
            if( ! isset($rates['USD']) ) {
                $rates['USD'] = 1;
            }
        }
        return $rates;
    }
    static function sanitize_oer($rates, $response) {
        if( isset( $response->rates ) && is_object( $response->rates ) ) {
            $rates = (array) $response->rates;
        }
        return $rates;
    }
    static function sanitize_currencylayer($rates, $response) {
        if( isset( $response->quotes ) && is_object( $response->quotes ) ) {
            $not_rates = (array) $response->quotes;
            foreach( $not_rates as $not_cur_name => $not_cur ) {
                $rates[substr($not_cur_name, 3)] = $not_cur;
            }
        }
        return $rates;
    }
    static function sanitize_fixerio($rates, $response) {
        if( isset($response->rates) && isset($response->base) ) {
            $rates = (array) $response->rates;
            $current_currency = $response->base;
            if( $current_currency != 'USD' ) {
                $current_rate = $rates['USD'];
                
                foreach( $rates as $rate_name => $rate ) {
                    $rates[$rate_name] = $rate / $current_rate;
                }
            }
        }
        return $rates;
    }
    static function sanitize_floatrates($rates, $response) {
        if( isset($response) ) {
            $response = (array) $response;
            foreach( $response as $rate_name => $rate ) {
                $rates[$rate->code] = $rate->rate;
            }
        }
        return $rates;
    }
    static function api_request($url, $json_decode = true) {
        // Open CURL session:
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Get the data:
        $json = curl_exec($ch);
        curl_close($ch);

        // Decode JSON response:
        if( $json_decode ) {
            $response = json_decode($json);
        } else {
            $response = $json;
        }
        return $response;
    }
}
new BeRocket_CE_apis();
