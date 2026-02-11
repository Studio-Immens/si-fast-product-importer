
function FP_Open_Detail(card){
    jQuery('.FPDetailSection').slideToggle();
    jQuery('.FPBackGroundSection').show();

    jQuery('.FPDetailTitle').text(card.attr('fp_title'));

    var blocks = ['short_title', 'slang_title', 'description', 'exerpt'];
    blocks.forEach(function(block) {
        var val = card.attr('fp_' + block);
        if (!val || val === '') {
            jQuery('p[fp-block="' + (block === 'exerpt' ? 'exerpt' : block) + '"]').parent().hide();
        } else {
            jQuery('p[fp-block="' + (block === 'exerpt' ? 'exerpt' : block) + '"]').parent().show();
            jQuery('p[fp-block="' + (block === 'exerpt' ? 'exerpt' : block) + '"]').text(val);
        }
    });

    FP_Create_Detail_tax_cloud( card, 'fp_macro_cat' );
    FP_Create_Detail_tax_cloud( card, 'fp_categories' );
    FP_Create_Detail_tax_cloud( card, 'fp_tag' );
    FP_Create_Detail_tax_cloud( card, 'fp_ingredient' );
    FP_Create_Detail_tax_cloud( card, 'fp_allerg' );
    FP_Create_Detail_tax_cloud( card, 'fp_sticker' );
    FP_Create_Detail_tax_cloud( card, 'fp_temp' );

    jQuery('.FPDetailBodyImages').empty();
    var thumb = card.attr('fp_img');
    jQuery('.FPDetailBodyImages').append( '<img src="'+thumb+'">' );
}

function FP_Close_Detail(){
    jQuery('.FPDetailSection').slideToggle();
    jQuery('.FPBackGroundSection').hide();
}

function FP_Create_Detail_tax_cloud( card, key ){
    var container = jQuery('div[fp-block="'+key+'"]');
    container.empty();
    var val = card.attr(key);
    if (!val || val === '') {
        container.parent().hide();
    } else {
        container.parent().show();
        var items = val.split(',');
        items.forEach(element => {
            if ( element.trim() != '' ) {
                container.append('<div class="PFCloud">'+element.trim()+'</div>');
            }
        });
    }
}

function FP_Import_product(btn){
    var card = btn.closest('.FPCard');
    var originalHtml = btn.html();

    if (btn.hasClass('fp-loading')) return;

    btn.addClass('fp-loading').html('<span class="dashicons dashicons-update spin"></span>');

    var productData = {
        post_title: card.attr('fp_title'),
        post_content: card.attr('fp_description'),
        post_excerpt: card.attr('fp_exerp'),
        fp_categories: card.attr('fp_categories'),
        fp_tag: card.attr('fp_tag'),
        fp_img: card.attr('fp_img')
    };

    jQuery.ajax({
        url: fp_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'fp_import_product',
            nonce: fp_ajax.nonce,
            product: productData
        },
        success: function(response) {
            btn.removeClass('fp-loading');
            if (response.success) {
                btn.html('<span class="dashicons dashicons-yes"></span>').css('background-color', 'var(--fp-completed)');
                FP_Notify(response.data.message, 'success');
            } else {
                btn.html(originalHtml);
                FP_Notify(response.data.message, 'error');
            }
        },
        error: function() {
            btn.removeClass('fp-loading').html(originalHtml);
            FP_Notify('An error occurred during import', 'error');
        }
    });
}

function FP_Notify(message, type) {
    var color = type === 'success' ? 'var(--fp-completed)' : 'var(--fp-error-color)';
    var notify = jQuery('<div class="fp-notification"></div>')
        .text(message)
        .css({
            'position': 'fixed',
            'bottom': '20px',
            'right': '20px',
            'background-color': color,
            'color': '#fff',
            'padding': '15px 25px',
            'border-radius': '5px',
            'z-index': '100000',
            'box-shadow': '0 4px 6px rgba(0,0,0,0.1)',
            'display': 'none'
        });
    
    jQuery('body').append(notify);
    notify.fadeIn().delay(3000).fadeOut(function() {
        jQuery(this).remove();
    });
}

