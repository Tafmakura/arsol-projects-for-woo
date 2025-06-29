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
            this.handleFormSubmission();
        },

        initializeComponents: function() {
            // Initialize any components specific to requests
            this.updateConversionButtonState();
            this.updateRequestStageVisibility();
        },

        handleConversionConfirmation: function() {
            // Handle the conversion confirmation for requests only
            $(document).on('click', '.arsol-confirm-conversion', function(e) {
                // Only handle if we're on a request page
                if (!$('#request_stage').length) return;
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
                
                // Step 2: Custom validation for request feedback
                if (!ArsolRequest.validateRequestFeedback()) {
                    return false;
                }
                
                // Step 3: Show confirmation dialog
                if (!confirm(message)) {
                    return false;
                }
                
                // Step 4: Add conversion URL as hidden input and submit form
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
            // Handle request stage changes
            $(document).on('change', '#request_stage', function() {
                const selectedStage = $(this).val();
                
                // Use the smart conditional system
                if (typeof window.ArsolConditionalVisibility !== 'undefined') {
                    window.ArsolConditionalVisibility.updateConditionalVisibilityForField('request_stage');
                }
                
                ArsolRequest.updateConversionButtonState();
            });
        },

        updateConversionButtonState: function() {
            // Update the conversion button state based on request stage
            var selectedStage = '';
            if ($('#request_stage').length) {
                selectedStage = $('#request_stage').val();
            }

            var convertButton = $('.arsol-confirm-conversion');
            if (convertButton.length) {
                if (selectedStage === 'approved') {
                    convertButton.prop('disabled', false).removeClass('disabled')
                        .attr('title', 'Converts this request into a new proposal.');
                } else {
                    var stageDisplay = selectedStage ? selectedStage.replace(/-/g, ' ') : 'none';
                    stageDisplay = stageDisplay.charAt(0).toUpperCase() + stageDisplay.slice(1);
                    
                    convertButton.prop('disabled', true).addClass('disabled')
                        .attr('title', 'The request stage must be "Approved" before it can be converted. Current stage: "' + stageDisplay + '".');
                }
            }
        },

        updateRequestStageVisibility: function() {
            // Use the smart conditional system to handle all stage-based visibility
            if (typeof window.ArsolConditionalVisibility !== 'undefined') {
                window.ArsolConditionalVisibility.updateConditionalVisibilityForField('request_stage');
            }
        },

        updateFeedbackValidation: function() {
            var selectedStage = $('#request_stage').val() || '';
            var $onholdValidation = $('#arsol_request_onhold_feedback_validation');
            
            // Only on-hold feedback is required
            if (selectedStage === 'on-hold') {
                $onholdValidation.prop('required', true);
            } else {
                $onholdValidation.prop('required', false);
            }
        },

        validateRequestFeedback: function() {
            var selectedStage = $('#request_stage').val() || '';
            
            // Validate on-hold feedback as required
            if (selectedStage === 'on-hold') {
                var feedbackContent = '';
                
                // Get content from TinyMCE editor if available
                if (typeof tinyMCE !== 'undefined' && tinyMCE.get('arsol_pfw_request_onhold_feedback')) {
                    feedbackContent = tinyMCE.get('arsol_pfw_request_onhold_feedback').getContent();
                } else {
                    // Fallback to textarea
                    feedbackContent = $('#arsol_pfw_request_onhold_feedback').val();
                }
                
                // Remove HTML tags and check if there's actual content
                var textContent = feedbackContent.replace(/<[^>]*>/g, '').trim();
                
                if (!textContent) {
                    alert('On-Hold feedback is required when request stage is "On Hold".');
                    
                    // Focus on the editor
                    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('arsol_pfw_request_onhold_feedback')) {
                        tinyMCE.get('arsol_pfw_request_onhold_feedback').focus();
                    } else {
                        $('#arsol_pfw_request_onhold_feedback').focus();
                    }
                    
                    return false;
                }
            }
            
            // Validate under-review feedback (optional but if provided should not be empty)
            if (selectedStage === 'under-review') {
                var underReviewContent = '';
                
                // Get content from TinyMCE editor if available
                if (typeof tinyMCE !== 'undefined' && tinyMCE.get('arsol_pfw_request_underreview_feedback')) {
                    underReviewContent = tinyMCE.get('arsol_pfw_request_underreview_feedback').getContent();
                } else {
                    // Fallback to textarea
                    underReviewContent = $('#arsol_pfw_request_underreview_feedback').val();
                }
                
                // Check if content was started but is essentially empty
                var underReviewTextContent = underReviewContent.replace(/<[^>]*>/g, '').trim();
                
                // If editor has some content but it's essentially empty, warn user
                if (underReviewContent && underReviewContent.length > 10 && !underReviewTextContent) {
                    alert('Under Review feedback appears to be empty. Please provide meaningful feedback or clear the field.');
                    
                    // Focus on the editor
                    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('arsol_pfw_request_underreview_feedback')) {
                        tinyMCE.get('arsol_pfw_request_underreview_feedback').focus();
                    } else {
                        $('#arsol_pfw_request_underreview_feedback').focus();
                    }
                    
                    return false;
                }
            }
            
            return true;
        },

        handleFormSubmission: function() {
            // Handle form submission validation for save/publish buttons
            $(document).on('click', '#save-post, #publish', function(e) {
                // Only handle if we're on a request page
                if (!$('#request_stage').length) return;
                
                // Validate request feedback before allowing form submission
                if (!ArsolRequest.validateRequestFeedback()) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Only initialize on request admin pages
        if ($('body').hasClass('post-type-arsol-pfw-request') || $('#request_details').length > 0) {
            ArsolRequest.init();
        }
        
        // Update button state on page load for request pages
        if ($('#request_stage').length) {
            ArsolRequest.updateConversionButtonState();
            ArsolRequest.updateRequestStageVisibility();
            ArsolRequest.updateFeedbackValidation();
        }
    });

})(jQuery);
