<?php
/**
 * WooCommerce More Sorting - Custom Meta Sorting Section Settings
 *
 * @version 3.2.5
 * @since   3.1.0
 * @author  Algoritmika Ltd.
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Alg_WC_More_Sorting_Settings_Custom_Meta_Sorting' ) ) :

class Alg_WC_More_Sorting_Settings_Custom_Meta_Sorting extends Alg_WC_More_Sorting_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function __construct() {
		$this->id   = 'custom_meta_sorting';
		$this->desc = __( 'Custom Meta Sorting', 'woocommerce-more-sorting' );
		parent::__construct();
		add_action( 'woocommerce_admin_field_alg_wc_more_sorting_custom_number', array( $this, 'output_custom_number' ) );
	}

	/**
	 * output_custom_number.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function output_custom_number( $value ) {
		$type         = 'number';
		$option_value = get_option( $value['id'], $value['default'] );
		$tooltip_html = ( isset( $value['desc_tip'] ) && '' != $value['desc_tip'] ) ? '<span class="woocommerce-help-tip" data-tip="' . $value['desc_tip'] . '"></span>' : '';
		$description  = ' <span class="description">' . $value['desc'] . '</span>';
		$style        = 'background: #ba0000; border-color: #aa0000; text-shadow: 0 -1px 1px #990000,1px 0 1px #990000,0 1px 1px #990000,-1px 0 1px #990000; box-shadow: 0 1px 0 #990000;';
		$save_button  = ' <input name="save" class="button-primary" style="' . $style . '" type="submit" value="' . __( 'Save changes', 'woocommerce' ) . '">';
		$custom_attributes = array();
		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}
		?><tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
				<?php echo $tooltip_html; ?>
			</th>
			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
				<input
					name="<?php echo esc_attr( $value['id'] ); ?>"
					id="<?php echo esc_attr( $value['id'] ); ?>"
					type="<?php echo esc_attr( $type ); ?>"
					style="<?php echo esc_attr( $value['css'] ); ?>"
					value="<?php echo esc_attr( $option_value ); ?>"
					class="<?php echo esc_attr( $value['class'] ); ?>"
					placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
					<?php echo implode( ' ', $custom_attributes ); ?>
					/><?php echo $save_button; ?><?php echo $description; ?>
			</td>
		</tr><?php
	}

	/**
	 * get_settings.
	 *
	 * @version 3.2.8
	 * @since   3.1.0
	 */
	public static function get_settings() {
		$settings = array(
			array(
				'title'     => __( 'Custom Meta Sorting', 'woocommerce-more-sorting' ),
				'desc'      => __( 'This section allows you to add sorting by any custom product meta.', 'woocommerce-more-sorting' ),
				'type'      => 'title',
				'id'        => 'alg_wc_more_sorting_custom_meta_sorting_options',
			),
			array(
				'title'     => __( 'Custom Meta Sorting', 'woocommerce-more-sorting' ),
				'desc'      => '<strong>' . __( 'Enable Section', 'woocommerce-more-sorting' ) . '</strong>',
				'id'        => 'alg_wc_more_sorting_custom_meta_sorting_enabled',
				'default'   => 'no',
				'type'      => 'checkbox',
			),
			array(
				'title'     => __( 'Total Options', 'woocommerce-more-sorting' ),
				'desc_tip'  => __( 'Save changes after you update this number.', 'woocommerce-more-sorting' ),
				'id'        => 'alg_wc_more_sorting_custom_meta_sorting_total_number',
				'default'   => 1,
				'type'      => 'alg_wc_more_sorting_custom_number',
				'custom_attributes' => apply_filters( 'alg_wc_more_sorting', array( 'min' => '0', 'max' => '1' ), 'settings_custom_meta_sorting' ),
				'desc'      => apply_filters( 'alg_wc_more_sorting', sprintf( __( 'You will need <a target="_blank" href="%s">More Sorting Options for WooCommerce Pro</a> plugin to add more than one custom meta sorting.', 'woocommerce-more-sorting' ), 'https://wpwham.com/products/more-sorting-options-for-woocommerce/?utm_source=settings_custom_meta_sorting&utm_campaign=free&utm_medium=more_sorting' ), 'settings' ),
			),
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_wc_more_sorting_custom_meta_sorting_options',
			),
		);
		$total_number = apply_filters( 'alg_wc_more_sorting', 1, 'custom_meta_sorting' );
		for ( $i = 1; $i <= $total_number; $i++ ) {
			$settings = array_merge( $settings, array(
				array(
					'title'     => __( 'Custom Meta Sorting', 'woocommerce-more-sorting' ) . ' #' . $i,
					'type'      => 'title',
					'id'        => 'alg_wc_more_sorting_custom_meta_sorting_options_' . $i,
				),
				array(
					'title'     => __( 'Enable/Disable', 'woocommerce-more-sorting' ),
					'desc'      => __( 'Enabled', 'woocommerce-more-sorting' ),
					'id'        => 'alg_wc_more_sorting_custom_meta_sorting_enabled_' . $i,
					'default'   => 'yes',
					'type'      => 'checkbox',
				),
				array(
					'title'     => __( 'Type', 'woocommerce-more-sorting' ),
					'id'        => 'alg_wc_more_sorting_custom_meta_sorting_type_' . $i,
					'default'   => 'meta_value',
					'type'      => 'select',
					'options'   => array(
						'meta_value'     => __( 'Text', 'woocommerce-more-sorting' ),
						'meta_value_num' => __( 'Numbers', 'woocommerce-more-sorting' ),
					),
					'css'       => 'width:250px;',
				),
				array(
					'title'     => __( 'Meta Key', 'woocommerce-more-sorting' ),
					'desc_tip'  => __( 'For example try "total_sales" or "_sku" or "_stock".', 'woocommerce-more-sorting' ),
					'id'        => 'alg_wc_more_sorting_custom_meta_sorting_meta_key_' . $i,
					'default'   => '',
					'type'      => 'text',
					'css'       => 'width:250px;',
				),
				array(
					'title'     => __( 'Secondary Sorting', 'woocommerce-more-sorting' ),
					'id'        => 'alg_wc_more_sorting_custom_meta_sorting_secondary_' . $i,
					'default'   => 'none',
					'type'      => 'select',
					'options'   => array(
						'none'          => __( 'None', 'woocommerce-more-sorting' ),
						'ID'            => __( 'ID', 'woocommerce-more-sorting' ),
						'parent'        => __( 'Parent ID', 'woocommerce-more-sorting' ),
						'title'         => __( 'Title', 'woocommerce-more-sorting' ),
						'name'          => __( 'Name (i.e. slug)', 'woocommerce-more-sorting' ),
						'date'          => __( 'Date', 'woocommerce-more-sorting' ),
						'modified'      => __( 'Last modified date', 'woocommerce-more-sorting' ),
						'author'        => __( 'Author', 'woocommerce-more-sorting' ),
						'rand'          => __( 'Random', 'woocommerce-more-sorting' ),
						'comment_count' => __( 'Comment count', 'woocommerce-more-sorting' ),
					),
					'css'       => 'width:250px;',
				),
				array(
					'title'     => __( 'Order', 'woocommerce-more-sorting' ),
					'id'        => 'alg_wc_more_sorting_custom_meta_sorting_order_' . $i,
					'default'   => 'asc',
					'type'      => 'select',
					'options'   => array(
						'asc'  => __( 'Ascending', 'woocommerce-more-sorting' ),
						'desc' => __( 'Descending', 'woocommerce-more-sorting' ),
					),
					'css'       => 'width:250px;',
				),
				array(
					'title'     => __( 'Parameter', 'woocommerce-more-sorting' ),
					'desc_tip'  => __( 'Important: Do not use hyphens (-). Order parameter (i.e. -asc or -desc) will be added automatically.', 'woocommerce-more-sorting' ),
					'id'        => 'alg_wc_more_sorting_custom_meta_sorting_param_' . $i,
					'default'   => 'custom_sorting_' . $i,
					'type'      => 'text',
					'css'       => 'width:250px;',
				),
				array(
					'title'     => __( 'Title', 'woocommerce-more-sorting' ),
					'id'        => 'alg_wc_more_sorting_custom_meta_sorting_title_' . $i,
					'default'   => __( 'Custom Meta Sorting', 'woocommerce-more-sorting' ) . ' #' . $i,
					'type'      => 'text',
					'css'       => 'width:250px;',
				),
				array(
					'type'      => 'sectionend',
					'id'        => 'alg_wc_more_sorting_custom_meta_sorting_options_' . $i,
				),
			) );
		}
		return $settings;
	}

}

endif;

return new Alg_WC_More_Sorting_Settings_Custom_Meta_Sorting();
