// JS for Arsol Proposal Admin (Invoice & Budget sections)
(function($) {
    'use strict';

    // Proposal Validation and Toggle System
    var ArsolProposal = {
        init: function() {
            this.bindEvents();
            this.initialValidation();
        },

        bindEvents: function() {
            // Initial toggle on page load
            this.toggleCostProposalSections();

            // Toggle when dropdown changes
            $('#cost_proposal_type').on('change', function() {
                ArsolProposal.toggleCostProposalSections();
                // Removed validation trigger here - let it happen naturally on input changes
                // ArsolProposal.validateProposal(); // Re-validate when type changes
            });

            // Run validation on various input changes
            $(document).on('input change', 'select[name="post_author_override"], input[name="proposal_budget"], input[name="proposal_budget_details"]', function() {
                ArsolProposal.validateProposal();
            });

            // Run validation on quotation line item changes
            $(document).on('input change', '.product-line-item input, .recurring-fee-line-item input, .onetime-fee-line-item input, .product-line-item select', function() {
                if ($('#cost_proposal_type').val() === 'quotation') {
                    ArsolProposal.validateProposal();
                }
            });

            // Add real-time validation feedback and smart cleanup on submission
            $('form#post').on('submit', function(e) {
                console.log('Form submission started');
                // First run all pre-save cleanup tasks (only on submit, not on real-time validation)
                ArsolProposal.presaveCleanup();
                console.log('Pre-save cleanup completed');
                // Then run validation to show inline error messages
                var isValid = ArsolProposal.validateProposal();
                console.log('Validation result:', isValid);
                // Let the browser's native HTML5 validation handle the actual submission blocking
                // This provides a better user experience with clear field-level feedback
            });
        },

        toggleCostProposalSections: function() {
            var selectedType = $('#cost_proposal_type').val();
            
            $('#arsol_budget_estimates_metabox').hide();
            $('#arsol_proposal_quotation_metabox').hide();

            if (selectedType === 'budget') {
                $('#arsol_budget_estimates_metabox').show();
            } else if (selectedType === 'quotation') {
                $('#arsol_proposal_quotation_metabox').show();
            }
        },

        validateProposal: function() {
            var isValid = true;
            var proposalType = $('#cost_proposal_type').val();
            var customer = $('select[name="post_author_override"]').val();

            // Clear any existing validation messages
            $('.arsol-validation-error').remove();

            // Customer is always required - add HTML5 validation
            var customerSelect = $('select[name="post_author_override"]');
            if (!customer) {
                isValid = false;
                customerSelect.attr('required', true);
                if (!customerSelect.siblings('.arsol-validation-error').length) {
                    customerSelect.after('<div class="arsol-validation-error" style="color: #dc3232; font-size: 13px; margin-top: 5px;">Please select a customer before publishing.</div>');
                }
            } else {
                customerSelect.removeAttr('required');
            }

            // Type-specific validation (without aggressive auto-cleanup)
            if (proposalType === 'budget') {
                var budgetAmountInput = $('input[name="proposal_budget"]');
                var budgetDetailsInput = $('input[name="proposal_budget_details"]');
                var recurringBudgetInput = $('input[name="proposal_recurring_budget"]');
                var budgetAmount = budgetAmountInput.val();
                var budgetDetails = budgetDetailsInput.val();
                var recurringBudget = recurringBudgetInput.val();
                
                // Check if user has started filling budget fields
                var hasBudgetContent = budgetAmount || budgetDetails || recurringBudget;
                
                if (hasBudgetContent) {
                    // Only validate if user has started adding content
                    if (!budgetAmount || parseFloat(budgetAmount) <= 0) {
                        isValid = false;
                        budgetAmountInput.attr('required', true);
                        if (!budgetAmountInput.siblings('.arsol-validation-error').length) {
                            budgetAmountInput.after('<div class="arsol-validation-error" style="color: #dc3232; font-size: 13px; margin-top: 5px;">Please enter a valid budget amount.</div>');
                        }
                    } else {
                        budgetAmountInput.removeAttr('required');
                    }
                    
                    if (budgetAmount && !budgetDetails) {
                        isValid = false;
                        budgetDetailsInput.attr('required', true);
                        if (!budgetDetailsInput.siblings('.arsol-validation-error').length) {
                            budgetDetailsInput.after('<div class="arsol-validation-error" style="color: #dc3232; font-size: 13px; margin-top: 5px;">Please provide budget details.</div>');
                        }
                    } else {
                        budgetDetailsInput.removeAttr('required');
                    }
                } else {
                    // Clear validation for empty budget
                    budgetAmountInput.removeAttr('required');
                    budgetDetailsInput.removeAttr('required');
                }
            } else if (proposalType === 'quotation') {
                // Clear any required attributes from budget fields since they're hidden
                $('input[name="proposal_budget"]').removeAttr('required');
                $('input[name="proposal_budget_details"]').removeAttr('required');
                $('input[name="proposal_recurring_budget"]').removeAttr('required');
                
                // Check if user has added any line items
                var hasAnyLineItems = $('.arsol-line-item.arsol-product-item, .arsol-line-item.arsol-recurring-fee-item, .arsol-line-item.arsol-fee-item, .arsol-line-item.arsol-shipping-fee-item').length > 0;
                
                if (hasAnyLineItems) {
                    // Only validate if user has started adding line items
                    var hasValidItem = false;
                    $('.arsol-line-item.arsol-product-item, .arsol-line-item.arsol-recurring-fee-item, .arsol-line-item.arsol-fee-item, .arsol-line-item.arsol-shipping-fee-item').each(function() {
                        var description = $(this).find('input[name*="description"], select[name*="product"]').val();
                        var amount = $(this).find('input[name*="amount"], input[name*="price"]').val();
                        
                        if (description && amount && parseFloat(amount) > 0) {
                            hasValidItem = true;
                            return false; // Break loop
                        }
                    });
                    
                    if (!hasValidItem) {
                        isValid = false;
                        var quotationContainer = $('#proposal_quotation_builder');
                        if (!quotationContainer.find('.arsol-validation-error').length) {
                            quotationContainer.prepend('<div class="arsol-validation-error" style="color: #dc3232; font-size: 13px; margin-bottom: 15px; padding: 10px; background: #fff2f2; border-left: 4px solid #dc3232;">Please complete the line items you\'ve added or remove them to save as a basic proposal.</div>');
                        }
                    }
                }
            }

            // Clear budget field required attributes for any proposal type that isn't 'budget'
            if (proposalType !== 'budget') {
                $('input[name="proposal_budget"]').removeAttr('required');
                $('input[name="proposal_budget_details"]').removeAttr('required');
                $('input[name="proposal_recurring_budget"]').removeAttr('required');
            }

            // Button disabling removed - now uses HTML5 validation with inline error messages
            // Auto-cleanup moved to form submission to prevent overly sensitive behavior
            // $('.arsol-confirm-conversion, #publish').prop('disabled', !isValid);
            
            return isValid;
        },

        // Pre-save cleanup function - runs all cleanup tasks before saving
        presaveCleanup: function() {
            console.log('Pre-save cleanup started');
            // Clean up empty proposal sections
            this.cleanupEmptyProposalSections();
            console.log('Pre-save cleanup finished');
            
            // Add other pre-save cleanup tasks here as needed
            // this.cleanupOtherStuff();
        },

        // Clean up empty proposal sections (moved from cleanupEmptySections)
        cleanupEmptyProposalSections: function() {
            var proposalType = $('#cost_proposal_type').val();
            console.log('Cleanup check - current proposal type:', proposalType);
            
            // Only cleanup if the user is actually trying to save/submit
            // Don't cleanup if user is just exploring different proposal types
            if (proposalType === 'budget') {
                var budgetAmountInput = $('input[name="proposal_budget"]');
                var budgetDetailsInput = $('input[name="proposal_budget_details"]');
                var recurringBudgetInput = $('input[name="proposal_recurring_budget"]');
                var budgetAmount = budgetAmountInput.val();
                var budgetDetails = budgetDetailsInput.val();
                var recurringBudget = recurringBudgetInput.val();
                
                // Check if budget section is completely empty
                var hasBudgetContent = budgetAmount || budgetDetails || recurringBudget;
                
                if (!hasBudgetContent) {
                    // Auto-cleanup: set type to 'none' if completely empty on save
                    $('#cost_proposal_type').val('none');
                    console.log('Pre-save cleanup: Empty budget section changed to "none"');
                }
            } else if (proposalType === 'quotation') {
                // Check if quotation section is completely empty
                var hasAnyLineItems = $('.arsol-line-item.arsol-product-item, .arsol-line-item.arsol-recurring-fee-item, .arsol-line-item.arsol-fee-item, .arsol-line-item.arsol-shipping-fee-item').length > 0;
                console.log('Cleanup check - found line items:', hasAnyLineItems, 'count:', $('.arsol-line-item.arsol-product-item, .arsol-line-item.arsol-recurring-fee-item, .arsol-line-item.arsol-fee-item, .arsol-line-item.arsol-shipping-fee-item').length);
                
                if (!hasAnyLineItems) {
                    // Auto-cleanup: set type to 'none' if no line items exist on save
                    $('#cost_proposal_type').val('none');
                    console.log('Pre-save cleanup: Empty quotation section changed to "none"');
                } else {
                    console.log('Pre-save cleanup: Quotation has line items, keeping proposal type as quotation');
                }
            }
        },

        initialValidation: function() {
            // Initial validation
            this.validateProposal();
        }
    };

    // Budget Totals System
    var ArsolBudget = {
        init: function() {
            this.bindEvents();
            this.updateBudgetTotals();
        },

        bindEvents: function() {
            // Bind events using consolidated selectors
            $('.js-amount-input').on('input', this.updateBudgetTotals.bind(this));
            $('.js-billing-input').on('change', this.updateBudgetTotals.bind(this));
        },

        formatPrice: function(price) {
            var currencySymbol = arsol_budget_vars.currency_symbol || '$';
            var formattedPrice = Number(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            return '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">' + currencySymbol + '</span>' + formattedPrice + '</bdi></span>';
        },

        updateBudgetTotals: function() {
            // Update one-time budget - target first amount input for one-time budget
            var oneTimeAmount = parseFloat($('.js-amount-input').first().val()) || 0;
            $('.budget-total-display').html(this.formatPrice(oneTimeAmount));
            $('#budget-onetime-total-display').html(this.formatPrice(oneTimeAmount));
            
            // Update recurring budget - target recurring amount input specifically
            var recurringAmount = parseFloat($('.recurring-budget-amount-input').val()) || 0;
            var interval = parseInt($('.billing-interval').val()) || 1;
            var period = $('.billing-period').val();
            
            var periodDisplay = period === 'month' ? 'mo' : (period === 'year' ? 'yr' : (period === 'week' ? 'wk' : (period === 'day' ? 'day' : period)));
            var intervalText = interval > 1 ? interval : '';
            var billingText = '/' + intervalText + periodDisplay;
            
            $('.recurring-budget-total-display').html(this.formatPrice(recurringAmount));
            $('.arsol-billing-period').text(billingText);
            $('#budget-recurring-total-display').html(this.formatPrice(recurringAmount));
            $('#budget-recurring-period').text(billingText);
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Initialize all systems
        ArsolProposal.init();
        
        // Initialize budget system if it exists
        if ($('#proposal_budget_builder').length > 0) {
            ArsolBudget.init();
        }
    });

})(jQuery); 