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
    });
    
})(jQuery);
