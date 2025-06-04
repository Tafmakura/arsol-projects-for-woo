/**
 * Arsol Projects for WooCommerce - Admin Scripts
 */
(function($) {
    'use strict';
    
    // Initialize when document is ready
    $(document).ready(function() {
        // Initialize enhanced select fields if Select2 is available
        if ($.fn.select2 && $('#arsol_project_selector').length) {
            $('#arsol_project_selector').select2({
                width: '100%',
                placeholder: 'Select a project'
            });
        }
        
        // Handle project field interactions
        $('#arsol_project_selector').on('change', function() {
            console.log('Project selected:', $(this).val());
            // Additional logic here if needed
        });

        // Hide all conditional fields by default (JS-only approach)
        // (No need to show the row until the condition is met)
        function toggleConditionalFields() {
            $('.arsol-conditional-field').each(function() {
                var $row = $(this);
                var conditionField = $row.data('condition-field');
                var conditionValue = $row.data('condition-value');
                var $controller = $('#' + conditionField);
                if ($controller.length && $controller.val() === conditionValue) {
                    $row.css('display', 'table-row');
                } else {
                    $row.css('display', 'none');
                }
            });
        }

        // Set data attributes on the conditional row if config is present
        if (window.arsolConditionalConfig) {
            $('.arsol-conditional-field')
                .attr('data-condition-field', window.arsolConditionalConfig.field)
                .attr('data-condition-value', window.arsolConditionalConfig.value);
        }

        // Initial state: do not show the row until the condition is checked
        toggleConditionalFields();

        // Listen for changes on all select fields that might be condition fields
        $('select').on('change', function() {
            toggleConditionalFields();
        });

        // MutationObserver to handle dynamic DOM changes
        var settingsTable = document.querySelector('.form-table');
        if (settingsTable && window.MutationObserver) {
            var observer = new MutationObserver(function(mutations) {
                toggleConditionalFields();
            });
            observer.observe(settingsTable, { childList: true, subtree: true });
        }
    });
    
})(jQuery);
