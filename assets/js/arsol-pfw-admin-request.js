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
                
                // Show confirmation first
                if (url && confirm(message)) {
                    // Save the post first, then convert
                    ArsolRequest.saveAndConvert($button, url);
                }
                
                return false;
            });
        },

        saveAndConvert: function($button, convertUrl) {
            // Disable the button to prevent double-clicks
            $button.prop('disabled', true).addClass('disabled');
            
            // Trigger WordPress's built-in save
            $('#publish, #save-post').trigger('click');
            
            // Wait for save to complete, then convert
            var checkSave = setInterval(function() {
                // Check if save is complete (no longer in saving state)
                if (!$('#publish').hasClass('button-primary-disabled') && !$('#save-post').hasClass('button-primary-disabled')) {
                    clearInterval(checkSave);
                    
                    // Small delay to ensure save is fully complete
                    setTimeout(function() {
                        window.location.href = convertUrl;
                    }, 500);
                }
            }, 100);
            
            // Fallback timeout in case save detection fails
            setTimeout(function() {
                clearInterval(checkSave);
                window.location.href = convertUrl;
            }, 5000);
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
                var isApproved = currentStatus === 'approved';
                
                if (isApproved) {
                    $conversionButton.removeClass('disabled').prop('disabled', false);
                } else {
                    $conversionButton.addClass('disabled').prop('disabled', true);
                }
            }
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Initialize on request and proposal admin pages
        if ($('body').hasClass('post-type-arsol-pfw-request') || 
            $('body').hasClass('post-type-arsol-pfw-proposal') || 
            $('#request_details').length > 0 ||
            $('.arsol-confirm-conversion').length > 0) {
            ArsolRequest.init();
        }
    });

})(jQuery);