// Event Listeners
jQuery(document).ready(function($) {
    // Search on change/keyup
    $('.FP_languages, .FP_categories, .FP_orderby, .FP_limit, .FP_offset').on('change', function() {
        FP_search_product();
    });

    $('.FP_keyword').on('keyup', function() {
        clearTimeout(window.fp_search_timeout);
        window.fp_search_timeout = setTimeout(FP_search_product, 500);
    });

    $('.FP_search_btn').on('click', function() {
        FP_search_product();
    });

    // Delegate clicks for dynamically loaded cards
    $(document).on('click', '.FPCardImg, .FPCardFoot', function(e) {
        FP_Open_Detail($(this).closest('.FPCard'));
    });

    $(document).on('click', '.FORapidImport', function(e) {
        e.stopPropagation();
        FP_Import_product($(this));
    });

    $(document).on('click', '.FPClose, .FPBackGroundSection', function() {
        FP_Close_Detail();
    });

    // Settings page handlers
    $(document).on('click', '.toggle-board', function() {
        var board = $(this).data('board');
        $('[board="' + board + '"]').slideToggle();
        $(this).toggleClass('dashicons-arrow-down dashicons-arrow-up');
    });

    $(document).on('click', '.dashicons-image-rotate', function() {
        var defaultValue = $(this).attr('data-default');
        var input = $(this).siblings('input, select, textarea');
        if (input.length) {
            input.val(defaultValue);
        }
    });

    // Initial search
    if ($('.FPMainContainer').length > 0 && !$('.AI_Generator').length) {
        FP_search_product();
    }

    // AI Generation
    $(document).on('click', '.AI_Generate_Btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var name = $('#ai_product_name').val();
        var context = $('#ai_product_context').val();

        if (!name) {
            FP_Notify('Inserisci almeno il nome del prodotto', 'error');
            return;
        }

        if ($btn.hasClass('fp-loading')) return;

        $btn.addClass('fp-loading').prop('disabled', true);
        var originalText = $btn.html();
        $btn.html('<span class="dashicons dashicons-update spin"></span> Generazione in corso...');

        $.ajax({
            url: fp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'fp_ai_generate_product',
                nonce: fp_ajax.nonce,
                name: name,
                context: context
            },
            success: function(response) {
                $btn.removeClass('fp-loading').prop('disabled', false).html(originalText);
                if (response.success) {
                    var data = response.data;
                    $('#out_post_title').val(data.post_title);
                    $('#out_post_excerpt').val(data.post_excerpt);
                    $('#out_fp_categories').val(data.fp_categories);
                    $('#out_fp_tag').val(data.fp_tag);
                    
                    // Update WP Editor (TinyMCE)
                    if (window.tinyMCE && tinyMCE.get('out_post_content')) {
                        tinyMCE.get('out_post_content').setContent(data.post_content);
                    } else {
                        $('#out_post_content').val(data.post_content);
                    }
                    
                    FP_Notify('Contenuto generato con successo!', 'success');
                } else {
                    FP_Notify(response.data.message, 'error');
                }
            },
            error: function() {
                $btn.removeClass('fp-loading').prop('disabled', false).html(originalText);
                FP_Notify('Errore durante la chiamata AI', 'error');
            }
        });
    });

    // AI Import
    $(document).on('click', '.AI_Import_Btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        
        // Sync TinyMCE content to textarea before serializing
        if (window.tinyMCE && tinyMCE.get('out_post_content')) {
            $('#out_post_content').val(tinyMCE.get('out_post_content').getContent());
        }

        var formData = $('#ai_product_form').serializeArray();
        var productData = {};
        $(formData).each(function(index, obj){
            productData[obj.name] = obj.value;
        });

        if (!productData.post_title) {
            FP_Notify('Il titolo è obbligatorio per l\'importazione', 'error');
            return;
        }

        if ($btn.hasClass('fp-loading')) return;

        $btn.addClass('fp-loading').prop('disabled', true);
        var originalText = $btn.html();
        $btn.html('<span class="dashicons dashicons-update spin"></span> Importazione...');

        $.ajax({
            url: fp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'fp_import_product',
                nonce: fp_ajax.nonce,
                product: productData
            },
            success: function(response) {
                $btn.removeClass('fp-loading').prop('disabled', false).html(originalText);
                if (response.success) {
                    FP_Notify(response.data.message, 'success');
                } else {
                    FP_Notify(response.data.message, 'error');
                }
            },
            error: function() {
                $btn.removeClass('fp-loading').prop('disabled', false).html(originalText);
                FP_Notify('Errore durante l\'importazione', 'error');
            }
        });
    });

    // AI Clear
    $(document).on('click', '.AI_Clear_Btn', function() {
        $('#ai_product_form')[0].reset();
        if (window.tinyMCE && tinyMCE.get('out_post_content')) {
            tinyMCE.get('out_post_content').setContent('');
        }
        $('#ai_product_name, #ai_product_context').val('');
    });
});






