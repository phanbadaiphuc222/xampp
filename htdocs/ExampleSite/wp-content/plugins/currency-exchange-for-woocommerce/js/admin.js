var br_saved_timeout;
var br_savin_ajax = false;
var br_do_not_stop_loading = false;
(function ($){
    $(document).ready( function () {
        $('.update_open_exchange').click(function(event) {
            var $parents = $(this).parents('.site_data_block');
            var all_is_correct = true;
            if( $parents.find('.app_id_option').length && $parents.find('.app_id_required').length ) {
                if(! $parents.find('.app_id_option').val() ) {
                    $parents.find('.app_id_option').addClass('error');
                    all_is_correct = false;
                }
            }
            if( all_is_correct ) {
                br_do_not_stop_loading = true;
                $(this).parents('form').trigger('submit');
                update_open_exchange();
            }
        });
        function update_open_exchange() {
            if( !br_savin_ajax ) {
                br_savin_ajax = true;
                var url = ajaxurl;
                var form_data = {action: 'open_exchange_load'}
                $.post(url, form_data, function (data) {
                    br_savin_ajax = false;
                    location.reload();
                }).fail(function() {
                    br_savin_ajax = false;
                    location.reload();
                });
            } else {
                setTimeout(update_open_exchange, 250);
            }
        }
        $(document).on('change', '.ce_select_type', function(e) {
            if($(this).val() == 'image') {
                $('.ce_image_type').show();
            } else {
                $('.ce_image_type').hide();
            }
        });
        $(document).on('click', '.add_br_currency_text', function(event) {
            event.preventDefault();
            var field_name = $(this).data('field_name');
            var name = $(this).data('name');
            var id = $(this).data('id');
            $('.br_currency_text').append($('<li><i class="button fa fa-caret-left"></i><i class="button fa fa-caret-right"></i><div style="clear:both;"></div><input type="hidden" name="'+field_name+'" value="'+id+'"><span class="br_type_of_text">'+name+'</span></li>')).trigger('change');
        });
        $(document).on('click', '.br_currency_text li', function(event) {
            event.preventDefault();
            $(this).trigger('change').remove();
        });
        $(document).on('click', '.br_currency_text li .fa-caret-right', function(event) {
            event.preventDefault();
            event.stopPropagation();
            var $li = $(this).parent();
            if( $li.next().is('.br_currency_text li') ) {
                $li.next().after($li).trigger('change');
            }
        });
        $(document).on('click', '.br_currency_text li .fa-caret-left', function(event) {
            event.preventDefault();
            event.stopPropagation();
            var $li = $(this).parent();
            if( $li.prev().is('.br_currency_text li') ) {
                $li.prev().before($li).trigger('change');
            }
        });
        $(document).on('change', '.br_ce_currency_site', function() {
            $('.site_data_block').hide();
            $('.'+$(this).val()+'_data_block').show().trigger('change');
        });
    });
})(jQuery);
