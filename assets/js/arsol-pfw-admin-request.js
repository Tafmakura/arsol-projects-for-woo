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
                
                // Step 1: HTML5 Validation (same as WordPress update/publish buttons)
                var $form = $('#post');
                if ($form.length && $form[0].checkValidity) {
                    if (!$form[0].checkValidity()) {
                        // Focus on first invalid field (WordPress behavior)
                        var $firstInvalid = $form.find(':invalid').first();
                        if ($firstInvalid.length) {
                            $firstInvalid.focus();
                            // Trigger validation display
                            $form[0].reportValidity();
                        }
                        return false;
                    }
                }
                
                // Step 2: Show confirmation dialog
                if (!confirm(message)) {
                    return false;
                }
                
                // Step 3: Add conversion URL as hidden input and submit form
                $('<input>').attr({
                    type: 'hidden',
                    name: 'arsol_convert_after_save',
                    value: url
                }).appendTo($form);
                
                // Submit form normally (WordPress will handle save and redirect)
                $form.submit();
                
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
        // Only initialize on request admin pages
        if ($('body').hasClass('post-type-arsol-pfw-request') || $('#request_details').length > 0) {
            ArsolRequest.init();
        }
    });

})(jQuery);
