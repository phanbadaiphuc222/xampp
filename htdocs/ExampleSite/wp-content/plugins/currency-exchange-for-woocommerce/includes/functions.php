<?php
if( ! function_exists( 'br_get_currency_text_for_element' ) ){
    function br_get_currency_text_for_element($elements, $currency_load = false) {
        $BeRocket_CE = BeRocket_CE::getInstance();
        $options = $BeRocket_CE->get_option();
        $currencies = array();
        $woo_currencies = get_woocommerce_currencies();
        if( ! isset($elements) || ! is_array($elements) ) {
            $elements = array('text');
        }
        if( $currency_load !== false ) {
            $text = "";
            foreach($elements as $element) {
                if( $element == 'text' ) {
                    $text .= $woo_currencies[$currency_load];
                } elseif( $element == 'custom' ) {
                    if( isset( $options['currency_custom'][$currency_load] ) ) {
                        $text .= $options['currency_custom'][$currency_load];
                    }
                } elseif( $element == 'flag' ) {
                    if( file_exists(dirname( __FILE__, 2 ).'/images/flag/'.$currency_load.'.png') ) {
                        $text .= '<img src=\''.plugin_dir_url( __FILE__ ).'../images/flag/'.$currency_load.'.png\' alt=\''.$woo_currencies[$currency_load].'\'>';
                    }
                } elseif( $element == 'symbol' ) {
                    $text .= get_woocommerce_currency_symbol( $currency_load );
                } elseif( $element == 'image' ) {
                    if( isset( $options['currency_image'][$currency_load] ) && strlen($options['currency_image'][$currency_load]) > 3 ) {
                        $text .= '<img src=\''.$options['currency_image'][$currency_load].'\' alt=\''.$woo_currencies[$currency_load].'\'>';
                    }
                } elseif( $element == 'space' ) {
                    $text .= '&nbsp;';
                } else {
                    $text .= apply_filters('br_currency_widget_do_type_of_text', '', $element, $currency_load);
                }
            }
            $currencies = $text;
        } else {
            foreach( $options['use_currency'] as $currency) {
                $text = "";
                foreach($elements as $element) {
                    if( $element == 'text' ) {
                        $text .= $woo_currencies[$currency];
                    } elseif( $element == 'custom' ) {
                        if( isset( $options['currency_custom'][$currency] ) ) {
                            $text .= $options['currency_custom'][$currency];
                        }
                    } elseif( $element == 'flag' ) {
                        if( file_exists(dirname( __FILE__, 2 ).'/images/flag/'.$currency.'.png') ) {
                            $text .= '<img src=\''.plugin_dir_url( __FILE__ ).'../images/flag/'.$currency.'.png\' alt=\''.$woo_currencies[$currency].'\'>';
                        }
                    } elseif( $element == 'symbol' ) {
                        $text .= get_woocommerce_currency_symbol( $currency );
                    } elseif( $element == 'image' ) {
                        if( isset( $options['currency_image'][$currency] ) && strlen($options['currency_image'][$currency]) > 3 ) {
                            $text .= '<img src=\''.$options['currency_image'][$currency].'\' alt=\''.$woo_currencies[$currency].'\'>';
                        }
                    } elseif( $element == 'space' ) {
                        $text .= '&nbsp;';
                    } else {
                        $text .= apply_filters('br_currency_widget_do_type_of_text', '', $element, $currency);
                    }
                }
                $currencies[$currency] = $text;
            }
        }
        return $currencies;
    }
}

