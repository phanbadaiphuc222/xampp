<?php

namespace Yay_Currency\CompatiblePlugins;

defined( 'ABSPATH' ) || exit;

// Link plugin: https://www.wpgridbuilder.com

class WPGridBuilderCaching {
	protected static $instance = null;

	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	private function doHooks() {
		if ( ! class_exists( 'WP_Grid_Builder_Caching\Includes\Plugin' ) ) {
			return;
		}
		add_filter( 'wp_grid_builder_caching/bypass', array( $this, 'bypass_grid_builder_caching' ), 10, 2 );
	}

	public function bypass_grid_builder_caching( $is_bypass, $attrs ) {
		return true;
	}

}
