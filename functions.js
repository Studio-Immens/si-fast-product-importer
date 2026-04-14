(function($) {
    'use strict';

    /**
     * SIFlashProducts JS Module
     */
    const SIFP = {
        init: function() {
            this.bindEvents();
            this.initAutocomplete();

            // Auto search on load if search container exists
            if ($('.sifp-container').length) {
                this.searchProducts();
            }
        },

        bindEvents: function() {
            // Search
            $(document).on('click', '.sifp-search-btn', () => this.searchProducts());
            $(document).on('keyup', '.sifp-keyword', (e) => {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => this.searchProducts(), 500);
            });
            $(document).on('change', '.sifp-languages, .sifp-categories, .sifp-source, .sifp-orderby, .sifp-limit, .sifp-offset', () => this.searchProducts());

            // Selection
            $(document).on('change', '.sifp-select-product', (e) => this.updateSelection(e));
            $(document).on('change', '.sifp-select-all-products', (e) => this.selectAll(e));

            // Sync duplicated controls
            $(document).on('change keyup', '.sifp-limit, .sifp-offset, .sifp-orderby, .sifp-select-all-products', (e) => {
                const $target = $(e.currentTarget);
                const val = $target.is(':checkbox') ? $target.prop('checked') : $target.val();
                const cls = '.' + $target.attr('class').split(' ')[0];
                $(cls).not($target).each(function() {
                    if ($(this).is(':checkbox')) {
                        $(this).prop('checked', val);
                    } else {
                        $(this).val(val);
                    }
                });
            });

            // Import
            $(document).on('click', '.sifp-rapid-import', (e) => this.importProduct($(e.currentTarget)));
            $(document).on('click', '.sifp-bulk-import-btn', () => this.bulkImport());
            $(document).on('click', '.sifp-import-edited-btn', () => this.importEditedProduct());

            // Modal
            $(document).on('click', '.sifp-card', (e) => {
                if ($(e.target).closest('.sifp-card-selection, .sifp-rapid-import').length) return;
                this.openDetail($(e.currentTarget));
            });
            $(document).on('click', '.sifp-button--close, .sifp-background-section', () => this.closeDetail());

            // AI Generator
            $(document).on('click', '.sifp-ai-generate-btn', () => this.generateAI());
            $(document).on('click', '.sifp-ai-import-btn', () => this.createAIProduct());
            $(document).on('click', '.sifp-ai-clear-btn', () => this.clearGenerator());
            $(document).on('click', '.clear-logs-btn', () => this.clearLogs());
            $(document).on('click', '.dashicons-image-rotate', (e) => this.resetSetting($(e.currentTarget)));

            // Attributes
            $(document).on('click', '#sifp_add_attribute_btn', () => this.addAttributeRow());
            $(document).on('click', '.sifp-attribute-row__remove', (e) => $(e.currentTarget).closest('.sifp-attribute-row').remove());

            // Media
            $(document).on('click', '#sifp_select_image_btn', (e) => this.openMediaUploader(e, 'single'));
            $(document).on('click', '#sifp_select_gallery_btn', (e) => this.openMediaUploader(e, 'gallery'));
        },

        // --- Core Functions ---

        searchProducts: function() {
            const $container = $(".sifp-container");
            const $found = $(".sifp-found");

            $container.css('opacity', '0.5');
            $found.html('<span class="dashicons dashicons-update spin"></span>');

            const data = {
                action: 'sifp_search_products',
                nonce: sifp_ajax.nonce,
                s: $(".sifp-keyword").val(),
                categories: $(".sifp-categories").val(),
                limit: $(".sifp-limit").val(),
                offset: $(".sifp-offset").val(),
                orderby: $(".sifp-orderby").val(),
                source: $(".sifp-source").val()
            };

            $.get(sifp_ajax.ajax_url, data, (response) => {
                $container.css('opacity', '1');
                if (response.success) {
                    $found.text(response.data.total_results);
                    this.renderProducts(response.data.result);
                } else {
                    this.notify(response.data.message || sifp_ajax.strings.error, 'error');
                }
            });
        },

        renderProducts: function(products) {
            const $container = $(".sifp-container");
            const $template = $('.sifp-default-card');
            
            // Capture the template BEFORE emptying the container
            if ($template.length && !this.cardTemplate) {
                this.cardTemplate = $template.clone().removeClass('sifp-default-card');
            }
            
            $container.empty();
            $('.bulk-actions').fadeOut();
            $('.sifp-select-all-products').prop('checked', false);
            $('.selected-count').text(0);

            if (!products || products.length === 0) {
                $container.append('<div class="sifp-no-results">' + sifp_ajax.strings.no_results + '</div>');
                return;
            }

            if (!this.cardTemplate) {
                this.notify('Template not found! Please reload.', 'error');
                return;
            }

            $('.bulk-actions').fadeIn();

            products.forEach(p => {
                const $card = this.cardTemplate.clone();
                $card.find('.sifp-card-title').text(p.post_title);
                $card.find('.sifp-card-head img').attr('src', p.sifp_img || '');
                
                // Store data in jQuery cache
                $card.data('product', p);
                $container.append($card);
            });
        },

        importProduct: function($btn, callback) {
            const $card = $btn.closest('.sifp-card');
            const product = $card.data('product');
            const originalHtml = $btn.html();
            
            if (!product) return;

            if ($btn.hasClass('sifp-loading')) return;

            $btn.addClass('sifp-loading').html('<span class="dashicons dashicons-update spin"></span>');

            $.post(sifp_ajax.ajax_url, {
                action: 'sifp_import_product',
                nonce: sifp_ajax.nonce,
                product: product
            }, (response) => {
                $btn.removeClass('sifp-loading');
                if (response.success) {
                    $btn.html('<span class="dashicons dashicons-yes"></span>').css('background-color', 'var(--sifp-success)');
                    if (!callback) this.notify(sifp_ajax.strings.bulk_success, 'success');
                    if (callback) callback(true);
                } else {
                    $btn.html(originalHtml);
                    if (!callback) this.notify(response.data, 'error');
                    if (callback) callback(false);
                }
            });
        },

        importEditedProduct: function() {
            const $modal = $('.sifp-detail-section');
            const originalProduct = $modal.data('product');
            const $btn = $('.sifp-import-edited-btn');
            const originalHtml = $btn.html();

            if ($btn.hasClass('sifp-loading')) return;

            // Collect edited values
            const editedProduct = { ...originalProduct };
            $modal.find('[sifp-edit]').each(function() {
                const key = $(this).attr('sifp-edit');
                editedProduct[key] = $(this).val();
            });

            $btn.addClass('sifp-loading').prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> ' + sifp_ajax.strings.importing);

            $.post(sifp_ajax.ajax_url, {
                action: 'sifp_import_product',
                nonce: sifp_ajax.nonce,
                product: editedProduct
            }, (response) => {
                $btn.removeClass('sifp-loading').prop('disabled', false).html(originalHtml);
                if (response.success) {
                    this.notify(sifp_ajax.strings.bulk_success, 'success');
                    this.closeDetail();
                } else {
                    this.notify(response.data || sifp_ajax.strings.error_import, 'error');
                }
            });
        },

        bulkImport: function() {
            const $selected = $('.sifp-card.selected');
            if ($selected.length === 0) return;

            if (!confirm(sifp_ajax.strings.confirm_bulk.replace('%d', $selected.length))) return;

            const $btn = $('.sifp-bulk-import-btn');
            const originalText = $btn.text();
            $btn.prop('disabled', true).text(sifp_ajax.strings.importing);

            let imported = 0;
            const total = $selected.length;

            const processNext = (index) => {
                if (index >= total) {
                    $btn.prop('disabled', false).text(originalText);
                    this.notify(imported + ' ' + sifp_ajax.strings.bulk_success, 'success');
                    return;
                }

                this.importProduct($selected.eq(index).find('.sifp-rapid-import'), (success) => {
                    if (success) imported++;
                    processNext(index + 1);
                });
            };

            processNext(0);
        },

        generateAI: function() {
            const $btn = $('.sifp-ai-generate-btn');
            const name = $('#sifp_ai_product_name').val();
            const context = $('#sifp_ai_product_context').val();

            if (!name) {
                this.notify(sifp_ajax.strings.error_missing_name, 'error');
                return;
            }

            $btn.addClass('sifp-loading').prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> ' + sifp_ajax.strings.generating);

            $.post(sifp_ajax.ajax_url, {
                action: 'sifp_ai_generate_product',
                nonce: sifp_ajax.nonce,
                data: { name: name, description: context }
            }, (response) => {
                $btn.removeClass('sifp-loading').prop('disabled', false).html('<span class="dashicons dashicons-sparkles"></span> MAKE MAGIC');
                
                if (response.success) {
                    this.fillGeneratorForm(response.data);
                    this.notify(sifp_ajax.strings.ai_gen_success, 'success');
                } else {
                    this.notify(response.data || sifp_ajax.strings.error_ai_call, 'error');
                }
            });
        },

        fillGeneratorForm: function(data) {
            $('#out_post_title').val(data.post_title);
            $('#out_post_excerpt').val(data.post_excerpt);
            
            if (window.tinyMCE && tinyMCE.get('out_post_content')) {
                tinyMCE.get('out_post_content').setContent(data.post_content);
            } else {
                $('#out_post_content').val(data.post_content);
            }

            $('#out_sku').val(data.sku);
            $('#out_regular_price').val(data.regular_price);
            $('#out_sifp_categories').val(data.sifp_categories);
            $('#out_sifp_tag').val(data.sifp_tag);

            // Attributes
            $('#sifp_attributes_container').empty();
            if (data.attributes) {
                data.attributes.forEach(attr => this.addAttributeRow(attr.name, attr.value));
            }

            // Variations
            const $varCard = $('#sifp_variations_card');
            const $varContainer = $('#sifp_variations_container');
            $varContainer.empty();
            if (data.variations && data.variations.length > 0) {
                $varCard.fadeIn();
                data.variations.forEach(v => this.addVariationRow(v));
            } else {
                $varCard.hide();
            }

            // SEO Fields (if added to form)
            $('#out_seo_title').val(data.seo_title);
            $('#out_seo_description').val(data.seo_description);
        },

        createAIProduct: function() {
            const $btn = $('.sifp-ai-import-btn');
            const formData = $('#sifp_ai_product_form').serializeArray();
            const product = {};

            formData.forEach(item => product[item.name] = item.value);
            
            // Get attributes
            product.attributes = [];
            $('.sifp-attribute-row:visible').each(function() {
                const name = $(this).find('.sifp-attribute-row__name').val();
                const value = $(this).find('.sifp-attribute-row__values').val();
                if (name && value) {
                    product.attributes.push({ name, value });
                }
            });

            // Get variations
            product.variations = [];
            $('.sifp-variation-row').each(function() {
                const $row = $(this);
                const attrs = {};
                $row.find('.sifp-variation-attr').each(function() {
                    attrs[$(this).data('attr')] = $(this).val();
                });
                product.variations.push({
                    attributes: attrs,
                    regular_price: $row.find('.sifp-variation-price').val(),
                    sku: $row.find('.sifp-variation-sku').val()
                });
            });

            // Get content from tinyMCE
            if (window.tinyMCE && tinyMCE.get('out_post_content')) {
                product.post_content = tinyMCE.get('out_post_content').getContent();
            }

            $btn.addClass('sifp-loading').prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> ' + sifp_ajax.strings.importing);

            $.post(sifp_ajax.ajax_url, {
                action: 'sifp_import_product',
                nonce: sifp_ajax.nonce,
                product: product
            }, (response) => {
                $btn.removeClass('sifp-loading').prop('disabled', false).html('<span class="dashicons dashicons-cloud-upload"></span> CREATE PRODUCT');
                if (response.success) {
                    this.notify(sifp_ajax.strings.bulk_success, 'success');
                    window.location.href = response.data.url;
                } else {
                    this.notify(response.data, 'error');
                }
            });
        },

        clearGenerator: function() {
            $('#sifp_ai_product_form')[0].reset();
            $('#sifp_attributes_container').empty();
            $('#sifp_variations_container').empty();
            $('#sifp_variations_card').hide();
            if (window.tinyMCE && tinyMCE.get('out_post_content')) {
                tinyMCE.get('out_post_content').setContent('');
            }
        },

        clearLogs: function() {
            if (!confirm(sifp_ajax.strings.confirm_clear_logs)) return;

            $.post(sifp_ajax.ajax_url, {
                action: 'sifp_clear_logs',
                nonce: sifp_ajax.nonce
            }, (response) => {
                if (response.success) {
                    $('.sifp-log-table-container tbody').html('<tr><td colspan="3" style="text-align:center;">' + sifp_ajax.strings.logs_cleared + '</td></tr>');
                    this.notify(response.data || sifp_ajax.strings.logs_cleared, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    this.notify(response.data || sifp_ajax.strings.error_clear_logs, 'error');
                }
            });
        },

        resetSetting: function($btn) {
            const defaultValue = $btn.data('default');
            const $input = $btn.siblings('input, select, textarea');
            
            if ($input.length) {
                $input.val(defaultValue);
                this.notify(sifp_ajax.strings.preset_loaded, 'success');
            }
        },

        // --- Helpers ---

        addAttributeRow: function(name = '', value = '') {
            const $template = $('.sifp-attribute-row-template').children().clone();
            $template.find('.sifp-attribute-row__name').val(name);
            $template.find('.sifp-attribute-row__values').val(value);
            $('#sifp_attributes_container').append($template);
        },

        addVariationRow: function(variation) {
            const $container = $('<div class="sifp-variation-row sifp-card"></div>');
            const $grid = $('<div class="grid-3"></div>');
            const $attrsGroup = $('<div class="sifp-variation-attrs-group"></div>');

            if (variation.attributes) {
                for (const [name, val] of Object.entries(variation.attributes)) {
                    const $field = $('<div class="sifp-form-field"></div>');
                    $field.append($('<label></label>').text(name));
                    $field.append($('<input type="text" class="sifp-variation-attr">').attr('data-attr', name).val(val));
                    $attrsGroup.append($field);
                }
            }

            $grid.append($attrsGroup);

            const $priceField = $('<div class="sifp-form-field"></div>');
            $priceField.append($('<label>Price (€)</label>'));
            $priceField.append($('<input type="number" class="sifp-variation-price">').val(variation.regular_price));
            $grid.append($priceField);

            const $skuField = $('<div class="sifp-form-field"></div>');
            $skuField.append($('<label>SKU</label>'));
            $skuField.append($('<input type="text" class="sifp-variation-sku">').val(variation.sku || ''));
            $grid.append($skuField);

            $container.append($grid);
            $('#sifp_variations_container').append($container);
        },

        updateSelection: function(e) {
            $(e.currentTarget).closest('.sifp-card').toggleClass('selected', e.currentTarget.checked);
            $('.selected-count').text($('.sifp-card.selected').length);
        },

        selectAll: function(e) {
            const checked = e.currentTarget.checked;
            $('.sifp-card').not('.sifp-default-card').each(function() {
                $(this).toggleClass('selected', checked);
                $(this).find('.sifp-select-product').prop('checked', checked);
            });
            $('.selected-count').text($('.sifp-card.selected').length);
        },

        openDetail: function($card) {
            const product = $card.data('product');
            if (!product) return;

            const $modal = $('.sifp-detail-section');
            
            // Populate all fields based on sifp-edit attribute
            $modal.find('[sifp-edit]').each(function() {
                const key = $(this).attr('sifp-edit');
                const val = product[key] || product['sifp_' + key] || '';
                $(this).val(val);
            });

            // Special handling for images
            const $imgContainer = $modal.find('.sifp-detail-body-images');
            $imgContainer.empty();
            if (product.sifp_img) {
                $imgContainer.append('<img src="' + product.sifp_img + '" class="sifp-detail-main-img">');
            }
            if (product.sifp_gallery) {
                const gallery = product.sifp_gallery.split(',');
                const $galleryContainer = $('<div class="sifp-detail-gallery"></div>');
                gallery.forEach(url => {
                    $galleryContainer.append('<img src="' + url.trim() + '">');
                });
                $imgContainer.append($galleryContainer);
            }
            
            $modal.data('product', product);
            $modal.removeClass('sifp-u-hidden').fadeIn();
            $('.sifp-background-section').removeClass('sifp-u-hidden').show();
        },

        closeDetail: function() {
            $('.sifp-detail-section').fadeOut(() => {
                $('.sifp-detail-section').addClass('sifp-u-hidden');
            });
            $('.sifp-background-section').hide(() => {
                $('.sifp-background-section').addClass('sifp-u-hidden');
            });
        },

        notify: function(message, type) {
            const color = type === 'success' ? 'var(--sifp-success)' : 'var(--sifp-error)';
            const $notify = $('<div class="sifp-notification"></div>')
                .text(message)
                .css({
                    'position': 'fixed', 'bottom': '20px', 'right': '20px',
                    'background-color': color, 'color': '#fff', 'padding': '15px 25px',
                    'border-radius': '5px', 'z-index': '100000', 'box-shadow': '0 4px 6px rgba(0,0,0,0.1)'
                });
            
            $('body').append($notify);
            $notify.fadeIn().delay(3000).fadeOut(() => $notify.remove());
        },

        initAutocomplete: function() {
            // Basic autocomplete logic for categories/tags
            $(document).on('keyup', '.sifp-taxonomy-selector input', (e) => {
                const $input = $(e.currentTarget);
                const $results = $input.siblings('.sifp-autocomplete-results');
                // Implementation would go here
            });
        },

        openMediaUploader: function(e, type) {
            e.preventDefault();
            const frame = wp.media({
                title: sifp_ajax.strings.select_image,
                button: { text: sifp_ajax.strings.use_image },
                multiple: type === 'gallery'
            });

            frame.on('select', () => {
                const selection = frame.state().get('selection');
                if (type === 'single') {
                    const attachment = selection.first().toJSON();
                    $('#out_sifp_img').val(attachment.url);
                    $('#sifp_img_preview_container').empty().append($('<img>').attr('src', attachment.url).css('max-width', '100px'));
                } else {
                    const urls = [];
                    selection.map(a => {
                        const data = a.toJSON();
                        urls.push(data.url);
                    });
                    $('#out_sifp_gallery').val(urls.join(','));
                    $('#sifp_gallery_preview_container').empty();
                    urls.forEach(url => {
                        $('#sifp_gallery_preview_container').append($('<img>').attr('src', url).css('max-width', '60px'));
                    });
                }
            });

            frame.open();
        }
    };

    $(document).ready(() => SIFP.init());

})(jQuery);