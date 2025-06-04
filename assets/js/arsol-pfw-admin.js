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

        // Simple conditional field toggle
        function toggleConditionalRow() {
            if ($('#user_project_permissions').val() === 'user_specific') {
                $('.arsol-conditional-field').removeAttr('style');
            } else {
                $('.arsol-conditional-field').css('display', 'none');
            }
        }
        // Initial check
        toggleConditionalRow();
        // Listen for changes
        $('#user_project_permissions').on('change', function() {
            toggleConditionalRow();
        });

        // MutationObserver to handle dynamic DOM changes
        var settingsTable = document.querySelector('.form-table');
        if (settingsTable && window.MutationObserver) {
            var observer = new MutationObserver(function(mutations) {
                toggleConditionalRow();
            });
            observer.observe(settingsTable, { childList: true, subtree: true });
        }
    });
    
})(jQuery);
