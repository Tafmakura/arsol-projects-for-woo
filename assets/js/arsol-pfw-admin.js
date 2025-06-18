/**
 * Arsol Projects for WooCommerce - Admin Scripts
 * Optimized script using WooCommerce Enhanced Select patterns
 */
jQuery(document).ready(function($) {
    'use strict';
    
    // Enhanced initialization using WooCommerce's built-in enhanced select
    function initWooCommerceEnhancedDropdowns() {
        // Check if WooCommerce enhanced select is available
        if (typeof $.fn.selectWoo === 'undefined' || typeof wc_enhanced_select_params === 'undefined') {
            // Retry after a short delay if WooCommerce scripts aren't loaded yet
            setTimeout(initWooCommerceEnhancedDropdowns, 250);
            return;
        }
        
        // Initialize all WooCommerce enhanced select fields
        $(':input.wc-enhanced-select, :input.wc-product-search, :input.wc-customer-search').filter(':not(.enhanced)').each(function() {
            var select2_args = $.extend({
                minimumResultsForSearch: 10,
                allowClear: $(this).data('allow_clear') ? true : false,
                placeholder: $(this).data('placeholder')
            }, getEnhancedSelectFormatString());

            if ($(this).data('action')) {
                select2_args = $.extend(select2_args, {
                    ajax: {
                        url: wc_enhanced_select_params.ajax_url,
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                term: params.term,
                                action: $(this).data('action'),
                                security: $(this).data('security') || wc_enhanced_select_params.search_products_nonce,
                                exclude: $(this).data('exclude'),
                                include: $(this).data('include'),
                                limit: $(this).data('limit')
                            };
                        },
                        processResults: function(data) {
                            var terms = [];
                            if (data) {
                                $.each(data, function(id, text) {
                                    terms.push({ id: id, text: text });
                                });
                            }
                            return { results: terms };
                        },
                        cache: true
                    }
                });
            }

            $(this).selectWoo(select2_args).addClass('enhanced');
        });
    }
    
    // Simplified user select initialization using native Select2
    function initUserSelectDropdowns() {
        if (typeof $.fn.select2 === 'undefined') {
            setTimeout(initUserSelectDropdowns, 250);
            return;
        }
        
        $('.arsol-user-select2').filter(':not(.enhanced)').each(function() {
            var placeholder = $(this).data('placeholder') || $(this).find('option:first').text() || 'Search...';
            
            $(this).select2({
                placeholder: placeholder,
                allowClear: true,
                width: '100%'
            }).addClass('enhanced');
        });
    }
    
    // WooCommerce enhanced select helper function (from WC core)
    function getEnhancedSelectFormatString() {
        return {
            'language': {
                errorLoading: function() {
                    // Workaround for https://github.com/select2/select2/issues/4355 instead of i18n_ajax_error.
                    return wc_enhanced_select_params.i18n_searching;
                },
                inputTooLong: function(args) {
                    var overChars = args.input.length - args.maximum;
                    if (1 === overChars) {
                        return wc_enhanced_select_params.i18n_input_too_long_1;
                    }
                    return wc_enhanced_select_params.i18n_input_too_long_n.replace('%qty%', overChars);
                },
                inputTooShort: function(args) {
                    var remainingChars = args.minimum - args.input.length;
                    if (1 === remainingChars) {
                        return wc_enhanced_select_params.i18n_input_too_short_1;
                    }
                    return wc_enhanced_select_params.i18n_input_too_short_n.replace('%qty%', remainingChars);
                },
                loadingMore: function() {
                    return wc_enhanced_select_params.i18n_load_more;
                },
                maximumSelected: function(args) {
                    if (args.maximum === 1) {
                        return wc_enhanced_select_params.i18n_selection_too_long_1;
                    }
                    return wc_enhanced_select_params.i18n_selection_too_long_n.replace('%qty%', args.maximum);
                },
                noResults: function() {
                    return wc_enhanced_select_params.i18n_no_matches;
                },
                searching: function() {
                    return wc_enhanced_select_params.i18n_searching;
                }
            }
        };
    }
    
    // Initialize disabled dropdowns with consistent styling
    function initDisabledDropdowns() {
        $('.arsol-disabled-select').each(function() {
            $(this).addClass('arsol-disabled-dropdown').prop('disabled', true);
        });
    }
    
    // Main initialization function
    function initAllDropdowns() {
        initWooCommerceEnhancedDropdowns();
        initUserSelectDropdowns();
        initDisabledDropdowns();
    }
    
    // Initialize on page load
    initAllDropdowns();
    
    // Re-initialize using modern MutationObserver API
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function(mutations) {
            var shouldReinit = false;
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            var $node = $(node);
                            if ($node.is('.wc-enhanced-select, .wc-product-search, .wc-customer-search, .arsol-user-select2, .arsol-disabled-select') || 
                                $node.find('.wc-enhanced-select, .wc-product-search, .wc-customer-search, .arsol-user-select2, .arsol-disabled-select').length) {
                                shouldReinit = true;
                            }
                        }
                    });
                }
            });
            if (shouldReinit) {
                setTimeout(initAllDropdowns, 100);
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    // Generic confirmation handler for conversion buttons
    $('body').on('click', '.arsol-confirm-conversion', function(e) {
        e.preventDefault();
        
        // If the button is disabled, do nothing
        if ($(this).is(':disabled') || $(this).hasClass('disabled')) {
            return;
        }

        var message = $(this).data('message');
        var url = $(this).data('url');

        if (confirm(message)) {
            window.location.href = url;
        }
    });

    // Conditional field logic for settings page
    function checkConditionalField() {
        var conditionalRows = $('.arsol-conditional-field');
        
        if (!conditionalRows.length) {
            return;
        }
        
        conditionalRows.each(function() {
            var $this = $(this);
            var conditionField = $this.data('condition-field');
            var conditionValue = $this.data('condition-value');
            
            if (!conditionField || !conditionValue) {
                return;
            }
            
            // Build the correct selector for WordPress settings fields
            var conditionFieldSelector = 'select[name="arsol_projects_settings[' + conditionField + ']"]';
            var $conditionField = $(conditionFieldSelector);
            
            if (!$conditionField.length) {
                // Fallback to ID-based selector
                conditionFieldSelector = '#' + conditionField;
                $conditionField = $(conditionFieldSelector);
            }
            
            var $targetRow = $this.closest('tr');
            if (!$targetRow.length) {
                $targetRow = $this; // Fallback if not in a table structure
            }
            
            // Mark the row as a conditional field row if not already marked
            if (!$targetRow.hasClass('arsol-conditional-field-row')) {
                $targetRow.addClass('arsol-conditional-field-row');
            }
            
            if ($conditionField.length) {
                var currentValue = $conditionField.val();
                
                if (currentValue === conditionValue) {
                    $targetRow.addClass('show-conditional-field');
                } else {
                    $targetRow.removeClass('show-conditional-field');
                }
            }
        });
    }

    // Check on change - use document ready to ensure all elements are loaded
    $(document).ready(function() {
        // Initial check after a small delay to ensure everything is loaded
        setTimeout(function() {
            checkConditionalField();
        }, 100);
        
        var conditionalFields = $('.arsol-conditional-field');
        if (conditionalFields.length) {
            conditionalFields.each(function() {
                var $this = $(this);
                var conditionField = $this.data('condition-field');
                if (conditionField) {
                    var conditionFieldSelector = 'select[name="arsol_projects_settings[' + conditionField + ']"]';
                    var $conditionField = $(conditionFieldSelector);
                    
                    if (!$conditionField.length) {
                        // Fallback to ID-based selector
                        conditionFieldSelector = '#' + conditionField;
                    }
                    
                    $(document).on('change', conditionFieldSelector, function() {
                        checkConditionalField();
                    });
                }
            });
        }
    });
    
    // Legacy support for specific selectors (backwards compatibility)
    if ($.fn.select2 && $('#arsol_project_selector').length) {
        $('#arsol_project_selector').select2({
            placeholder: 'Select a project',
            allowClear: true
        });
    }

    if ($.fn.select2 && $('#arsol_user_selector').length) {
        $('#arsol_user_selector').select2({
            placeholder: 'Select users',
            allowClear: true,
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        action: 'arsol_pfw_search_users',
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.data
                    };
                },
                cache: true
            },
            minimumInputLength: 2
        });
    }

    // Function to format currency inputs
    function formatMoneyInput(input) {
        let value = input.val();
        if (!value) return;

        // Sanitize to only numbers and one decimal point
        let numericValue = value.replace(/[^0-9.]/g, '');
        let parts = numericValue.split('.');
        if (parts.length > 2) {
            numericValue = parts[0] + '.' + parts.slice(1).join('');
        }
        
        // Split into integer and decimal parts
        parts = numericValue.split('.');
        let integerPart = parts[0];
        let decimalPart = parts.length > 1 ? '.' + parts[1].substring(0, 2) : '';

        // Add thousand separators to integer part
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        
        // Combine and set value
        input.val(integerPart + decimalPart);
    }

    // Initialize formatting for existing money fields on page load
    $('.arsol-money-input').each(function() {
        formatMoneyInput($(this));
    });

    // Re-format on input
    $('body').on('input', '.arsol-money-input', function() {
        formatMoneyInput($(this));
    });
});
