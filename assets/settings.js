(function($) {
    'use strict';

    function getCurrentProvider() {
        return $('#sifp_active_ai_provider').val();
    }

    function getProviderSection(provider) {
        return $('#sifp-provider-' + provider);
    }

    function getModelSelect(provider) {
        return getProviderSection(provider).find('.sifp-ai-model-select');
    }

    function getApiKeyInput(provider) {
        return getProviderSection(provider).find('.sifp-ai-api-key');
    }

    function getSelectedModel(provider) {
        var select = getModelSelect(provider);
        var model = select.val();
        if (model === 'custom') {
            var customInput = getProviderSection(provider).find('input[name$="_custom"]');
            return customInput.val() || '';
        }
        return model;
    }

    function formatContext(ctx) {
        if (!ctx) return '0';
        if (ctx >= 1000000) return (ctx / 1000000).toFixed(1).replace('.', ',') + 'M';
        if (ctx >= 1000) return Math.round(ctx / 1000) + 'K';
        return ctx.toString();
    }

    function formatPricing(pricing) {
        if (!pricing) return null;
        var prompt = pricing.prompt;
        var completion = pricing.completion;
        if (typeof prompt === 'undefined' || typeof completion === 'undefined') return null;
        var input1M = prompt * 1000000;
        var output1M = completion * 1000000;
        if (input1M === 0 && output1M === 0) return { label: 'FREE', input: 'FREE', output: 'FREE' };
        var fmt = function(v) {
            if (v >= 10) return '$' + v.toFixed(2);
            if (v >= 1) return '$' + v.toFixed(3);
            if (v >= 0.01) return '$' + v.toFixed(4);
            if (v > 0) return '$' + v.toFixed(2);
            return '$0';
        };
        return {
            label: fmt(input1M) + ' / ' + fmt(output1M),
            input: fmt(input1M) + '/1M tok',
            output: fmt(output1M) + '/1M tok',
        };
    }

    function buildCapabilitiesCard(caps, context, description, pricing) {
        if (!caps) return '';
        var labels = {
            coding: 'Coding',
            reasoning: 'Reasoning',
            writing: 'Writing',
            speed: 'Speed',
            vision: 'Vision',
            json_mode: 'JSON Mode',
            streaming: 'Streaming'
        };
        var html = '<div class="sifp-caps-card">';
        if (description) {
            html += '<div class="sifp-caps-desc">' + $('<span>').text(description).html() + '</div>';
        }
        html += '<table class="sifp-caps-table">';
        if (context > 0) {
            html += '<tr><td class="sifp-caps-label">Context</td><td class="sifp-caps-val">' + formatContext(context) + '</td></tr>';
        }
        var pricingFmt = formatPricing(pricing);
        if (pricingFmt) {
            if (pricingFmt.input === 'FREE' && pricingFmt.output === 'FREE') {
                html += '<tr><td class="sifp-caps-label">Cost</td><td class="sifp-caps-val"><strong style="color:#10b981;">FREE</strong></td></tr>';
            } else {
                html += '<tr><td class="sifp-caps-label">Input cost</td><td class="sifp-caps-val sifp-caps-cost">' + pricingFmt.input + '</td></tr>';
                html += '<tr><td class="sifp-caps-label">Output cost</td><td class="sifp-caps-val sifp-caps-cost">' + pricingFmt.output + '</td></tr>';
            }
        }
        ['coding', 'reasoning', 'writing', 'speed'].forEach(function(key) {
            if (typeof caps[key] === 'number') {
                var score = caps[key];
                var color = score >= 80 ? '#10b981' : (score >= 60 ? '#f59e0b' : '#ef4444');
                html += '<tr><td class="sifp-caps-label">' + (labels[key] || key) + '</td>';
                html += '<td class="sifp-caps-val"><div class="sifp-caps-bar"><div class="sifp-caps-fill" style="width:' + score + '%;background:' + color + ';"></div></div><span class="sifp-caps-score" style="color:' + color + ';">' + score + '/100</span></td></tr>';
            }
        });
        ['vision', 'json_mode', 'streaming'].forEach(function(key) {
            if (typeof caps[key] === 'boolean') {
                html += '<tr><td class="sifp-caps-label">' + (labels[key] || key) + '</td>';
                html += '<td class="sifp-caps-val">' + (caps[key] ? '<span class="sifp-caps-yes">&#10003;</span>' : '<span class="sifp-caps-no">&#10007;</span>') + '</td></tr>';
            }
        });
        html += '</table></div>';
        return html;
    }

    function updateCapabilities(provider) {
        $('.sifp-model-caps').hide();
        var section = getProviderSection(provider);
        if (!section.length) return;
        var select = getModelSelect(provider);
        if (!select.length) return;
        var opt = select.find('option:selected');
        var caps = opt.data('caps');
        var context = opt.data('context') || 0;
        var description = opt.data('description') || '';
        var pricing = opt.data('pricing');
        var target = $('#sifp-caps-' + provider);
        if (caps && target.length) {
            var html = buildCapabilitiesCard(caps, context, description, pricing);
            if (html) {
                target.html(html).show();
            }
        }
    }

    $(document).ready(function() {
        // Provider switch
        $('#sifp_active_ai_provider').on('change', function() {
            var provider = $(this).val();
            // Sync hidden input for form submission
            $('input[name="sifp_active_tab"]').val('ai-providers');
            // Switch visible provider sections
            $('.sifp-provider-section').hide();
            getProviderSection(provider).show();
            // Update capabilities for the newly visible provider
            updateCapabilities(provider);
        });

        // Model change -> update capabilities card
        $(document).on('change', '.sifp-ai-model-select', function() {
            var provider = $(this).data('provider');
            updateCapabilities(provider);
        });

        // Test connection button
        $(document).on('click', '.sifp-btn-test-ai', function() {
            var $btn = $(this);
            var $result = $btn.siblings('.sifp-ai-test-result');
            var provider = $btn.data('provider');
            var model = getSelectedModel(provider);
            var apiKey = getApiKeyInput(provider).val();

            if (!model) {
                $result.text('Select a model first.').css('color', '#ef4444');
                return;
            }

            $btn.prop('disabled', true);
            $result.text('Testing...').css('color', '#64748b');

            $.ajax({
                url: sifp_ajax.ajax_url,
                type: 'POST',
                timeout: 60000,
                data: {
                    action: 'sifp_test_ai_connection',
                    nonce: sifp_ajax.nonce,
                    provider: provider,
                    model: model,
                    api_key: apiKey
                },
                success: function(response) {
                    $btn.prop('disabled', false);
                    if (response.success) {
                        $result.html('<span class="dashicons dashicons-yes-alt" style="color:#10b981;"></span> ' + (response.data || 'OK')).css('color', '#10b981');
                    } else {
                        $result.html('<span class="dashicons dashicons-no-alt" style="color:#ef4444;"></span> ' + (response.data || 'Error')).css('color', '#ef4444');
                    }
                },
                error: function(jqXHR, textStatus) {
                    $btn.prop('disabled', false);
                    $result.html('<span class="dashicons dashicons-no-alt" style="color:#ef4444;"></span> ' + (textStatus === 'timeout' ? 'Request timed out.' : 'Connection error.')).css('color', '#ef4444');
                }
            });
        });

        // Refresh models button
        $(document).on('click', '.sifp-btn-refresh-models', function() {
            var $btn = $(this);
            var provider = $btn.data('provider');
            $btn.addClass('sifp-loading');
            $.ajax({
                url: sifp_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'sifp_refresh_models',
                    nonce: sifp_ajax.nonce,
                    provider: provider
                },
                complete: function() {
                    $btn.removeClass('sifp-loading');
                    location.reload();
                }
            });
        });

        // Show capabilities for initially selected provider
        setTimeout(function() {
            var provider = getCurrentProvider();
            updateCapabilities(provider);
        }, 200);

        // Tab switching via JS for same-page navigation
        $('.sifp-settings-tabs .sifp-main-nav-el').on('click', function(e) {
            e.preventDefault();
            var tab = $(this).data('tab');
            $('.sifp-settings-tabs .sifp-main-nav-el').removeClass('sifp-main-nav-el--active');
            $(this).addClass('sifp-main-nav-el--active');
            $('.sifp-tab-content').hide();
            $('.sifp-tab-content[data-tab="' + tab + '"]').show();
            $('input[name="sifp_active_tab"]').val(tab);
            // Update URL without reload
            if (window.history.replaceState) {
                var url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url.toString());
            }
        });
    });

})(jQuery);