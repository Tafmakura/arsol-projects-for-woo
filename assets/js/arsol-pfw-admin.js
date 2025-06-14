/**
 * Arsol Projects for WooCommerce - Admin Scripts
 */
jQuery(document).ready(function($) {
    'use strict';
    
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
        var conditionalRow = $('.arsol-conditional-field');
        if (!conditionalRow.length) {
            return;
        }
        
        var conditionFieldSelector = 'select[name="' + conditionalRow.data('condition-field') + '"]';
        var conditionValue = conditionalRow.data('condition-value');
        
        if ($(conditionFieldSelector).val() === conditionValue) {
            conditionalRow.closest('tr').show();
            } else {
            conditionalRow.closest('tr').hide();
        }
    }

        // Initial check
    checkConditionalField();

    // Check on change
    var conditionalRow = $('.arsol-conditional-field');
    if (conditionalRow.length) {
        var conditionFieldSelector = 'select[name="' + conditionalRow.data('condition-field') + '"]';
        $(document).on('change', conditionFieldSelector, function() {
            checkConditionalField();
        });
    }
    
    // Initialize enhanced select fields if Select2 is available
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