if ( ! function_exists( 'br_ce_get_currency' ) ){
    function br_ce_get_currency() {
        $BeRocket_CE = BeRocket_CE::getInstance();
        $country_to_currency = br_currencies_list();
        $current_currency = $BeRocket_CE->get_woocommerce_currency();

        $ip_country = false;
        if ( class_exists('WC_Geolocation') and method_exists('WC_Geolocation', 'geolocate_ip') ) {
            $ip_country = WC_Geolocation::geolocate_ip();
            $ip_country = strtolower($ip_country['country']);
            if( empty($ip_country) ) {
                $ip_country = false;
            }
        }

        if ( $ip_country === false ) {
            return '';
        }
        
        $ip_currency = $current_currency;
        if ( ! empty($country_to_currency[$ip_country]) ) {
            $ip_currency = $country_to_currency[$ip_country];
        }
        return $ip_currency;
    }
}
if ( ! function_exists( 'br_currencies_list' ) ) {
    function br_currencies_list() {
        return array(
            "01" => "USD",
            "ad" => "EUR",
            "ae" => "AED",
            "af" => "AFN",
            "ag" => "XCD",
            "ai" => "XCD",
            "al" => "ALL",
            "am" => "AMD",
            "ao" => "AOA",
            "ar" => "ARS",
            "as" => "USD",
            "at" => "EUR",
            "au" => "AUD",
            "aw" => "AWG",
            "az" => "AZN",
            "ba" => "BAM",
            "bb" => "BBD",
            "bd" => "BDT",
            "be" => "EUR",
            "bf" => "XOF",
            "bg" => "BGN",
            "bh" => "BHD",
            "bi" => "BIF",
            "bj" => "XOF",
            "bl" => "EUR",
            "bm" => "BMD",
            "bn" => "BND",
            "bo" => "BOB",
            "bq" => "USD",
            "br" => "BRL",
            "bs" => "BSD",
            "bt" => "BTN",
            "bw" => "BWP",
            "by" => "BYR",
            "bz" => "BZD",
            "ca" => "CAD",
            "cd" => "CDF",
            "cf" => "XAF",
            "cg" => "XAF",
            "ch" => "CHF",
            "ci" => "XOF",
            "ck" => "NZD",
            "cl" => "CLF",
            "cm" => "XAF",
            "cn" => "CNY",
            "co" => "COP",
            "cr" => "CRC",
            "cu" => "CUC",
            "cv" => "CVE",
            "cw" => "ANG",
            "cy" => "EUR",
            "cz" => "CZK",
            "de" => "EUR",
            "dj" => "DJF",
            "dk" => "DKK",
            "dm" => "XCD",
            "do" => "DOP",
            "dz" => "DZD",
            "ec" => "USD",
            "ee" => "EUR",
            "eg" => "EGP",
            "er" => "ERN",
            "es" => "EUR",
            "et" => "ETB",
            "eu" => "EUR",
            "fi" => "EUR",
            "fj" => "FJD",
            "fm" => "USD",
            "fo" => "DKK",
            "fr" => "EUR",
            "ga" => "XAF",
            "gd" => "XCD",
            "ge" => "GEL",
            "gf" => "EUR",
            "gg" => "GBP",
            "gh" => "GHS",
            "gi" => "GIP",
            "gl" => "DKK",
            "gm" => "GMD",
            "gn" => "GNF",
            "gp" => "EUR",
            "gq" => "XAF",
            "gr" => "EUR",
            "gt" => "GTQ",
            "gu" => "USD",
            "gw" => "XOF",
            "gy" => "GYD",
            "hk" => "HKD",
            "hn" => "HNL",
            "hr" => "HRK",
            "ht" => "HTG",
            "hu" => "HUF",
            "id" => "IDR",
            "ie" => "EUR",
            "il" => "ILS",
            "im" => "GBP",
            "in" => "INR",
            "io" => "GBP",
            "iq" => "IQD",
            "ir" => "IRR",
            "is" => "ISK",
            "it" => "EUR",
            "je" => "GBP",
            "jm" => "JMD",
            "jo" => "JOD",
            "jp" => "JPY",
            "ke" => "KES",
            "kg" => "KGS",
            "kh" => "KHR",
            "ki" => "AUD",
            "km" => "KMF",
            "kn" => "XCD",
            "kp" => "KPW",
            "kr" => "KRW",
            "kw" => "KWD",
            "ky" => "KYD",
            "kz" => "KZT",
            "la" => "LAK",
            "lb" => "LBP",
            "lc" => "XCD",
            "li" => "CHF",
            "lk" => "LKR",
            "lr" => "LRD",
            "ls" => "LSL",
            "lt" => "LTL",
            "lu" => "EUR",
            "lv" => "LVL",
            "ly" => "LYD",
            "ma" => "MAD",
            "mc" => "EUR",
            "md" => "MDL",
            "me" => "EUR",
            "mf" => "EUR",
            "mg" => "MGA",
            "mh" => "USD",
            "mk" => "MKD",
            "ml" => "XOF",
            "mm" => "MMK",
            "mn" => "MNT",
            "mo" => "HKD",
            "mp" => "USD",
            "mq" => "EUR",
            "mr" => "MRO",
            "ms" => "XCD",
            "mt" => "EUR",
            "mu" => "MUR",
            "mv" => "MVR",
            "mw" => "MWK",
            "mx" => "MXN",
            "my" => "MYR",
            "mz" => "MZN",
            "na" => "NAD",
            "nc" => "XPF",
            "ne" => "XOF",
            "nf" => "AUD",
            "ng" => "NGN",
            "ni" => "NIO",
            "nl" => "EUR",
            "no" => "NOK",
            "np" => "NPR",
            "nr" => "AUD",
            "nu" => "NZD",
            "nz" => "NZD",
            "om" => "OMR",
            "pa" => "PAB",
            "pe" => "PEN",
            "pf" => "XPF",
            "pg" => "PGK",
            "ph" => "PHP",
            "pk" => "PKR",
            "pl" => "PLN",
            "pm" => "CAD",
            "pr" => "USD",
            "ps" => "EGP",
            "pt" => "EUR",
            "pw" => "USD",
            "py" => "PYG",
            "qa" => "QAR",
            "re" => "EUR",
            "ro" => "RON",
            "rs" => "RSD",
            "ru" => "RUB",
            "rw" => "RWF",
            "sa" => "SAR",
            "sb" => "SBD",
            "sc" => "SCR",
            "sd" => "SDG",
            "se" => "SEK",
            "sg" => "BND",
            "si" => "EUR",
            "sk" => "EUR",
            "sl" => "SLL",
            "sm" => "EUR",
            "sn" => "XOF",
            "so" => "SOS",
            "sr" => "SRD",
            "ss" => "SSP",
            "st" => "STD",
            "sv" => "USD",
            "sx" => "ANG",
            "sy" => "SYP",
            "sz" => "SZL",
            "tc" => "USD",
            "td" => "XAF",
            "tg" => "XOF",
            "th" => "THB",
            "tj" => "TJS",
            "tk" => "NZD",
            "tl" => "USD",
            "tm" => "TMT",
            "tn" => "TND",
            "to" => "TOP",
            "tr" => "TRY",
            "tt" => "TTD",
            "tv" => "AUD",
            "tw" => "TWD",
            "tz" => "TZS",
            "ua" => "UAH",
            "ug" => "UGX",
            "uk" => "GBP",
            "us" => "USD",
            "uy" => "UYI",
            "uz" => "UZS",
            "va" => "EUR",
            "vc" => "XCD",
            "ve" => "VEF",
            "vg" => "USD",
            "vi" => "USD",
            "vn" => "VND",
            "vu" => "VUV",
            "wf" => "XPF",
            "ws" => "WST",
            "ye" => "YER",
            "yt" => "EUR",
            "za" => "ZAR",
            "zm" => "ZMW",
            "zw" => "USD",
            "ax" => "EUR",
            "aq" => "",
            "bv" => "NOK",
            "cx" => "AUD",
            "cc" => "AUD",
            "fk" => "FKP",
            "tf" => "EUR",
            "hm" => "AUD",
            "pn" => "NZD",
            "sh" => "SHP",
            "gs" => "GBP",
            "sj" => "NOK",
            "gb" => "GBP",
            "um" => "USD",
            "eh" => "MAD",
        );
    }
}
