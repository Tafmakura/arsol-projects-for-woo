// JS for Arsol Request Admin
(function($) {
    'use strict';

    var ArsolRequest = {
        init: function() {
            this.bindEvents();
            this.initializeComponents();
        },

        bindEvents: function() {
            // Handle conversion confirmation for requests
            this.handleConversionConfirmation();
            this.handleStatusChanges();
        },

        initializeComponents: function() {
            // Initialize any components specific to requests
            this.updateConversionButtonState();
        },

        handleConversionConfirmation: function() {
            // Handle the conversion confirmation for requests
            $(document).on('click', '.arsol-confirm-conversion', function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var url = $button.data('url');
                var message = $button.data('message');
                
                if ($button.hasClass('disabled') || $button.prop('disabled')) {
                    return false;
                }
                
                if (url && confirm(message)) {
                    window.location.href = url;
                }
                
                return false;
            });
        },

        handleStatusChanges: function() {
            // Handle request status changes
            $('select[name="request_status"]').on('change', function() {
                var newStatus = $(this).val();
                ArsolRequest.updateConversionButtonState();
            });
        },

        updateConversionButtonState: function() {
            // Update the conversion button state based on request status
            var $statusSelect = $('select[name="request_status"]');
            var $conversionButton = $('.arsol-confirm-conversion');
            
            if ($statusSelect.length && $conversionButton.length) {
                var currentStatus = $statusSelect.val();
                var isUnderReview = currentStatus === 'under-review';
                
                if (isUnderReview) {
                    $conversionButton.removeClass('disabled').prop('disabled', false);
                } else {
                    $conversionButton.addClass('disabled').prop('disabled', true);
                }
            }
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Only initialize on request admin pages
        if ($('body').hasClass('post-type-arsol-pfw-request') || $('#request_details').length > 0) {
            ArsolRequest.init();
        }
    });

})(jQuery);
