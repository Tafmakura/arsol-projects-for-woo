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
            this.handleSecondaryStatusChanges();
        },

        initializeComponents: function() {
            // Initialize any components specific to requests
            this.updateProposalConversionButtonState();
            this.updateRequestConversionButtonState();
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
            
            // Check if we need to publish or just save
            var $publishButton = $('#publish');
            var $saveButton = $('#save-post');
            var needsPublishing = $publishButton.length > 0 && $publishButton.is(':visible');
            
            if (needsPublishing) {
                // Trigger publish to make the post available for conversion
                $publishButton.trigger('click');
            } else {
                // Trigger save for already published posts
                $saveButton.trigger('click');
            }
            
            // Wait for save/publish to complete, then convert
            var checkSave = setInterval(function() {
                // Check if save/publish is complete (no longer in saving state)
                var publishComplete = !$publishButton.hasClass('button-primary-disabled');
                var saveComplete = !$saveButton.hasClass('button-primary-disabled');
                
                if (publishComplete && saveComplete) {
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
                ArsolRequest.updateRequestConversionButtonState();
            });
        },

        handleSecondaryStatusChanges: function() {
            // Handle proposal secondary status changes
            $('select[name="arsol_pfw_proposal_secondary_status"]').on('change', function() {
                ArsolRequest.updateProposalConversionButtonState();
            });
        },

        updateProposalConversionButtonState: function() {
            // Update the conversion button state for proposals based on published status and secondary status
            var $conversionButton = $('.arsol-confirm-conversion');
            
            if ($conversionButton.length) {
                var isPublished = $conversionButton.data('published') === 'true' || $conversionButton.data('published') === true;
                var currentSecondaryStatus = $('select[name="arsol_pfw_proposal_secondary_status"]').val();
                var isSecondaryApproved = currentSecondaryStatus === 'approved';
                
                // Update data attribute for current secondary status
                $conversionButton.attr('data-secondary-status', currentSecondaryStatus);
                
                // Enable only if both conditions are met
                if (isPublished && isSecondaryApproved) {
                    $conversionButton.removeClass('disabled').prop('disabled', false);
                    $conversionButton.parent().attr('title', 'Converts this proposal into a new project.');
                } else {
                    $conversionButton.addClass('disabled').prop('disabled', true);
                    
                    // Update tooltip based on what's missing
                    var tooltip = '';
                    if (!isPublished && !isSecondaryApproved) {
                        tooltip = 'The proposal must be published and have secondary status "approved" before it can be converted.';
                    } else if (!isPublished) {
                        tooltip = 'The proposal must be published before it can be converted.';
                    } else if (!isSecondaryApproved) {
                        tooltip = 'The proposal secondary status must be set to "approved" before it can be converted.';
                    }
                    $conversionButton.parent().attr('title', tooltip);
                }
            }
        },

        updateRequestConversionButtonState: function() {
            // Update the conversion button state for requests based on request status
            var $conversionButton = $('.arsol-confirm-conversion');
            var $statusSelect = $('select[name="request_status"]');
            
            // Only proceed if this is a request page (has request status dropdown)
            if ($statusSelect.length && $conversionButton.length) {
                var currentStatus = $statusSelect.val();
                var isApproved = currentStatus === 'approved';
                
                // Update data attribute for current status
                $conversionButton.attr('data-request-status', currentStatus);
                
                // Enable only if status is approved
                if (isApproved) {
                    $conversionButton.removeClass('disabled').prop('disabled', false);
                    $conversionButton.parent().attr('title', 'Converts this request into a new proposal.');
                } else {
                    $conversionButton.addClass('disabled').prop('disabled', true);
                    $conversionButton.parent().attr('title', 'The request must be in approved status before it can be converted.');
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
