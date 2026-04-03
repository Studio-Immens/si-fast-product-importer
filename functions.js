
(function($) {
    'use strict';

    window.sifp_open_detail = function(card) {
        $('.sifp-detail-section').slideToggle();
        $('.sifp-background-section').show();

        // Populate basic fields
        $('.sifp-detail-title').val(card.attr('fp_title'));

        // Taxonomies and extra fields
        var fields = [
            'fp_categories', 'fp_tag', 'fp_ingredient', 'fp_allerg', 
            'fp_sticker', 'fp_temp', 'sku', 'regular_price', 'sale_price'
        ];

        fields.forEach(function(field) {
            var attrKey = field.startsWith('fp_') ? field : 'fp_' + field;
            var value = card.attr(attrKey) || '';
            var $input = $('input[fp-edit="' + field + '"]');
            $input.val(value);
            
            // Show/Hide container based on value
            var $container = $input.closest('.sifp-detail-block');
            if (value === '') {
                $container.hide();
            } else {
                $container.show();
            }
        });

        // Handle textareas (excerpt and content)
        var textareas = ['post_excerpt', 'post_content'];
        textareas.forEach(function(field) {
            var attrKey = field === 'post_excerpt' ? 'fp_exerp' : 'fp_description';
            var value = card.attr(attrKey) || '';
            var $textarea = $('textarea[fp-edit="' + field + '"]');
            $textarea.val(value);
            
            var $container = $textarea.closest('.sifp-detail-block');
            if (value === '') {
                $container.hide();
            } else {
                $container.show();
            }
        });

        // Store card reference for import
        $('.sifp-detail-section').data('source-card', card);

        $('.sifp-detail-body-images').empty();
        var thumb = card.attr('fp_img');
        $('.sifp-detail-body-images').append( '<img src="'+thumb+'">' );
    };

    window.sifp_import_edited_product = function() {
        var $modal = $('.sifp-detail-section');
        var $btn = $('.sifp-import-edited-btn');
        var card = $modal.data('source-card');

        if ($btn.hasClass('fp-loading')) return;
        $btn.addClass('fp-loading').html('<span class="dashicons dashicons-update spin"></span> Importing...');

        var productData = {
            post_title: $('input[fp-edit="post_title"]').val(),
            post_content: $('textarea[fp-edit="post_content"]').val(),
            post_excerpt: $('textarea[fp-edit="post_excerpt"]').val(),
            fp_categories: $('input[fp-edit="fp_categories"]').val(),
            fp_tag: $('input[fp-edit="fp_tag"]').val(),
            fp_img: card.attr('fp_img'),
            fp_gallery: card.attr('fp_gallery'),
            regular_price: $('input[fp-edit="regular_price"]').val(),
            sale_price: $('input[fp-edit="sale_price"]').val(),
            sku: $('input[fp-edit="sku"]').val(),
            stock_qty: card.attr('fp_stock_qty'),
            weight: card.attr('fp_weight'),
            length: card.attr('fp_length'),
            width: card.attr('fp_width'),
            height: card.attr('fp_height'),
            attributes: card.attr('fp_attributes') ? JSON.parse(card.attr('fp_attributes')) : [],
            custom_taxonomy: card.attr('fp_custom_taxonomy') ? JSON.parse(card.attr('fp_custom_taxonomy')) : {},
            // Add specific editable fields to custom taxonomy if they were there
            fp_ingredient: $('input[fp-edit="fp_ingredient"]').val(),
            fp_allerg: $('input[fp-edit="fp_allerg"]').val(),
            fp_sticker: $('input[fp-edit="fp_sticker"]').val(),
            fp_temp: $('input[fp-edit="fp_temp"]').val()
        };

        $.ajax({
            url: sifp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'sifp_import_product',
                nonce: sifp_ajax.nonce,
                product: productData
            },
            success: function(response) {
                $btn.removeClass('fp-loading').html('<span class="dashicons dashicons-download"></span> Import with Edited Values');
                if (response.success) {
                    sifp_notify(response.data.message, 'success');
                    sifp_close_detail();
                    // Optionally update the card in the UI to reflect changes
                    card.find('.sifp-card-title').text(productData.post_title);
                } else {
                    sifp_notify(response.data.message, 'error');
                }
            },
            error: function() {
                $btn.removeClass('fp-loading').html('<span class="dashicons dashicons-download"></span> Import with Edited Values');
                sifp_notify(sifp_ajax.strings.error_import, 'error');
            }
        });
    };

    window.sifp_close_detail = function() {
        $('.sifp-detail-section').slideToggle();
        $('.sifp-background-section').hide();
    };

    window.sifp_create_detail_tax_cloud = function( card, key ){
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

    window.sifp_import_bulk = function() {
        var $selectedCards = $('.sifp-card.selected');
        if ($selectedCards.length === 0) return;

        if (!confirm(sifp_ajax.strings.confirm_bulk + ' (' + $selectedCards.length + ')')) return;

        var $btn = $('.sifp-bulk-import-btn');
        var originalText = $btn.text();
        $btn.prop('disabled', true).text(sifp_ajax.strings.importing + '...');

        var importedCount = 0;
        var totalToImport = $selectedCards.length;

        function importNext(index) {
            if (index >= totalToImport) {
                $btn.prop('disabled', false).text(originalText);
                sifp_notify(importedCount + ' ' + sifp_ajax.strings.bulk_success, 'success');
                $('.bulk-actions').fadeOut();
                $('#select_all_products').prop('checked', false);
                $('.sifp-card').removeClass('selected').find('.fp-select-product').prop('checked', false);
                return;
            }

            var $card = $($selectedCards[index]);
            var $importBtn = $card.find('.sifp-import-btn');

            sifp_import_product($importBtn, function(success) {
                if (success) importedCount++;
                importNext(index + 1);
            });
        }

        importNext(0);
    };

    window.sifp_import_product = function(btn, callback){
        var card = btn.closest('.sifp-card');
        var originalHtml = btn.html();

        if (btn.hasClass('fp-loading')) return;

        btn.addClass('fp-loading').html('<span class="dashicons dashicons-update spin"></span>');

        var productData = {
            post_title: card.attr('fp_title'),
            post_content: card.attr('fp_description'),
            post_excerpt: card.attr('fp_exerp'),
            fp_categories: card.attr('fp_categories'),
            fp_tag: card.attr('fp_tag'),
            fp_img: card.attr('fp_img'),
            fp_gallery: card.attr('fp_gallery'),
            regular_price: card.attr('fp_regular_price'),
            sale_price: card.attr('fp_sale_price'),
            sku: card.attr('fp_sku'),
            stock_qty: card.attr('fp_stock_qty'),
            weight: card.attr('fp_weight'),
            length: card.attr('fp_length'),
            width: card.attr('fp_width'),
            height: card.attr('fp_height'),
            attributes: card.attr('fp_attributes') ? JSON.parse(card.attr('fp_attributes')) : [],
            custom_taxonomy: card.attr('fp_custom_taxonomy') ? JSON.parse(card.attr('fp_custom_taxonomy')) : {}
        };

        $.ajax({
            url: sifp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'sifp_import_product',
                nonce: sifp_ajax.nonce,
                product: productData
            },
            success: function(response) {
                btn.removeClass('fp-loading');
                if (response.success) {
                    btn.html('<span class="dashicons dashicons-yes"></span>').css('background-color', 'var(--fp-completed)');
                    if (!callback) sifp_notify(response.data.message, 'success');
                    if (callback) callback(true);
                } else {
                    btn.html(originalHtml);
                    if (!callback) sifp_notify(response.data.message, 'error');
                    if (callback) callback(false);
                }
            },
            error: function() {
                btn.removeClass('fp-loading').html(originalHtml);
                if (!callback) sifp_notify(sifp_ajax.strings.error_import, 'error');
                if (callback) callback(false);
            }
        });
    };

    window.sifp_notify = function(message, type) {
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

    window.sifp_search_product = function(){
        var $container = $(".sifp-container");
        var $found = $(".sifp-found");

        $('.sifp-detail-section').hide();
        $('.sifp-background-section').hide();
        $('.bulk-actions').fadeOut();
        $('#select_all_products').prop('checked', false);
        $('.selected-count').text('0');

        // Show loading state
        $container.css('opacity', '0.5');
        $found.html('<span class="dashicons dashicons-update spin"></span>');

        var limit = parseInt($(".sifp-limit").val()) || 100;
        var offset = parseInt($(".sifp-offset").val()) || 0;

        var data = {
            action: 'sifp_search_products',
            nonce: sifp_ajax.nonce,
            categories: $(".sifp-categories").val() || '',
            languages: $(".sifp-languages").val() || '',
            source: $(".sifp-source").val() || 'all',
            orderby: $(".sifp-orderby").val() || '',
            limit: limit,
            offset: offset,
            s: $(".sifp-keyword").val() || ''
        };

        $.ajax({
            url: sifp_ajax.ajax_url,
            type: 'GET',
            data: data,
            success: function(response) {
                $container.css('opacity', '1');
                if (response.success) {
                    var clone = $('.sifp-default-card').clone();
                    $container.empty().append(clone);
                    $found.text(response.data.total_results);
                    sifp_loop_products(response.data.result);
                } else {
                    $found.text(sifp_ajax.strings.error);
                    sifp_notify(response.data.message, 'error');
                }
            },
            error: function() {
                $container.css('opacity', '1');
                $found.text(sifp_ajax.strings.error);
                sifp_notify(sifp_ajax.strings.fetch_error, 'error');
            }
        });
    };

    window.sifp_loop_products = function( products ){
        if (!products || products.length === 0) {
            $('.bulk-actions').fadeOut();
            return;
        }

        $('.bulk-actions').fadeIn();

        $(products).each(function(i,e){
            var macro_categories = '';
            var product_cat = '';
            var product_tag = '';

            var copy = $('.sifp-default-card').clone();
            copy.removeClass('sifp-default-card');

            copy.find('.sifp-card-title').text( e.post_title );

            // Generic Attributes
            copy.attr('fp_title', e.post_title);
            copy.attr('fp_short_title', e.short_title || '');
            copy.attr('fp_slang_title', e.slang_title || '');
            copy.attr('fp_description', e.post_content);
            copy.attr('fp_exerp', e.post_excerpt);
            copy.attr('fp_ingredient', e.fp_ingredient || e.Ingredienti || '');
            copy.attr('fp_allerg', e.fp_allerg || e.Allergeni || '');
            copy.attr('fp_sticker', e.fp_sticker || e.Sticker || '');
            copy.attr('fp_temp', e.fp_temp || e.Temperature || '');
            
            // New WooCommerce Attributes
            copy.attr('fp_regular_price', e.regular_price || '');
            copy.attr('fp_sale_price', e.sale_price || '');
            copy.attr('fp_sku', e.sku || '');
            copy.attr('fp_stock_qty', e.stock_qty || '');
            copy.attr('fp_weight', e.weight || '');
            copy.attr('fp_length', e.length || '');
            copy.attr('fp_width', e.width || '');
            copy.attr('fp_height', e.height || '');
            copy.attr('fp_attributes', e.attributes ? JSON.stringify(e.attributes) : '');
            copy.attr('fp_custom_taxonomy', e.custom_taxonomy ? JSON.stringify(e.custom_taxonomy) : '');

            // Remote vs Local taxonomies
            if (e.macro_categories) {
                if (typeof e.macro_categories === 'string') {
                    macro_categories = e.macro_categories;
                } else {
                    $(e.macro_categories).each(function(ind,ele){
                        macro_categories += (macro_categories ? ',' : '') + ele.name;
                    });
                }
                copy.attr('fp_macro_cat', macro_categories);
            }
            
            if (e.fp_categories || e.product_cat) {
                var cats = e.fp_categories || e.product_cat;
                if (typeof cats === 'string') {
                    product_cat = cats;
                } else {
                    $(cats).each(function(ind,ele){
                        product_cat += (product_cat ? ',' : '') + ele.name;
                    });
                }
                copy.attr('fp_categories', product_cat);
            }

            if (e.fp_tag || e.product_tag) {
                var tags = e.fp_tag || e.product_tag;
                if (typeof tags === 'string') {
                    product_tag = tags;
                } else {
                    $(tags).each(function(ind,ele){
                        product_tag += (product_tag ? ',' : '') + ele.name;
                    });
                }
                copy.attr('fp_tag', product_tag);
            }

            // Image handling
            var thumbUrl = '';
            if (e.fp_img) {
                thumbUrl = e.fp_img;
            } else if (e.thumbnail != '' && e.gallery && e.gallery.length > 0) {
                thumbUrl = e.gallery[e.thumbnail] ? e.gallery[e.thumbnail].guid : e.gallery[0].guid;
            }
            
            if (thumbUrl) {
                copy.find('.sifp-card-head img').attr('src', thumbUrl);
                copy.attr('fp_img', thumbUrl);
            }

            if (e.fp_gallery) {
                copy.attr('fp_gallery', e.fp_gallery);
            }

            $(".sifp-container").append(copy);
        });
    };

    // Event Listeners
    $(document).ready(function() {
    
    // Selection management
    $(document).on('change', '.fp-select-product', function() {
        $(this).closest('.sifp-card').toggleClass('selected', this.checked);
        var count = $('.sifp-card.selected').length;
        $('.selected-count').text(count);
    });

    $('#select_all_products').on('change', function() {
        var checked = this.checked;
        $('.sifp-card').not('.sifp-default-card').each(function() {
            $(this).toggleClass('selected', checked);
            $(this).find('.fp-select-product').prop('checked', checked);
        });
        $('.selected-count').text($('.sifp-card.selected').length);
    });

    $('.sifp-bulk-import-btn').on('click', function() {
        sifp_import_bulk();
    });
    // Search on change/keyup
    $('.sifp-languages, .sifp-categories, .sifp-source, .sifp-orderby, .sifp-limit, .sifp-offset').on('change', function() {
        sifp_search_product();
    });

    $('.sifp-keyword').on('keyup', function() {
        clearTimeout(window.sifp_search_timeout);
        window.sifp_search_timeout = setTimeout(sifp_search_product, 500);
    });

    $('.sifp-search-btn').on('click', function() {
        sifp_search_product();
    });

    $(document).on('click', '.sifp-button--import-edited', function() {
        sifp_import_edited_product();
    });

    // Delegate clicks for dynamically loaded cards
    $(document).on('click', '.sifp-card-head, .sifp-card-foot', function(e) {
        sifp_open_detail($(this).closest('.sifp-card'));
    });

    $(document).on('click', '.sifp-rapid-import', function(e) {
        e.stopPropagation();
        sifp_import_product($(this));
    });

    $(document).on('click', '.sifp-button--close, .sifp-modal-overlay', function() {
        sifp_close_detail();
    });

    // Selection logic
    $(document).on('change', '.fp-product-select', function() {
        var $card = $(this).closest('.sifp-card');
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
        $('.sifp-card:not(.sifp-default-card) .fp-product-select').prop('checked', isChecked).trigger('change');
    });

    function updateBulkActionUI() {
        var selectedCount = $('.sifp-card:not(.sifp-default-card) .fp-product-select:checked').length;
        if (selectedCount > 0) {
            $('.bulk-actions').fadeIn();
            $('.selected-count').text(selectedCount);
            $('.sifp-bulk-import-btn').prop('disabled', false);
        } else {
            $('.sifp-bulk-import-btn').prop('disabled', true);
            if ($('.sifp-card:not(.sifp-default-card)').length === 0) {
                $('.bulk-actions').fadeOut();
            }
        }
    }

    $(document).on('click', '.sifp-bulk-import-btn', function() {
        var $selectedCheckboxes = $('.sifp-card:not(.sifp-default-card) .fp-product-select:checked');
        var total = $selectedCheckboxes.length;
        
        if (total === 0) return;
        
        if (!confirm(sifp_ajax.strings.confirm_bulk_import.replace('%d', total))) return;

        var $btn = $(this);
        var originalHtml = $btn.html();
        $btn.addClass('sifp-loading').prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> ' + sifp_ajax.strings.importing);

        var imported = 0;
        var failed = 0;

        function importNext(index) {
            if (index >= total) {
                $btn.removeClass('sifp-loading').prop('disabled', false).html(originalHtml);
                var msg = sifp_ajax.strings.bulk_import_done.replace('%d', imported).replace('%d', failed);
                sifp_notify(msg, imported > 0 ? 'success' : 'error');
                $('#select_all_products').prop('checked', false);
                $('.fp-product-select').prop('checked', false).trigger('change');
                return;
            }

            var $checkbox = $($selectedCheckboxes[index]);
            var $card = $checkbox.closest('.sifp-card');
            var $importBtn = $card.find('.sifp-rapid-import');

            $card.addClass('importing');

            // Reuse existing single import logic but with a callback
            sifp_import_product($importBtn, function(success) {
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
        if (!confirm(sifp_ajax.strings.confirm_clear_logs)) return;
        
        var $btn = $(this);
        $btn.addClass('sifp-loading').prop('disabled', true);
        
        $.ajax({
            url: sifp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'sifp_clear_logs',
                nonce: sifp_ajax.nonce
            },
            success: function(response) {
                $btn.removeClass('sifp-loading').prop('disabled', false);
                if (response.success) {
                    $('.sifp-log-table-container tbody').html('<tr><td colspan="3" style="text-align:center;">' + response.data.message + '</td></tr>');
                    sifp_notify(response.data.message, 'success');
                } else {
                    sifp_notify(response.data.message, 'error');
                }
            },
            error: function() {
                $btn.removeClass('sifp-loading').prop('disabled', false);
                sifp_notify(sifp_ajax.strings.error_clear_logs, 'error');
            }
        });
    });

    // Initial search
    if ($('.sifp-main-container').length > 0 && !$('.sifp-generator-section').length) {
        sifp_search_product();
    }

    // AI Generation
    $(document).on('click', '.sifp-button--ai-generate', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var name = $('#ai_product_name').val();
        var context = $('#ai_product_context').val();

        if (!name) {
            sifp_notify(sifp_ajax.strings.error_missing_name, 'error');
            return;
        }

        if ($btn.hasClass('sifp-loading')) return;

        $btn.addClass('sifp-loading').prop('disabled', true);
        var originalText = $btn.html();
        $btn.html('<span class="dashicons dashicons-update spin"></span> ' + sifp_ajax.strings.generating);

        $.ajax({
            url: sifp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'sifp_ai_generate_product',
                nonce: sifp_ajax.nonce,
                name: name,
                context: context
            },
            success: function(response) {
                $btn.removeClass('sifp-loading').prop('disabled', false).html(originalText);
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
                    
                    sifp_notify(sifp_ajax.strings.ai_gen_success, 'success');
                } else {
                    sifp_notify(response.data.message, 'error');
                }
            },
            error: function() {
                $btn.removeClass('sifp-loading').prop('disabled', false).html(originalText);
                sifp_notify(sifp_ajax.strings.error_ai_call, 'error');
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
    $(document).on('click', '.sifp-button--ai-import', function(e) {
        e.preventDefault();
        var $btn = $(this);
        
        // Basic validation
        var title = $('#out_post_title').val();
        if (!title) {
            $('#out_post_title').closest('.sifp-form-field').addClass('has-error');
            sifp_notify(sifp_ajax.strings.error_missing_name, 'error');
            return;
        } else {
            $('#out_post_title').closest('.sifp-form-field').removeClass('has-error');
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
            sifp_notify(sifp_ajax.strings.error_missing_name, 'error');
            return;
        }

        if ($btn.hasClass('sifp-loading')) return;

        $btn.addClass('sifp-loading').prop('disabled', true);
        var originalText = $btn.html();
        $btn.html('<span class="dashicons dashicons-update spin"></span> ' + sifp_ajax.strings.importing);

        $.ajax({
            url: sifp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'sifp_import_product',
                nonce: sifp_ajax.nonce,
                product: productData
            },
            success: function(response) {
                $btn.removeClass('sifp-loading').prop('disabled', false).html(originalText);
                if (response.success) {
                    sifp_notify(response.data.message, 'success');
                } else {
                    sifp_notify(response.data.message, 'error');
                }
            },
            error: function() {
                $btn.removeClass('sifp-loading').prop('disabled', false).html(originalText);
                sifp_notify(sifp_ajax.strings.error_import, 'error');
            }
        });
    });

    // AI Clear
    $(document).on('click', '.sifp-button--ai-clear', function() {
        $('#ai_product_form')[0].reset();
        if (window.tinyMCE && tinyMCE.get('out_post_content')) {
            tinyMCE.get('out_post_content').setContent('');
        }
        $('#ai_product_name, #ai_product_context').val('');
        $('.sifp-image-preview').css('background-image', 'none');
    });

    // Image Preview
    $('#out_fp_img').on('input change', function() {
        var url = $(this).val();
        if (url) {
            $('.sifp-image-preview').css('background-image', 'url(' + url + ')');
        } else {
            $('.sifp-image-preview').css('background-image', 'none');
        }
    });

    // Presets
    $('.sifp-button--preset[data-preset]').on('click', function() {
        var preset = $(this).data('preset');
        var now = new Date();
        var skuPrefix = sifp_ajax.sku_prefix || 'PROD-';
        var defaultStock = sifp_ajax.default_stock || '10';
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
        sifp_notify(sifp_ajax.strings.preset_loaded, 'success');
    });

    // Media Uploader
    var media_uploader;
    $('#sifp-select-image-btn').on('click', function(e) {
        e.preventDefault();

        if (media_uploader) {
            media_uploader.open();
            return;
        }

        media_uploader = wp.media({
            title: sifp_ajax.strings.select_image,
            button: {
                text: sifp_ajax.strings.use_image
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
    $('#sifp-select-gallery-btn').on('click', function(e) {
        e.preventDefault();

        if (gallery_uploader) {
            gallery_uploader.open();
            return;
        }

        gallery_uploader = wp.media({
            title: sifp_ajax.strings.select_gallery,
            button: {
                text: sifp_ajax.strings.add_to_gallery
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
        var $container = $('.sifp-gallery-preview-grid');
        $container.empty();

        urls.forEach(function(url) {
            if (!url) return;
            var $item = $('<div class="sifp-gallery-item"><img src="' + url + '"><span class="sifp-gallery-item__remove" data-url="' + url + '">&times;</span></div>');
            $container.append($item);
        });
    });

    // Remove Gallery Item
    $(document).on('click', '.sifp-gallery-item__remove', function() {
        var urlToRemove = $(this).data('url');
        var current_gallery = $('#out_fp_gallery').val().split(',');
        var updated_gallery = current_gallery.filter(function(url) {
            return url !== urlToRemove;
        });
        $('#out_fp_gallery').val(updated_gallery.join(',')).trigger('change');
    });

    // Taxonomy Autocomplete
    var sifp_tax_timeout;
    $(document).on('keyup', '.sifp-taxonomy-selector input', function() {
        var $input = $(this);
        var $results = $input.siblings('.sifp-autocomplete-results');
        var tax = $input.closest('.sifp-taxonomy-selector').data('tax');
        var query = $input.val().split(',').pop().trim();

        clearTimeout(sifp_tax_timeout);

        if (query.length < 2) {
            $results.hide().empty();
            return;
        }

        sifp_tax_timeout = setTimeout(function() {
            $.ajax({
                url: sifp_ajax.ajax_url,
                type: 'GET',
                data: {
                    action: 'sifp_search_terms',
                    nonce: sifp_ajax.nonce,
                    taxonomy: tax,
                    q: query
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        $results.empty().show();
                        response.data.forEach(function(term) {
                            $results.append('<div class="sifp-autocomplete-results__item" data-val="' + term.name + '">' + term.name + '</div>');
                        });
                    } else {
                        $results.hide().empty();
                    }
                }
            });
        }, 300);
    });

    $(document).on('click', '.sifp-autocomplete-results__item', function() {
        var $item = $(this);
        var val = $item.data('val');
        var $input = $item.closest('.sifp-taxonomy-selector').find('input');
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
        if (!$(e.target).closest('.sifp-taxonomy-selector').length) {
            $('.sifp-autocomplete-results').hide().empty();
        }
    });

    // Sync Badges on input change (Removed as we use autocomplete now)
    });

})(jQuery);




