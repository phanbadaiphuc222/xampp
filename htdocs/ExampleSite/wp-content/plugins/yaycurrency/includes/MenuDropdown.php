<?php

namespace Yay_Currency;

defined( 'ABSPATH' ) || exit;

class MenuDropdown {

	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	private function doHooks() {
		add_action(
			'admin_init',
			function () {
				add_meta_box(
					'yaycurrency-switcher',
					'YayCurrency',
					array( $this, 'nav_menu_currency_dropdown' ),
					'nav-menus',
					'side',
					'high'
				);
			}
		);
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'menu_item_custom_fields' ), 10, 2 );
		add_action( 'wp_update_nav_menu_item', array( $this, 'save_menu_item_custom_fields' ), 10, 2 );
		add_filter( 'walker_nav_menu_start_el', array( $this, 'render_currency_switcher_as_menu_item' ), 10, 4 );
	}

	public function render_currency_switcher_as_menu_item( $item_output, $menu_item, $depth, $args ) {
		if ( in_array( 'yay-currency-dropdown', $menu_item->classes ) ) {
			$item_output = do_shortcode( '[yaycurrency-menu-item-switcher]' );
		}
		return $item_output;
	}

	public function save_menu_item_custom_fields( $menu_id, $menu_item_db_id ) {
		if ( isset( $_REQUEST['yay-currency-nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['yay-currency-nonce'] ), 'yay-currency-check-nonce' ) ) {
			$is_show_flag_in_menu_item            = isset( $_POST['show-flag'] ) ? sanitize_text_field( $_POST['show-flag'] ) : 0;
			$is_show_currency_name_in_menu_item   = isset( $_POST['show-currency-name'] ) ? sanitize_text_field( $_POST['show-currency-name'] ) : 0;
			$is_show_currency_symbol_in_menu_item = isset( $_POST['show-currency-symbol'] ) ? sanitize_text_field( $_POST['show-currency-symbol'] ) : 0;
			$is_show_currency_code_in_menu_item   = isset( $_POST['show-currency-code'] ) ? sanitize_text_field( $_POST['show-currency-code'] ) : 0;
			$menu_item_size                       = isset( $_POST['menu-item-size'] ) ? sanitize_text_field( $_POST['menu-item-size'] ) : 'small';

			update_option( 'yay_currency_show_flag_in_menu_item', $is_show_flag_in_menu_item );
			update_option( 'yay_currency_show_currency_name_in_menu_item', $is_show_currency_name_in_menu_item );
			update_option( 'yay_currency_show_currency_symbol_in_menu_item', $is_show_currency_symbol_in_menu_item );
			update_option( 'yay_currency_show_currency_code_in_menu_item', $is_show_currency_code_in_menu_item );
			update_option( 'yay_currency_menu_item_size', $menu_item_size );
		}

	}

	public function menu_item_custom_fields( $item_id, $item ) {
		if ( 'yaycurrency-switcher' === $item->post_name || 'YayCurrency Switcher' === $item->post_title ) {
			$is_show_flag_in_menu_item            = get_option( 'yay_currency_show_flag_in_menu_item', 1 );
			$is_show_currency_name_in_menu_item   = get_option( 'yay_currency_show_currency_name_in_menu_item', 1 );
			$is_show_currency_symbol_in_menu_item = get_option( 'yay_currency_show_currency_symbol_in_menu_item', 1 );
			$is_show_currency_code_in_menu_item   = get_option( 'yay_currency_show_currency_code_in_menu_item', 1 );
			$menu_item_size                       = get_option( 'yay_currency_menu_item_size', 'small' );
			wp_nonce_field( 'yay-currency-check-nonce', 'yay-currency-nonce' );
			?>
		<div class="yay-currency-menu-item-custom-fields">
			<span class="yay-currency-menu-item-custom-fields__title">Switcher elements:</span>
			<div class="yay-currency-menu-item-custom-fields__field">
				<input class="yay-currency-menu-item-custom-fields__field--checkbox" type="checkbox" id="show-flag" name="show-flag" value="1" <?php echo $is_show_flag_in_menu_item ? 'checked' : null; ?> />
				<label for="show-flag">Show flag</label>
			</div>
			<div class="yay-currency-menu-item-custom-fields__field">
				<input class="yay-currency-menu-item-custom-fields__field--checkbox" type="checkbox" id="show-currency-name" name="show-currency-name" value="1" <?php echo $is_show_currency_name_in_menu_item ? 'checked' : null; ?> />
				<label for="show-currency-name">Show currency name</label>
			</div>
			<div class="yay-currency-menu-item-custom-fields__field">
				<input class="yay-currency-menu-item-custom-fields__field--checkbox" type="checkbox" id="show-currency-symbol" name="show-currency-symbol" value="1" <?php echo $is_show_currency_symbol_in_menu_item ? 'checked' : null; ?> />
				<label for="show-currency-symbol">Show currency symbol</label>
			</div>
			<div class="yay-currency-menu-item-custom-fields__field">
				<input class="yay-currency-menu-item-custom-fields__field--checkbox" type="checkbox" id="show-currency-code" name="show-currency-code" value="1" <?php echo $is_show_currency_code_in_menu_item ? 'checked' : null; ?> />
				<label for="show-currency-code">Show currency code</label>
			</div>
			<div class="yay-currency-menu-item-custom-fields">
				<span class="yay-currency-menu-item-custom-fields__title">Switcher size:</span>
				<div class="yay-currency-menu-item-custom-field__field-group">
					<div class="yay-currency-menu-item-custom-field__field">
						<input class="yay-currency-menu-item-custom-fields__field--radio" type="radio" id="menu-item-size-small" name="menu-item-size" value="small" <?php echo 'small' === $menu_item_size ? 'checked' : null; ?> />
						<label for="menu-item-size">Small</label>
					</div>
					<div class="yay-currency-menu-item-custom-field__field">
						<input class="yay-currency-menu-item-custom-fields__field--radio" type="radio" id="menu-item-size-medium" name="menu-item-size" value="medium" <?php echo 'medium' === $menu_item_size ? 'checked' : null; ?> />
						<label for="menu-item-size">Medium</label>
					</div>
				</div>
			</div>
		</div>
			<?php
		}
	}

	public function nav_menu_currency_dropdown() {
		?>
	<div id="yay-currency-dropdown" class="posttypediv">
	  <div id="tabs-panel-yay-currency-dropdown" class="tabs-panel tabs-panel-active">
		<ul id="yay-currency-dropdown-checklist" class="categorychecklist form-no-clear">
		  <li>
			<label class="menu-item-title">
				<input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="-1"> <?php echo esc_html_e( 'YayCurrency Switcher', 'yay-currency' ); ?>
			</label>
			<input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="YayCurrency Switcher">
			<input type="hidden" class="menu-item-classes" name="menu-item[-1][menu-item-classes]" value="yay-currency-dropdown">
		  </li>
		</ul>
	  </div>
	  <p class="button-controls">
		<span class="add-to-menu">
		  <input type="submit" class="button-secondary submit-add-to-menu right" value="Add to Menu" name="add-post-type-menu-item" id="submit-yay-currency-dropdown">
		  <span class="spinner"></span>
		</span>
	  </p>
	</div>
		<?php
	}
}
