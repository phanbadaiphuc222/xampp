var brjsf_ce;
(function ($){
    brjsf_ce = function ( block ) {
        if ( typeof(block) != 'undefined' ) {
            brjsf_ce_reload(block);
        }
        $('.brjsf_ce').each( function( i, o ) {
            var width = 200;
            if ( $(o).data('width') ) {
                width = $(o).data('width');
            } 
            var html = '<div class="brjsf_ce_select"><span class="brjsf_ce_text">'+$(o).find('option:selected').data('text')+'</span> <i class="fa fa-caret-down"></i><ul>';
            $(o).find('option').each( function ( i_option, o_option ) {
                html += '<li data-value="'+$(o_option).val()+'">'+$(o_option).data('text')+'</li>';
            });
            html += '</ul></div>';
            $(o).after( html );
            $(o).removeClass('brjsf_ce').addClass('brjsf_ce_ready');
        });
    }
    function brjsf_ce_reload ( input ) {
        if ( $(input).is('.brjsf_ce_ready') ) {
            $(input).removeClass('brjsf_ce_ready').addClass('brjsf_ce').next().remove();
        } else if ( !$(input).is('.brjsf_ce') ) {
            $(input).addClass('brjsf_ce');
        }
    }
    $(document).on( 'click', '.brjsf_ce_select', function (event) {
        event.preventDefault();
        if ( $(this).is('.brjsf_ce_show') ) {
            $(this).removeClass('brjsf_ce_show');
            $(this).find('.fa').removeClass('fa-caret-up').addClass('fa-caret-down');
        } else {
            $('.brjsf_ce_show').removeClass('brjsf_ce_show');
            $(this).addClass('brjsf_ce_show');
            $(this).find('.fa').removeClass('fa-caret-down').addClass('fa-caret-up');
            $(this).find('ul').attr('style', '');
            if( ( jQuery(this).offset().top - jQuery(window).scrollTop() ) > ( jQuery(window).height() / 2 ) ) {
                $(this).find('ul').css('bottom', '100%').css('top', 'initial');
            } else {
                $(this).find('ul').css('bottom', 'initial').css('top', '100%');
            }
        }
    });
    $(document).on( 'click', '.brjsf_ce_select ul li', function(event) {
        var $select = $(this).parents('.brjsf_ce_select');
        $select.find('span.brjsf_ce_text').html($(this).html());
        $select.prev().val($(this).data('value')).trigger('change');
        
    });
    $(document).on( 'mousedown', '.brjsf_ce_select ul li, .brjsf_ce_select', function(event) {
        event.preventDefault();
        event.stopPropagation();
    });
    $(document).on( 'mousedown', function(event) {
        $('.brjsf_ce_show').removeClass('brjsf_ce_show').find('.fa').removeClass('fa-caret-up').addClass('fa-caret-down');
    });
    $(document).ready( function () {
        ce_execute_func( the_ce_js_data.script.js_page_load );
        jQuery(document).trigger('berocket_ce-js_page_load');
        $(document).on( 'change', '.br_ce_currency_select, .br_ce_select_currency', function(event){
            ce_execute_func( the_ce_js_data.script.js_before_set );
            jQuery(document).trigger('berocket_ce-js_before_set');
            var val = $(this).val();
            $.cookie( 'br_ce_language', val, { path: '/', domain: document.domain } );
            $('.br_ce_currency_select').val(val);
            $('.br_ce_'+val).prop('checked', true);
            ce_execute_func( the_ce_js_data.script.js_after_set );
            jQuery(document).trigger('berocket_ce-js_after_set');
        });
        if( the_ce_js_data.visual_only ) {
            fx.base = the_ce_js_data.base;
            fx.rates = the_ce_js_data.rates;
            if( the_ce_js_data.current != 'none' ) {
                fx.settings = {
                    from : fx.base,
                    to : the_ce_js_data.current
                };
                ce_money_replace();
            }
            jQuery(document).on('berocket_ajax_filtering_end', ce_money_replace);
            jQuery(document).on('berocket_lmp_end', ce_money_replace);
            jQuery(document).on('berocket_product_preview-popup_open', ce_money_replace);
        }
        brjsf_ce();
        jQuery(document).ajaxComplete(function() {
            if( the_ce_js_data.visual_only ) {
                ce_money_replace();
            }
        });
        if( the_ce_js_data.visual_only ) {
            ce_money_replace();
        }
    });
    if( the_ce_js_data.visual_only ) {
        $(document).on('change', '.variations select', ce_money_replace);
        $(document).on('br_popup-show_popup', ce_money_replace);
        $(document).on('berocket_product_preview-popup_open', ce_money_replace);
    }
})(jQuery);
function ce_money_replace() {
    if( the_ce_js_data.current != 'none' ) {
        jQuery('span.amount').each(function(i, o) {
            if( ! jQuery(o).is('.exchanged') ) {
                var money = accounting.unformat(jQuery(o).text(), the_ce_js_data.accounting.decimal);
                money = fx.convert(money);
                money = accounting.formatMoney(money, the_ce_js_data.accounting);
                jQuery(o).html(money).addClass('exchanged');
            }
        });
    }
}
function ce_execute_func ( func ) {
    if( the_ce_js_data.script != 'undefined'
        && the_ce_js_data.script != null
        && typeof func != 'undefined' 
        && func.length > 0 ) {
        try{
            eval( func );
        } catch(err){
            alert('You have some incorrect JavaScript code (Currency Exchange)');
        }
    }
}
