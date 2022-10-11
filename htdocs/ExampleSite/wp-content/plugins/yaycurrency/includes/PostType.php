<?php
namespace Yay_Currency;

defined( 'ABSPATH' ) || exit;

class PostType {

	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}
		return self::$instance;
	}


	public function __construct() {
	}

	private function doHooks() {
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	public function register_post_type() {
		$labels = array(
			'name'          => __( 'Currencies Manage', 'yay-currency' ),
			'singular_name' => __( 'Currency Manage', 'yay-currency' ),
		);

		$args = array(
			'labels'            => $labels,
			'description'       => __( 'Currency Manage', 'yay-currency' ),
			'public'            => false,
			'show_ui'           => false,
			'has_archive'       => true,
			'show_in_admin_bar' => false,
			'show_in_rest'      => true,
			'show_in_menu'      => false,
			'query_var'         => 'yay-currency-manage',
			'supports'          => array(
				'title',
				'thumbnail',
			),
			'capabilities'      => array(
				'edit_post'          => 'manage_options',
				'read_post'          => 'manage_options',
				'delete_post'        => 'manage_options',
				'edit_posts'         => 'manage_options',
				'edit_others_posts'  => 'manage_options',
				'delete_posts'       => 'manage_options',
				'publish_posts'      => 'manage_options',
				'read_private_posts' => 'manage_options',
			),
		);
		register_post_type( 'yay-currency-manage', $args );
	}
}
