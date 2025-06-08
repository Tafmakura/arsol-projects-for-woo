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

        // For example, you might want to show a loading spinner
        // or disable the button to prevent multiple submissions.
    });

    // Add a confirmation dialog for actions
    $('.arsol-confirm-action').on('click', function(e) {
        var message = $(this).data('message');
        if (!confirm(message)) {
            e.preventDefault();
        }
    });

})(jQuery);