function FP_search_product(){
    var $container = jQuery(".FPContainer");
    var $found = jQuery(".FPfound");

    jQuery('.FPDetailSection').hide();
    jQuery('.FPBackGroundSection').hide();

    // Show loading state
    $container.css('opacity', '0.5');
    $found.html('<span class="dashicons dashicons-update spin"></span>');

    var data = {
        action: 'fp_search_products',
        nonce: fp_ajax.nonce,
        categories: jQuery(".FP_categories").val() || '',
        languages: jQuery(".FP_languages").val() || '',
        orderby: jQuery(".FP_orderby").val() || '',
        limit: jQuery(".FP_limit").val() || '',
        offset: jQuery(".FP_offset").val() || '',
        s: jQuery(".FP_keyword").val() || ''
    };

    jQuery.ajax({
        url: fp_ajax.ajax_url,
        type: 'GET',
        data: data,
        success: function(response) {
            $container.css('opacity', '1');
            if (response.success) {
                var clone = jQuery('.FPdefaultCard').clone();
                $container.empty().append(clone);
                $found.text(response.data.founds);
                FPloopProducts(response.data.result);
            } else {
                $found.text('Error');
                FP_Notify(response.data.message, 'error');
            }
        },
        error: function() {
            $container.css('opacity', '1');
            $found.text('Error');
            FP_Notify('Failed to fetch products', 'error');
        }
    });
}

function FPloopProducts( products ){
    if (!products) return;

    jQuery(products).each(function(i,e){
        var macro_categories = '';
        var product_cat = '';
        var product_tag = '';

        var copy = jQuery('.FPdefaultCard').clone();
        copy.removeClass('FPdefaultCard');

        copy.find('.FPCardTitle').text( e.post_title );

        copy.attr('fp_title', e.post_title);
        copy.attr('fp_short_title', e.short_title);
        copy.attr('fp_slang_title', e.slang_title);
        copy.attr('fp_description', e.post_content);
        copy.attr('fp_exerp', e.post_excerpt);
        copy.attr('fp_ingredient', e.Ingredienti);
        copy.attr('fp_allerg', e.Allergeni);
        copy.attr('fp_sticker', e.Sticker);
        copy.attr('fp_temp', e.Temperature);

        if (e.macro_categories) {
            jQuery(e.macro_categories).each(function(ind,ele){
                macro_categories += (macro_categories ? ',' : '') + ele.name;
            });
            copy.attr('fp_macro_cat', macro_categories);
        }
        if (e.product_cat) {
            jQuery(e.product_cat).each(function(ind,ele){
                product_cat += (product_cat ? ',' : '') + ele.name;
            });
            copy.attr('fp_categories', product_cat);
        }
        if (e.product_tag) {
            jQuery(e.product_tag).each(function(ind,ele){
                product_tag += (product_tag ? ',' : '') + ele.name;
            });
            copy.attr('fp_tag', product_tag);
        }

        if (e.thumbnail != '' && e.gallery && e.gallery.length > 0) {
            var thumbUrl = e.gallery[e.thumbnail] ? e.gallery[e.thumbnail].guid : e.gallery[0].guid;
            copy.find('.FPCardHead img').attr('src', thumbUrl);
            copy.attr('fp_img', thumbUrl);
        }

        jQuery(".FPContainer").append(copy);
    });
}




