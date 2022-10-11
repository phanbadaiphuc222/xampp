<?php
/**
 * List/Grid widget
 */
class BeRocket_CE_Widget extends WP_Widget 
{
    public static $defaults = array(
        'title'         => '',
        'type'          => 'select',
        'currency_text' => array('text'),
    );
	public function __construct() {
        parent::__construct("berocket_ce_widget", "WooCommerce Currency Exchange",
            array("description" => "Show currency exchange widget"));
    }
    /**
     * WordPress widget for display Currency Exchange buttons
     */
    public function widget($args, $instance)
    {
        $instance = wp_parse_args( (array) $instance, self::$defaults );
        $instance['title'] = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance );
        $BeRocket_CE = BeRocket_CE::getInstance();
        $options = $BeRocket_CE->get_option();
        $query_var = array(
            'title' => apply_filters( 'ce_widget_title', $instance['title'] ),
            'type' => apply_filters( 'ce_widget_type', $instance['type'] ),
            'currency_text' => apply_filters( 'ce_widget_text_builder', $instance['currency_text'] ),
            'options' => $options,
            'args' => $args
        );
        set_query_var( 'brce_widget_query_var', $query_var );

        $widget_template = 'widget';
        if ( $instance['type'] == 'floating-bar' ) $widget_template = 'floating-widget';
        $widget_template = apply_filters( 'ce_widget_template', $widget_template );

        echo $args['before_widget'];
        $BeRocket_CE->br_get_template_part( $widget_template );
        echo $args['after_widget'];
	}
    /**
     * Update widget settings
     */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['type'] = strip_tags( $new_instance['type'] );
		$instance['currency_text'] = $new_instance['currency_text'];
		return $instance;
	}
    /**
     * Widget settings form
     */
	public function form($instance)	{
        $instance = wp_parse_args( (array) $instance, self::$defaults );
		$title = strip_tags($instance['title']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
        <p>
            <select class="ce_select_type" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>">
                <option value="select"<?php if($instance['type'] == 'select') echo ' selected'; ?>><?php _e('Select', 'currency-exchange-for-woocommerce') ?></option>
                <option value="radio"<?php if($instance['type'] == 'radio') echo ' selected'; ?>><?php _e('Radio', 'currency-exchange-for-woocommerce') ?></option>
                <option value="floating-bar"<?php if($instance['type'] == 'floating-bar') echo ' selected'; ?>><?php _e('Floating Bar', 'currency-exchange-for-woocommerce') ?></option>
            </select>
        </p>
        <p>
            <?php 
            $type_of_text = array(
                'text'      => __('Text', 'currency-exchange-for-woocommerce'),
                'custom'    => __('Custom', 'currency-exchange-for-woocommerce'),
                'flag'      => __('Flag', 'currency-exchange-for-woocommerce'),
                'symbol'    => __('Symbol', 'currency-exchange-for-woocommerce'),
                'image'     => __('Image', 'currency-exchange-for-woocommerce'),
                'space'     => __('&nbsp;', 'currency-exchange-for-woocommerce'),
            );
            $type_of_text = apply_filters('br_currency_widget_type_of_text', $type_of_text);
            foreach ( $type_of_text as $type_id => $type_name ) {
                echo '<a href="#add_', $type_id, '" class="add_br_currency_text button" data-id="', $type_id, '" data-name="', $type_name, '" data-field_name="', $this->get_field_name('currency_text'), '[]">', $type_name, '</a>';
            }
            echo '<ul class="br_currency_text">';
            foreach ( $instance['currency_text'] as $currency_text ) {
                echo '<li><i class="button fa fa-caret-left"></i><i class="button fa fa-caret-right"></i><div style="clear:both;"></div><input type="hidden" name="', $this->get_field_name('currency_text'), '[]" value="', $currency_text, '"><span class="br_type_of_text">', $type_of_text[$currency_text], '</span></li>';
            }
            echo '</ul>';
            ?>
        </p>
		<?php
	}
}
?>
