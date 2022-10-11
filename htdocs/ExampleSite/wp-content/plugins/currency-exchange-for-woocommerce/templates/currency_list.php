<?php
$currencies = get_woocommerce_currencies();
?>
<h3><?php _e('Additional currencies', 'currency-exchange-for-woocommerce'); ?></h3>
<div class="brce_currency_list_checkboxes_filtering">
    <label>
        <?php _e('Search:', 'currency-exchange-for-woocommerce'); ?> 
        <input type="text" class="brce_currency_list_cf_text">
    </label>
    <label>
        <input type="checkbox" value="1" class="brce_currency_list_cf_enabled">
        <?php _e('Enabled only', 'currency-exchange-for-woocommerce'); ?>
    </label>
</div>
<div class="brce_currency_list_checkboxes">
<?php
foreach($currencies as $name => $currency) {
    echo '<label class="brce_currency brce_currency_'.$name.'">
        <input name="br_ce_options[use_currency][]" value="'.$name.'" type="checkbox"'.(in_array($name, $options['use_currency']) ? ' checked' : '').'>
        '.$currency.' ['.$name.']
    </label>';
}
?>
</div>
<?php
foreach($currencies as $name => $currency) {
    echo '<div class="br_ce_lang_select br_ce_lang_select_' . $name . '">
        <h3>';
        if( strlen(berocket_isset($options['currency_image'][$name])) > 3 ) {
            echo "<img src='".berocket_isset($options['currency_image'][$name])."'>";
        } elseif( file_exists(dirname( __FILE__, 2 ).'/images/flag/'.$name.'.png') ) {
            $flag = plugin_dir_url( __FILE__ ).'../images/flag/'.$name.'.png';
            echo '<img src="'.$flag.'" alt="'.$currency.'">';
        }
        echo $currency.' <small>['.$name.']</small> <span></span><i class="fas fa-chevron-down"></i></h3>
        <div class="br_ce_lang_options">
            <p>
                <label>
                    '.__('Currency exchange rate', 'currency-exchange-for-woocommerce').'
                    <input class="br_ce_lang_options_rate" name="br_ce_options[currency]['.$name.']" type="number" step="0.0000000000001" value="'.( isset($options['currency'][$name]) && $options['currency'][$name] > 0 ? $options['currency'][$name] : '1' ).'">
                </label>
            </p>
            <p>
                <label>
                    '.__('Currency custom text', 'currency-exchange-for-woocommerce').'
                    <input name="br_ce_options[currency_custom]['.$name.']" type="text" value="'.( isset($options['currency_custom'][$name]) ? $options['currency_custom'][$name] : '' ).'">
                </label>
            </p>
            '.__('Currency image', 'currency-exchange-for-woocommerce').br_upload_image('br_ce_options[currency_image]['.$name.']', berocket_isset($options['currency_image'][$name])).'
        </div>
    </div>';
}
?>

<script>
    function brce_currency_list_checkboxes_filtering() {
        var text = jQuery('.brce_currency_list_cf_text').val();
        text = text.toLowerCase();
        var enabled = jQuery('.brce_currency_list_cf_enabled').prop('checked');
        jQuery('.brce_currency_list_checkboxes .brce_currency').each(function() {
            var elem_text = jQuery(this).text();
            elem_text = elem_text.toLowerCase();
            var elem_enabled = jQuery(this).find('input').prop('checked');
            var show = true;
            if( text.length && elem_text.search(text) == -1 ) {
                show = false;
            }
            if( enabled && ! elem_enabled ) {
                show = false;
            }
            if( show ) {
                jQuery(this).show();
            } else {
                jQuery(this).hide();
            }
        });
    }
    jQuery(document).on('change', '.brce_currency_list_cf_enabled, .brce_currency_list_cf_text', brce_currency_list_checkboxes_filtering);
    jQuery(document).on('keyup', '.brce_currency_list_cf_text', brce_currency_list_checkboxes_filtering);
    jQuery(document).on('click', '.br_ce_lang_select h3', function() {
        if( jQuery(this).find('.fa-chevron-down').length ) {
            jQuery(this).next().show();
            jQuery(this).find('.fa-chevron-down').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        } else {
            jQuery(this).next().hide();
            jQuery(this).find('.fa-chevron-up').addClass('fa-chevron-down').removeClass('fa-chevron-up');
        }
    });
    function brce_currency_list_hide_options() {
        jQuery('.br_ce_lang_select').hide();
        jQuery('.brce_currency_list_checkboxes .brce_currency input:checked').each(function() {
            jQuery('.br_ce_lang_select_'+jQuery(this).val()).show();
        });
    }
    brce_currency_list_hide_options();
    jQuery(document).on('change', '.brce_currency_list_checkboxes .brce_currency input', brce_currency_list_hide_options);
    function br_ce_lang_options_rate() {
        jQuery('.br_ce_lang_select').each(function() {
            jQuery(this).find('h3 span').text(jQuery(this).find('.br_ce_lang_options_rate').val());
        });
    }
    br_ce_lang_options_rate();
    jQuery(document).on('change', '.br_ce_lang_options_rate', br_ce_lang_options_rate);
</script>
