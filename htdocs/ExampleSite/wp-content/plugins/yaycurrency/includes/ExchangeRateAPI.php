<?php

namespace Yay_Currency;

defined( 'ABSPATH' ) || exit;

class ExchangeRateAPI {

	protected static $instance = null;
	private $url_template;
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	private function doHooks() {
	}

	public function get_exchange_rates( $currency_params_template ) {
		$this->url_template = 'https://query1.finance.yahoo.com/v8/finance/chart/$src$dest=X?interval=2m';
		$url                = strtr( $this->url_template, $currency_params_template );
		$json_data          = wp_remote_get( $url );
		return $json_data;
	}
}
