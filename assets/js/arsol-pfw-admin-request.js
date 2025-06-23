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
            // Handle the conversion confirmation for requests only
            $(document).on('click', '.arsol-confirm-conversion', function(e) {
                // Only handle if we're on a request page
                if (!$('#request_status').length) return;
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
            $(document).on('change', '#request_status', function() {
                ArsolRequest.updateConversionButtonState();
            });
        },

        updateConversionButtonState: function() {
            // Update the conversion button state based on request status
            var $convertBtn = $('.arsol-confirm-conversion');
            if ($convertBtn.length === 0) return;
            
            // Get the selected status
            var selectedStatus = '';
            if ($('#request_status').length) {
                selectedStatus = $('#request_status').val();
            }
            
            if (selectedStatus === 'approved') {
                // Enable button and update tooltip
                $convertBtn.prop('disabled', false)
                          .removeClass('disabled')
                          .closest('span')
                          .attr('title', 'Converts this request to a proposal.');
            } else {
                // Keep disabled and update tooltip
                var statusDisplay = selectedStatus || 'none';
                $convertBtn.prop('disabled', true)
                          .addClass('disabled')
                          .closest('span')
                          .attr('title', 'The request status must be "Approved" before it can be converted. Current status: "' + statusDisplay + '".');
            }
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Only initialize on request admin pages
        if ($('body').hasClass('post-type-arsol-pfw-request') || $('#request_details').length > 0) {
            ArsolRequest.init();
        }
        
        // Update button state on page load for request pages
        if ($('#request_status').length) {
            ArsolRequest.updateConversionButtonState();
        }
    });

})(jQuery);
