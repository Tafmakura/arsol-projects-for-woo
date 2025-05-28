/**
 * Arsol Projects for WooCommerce - Frontend Scripts
 */
(function($) {
    'use strict';
    
    // Initialize when document is ready
    $(document).ready(function() {
        // Initialize checkout field interactions
        if ($('form.woocommerce-checkout').length) {
            // Add custom validation or behavior for the project field
            $('select[name="arsol-projects-for-woo/project"]').on('change', function() {
                console.log('Project selected:', $(this).val());
                // Additional validation logic here if needed
            });
        }
        
        // Initialize view order page interactions
        if ($('.woocommerce-view-order').length) {
            // Add any custom behaviors for the project table
            $('.projects-row .woocommerce-button.view').on('click', function() {
                console.log('View project button clicked');
            });
        }
    });
    
})(jQuery);
