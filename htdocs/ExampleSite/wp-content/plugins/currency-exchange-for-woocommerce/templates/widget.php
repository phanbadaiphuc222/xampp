<?php
global $wpdb;
extract($brce_widget_query_var);
$currencies       = get_woocommerce_currencies();
$BeRocket_CE      = BeRocket_CE::getInstance();
$current_currency = $BeRocket_CE->get_woocommerce_currency();
$rand             = rand();
$currencies_text  = br_get_currency_text_for_element($currency_text);
$brjsf_rand       = rand();

if ( $title )
    echo (empty($args['before_title']) ? '' : $args['before_title']) . $title . (empty($args['after_title']) ? '' : $args['after_title']);
?>
<div>
    <?php if ( ! isset($type) || $type == "" || $type == 'select' ) { ?>
    <select class="br_ce_currency_select brjsf_ce br_ce_brjsv_<?php echo $brjsf_rand; ?>">
        <?php
        echo '<option data-text="'.br_get_currency_text_for_element($currency_text, $current_currency).'" value="">'.br_get_currency_text_for_element($currency_text, $current_currency).'</option>';
        foreach($currencies_text as $currency_slug => $currency)
        {
            if( $current_currency != $currency_slug ) {
                echo '<option data-text="'.$currency.'" value="'.$currency_slug.'"'.( ( isset(BeRocket_CE::$currency) && BeRocket_CE::$currency == $currency_slug ) ? ' selected' : '').'>'.$currency.'</option>';
            }
        }
        ?>
    </select>
    <?php } elseif ($type == 'radio') {
        echo '<div><label><input class="br_ce_select_currency br_ce_" name="ce_select_currency_'.$rand.'" type="radio" value=""'.( ( ! isset(BeRocket_CE::$currency) || BeRocket_CE::$currency == "" ) ? ' checked' : '').'>'.br_get_currency_text_for_element($currency_text, $current_currency).'</label></div>';
        foreach($currencies_text as $currency_slug => $currency)
        {
            if( $current_currency != $currency_slug ) {
                echo '<div><label><input class="br_ce_select_currency" name="ce_select_currency_'.$rand.'" type="radio" value="'.$currency_slug.'"'.( ( isset(BeRocket_CE::$currency) && BeRocket_CE::$currency == $currency_slug ) ? ' checked' : '').'>'.$currency.'</label></div>';
            }
        }
    } ?>
</div>
<script>
    jQuery(document).ready( function () {
        brjsf_ce(".br_ce_brjsv_<?php echo $brjsf_rand; ?>");
    });
</script>
