// JS for Arsol Request Admin
(function($) {
    'use strict';

    var ArsolRequest = {
        init: function() {
            console.log('ArsolRequest: Initializing request admin functionality');
            
            this.initConvertButton();
            this.initValidation();
            this.updateRequestStageVisibility();
        },
        
        initConvertButton: function() {
            console.log('ArsolRequest: Initializing convert button');
            
            if (!$('#request_stage').length) return;
            
            this.updateConvertButtonState();
        },
        
        initValidation: function() {
            console.log('ArsolRequest: Initializing validation');
            
            // Add validation for required fields based on conditions
            $('form').on('submit', function(e) {
                var validationErrors = [];
                
                // Check all validation fields
                $('.arsol-pfw-validation-field').each(function() {
                    var $field = $(this);
                    var validationType = $field.data('validation-type');
                    var validationCondition = $field.data('validation-condition');
                    var validationValue = $field.data('validation-value');
                    var validationRequired = $field.data('validation-required');
                    var validationMessage = $field.data('validation-message');
                    
                    if (validationType === 'conditional' && validationCondition === 'request_stage') {
                        var currentStage = $('#request_stage').val();
                        
                        if (currentStage === validationValue) {
                            var requiredFieldValue = $('#' + validationRequired).val();
                            
                            if (!requiredFieldValue || requiredFieldValue.trim() === '') {
                                validationErrors.push(validationMessage);
                            }
                        }
                    }
                });
                
                if (validationErrors.length > 0) {
                    e.preventDefault();
                    alert(validationErrors.join('\n'));
                    return false;
                }
            });
        },

        // Initialize event handlers
        attachEventHandlers: function() {
            // Handle request stage changes
            $(document).on('change', '#request_stage', function() {
                console.log('Request stage changed to:', $(this).val());
                ArsolRequest.updateRequestStageVisibility();
            });
            
        bindEvents: function() {
            // Handle conversion confirmation for requests
            this.handleConversionConfirmation();
            this.handleStatusChanges();
            this.handleFormSubmission();
        },

        initializeComponents: function() {
            // Initialize any components specific to requests
            this.updateConversionButtonState();
            this.updateRequestStatusVisibility();
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
            // Handle request status changes
            $(document).on('change', '#request_status', function() {
                ArsolRequest.updateConversionButtonState();
                ArsolRequest.updateRequestStatusVisibility();
                ArsolRequest.updateFeedbackValidation();
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
        },

        updateRequestStatusVisibility: function() {
            var selectedStatus = $('#request_status').val() || '';
            
            // Hide all feedback sections first
            $('#arsol_request_onhold_feedback_section, #arsol_request_underreview_feedback_section').each(function() {
                this.style.setProperty('display', 'none', 'important');
            });
            
            // Show the appropriate section based on current status
            if (selectedStatus === 'on-hold') {
                $('#arsol_request_onhold_feedback_section')[0].style.setProperty('display', 'block', 'important');
            } else if (selectedStatus === 'under-review') {
                $('#arsol_request_underreview_feedback_section')[0].style.setProperty('display', 'block', 'important');
            }
        },

        updateFeedbackValidation: function() {
            var selectedStatus = $('#request_status').val() || '';
            var $onholdValidation = $('#arsol_request_onhold_feedback_validation');
            
            // Only on-hold feedback is required
            if (selectedStatus === 'on-hold') {
                $onholdValidation.prop('required', true);
            } else {
                $onholdValidation.prop('required', false);
            }
        },

        validateRequestFeedback: function() {
            var selectedStatus = $('#request_status').val() || '';
            
            // Validate on-hold feedback as required
            if (selectedStatus === 'on-hold') {
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
                    alert('On-Hold feedback is required when request status is "On Hold".');
                    
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
            if (selectedStatus === 'under-review') {
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
                if (!$('#request_status').length) return;
                
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
        if ($('#request_status').length) {
            ArsolRequest.updateConversionButtonState();
            ArsolRequest.updateRequestStatusVisibility();
            ArsolRequest.updateFeedbackValidation();
        }
    });

})(jQuery);
