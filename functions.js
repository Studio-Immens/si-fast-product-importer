
(function($) {
    'use strict';

    window.FP_Open_Detail = function(card) {
        $('.FPDetailSection').slideToggle();
        $('.FPBackGroundSection').show();

        $('.FPDetailTitle').text(card.attr('fp_title'));

        var blocks = ['short_title', 'slang_title', 'description', 'exerpt'];
        blocks.forEach(function(block) {
            var val = card.attr('fp_' + block);
            if (!val || val === '') {
                $('p[fp-block="' + (block === 'exerpt' ? 'exerpt' : block) + '"]').parent().hide();
            } else {
                $('p[fp-block="' + (block === 'exerpt' ? 'exerpt' : block) + '"]').parent().show();
                $('p[fp-block="' + (block === 'exerpt' ? 'exerpt' : block) + '"]').text(val);
            }
        });

        FP_Create_Detail_tax_cloud( card, 'fp_macro_cat' );
        FP_Create_Detail_tax_cloud( card, 'fp_categories' );
        FP_Create_Detail_tax_cloud( card, 'fp_tag' );
        FP_Create_Detail_tax_cloud( card, 'fp_ingredient' );
        FP_Create_Detail_tax_cloud( card, 'fp_allerg' );
        FP_Create_Detail_tax_cloud( card, 'fp_sticker' );
        FP_Create_Detail_tax_cloud( card, 'fp_temp' );

        $('.FPDetailBodyImages').empty();
        var thumb = card.attr('fp_img');
        $('.FPDetailBodyImages').append( '<img src="'+thumb+'">' );
    };

    window.FP_Close_Detail = function() {
        $('.FPDetailSection').slideToggle();
        $('.FPBackGroundSection').hide();
    };

    window.FP_Create_Detail_tax_cloud = function( card, key ){
        var container = $('div[fp-block="'+key+'"]');
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
    };

    window.FP_Import_product = function(btn, callback){
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

        $.ajax({
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
                    if (!callback) FP_Notify(response.data.message, 'success');
                    if (callback) callback(true);
                } else {
                    btn.html(originalHtml);
                    if (!callback) FP_Notify(response.data.message, 'error');
                    if (callback) callback(false);
                }
            },
            error: function() {
                btn.removeClass('fp-loading').html(originalHtml);
                if (!callback) FP_Notify(fp_ajax.strings.error_import, 'error');
                if (callback) callback(false);
            }
        });
    };

    window.FP_Notify = function(message, type) {
        var color = type === 'success' ? 'var(--fp-completed)' : 'var(--fp-error-color)';
        var notify = $('<div class="fp-notification"></div>')
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
        
        $('body').append(notify);
        notify.fadeIn().delay(3000).fadeOut(function() {
            $(this).remove();
        });
    };

    window.FP_search_product = function(){
        var $container = $(".FPContainer");
        var $found = $(".FPfound");

        $('.FPDetailSection').hide();
        $('.FPBackGroundSection').hide();
        $('.bulk-actions').fadeOut();
        $('#select_all_products').prop('checked', false);

        // Show loading state
        $container.css('opacity', '0.5');
        $found.html('<span class="dashicons dashicons-update spin"></span>');

        var data = {
            action: 'fp_search_products',
            nonce: fp_ajax.nonce,
            categories: $(".FP_categories").val() || '',
            languages: $(".FP_languages").val() || '',
            orderby: $(".FP_orderby").val() || '',
            limit: $(".FP_limit").val() || '',
            offset: $(".FP_offset").val() || '',
            s: $(".FP_keyword").val() || ''
        };

        $.ajax({
            url: fp_ajax.ajax_url,
            type: 'GET',
            data: data,
            success: function(response) {
                $container.css('opacity', '1');
                if (response.success) {
                    var clone = $('.FPdefaultCard').clone();
                    $container.empty().append(clone);
                    $found.text(response.data.founds);
                    FPloopProducts(response.data.result);
                } else {
                    $found.text(fp_ajax.strings.error);
                    FP_Notify(response.data.message, 'error');
                }
            },
            error: function() {
                $container.css('opacity', '1');
                $found.text(fp_ajax.strings.error);
                FP_Notify(fp_ajax.strings.fetch_error, 'error');
            }
        });
    };

    window.FPloopProducts = function( products ){
        if (!products || products.length === 0) {
            $('.bulk-actions').fadeOut();
            return;
        }

        $('.bulk-actions').fadeIn();

        $(products).each(function(i,e){
            var macro_categories = '';
            var product_cat = '';
            var product_tag = '';

            var copy = $('.FPdefaultCard').clone();
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
                $(e.macro_categories).each(function(ind,ele){
                    macro_categories += (macro_categories ? ',' : '') + ele.name;
                });
                copy.attr('fp_macro_cat', macro_categories);
            }
            if (e.product_cat) {
                $(e.product_cat).each(function(ind,ele){
                    product_cat += (product_cat ? ',' : '') + ele.name;
                });
                copy.attr('fp_categories', product_cat);
            }
            if (e.product_tag) {
                $(e.product_tag).each(function(ind,ele){
                    product_tag += (product_tag ? ',' : '') + ele.name;
                });
                copy.attr('fp_tag', product_tag);
            }

            if (e.thumbnail != '' && e.gallery && e.gallery.length > 0) {
                var thumbUrl = e.gallery[e.thumbnail] ? e.gallery[e.thumbnail].guid : e.gallery[0].guid;
                copy.find('.FPCardHead img').attr('src', thumbUrl);
                copy.attr('fp_img', thumbUrl);
            }

            $(".FPContainer").append(copy);
        });
    };

    // Event Listeners
    $(document).ready(function() {
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

    // Selection logic
    $(document).on('change', '.fp-product-select', function() {
        var $card = $(this).closest('.FPCard');
        if ($(this).is(':checked')) {
            $card.addClass('selected');
        } else {
            $card.removeClass('selected');
            $('#select_all_products').prop('checked', false);
        }
        updateBulkActionUI();
    });

    $(document).on('change', '#select_all_products', function() {
        var isChecked = $(this).is(':checked');
        $('.FPCard:not(.FPdefaultCard) .fp-product-select').prop('checked', isChecked).trigger('change');
    });

    function updateBulkActionUI() {
        var selectedCount = $('.FPCard:not(.FPdefaultCard) .fp-product-select:checked').length;
        if (selectedCount > 0) {
            $('.bulk-actions').fadeIn();
            $('.selected-count').text(selectedCount);
            $('.FP_bulk_import_btn').prop('disabled', false);
        } else {
            $('.FP_bulk_import_btn').prop('disabled', true);
            if ($('.FPCard:not(.FPdefaultCard)').length === 0) {
                $('.bulk-actions').fadeOut();
            }
        }
    }

    $(document).on('click', '.FP_bulk_import_btn', function() {
        var $selectedCheckboxes = $('.FPCard:not(.FPdefaultCard) .fp-product-select:checked');
        var total = $selectedCheckboxes.length;
        
        if (total === 0) return;
        
        if (!confirm(fp_ajax.strings.confirm_bulk_import.replace('%d', total))) return;

        var $btn = $(this);
        var originalHtml = $btn.html();
        $btn.addClass('fp-loading').prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> ' + fp_ajax.strings.importing);

        var imported = 0;
        var failed = 0;

        function importNext(index) {
            if (index >= total) {
                $btn.removeClass('fp-loading').prop('disabled', false).html(originalHtml);
                var msg = fp_ajax.strings.bulk_import_done.replace('%d', imported).replace('%d', failed);
                FP_Notify(msg, imported > 0 ? 'success' : 'error');
                $('#select_all_products').prop('checked', false);
                $('.fp-product-select').prop('checked', false).trigger('change');
                return;
            }

            var $checkbox = $($selectedCheckboxes[index]);
            var $card = $checkbox.closest('.FPCard');
            var $importBtn = $card.find('.FORapidImport');

            $card.addClass('importing');

            // Reuse existing single import logic but with a callback
            FP_Import_product($importBtn, function(success) {
                $card.removeClass('importing');
                if (success) {
                    imported++;
                    $card.addClass('import-success');
                } else {
                    failed++;
                    $card.addClass('import-error');
                }
                
                $btn.html('<span class="dashicons dashicons-update spin"></span> ' + (index + 1) + '/' + total);
                importNext(index + 1);
            });
        }

        importNext(0);
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

    $(document).on('click', '.clear-logs-btn', function() {
        if (!confirm(fp_ajax.strings.confirm_clear_logs)) return;
        
        var $btn = $(this);
        $btn.addClass('fp-loading').prop('disabled', true);
        
        $.ajax({
            url: fp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'fp_clear_logs',
                nonce: fp_ajax.nonce
            },
            success: function(response) {
                $btn.removeClass('fp-loading').prop('disabled', false);
                if (response.success) {
                    $('.FPLogTableContainer tbody').html('<tr><td colspan="3" style="text-align:center;">' + response.data.message + '</td></tr>');
                    FP_Notify(response.data.message, 'success');
                } else {
                    FP_Notify(response.data.message, 'error');
                }
            },
            error: function() {
                $btn.removeClass('fp-loading').prop('disabled', false);
                FP_Notify(fp_ajax.strings.error_clear_logs, 'error');
            }
        });
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
            FP_Notify(fp_ajax.strings.error_missing_name, 'error');
            return;
        }

        if ($btn.hasClass('fp-loading')) return;

        $btn.addClass('fp-loading').prop('disabled', true);
        var originalText = $btn.html();
        $btn.html('<span class="dashicons dashicons-update spin"></span> ' + fp_ajax.strings.generating);

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
                    
                    // New WooCommerce fields
                    $('#out_regular_price').val(data.regular_price);
                    $('#out_sale_price').val(data.sale_price);
                    $('#out_sku').val(data.sku);
                    $('#out_stock_status').val(data.stock_status || 'instock');
                    $('#out_stock_qty').val(data.stock_qty || 10);
                    $('#out_weight').val(data.weight);
                    $('#out_length').val(data.length);
                    $('#out_width').val(data.width);
                    $('#out_height').val(data.height);
                    
                    // Handle gallery from AI
                    if (data.fp_gallery) {
                        $('#out_fp_gallery').val(data.fp_gallery).trigger('change');
                    }

                    // Handle attributes from AI
                    var $container = $('#attributes_container');
                    $container.find('.AttributeRow:not(.attribute-row-template .AttributeRow)').remove();
                    if (data.attributes && Array.isArray(data.attributes)) {
                        data.attributes.forEach(function(attr) {
                            var $template = $($container.find('.attribute-row-template').html());
                            $template.find('.attr-name').val(attr.name);
                            $template.find('.attr-values').val(attr.values);
                            $container.append($template);
                        });
                    }

                    // Update WP Editor (TinyMCE)
                    if (window.tinyMCE && tinyMCE.get('out_post_content')) {
                        tinyMCE.get('out_post_content').setContent(data.post_content);
                    } else {
                        $('#out_post_content').val(data.post_content);
                    }
                    
                    FP_Notify(fp_ajax.strings.ai_gen_success, 'success');
                } else {
                    FP_Notify(response.data.message, 'error');
                }
            },
            error: function() {
                $btn.removeClass('fp-loading').prop('disabled', false).html(originalText);
                FP_Notify(fp_ajax.strings.error_ai_call, 'error');
            }
        });
    });

    // Product Attributes Logic
    $(document).on('click', '#add_attribute_btn', function() {
        var $container = $('#attributes_container');
        var $template = $container.find('.attribute-row-template').html();
        $container.append($template);
    });

    /**
     * Handles the removal of an attribute row.
     * @param {jQuery} $btn - The button element that was clicked.
     */
    function removeAttributeRow($btn) {
        var $row = $btn.closest('.AttributeRow');
        if ($('.AttributeRow').length > 1) {
            $row.fadeOut(300, function() {
                $(this).remove();
            });
        } else {
            $row.find('input').val('');
            FP_Notify(fp_ajax.strings.attr_limit_reached, 'info');
        }
    }

    $(document).on('click', '.remove-attribute-btn', function() {
        removeAttributeRow($(this));
    });

    // Handle attribute data in import
    function getProductAttributes() {
        var attributes = [];
        $('.AttributeRow:not(.attribute-row-template .AttributeRow)').each(function() {
            var name = $(this).find('.attr-name').val().trim();
            var values = $(this).find('.attr-values').val().trim();
            if (name && values) {
                attributes.push({
                    name: name,
                    values: values
                });
            }
        });
        return attributes;
    }

    // AI Import
    $(document).on('click', '.AI_Import_Btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        
        // Basic validation
        var title = $('#out_post_title').val();
        if (!title) {
            $('#out_post_title').closest('.FormField').addClass('has-error');
            FP_Notify(fp_ajax.strings.error_missing_name, 'error');
            return;
        } else {
            $('#out_post_title').closest('.FormField').removeClass('has-error');
        }

        // Sync TinyMCE content to textarea before serializing
        if (window.tinyMCE && tinyMCE.get('out_post_content')) {
            $('#out_post_content').val(tinyMCE.get('out_post_content').getContent());
        }

        var formData = $('#ai_product_form').serializeArray();
        var productData = {};
        $(formData).each(function(index, obj){
            productData[obj.name] = obj.value;
        });

        // Add attributes
        productData['attributes'] = getProductAttributes();

        if (!productData.post_title) {
            FP_Notify(fp_ajax.strings.error_missing_name, 'error');
            return;
        }

        if ($btn.hasClass('fp-loading')) return;

        $btn.addClass('fp-loading').prop('disabled', true);
        var originalText = $btn.html();
        $btn.html('<span class="dashicons dashicons-update spin"></span> ' + fp_ajax.strings.importing);

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
                FP_Notify(fp_ajax.strings.error_import, 'error');
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
        $('#img_preview_container').css('background-image', 'none');
    });

    // Image Preview
    $('#out_fp_img').on('input change', function() {
        var url = $(this).val();
        if (url) {
            $('#img_preview_container').css('background-image', 'url(' + url + ')');
        } else {
            $('#img_preview_container').css('background-image', 'none');
        }
    });

    // Presets
    $('.PresetBtn[data-preset]').on('click', function() {
        var preset = $(this).data('preset');
        var now = new Date();
        var skuPrefix = fp_ajax.sku_prefix || 'PROD-';
        var defaultStock = fp_ajax.default_stock || '10';
        var skuBase = skuPrefix + now.getTime().toString().slice(-6);

        switch(preset) {
            case 'simple':
                $('#out_post_title').val('Simple Product Draft');
                $('#out_regular_price').val('19.90');
                $('#out_sku').val(skuBase);
                $('#out_stock_status').val('instock');
                $('#out_stock_qty').val(defaultStock);
                $('#out_is_virtual, #out_is_downloadable').prop('checked', false);
                break;
            case 'physical':
                $('#out_post_title').val('New Physical Product');
                $('#out_regular_price').val('49.00');
                $('#out_sku').val(skuBase);
                $('#out_stock_qty').val(defaultStock);
                $('#out_weight').val('1.5');
                $('#out_length').val('20');
                $('#out_width').val('15');
                $('#out_height').val('10');
                $('#out_is_virtual, #out_is_downloadable').prop('checked', false);
                break;
            case 'premium':
                $('#out_post_title').val('Premium Gold Product');
                $('#out_regular_price').val('299.00');
                $('#out_sale_price').val('249.00');
                $('#out_sku').val('PREM-' + skuBase.replace(skuPrefix, ''));
                $('#out_stock_qty').val('5');
                $('#out_is_virtual, #out_is_downloadable').prop('checked', false);
                break;
            case 'virtual':
                $('#out_post_title').val('Virtual Service');
                $('#out_regular_price').val('99.00');
                $('#out_sku').val('VIRT-' + skuBase.replace(skuPrefix, ''));
                $('#out_is_virtual').prop('checked', true);
                $('#out_is_downloadable').prop('checked', false);
                $('#out_weight, #out_length, #out_width, #out_height').val('');
                break;
            case 'downloadable':
                $('#out_post_title').val('Ebook / Digital Product');
                $('#out_regular_price').val('29.00');
                $('#out_sku').val('DIGI-' + skuBase.replace(skuPrefix, ''));
                $('#out_is_virtual').prop('checked', true);
                $('#out_is_downloadable').prop('checked', true);
                $('#out_weight, #out_length, #out_width, #out_height').val('');
                break;
        }
        FP_Notify(fp_ajax.strings.preset_loaded, 'success');
    });

    // Media Uploader
    var media_uploader;
    $('#select_image_btn').on('click', function(e) {
        e.preventDefault();

        if (media_uploader) {
            media_uploader.open();
            return;
        }

        media_uploader = wp.media({
            title: fp_ajax.strings.select_image,
            button: {
                text: fp_ajax.strings.use_image
            },
            multiple: false
        });

        media_uploader.on('select', function() {
            var attachment = media_uploader.state().get('selection').first().toJSON();
            $('#out_fp_img').val(attachment.url).trigger('change');
        });

        media_uploader.open();
    });

    // Gallery Uploader
    var gallery_uploader;
    $('#select_gallery_btn').on('click', function(e) {
        e.preventDefault();

        if (gallery_uploader) {
            gallery_uploader.open();
            return;
        }

        gallery_uploader = wp.media({
            title: fp_ajax.strings.select_gallery,
            button: {
                text: fp_ajax.strings.add_to_gallery
            },
            multiple: true
        });

        gallery_uploader.on('select', function() {
            var selection = gallery_uploader.state().get('selection');
            var current_gallery = $('#out_fp_gallery').val() ? $('#out_fp_gallery').val().split(',') : [];
            
            selection.map(function(attachment) {
                attachment = attachment.toJSON();
                if (current_gallery.indexOf(attachment.url) === -1) {
                    current_gallery.push(attachment.url);
                }
            });

            $('#out_fp_gallery').val(current_gallery.join(',')).trigger('change');
        });

        gallery_uploader.open();
    });

    // Gallery Preview Update
    $('#out_fp_gallery').on('change', function() {
        var urls = $(this).val() ? $(this).val().split(',') : [];
        var $container = $('#gallery_preview_container');
        $container.empty();

        urls.forEach(function(url) {
            if (!url) return;
            var $item = $('<div class="gallery-item"><img src="' + url + '"><span class="remove-gallery-item" data-url="' + url + '">&times;</span></div>');
            $container.append($item);
        });
    });

    // Remove Gallery Item
    $(document).on('click', '.remove-gallery-item', function() {
        var urlToRemove = $(this).data('url');
        var current_gallery = $('#out_fp_gallery').val().split(',');
        var updated_gallery = current_gallery.filter(function(url) {
            return url !== urlToRemove;
        });
        $('#out_fp_gallery').val(updated_gallery.join(',')).trigger('change');
    });

    // Taxonomy Autocomplete
    var fp_tax_timeout;
    $(document).on('keyup', '.TaxonomySelector input', function() {
        var $input = $(this);
        var $results = $input.siblings('.AutocompleteResults');
        var tax = $input.closest('.TaxonomySelector').data('tax');
        var query = $input.val().split(',').pop().trim();

        clearTimeout(fp_tax_timeout);

        if (query.length < 2) {
            $results.hide().empty();
            return;
        }

        fp_tax_timeout = setTimeout(function() {
            $.ajax({
                url: fp_ajax.ajax_url,
                type: 'GET',
                data: {
                    action: 'fp_search_terms',
                    nonce: fp_ajax.nonce,
                    taxonomy: tax,
                    q: query
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        $results.empty().show();
                        response.data.forEach(function(term) {
                            $results.append('<div class="autocomplete-item" data-val="' + term.name + '">' + term.name + '</div>');
                        });
                    } else {
                        $results.hide().empty();
                    }
                }
            });
        }, 300);
    });

    $(document).on('click', '.autocomplete-item', function() {
        var $item = $(this);
        var val = $item.data('val');
        var $input = $item.closest('.TaxonomySelector').find('input');
        var currentVal = $input.val();
        var terms = currentVal.split(',').map(s => s.trim());
        
        // Remove the last partial term and add the selected one
        terms.pop();
        if (terms.indexOf(val) === -1) {
            terms.push(val);
        }
        
        $input.val(terms.filter(t => t !== '').join(', ') + (terms.length > 0 ? ', ' : ''));
        $item.parent().hide().empty();
        $input.focus();
    });

    // Close autocomplete on click outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.TaxonomySelector').length) {
            $('.AutocompleteResults').hide().empty();
        }
    });

    // Sync Badges on input change (Removed as we use autocomplete now)
    });

})(jQuery);




