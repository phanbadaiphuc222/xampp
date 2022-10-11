<?php
extract($brce_widget_query_var);
$BeRocket_CE      = BeRocket_CE::getInstance();
$cur_currency     = isset( BeRocket_CE::$currency ) ? BeRocket_CE::$currency : '';
$rand             = rand();
$currencies_text  = br_get_currency_text_for_element( $currency_text );
$brjsf_rand       = rand();

if ( empty( $cur_currency ) )
    $cur_currency = get_woocommerce_currency();

if ( $type == 'floating-bar' ) {
    ?>
    <div class="brjsf_ce_floating_bar brjsf_ce_floating_bar_<?php echo $brjsf_rand; ?>">
        <?php if ( ! empty( $title ) ) { ?>
            <div class="floating_bar_title">
                <?= $args['before_title'] . $title . $args['after_title']; ?>
            </div>
        <?php } ?>

        <div class="floating_bar_currencies">
            <?php foreach ( $currencies_text as $currency_slug => $currency ) {
                $checked = ( $cur_currency == $currency_slug ) ? 'checked' : '';
                ?>
                <div class="brjsf_ce_<?=$checked?>">
                    <label>
                        <input class="br_ce_select_currency" name="ce_select_currency_'<?= $rand ?>'" type="radio"
                               value="<?= $currency_slug ?>" <?= $checked ?>>
                        <span class="cur_holder"><?= $currency_slug ?></span>
                        <span class="cur_content"><?= $currency ?></span>
                    </label>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php
}
?>
<div class="close_parent_<?php echo $brjsf_rand; ?>"></div>
<script>
    jQuery('.close_parent_<?php echo $brjsf_rand; ?>').parent().addClass('no-presence');
</script>
