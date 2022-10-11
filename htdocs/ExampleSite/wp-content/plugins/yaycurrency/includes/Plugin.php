<?php

namespace Yay_Currency;

defined( 'ABSPATH' ) || exit;
class Plugin {

	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
	}

	public static function install_yaycurrency_admin_notice() {
		/* translators: %s: Woocommerce link */
		echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'YayCurrency is enabled but not effective. It requires %s in order to work', 'yay-currency' ), '<a href="' . esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ) . '">WooCommerce</a>' ) . '</strong></p></div>';
		return false;
	}

	public static function activate() {
		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', array( 'Yay_Currency\\Plugin', 'install_yaycurrency_admin_notice' ) );
		}
	}

	public static function deactivate() {
	}
}
