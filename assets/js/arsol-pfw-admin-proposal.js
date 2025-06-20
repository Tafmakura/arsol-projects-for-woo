// JS for Arsol Proposal Admin (Invoice & Budget sections)
(function($) {
    'use strict';

    // Proposal Validation and Toggle System
    var ArsolProposal = {
        init: function() {
            this.bindEvents();
            this.updateRequiredFields();
        },

        bindEvents: function() {
            // Initial toggle on page load
            this.toggleCostProposalSections();
            this.updateConditionalVisibility();

            // Toggle when dropdown changes
            $('#arsol_pfw_proposal_costing_type').on('change', function() {
                ArsolProposal.toggleCostProposalSections();
                ArsolProposal.updateRequiredFields();
                ArsolProposal.updateConditionalVisibility();
                // Also update quotation field requirements if quotation object exists
                if (typeof ArsolProposalQuotation !== 'undefined') {
                    ArsolProposalQuotation.updateQuotationFieldRequirements();
                }
            });

            // Update required fields on input changes
            $(document).on('input change', 'select[name="post_author_override"], input[name="arsol_pfw_proposal_budget_onetime_amount"], input[name="arsol_pfw_proposal_budget_onetime_amount_details"]', function() {
                ArsolProposal.updateRequiredFields();
            });

            // Pre-save cleanup on form submission
            $('form#post').on('submit', function(e) {
                ArsolProposal.presaveCleanup();
            });
        },

        toggleCostProposalSections: function() {
            var selectedType = $('#arsol_pfw_proposal_costing_type').val();
            
            $('#arsol_budget_estimates_metabox').hide();
            $('#arsol_proposal_quotation_metabox').hide();

            if (selectedType === 'budget') {
                $('#arsol_budget_estimates_metabox').show();
            } else if (selectedType === 'quotation') {
                $('#arsol_proposal_quotation_metabox').show();
            }
            
            // Update proposal summary based on type
            this.updateProposalSummary(selectedType);
        },

        updateRequiredFields: function() {
            var proposalType = $('#arsol_pfw_proposal_costing_type').val();

            // Customer is always required
            var customerSelect = $('select[name="post_author_override"]');
            customerSelect.attr('required', true);

            // Clear all field requirements first - budget fields
            $('input[name="arsol_pfw_proposal_budget_onetime_amount"]').removeAttr('required');
            $('input[name="arsol_pfw_proposal_budget_onetime_amount_details"]').removeAttr('required');
            
            // Clear all field requirements first - quotation fields (existing and new line items)
            $('input[name*="line_items"][name*="price"]').removeAttr('required');
            $('select[name*="line_items"][name*="product_id"]').removeAttr('required');
            $('input[name*="line_items"][name*="description"]').removeAttr('required');
            $('input[name*="line_items"][name*="amount"]').removeAttr('required');

            // Type-specific required field management
            if (proposalType === 'budget') {
                // Make budget fields required (these have hardcoded required in HTML)
                $('input[name="arsol_pfw_proposal_budget_onetime_amount"]').attr('required', true);
                $('input[name="arsol_pfw_proposal_budget_onetime_amount_details"]').attr('required', true);
                
                // Ensure quotation fields are not required when budget is selected
                $('input[name*="line_items"][name*="price"]').removeAttr('required');
                $('select[name*="line_items"][name*="product_id"]').removeAttr('required');
                $('input[name*="line_items"][name*="description"]').removeAttr('required');
                $('input[name*="line_items"][name*="amount"]').removeAttr('required');
                
            } else if (proposalType === 'quotation') {
                // Ensure budget fields are not required when quotation is selected
                $('input[name="arsol_pfw_proposal_budget_onetime_amount"]').removeAttr('required');
                $('input[name="arsol_pfw_proposal_budget_onetime_amount_details"]').removeAttr('required');
                
                // Make quotation line item fields required (these have hardcoded required in templates)
                $('input[name*="line_items"][name*="price"]').attr('required', true);
                $('select[name*="line_items"][name*="product_id"]').attr('required', true);
                $('input[name*="line_items"][name*="description"]').attr('required', true);
                $('input[name*="line_items"][name*="amount"]').attr('required', true);
                
            } else {
                // For 'none' type, ensure no fields are required
                $('input[name="arsol_pfw_proposal_budget_onetime_amount"]').removeAttr('required');
                $('input[name="arsol_pfw_proposal_budget_onetime_amount_details"]').removeAttr('required');
                $('input[name*="line_items"][name*="price"]').removeAttr('required');
                $('select[name*="line_items"][name*="product_id"]').removeAttr('required');
                $('input[name*="line_items"][name*="description"]').removeAttr('required');
                $('input[name*="line_items"][name*="amount"]').removeAttr('required');
            }
            
            // Note: WordPress backend validation will handle actual validation
            // This just provides visual feedback to users
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
            var proposalType = $('#arsol_pfw_proposal_costing_type').val();
            console.log('Cleanup check - current proposal type:', proposalType);
            
            // Only cleanup if the user is actually trying to save/submit
            // Don't cleanup if user is just exploring different proposal types
            if (proposalType === 'budget') {
                var budgetAmountInput = $('input[name="arsol_pfw_proposal_budget_onetime_amount"]');
                var budgetDetailsInput = $('input[name="arsol_pfw_proposal_budget_onetime_amount_details"]');
                var recurringBudgetInput = $('input[name="proposal_recurring_budget"]');
                var budgetAmount = budgetAmountInput.val();
                var budgetDetails = budgetDetailsInput.val();
                var recurringBudget = recurringBudgetInput.val();
                
                // Check if budget section is completely empty
                var hasBudgetContent = budgetAmount || budgetDetails || recurringBudget;
                
                if (!hasBudgetContent) {
                    // Auto-cleanup: set type to 'none' if completely empty on save
                    $('#arsol_pfw_proposal_costing_type').val('none');
                    console.log('Pre-save cleanup: Empty budget section changed to "none"');
                }
            } else if (proposalType === 'quotation') {
                // Check if quotation section is completely empty
                var hasAnyLineItems = $('.arsol-line-item.arsol-product-item, .arsol-line-item.arsol-recurring-fee-item, .arsol-line-item.arsol-fee-item, .arsol-line-item.arsol-shipping-fee-item').length > 0;
                console.log('Cleanup check - found line items:', hasAnyLineItems, 'count:', $('.arsol-line-item.arsol-product-item, .arsol-line-item.arsol-recurring-fee-item, .arsol-line-item.arsol-fee-item, .arsol-line-item.arsol-shipping-fee-item').length);
                
                if (!hasAnyLineItems) {
                    // Auto-cleanup: set type to 'none' if no line items exist on save
                    $('#arsol_pfw_proposal_costing_type').val('none');
                    console.log('Pre-save cleanup: Empty quotation section changed to "none"');
                } else {
                    console.log('Pre-save cleanup: Quotation has line items, keeping proposal type as quotation');
                }
            }
        },

        initialValidation: function() {
            // Set initial required fields
            this.updateRequiredFields();
        },

        updateProposalSummary: function(proposalType) {
            // Trigger appropriate summary updates based on proposal type
            if (proposalType === 'budget') {
                // Trigger budget summary update
                if (typeof ArsolBudget !== 'undefined' && ArsolBudget.updateSummary) {
                    ArsolBudget.updateSummary();
                }
            } else if (proposalType === 'quotation') {
                // Trigger quotation summary update
                if (typeof ArsolProposalQuotation !== 'undefined' && ArsolProposalQuotation.updateSummary) {
                    ArsolProposalQuotation.updateSummary();
                }
            }
            // Note: Visibility is now handled by the class-based conditional system
        },

        /**
         * Update conditional visibility based on cost proposal type
         */
        updateConditionalVisibility: function() {
            const costType = $('#arsol_pfw_proposal_costing_type').val();
            
            // Hide all conditional elements with inline display:none
            $('.arsol-pfw-show-if-proposal-cost-type-is-none, .arsol-pfw-show-if-proposal-cost-type-is-budget, .arsol-pfw-show-if-proposal-cost-type-is-quotation').css('display', 'none');
            
            // Show elements based on current cost type by removing inline display style
            if (costType === 'none') {
                $('.arsol-pfw-show-if-proposal-cost-type-is-none').css('display', '');
            } else if (costType === 'budget') {
                $('.arsol-pfw-show-if-proposal-cost-type-is-budget').css('display', '');
            } else if (costType === 'quotation') {
                $('.arsol-pfw-show-if-proposal-cost-type-is-quotation').css('display', '');
            }
            
            // Handle hide-if classes (show all first, then hide specific ones)
            $('.arsol-pfw-hide-if-proposal-cost-type-is-none, .arsol-pfw-hide-if-proposal-cost-type-is-budget, .arsol-pfw-hide-if-proposal-cost-type-is-quotation').css('display', '');
            
            // Hide elements that should be hidden for current cost type
            if (costType === 'none') {
                $('.arsol-pfw-hide-if-proposal-cost-type-is-none').css('display', 'none');
            } else if (costType === 'budget') {
                $('.arsol-pfw-hide-if-proposal-cost-type-is-budget').css('display', 'none');
            } else if (costType === 'quotation') {
                $('.arsol-pfw-hide-if-proposal-cost-type-is-quotation').css('display', 'none');
            }
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
            $('#budget-recurring-period').text(billingText);
            $('#budget-recurring-total-display').html(this.formatPrice(recurringAmount));
            
            // Update summary if it exists
            this.updateSummary();
        },
        
        updateSummary: function() {
            // Copy existing totals from budget displays instead of recalculating
            // This prevents NaN errors and ensures consistency
            
            // Copy one-time budget total
            $('#summary-budget-onetime-display').html($('#budget-onetime-total-display').html());
            
            // Copy recurring budget total and billing period
            $('#summary-budget-recurring-display').html($('#budget-recurring-total-display').html());
            $('#summary-budget-billing-period').text($('#budget-recurring-period').text());
            
            // Show/hide budget rows based on display content
            var oneTimeText = $('#budget-onetime-total-display').text();
            var hasOneTime = oneTimeText && !oneTimeText.includes('$0.00');
            $('#budget-onetime-row').toggle(hasOneTime);
            
            var recurringText = $('#budget-recurring-total-display').text();
            var hasRecurring = recurringText && !recurringText.includes('$0.00');
            $('#budget-recurring-row').toggle(hasRecurring);
            
            if (hasRecurring) {
                // Add start date if available
                var startDate = $('.recurring-budget-start-date').val();
                if (startDate) {
                    var formattedDate = new Date(startDate).toLocaleDateString();
                    $('#summary-budget-start-date').text(' (starts ' + formattedDate + ')');
                } else {
                    $('#summary-budget-start-date').text('');
                }
            }
            
            // Show empty state if no budget data is available
            var hasBudgetData = hasOneTime || hasRecurring;
            $('#budget-empty-state').toggle(!hasBudgetData);
        }
    };

    var ArsolProposalQuotation = {
        // A flag to prevent multiple AJAX requests from firing at once.
        calculating: false,

        init: function() {
            this.product_template = wp.template('arsol-product-line-item');
            this.onetime_fee_template = wp.template('arsol-onetime-fee-line-item');
            this.recurring_fee_template = wp.template('arsol-recurring-fee-line-item');
            this.shipping_fee_template = wp.template('arsol-shipping-fee-line-item');
            this.line_item_id = 0;
            this.bindEvents();
            this.loadExistingItems();
            this.setInitialAverageTotal();
        },

        getDailyCost: function(price, interval, period) {
            price = parseFloat(price) || 0;
            interval = parseInt(interval) || 1;
            var days_in_period = 0;
            var constants = arsol_proposal_quotation_vars.calculation_constants;

            switch (period) {
                case 'day':
                    days_in_period = 1;
                    break;
                case 'week':
                    days_in_period = 7;
                    break;
                case 'month':
                    days_in_period = constants.days_in_month;
                    break;
                case 'year':
                    days_in_period = constants.days_in_year;
                    break;
            }

            if (days_in_period === 0 || interval === 0) {
                return 0;
            }
            
            var total_days_in_cycle = days_in_period * interval;
            return price / total_days_in_cycle;
        },

        setInitialAverageTotal: function() {
            // This function is now obsolete with client-side calculations, 
            // but we'll keep it here in case it's needed later.
            // It's called on init but does nothing if the localized var isn't there.
            var initialTotal = arsol_proposal_quotation_vars.average_monthly_total_formatted;
            if (initialTotal) {
                $('#average-monthly-total-display').html(initialTotal);
            }
        },

        updateRecurringTotals: function(recurringTotals, interval, period, amount) {
            if (interval && period && amount > 0) {
                var cycleKey = this.getCycleKey(interval, period);
                if (!recurringTotals[cycleKey]) {
                    recurringTotals[cycleKey] = { total: 0, interval: interval, period: period };
                }
                recurringTotals[cycleKey].total += amount;
            }
        },

        shippingMethodChanged: function(e) {
            var $select = $(e.currentTarget);
            var $row = $select.closest('.arsol-line-item');
            var $input = $row.find('.arsol-description-input');
            var selectedVal = $select.val();

            if (selectedVal === 'custom') {
                $input.val('').show().focus();
            } else if (selectedVal === '') {
                $input.val('').show();
            } else {
                var methodName = $select.find('option:selected').data('name');
                $input.val(methodName).hide();
            }
        },

        bindEvents: function() {
            var $builder = $('#proposal_quotation_builder');
            
            // Use event delegation for better performance with dynamic content
            $builder
                .on('click', '.add-line-item', this.addLineItem.bind(this))
                .on('click', '.remove-line-item', this.removeLineItem.bind(this))
                .on('change', '.arsol-product-item select.arsol-description-input', this.productChanged.bind(this))
                .on('change', '.arsol-select-full', this.shippingMethodChanged.bind(this));
            
            // Use jQuery's one() method for input events with debouncing (WordPress pattern)
            var debouncedCalculate = _.debounce(this.calculateTotals.bind(this), 300);
            
            // Targeted validation for each section
            var debouncedValidateProduct = _.debounce(this.updateProductButtonState.bind(this), 300);
            var debouncedValidateRecurringFee = _.debounce(this.updateRecurringFeeButtonState.bind(this), 300);
            var debouncedValidateOnetimeFee = _.debounce(this.updateOnetimeFeeButtonState.bind(this), 300);
            var debouncedValidateShippingFee = _.debounce(this.updateShippingFeeButtonState.bind(this), 300);
            
            // Products & Services section
            $builder.on('input change', '#product-lines-body .arsol-quantity-input, #product-lines-body .arsol-sale-price-input, #product-lines-body .arsol-price-input, #product-lines-body .arsol-description-input', function() {
                debouncedCalculate();
                debouncedValidateProduct();
            });
            
            // Recurring Fees section
            $builder.on('input change', '#recurring-fee-lines-body .arsol-amount-input, #recurring-fee-lines-body .arsol-billing-select, #recurring-fee-lines-body .arsol-description-input', function() {
                debouncedCalculate();
                debouncedValidateRecurringFee();
            });
            
            // One-Time Fees section
            $builder.on('input change', '#onetime-fee-lines-body .arsol-amount-input, #onetime-fee-lines-body .arsol-description-input', function() {
                debouncedCalculate();
                debouncedValidateOnetimeFee();
            });
            
            // Shipping Fees section
            $builder.on('input change', '#shipping-lines-body .arsol-amount-input, #shipping-lines-body .arsol-description-input', function() {
                debouncedCalculate();
                debouncedValidateShippingFee();
            });
            
            // Add WordPress-style custom event triggers for extensibility
            $(document).trigger('arsol:quotation-events-bound', [$builder]);
        },
        
        loadExistingItems: function() {
            var self = this;
            var items = arsol_proposal_quotation_vars.line_items;

            if (items && items.products) {
                $.each(items.products, function(id, itemData) { self.renderRow('product', itemData); });
            }
            if (items && items.one_time_fees) {
                $.each(items.one_time_fees, function(id, itemData) { self.renderRow('onetime-fee', itemData); });
            }
            if (items && items.recurring_fees) {
                $.each(items.recurring_fees, function(id, itemData) { self.renderRow('recurring-fee', itemData); });
            }
            if (items && items.shipping_fees) {
                $.each(items.shipping_fees, function(id, itemData) { self.renderRow('shipping-fee', itemData); });
            }
            this.calculateTotals();
            this.toggleStartDateColumn();
            this.updateAddButtonStates();
        },

        renderRow: function(type, data) {
            this.line_item_id++;
            data.id = this.line_item_id;
            var template, container;

            if (type === 'product') {
                template = this.product_template;
                container = '#product-lines-body';
            } else if (type === 'onetime-fee') {
                template = this.onetime_fee_template;
                container = '#onetime-fee-lines-body';
            } else if (type === 'recurring-fee') {
                template = this.recurring_fee_template;
                container = '#recurring-fee-lines-body';
            } else if (type === 'shipping-fee') {
                template = this.shipping_fee_template;
                container = '#shipping-lines-body';
            } else {
                return;
            }
            
            var $newRow = $(template(data));
            $(container).append($newRow);

            if (type === 'shipping-fee') {
                var $select = $newRow.find('.arsol-select-full');
                var $input = $newRow.find('.arsol-description-input');
                var initialName = data.name || '';
                var matchFound = false;

                if (initialName) {
                    $select.find('option').each(function() {
                        if ($(this).data('name') === initialName) {
                            $(this).prop('selected', true);
                            matchFound = true;
                            return false; // break loop
                        }
                    });
                }

                if (matchFound) {
                    $input.hide();
                } else {
                    if (initialName) {
                        $select.val('custom');
                    }
                    $input.show();
                }
            }

            if (type === 'product') {
                this.initSelect2($newRow);
                if(data.product_id && !data.regular_price) {
                     this.fetchProductDetails($newRow, data.product_id);
                } else if (data.product_type && (data.product_type === 'subscription' || data.product_type === 'subscription_variation')) {
                    // Set subscription data for existing saved subscription products
                    $newRow.data('billing-interval', data.interval || 1);
                    $newRow.data('billing-period', data.period || 'month');
                    $newRow.data('is-subscription', true);
                    
                    // Show date input for existing subscriptions (default is "—")
                    $newRow.find('.arsol-date-column .arsol-not-applicable').hide();
                    $newRow.find('.arsol-date-column .arsol-date-input').show();
                }
                // Non-subscription products use default state (show "—", hide date input)
            }
        },

        initSelect2: function($row) {
            var $select = $row.find('.arsol-description-input');
            
            // Set up WooCommerce attributes but don't add the auto-init class yet
            // This prevents WooCommerce from automatically initializing with default settings
            $select.attr('data-placeholder', 'Search for a product...')
                   .attr('data-action', 'arsol_search_products_with_price')
                   .attr('data-allow_clear', 'false');
            
            // Manually trigger WooCommerce's enhanced select initialization
            // This gives us control over when it happens and with what configuration
            setTimeout(function() {
                // Don't add wc-product-search class to avoid WooCommerce's auto-init
                // $select.addClass('wc-product-search');
                
                // Trigger WooCommerce's enhanced select initialization with proper config
                if (typeof $.fn.selectWoo !== 'undefined') {
                    $select.filter(':not(.enhanced)').selectWoo({
                ajax: {
                    url: arsol_proposal_quotation_vars.ajax_url,
                    dataType: 'json',
                            delay: 250, // Wait 250ms after user stops typing before making request
                    data: function(params) {
                        return {
                                    action: 'arsol_search_products_with_price', // Our custom AJAX action
                                    security: arsol_proposal_quotation_vars.search_products_nonce, // WordPress nonce for security
                                    term: params.term, // The search term user typed
                                    limit: 20 // Maximum number of results to return
                                };
                            },
                            processResults: function(data) {
                                // Convert the AJAX response into Select2 format
                                var terms = [];
                                if (data) {
                                    $.each(data, function(id, text) {
                                        terms.push({ id: id, text: text });
                                    });
                                }
                                return { results: terms };
                            }
                        },
                        placeholder: 'Search for a product...', // Text shown when nothing is selected
                        minimumInputLength: 1, // User must type at least 1 character before search triggers
                        allowClear: false, // Don't show the 'X' to clear selection
                        language: {
                            errorLoading: function() {
                                // Custom message when AJAX request fails or times out
                                // This replaces the default "The results could not be loaded" message
                                // WooCommerce uses a similar workaround for Select2 issue #4355
                                // where errorLoading gets called inappropriately on dropdown open
                                return 'Still searching...';
                            }
                        }
                    }).addClass('enhanced'); // Mark as enhanced to prevent re-initialization
                }
            }, 100); // Small delay to ensure DOM is ready
        },

        fetchProductDetails: function($row, productId) {
            $.ajax({
                url: arsol_proposal_quotation_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'arsol_proposal_quotation_ajax_get_product_details',
                    nonce: arsol_proposal_quotation_vars.nonce,
                    product_id: productId
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        $row.find('.arsol-price-input').val(data.regular_price);
                        $row.find('.arsol-sale-price-input').val(data.sale_price);
                        
                        // Store the product type in the hidden input field
                        $row.find('input[name*="[product_type]"]').val(data.product_type || '');
                        
                        // Check if product is subscription type (backward compatible)
                        var isSubscription = data.product_type && (data.product_type === 'subscription' || data.product_type === 'subscription_variation');
                        console.log('fetchProductDetails response:', {
                            productType: data.product_type,
                            isSubscription: isSubscription,
                            billingInterval: data.billing_interval,
                            billingPeriod: data.billing_period
                        });
                        
                        if (isSubscription) {
                            // Store subscription billing data on the row for calculations
                            $row.data('billing-interval', data.billing_interval || 1);
                            $row.data('billing-period', data.billing_period || 'month');
                            $row.data('is-subscription', true);
                            
                            // Show date input for subscriptions (default is "—")
                            $row.find('.arsol-date-column .arsol-not-applicable').hide();
                            $row.find('.arsol-date-column .arsol-date-input').show();
                        } else {
                            // Remove subscription data for non-subscription products
                            $row.removeData('billing-interval billing-period is-subscription');
                            
                            // Switch back to default state (show "—", hide date input)
                            $row.find('.arsol-date-column .arsol-date-input').hide();
                            $row.find('.arsol-date-column .arsol-not-applicable').show();
                        }
                        
                        ArsolProposalQuotation.toggleStartDateColumn();
                        ArsolProposalQuotation.calculateTotals();
                        // Delay button state update to ensure DOM is fully updated
                        setTimeout(function() {
                            ArsolProposalQuotation.updateProductButtonState();
                        }, 100);
                    }
                }
            });
        },

        addLineItem: function(e) {
            e.preventDefault();
            
            // Validate last line item before adding new one
            var type = $(e.currentTarget).data('type');
            var lastRowValid = this.validateLastLineItem(type);
            
            if (!lastRowValid) {
                alert('Please complete the last line item before adding a new one.');
                return;
            }
            
            this.renderRow(type, {});
            this.toggleStartDateColumn();
            
            // Update required fields for newly added line items
            if (typeof ArsolProposal !== 'undefined') {
                ArsolProposal.updateRequiredFields();
            }
            
            // Update only the button for this specific section
            switch(type) {
                case 'product':
                    this.updateProductButtonState();
                    break;
                case 'recurring-fee':
                    this.updateRecurringFeeButtonState();
                    break;
                case 'onetime-fee':
                    this.updateOnetimeFeeButtonState();
                    break;
                case 'shipping-fee':
                    this.updateShippingFeeButtonState();
                    break;
            }
        },

        validateLastLineItem: function(type) {
            var containerMap = {
                'product': '#product-lines-body',
                'onetime-fee': '#onetime-fee-lines-body', 
                'recurring-fee': '#recurring-fee-lines-body',
                'shipping-fee': '#shipping-lines-body'
            };
            
            var container = containerMap[type];
            if (!container) return true;
            
            var $lastRow = $(container + ' tr.arsol-line-item').last();
            if ($lastRow.length === 0) return true; // No existing rows
            
            var description = '';
            var amount = '';
            
            if (type === 'product') {
                description = $lastRow.find('select.arsol-description-input option:selected').text();
                var price = $lastRow.find('.arsol-price-input').val();
                var salePrice = $lastRow.find('.arsol-sale-price-input').val();
                amount = salePrice || price;
                
                // Debug logging for product validation
                console.log('Product validation check:', {
                    description: description,
                    descriptionTrimmed: description.trim(),
                    price: price,
                    salePrice: salePrice,
                    amount: amount,
                    amountParsed: parseFloat(amount),
                    isValid: description && description.trim() && amount && parseFloat(amount) > 0
                });
                
                // Debug logging for product validation
                console.log('Product validation check:', {
                    description: description,
                    descriptionTrimmed: description.trim(),
                    price: price,
                    salePrice: salePrice,
                    amount: amount,
                    amountParsed: parseFloat(amount),
                    isValid: description && description.trim() && amount && parseFloat(amount) > 0
                });
            } else {
                description = $lastRow.find('.arsol-description-input').val();
                amount = $lastRow.find('.arsol-amount-input').val();
            }
            
            return description && description.trim() && amount && parseFloat(amount) > 0;
        },

        updateAddButtonStates: function() {
            // Update each section's add button independently
            this.updateAddButtonState('product', '.add-product-button');
            this.updateAddButtonState('recurring-fee', '.add-recurring-fee-button');
            this.updateAddButtonState('onetime-fee', '.add-onetime-fee-button');
            this.updateAddButtonState('shipping-fee', '.add-shipping-fee-button');
        },

        updateAddButtonState: function(type, buttonSelector) {
            var isValid = this.validateLastLineItem(type);
            $(buttonSelector).prop('disabled', !isValid);
        },

        // Convenience methods for updating individual section buttons
        updateProductButtonState: function() {
            console.log('updateProductButtonState called');
            this.updateAddButtonState('product', '.add-product-button');
        },

        updateRecurringFeeButtonState: function() {
            this.updateAddButtonState('recurring-fee', '.add-recurring-fee-button');
        },

        updateOnetimeFeeButtonState: function() {
            this.updateAddButtonState('onetime-fee', '.add-onetime-fee-button');
        },

        updateShippingFeeButtonState: function() {
            this.updateAddButtonState('shipping-fee', '.add-shipping-fee-button');
        },

        updateQuotationFieldRequirements: function() {
            var proposalType = $('#arsol_pfw_proposal_costing_type').val();
            
            if (proposalType === 'quotation') {
                // Make quotation line item fields required
                $('input[name*="line_items"][name*="regular_price"]').attr('required', true);
                $('select[name*="line_items"][name*="product_id"]').attr('required', true);
                $('input[name*="line_items"][name*="description"]').attr('required', true);
                $('input[name*="line_items"][name*="amount"]').attr('required', true);
            } else {
                // Remove required from quotation fields
                $('input[name*="line_items"][name*="regular_price"]').removeAttr('required');
                $('select[name*="line_items"][name*="product_id"]').removeAttr('required');
                $('input[name*="line_items"][name*="description"]').removeAttr('required');
                $('input[name*="line_items"][name*="amount"]').removeAttr('required');
            }
        },

        removeLineItem: function(e) {
            e.preventDefault();
            var $row = $(e.currentTarget).closest('tr');
            
            // Determine which section this row belongs to
            var type = '';
            if ($row.closest('#product-lines-body').length) {
                type = 'product';
            } else if ($row.closest('#recurring-fee-lines-body').length) {
                type = 'recurring-fee';
            } else if ($row.closest('#onetime-fee-lines-body').length) {
                type = 'onetime-fee';
            } else if ($row.closest('#shipping-lines-body').length) {
                type = 'shipping-fee';
            }
            
            $row.remove();
            this.calculateTotals();
            this.toggleStartDateColumn();
            
            // Update only the button for the section that had an item removed
            switch(type) {
                case 'product':
                    this.updateProductButtonState();
                    break;
                case 'recurring-fee':
                    this.updateRecurringFeeButtonState();
                    break;
                case 'onetime-fee':
                    this.updateOnetimeFeeButtonState();
                    break;
                case 'shipping-fee':
                    this.updateShippingFeeButtonState();
                    break;
            }
        },

        productChanged: function(e) {
            var $select = $(e.currentTarget);
            var productId = $select.val();
            var $row = $select.closest('tr');

            if (productId) {
                this.fetchProductDetails($row, productId);
            } else {
                $row.find('.arsol-price-input, .arsol-sale-price-input').val('');
                $row.removeData('billing-interval billing-period is-subscription');
                this.toggleStartDateColumn();
                this.calculateTotals();
            }
        },

        toggleStartDateColumn: function() {
            var hasSubscriptions = false;
            
            // Stage 1: Check if ANY product is a subscription (column-level visibility)
            $('#product-lines-body tr.arsol-line-item').each(function() {
                if ($(this).data('is-subscription')) {
                    hasSubscriptions = true;
                    return false; // break loop
                }
            });

            // Stage 1: Show/hide entire start date column in Products & Services
            if (hasSubscriptions) {
                $('#product-line-items .arsol-date-column').show();
            } else {
                $('#product-line-items .arsol-date-column').hide();
            }
            
            // Stage 2: Show/hide individual start date inputs per product line
            $('#product-lines-body tr.arsol-line-item').each(function() {
                var $row = $(this);
                var $dateInput = $row.find('.arsol-date-input');
                
                if ($row.data('is-subscription')) {
                    // This specific product is a subscription - show its start date input
                    $dateInput.removeClass('hidden-start-date');
                } else {
                    // This specific product is not a subscription - hide its start date input
                    $dateInput.addClass('hidden-start-date');
                }
            });
        },

        getCycleKey: function(interval, period) {
            return interval + '_' + period;
        },

        calculateTotals: function() {
            if (this.calculating) {
                return;
            }
            this.calculating = true;

            var oneTimeTotal = 0;
            var recurringTotals = {};
            var productRecurringTotals = {};
            var recurringFeeRecurringTotals = {};
            var productSubtotal = 0;
            var onetimeFeeSubtotal = 0;
            var recurringFeeSubtotal = 0;
            var shippingSubtotal = 0;

            // Calculate product totals
            $('#product-lines-body tr.arsol-line-item').each(function() {
                var quantity = parseFloat($(this).find('.arsol-quantity-input').val()) || 0;
                var salePrice = parseFloat($(this).find('.arsol-sale-price-input').val());
                var regularPrice = parseFloat($(this).find('.arsol-price-input').val());
                var price = !isNaN(salePrice) && salePrice > 0 ? salePrice : regularPrice;
                price = isNaN(price) ? 0 : price;
                var subtotal = quantity * price;
                
                $(this).find('.arsol-subtotal-column').html(ArsolProposalQuotation.formatPrice(subtotal));
                
                // Debug logging for subscription detection
                console.log('Product row check:', {
                    isSubscription: $(this).data('is-subscription'),
                    billingInterval: $(this).data('billing-interval'),
                    billingPeriod: $(this).data('billing-period'),
                    productType: $(this).find('input[name*="[product_type]"]').val(),
                    subtotal: subtotal
                });
                
                // Check if this is a subscription product based on stored data
                if ($(this).data('is-subscription')) {
                    // This is a subscription product
                    var interval = parseInt($(this).data('billing-interval')) || 1;
                    var period = $(this).data('billing-period') || 'month';
                    
                    console.log('Subscription product found - adding to recurring totals:', {
                        subtotal: subtotal,
                        interval: interval,
                        period: period
                    });
                    
                    // Create billing text for display
                    var periodText = period === 'month' ? 'mo' : (period === 'year' ? 'yr' : (period === 'week' ? 'wk' : (period === 'day' ? 'day' : period)));
                    var intervalText = interval > 1 ? interval : '';
                    var billingText = '/' + intervalText + periodText;
                    
                    $(this).find('.arsol-subtotal-column').html(ArsolProposalQuotation.formatPrice(subtotal) + ' ' + billingText);
                    
                    // Add to recurring totals
                    ArsolProposalQuotation.updateRecurringTotals(recurringTotals, interval, period, subtotal);
                    ArsolProposalQuotation.updateRecurringTotals(productRecurringTotals, interval, period, subtotal);
                } else {
                    // This is a one-time product
                    console.log('One-time product - adding to one-time total:', subtotal);
                    oneTimeTotal += subtotal;
                    productSubtotal += subtotal;
                }
            });

            // Calculate one-time fee totals
            $('#onetime-fee-lines-body tr.arsol-line-item').each(function() {
                var amount = parseFloat($(this).find('.arsol-amount-input').val()) || 0;
                $(this).find('.arsol-subtotal-column').html(ArsolProposalQuotation.formatPrice(amount));
                oneTimeTotal += amount;
                onetimeFeeSubtotal += amount;
            });

            // Calculate recurring fee totals
            $('#recurring-fee-lines-body tr.arsol-line-item').each(function() {
                var amount = parseFloat($(this).find('.arsol-amount-input').val()) || 0;
                var interval = parseInt($(this).find('.arsol-billing-select').eq(0).val()) || 1;
                var period = $(this).find('.arsol-billing-select').eq(1).val() || 'month';
                
                var periodText = period === 'month' ? 'mo' : (period === 'year' ? 'yr' : (period === 'week' ? 'wk' : (period === 'day' ? 'day' : period)));
                var intervalText = interval > 1 ? interval : '';
                var billingText = '/' + intervalText + periodText;
                
                $(this).find('.arsol-subtotal-column').html(ArsolProposalQuotation.formatPrice(amount) + ' ' + billingText);
                
                ArsolProposalQuotation.updateRecurringTotals(recurringTotals, interval, period, amount);
                ArsolProposalQuotation.updateRecurringTotals(recurringFeeRecurringTotals, interval, period, amount);
                recurringFeeSubtotal += amount;
            });

            // Calculate shipping totals
            $('#shipping-lines-body tr.arsol-line-item').each(function() {
                var amount = parseFloat($(this).find('.arsol-amount-input').val()) || 0;
                $(this).find('.arsol-subtotal-column').html(ArsolProposalQuotation.formatPrice(amount));
                oneTimeTotal += amount;
                shippingSubtotal += amount;
            });

            // Update section subtotals
            $('#product-subtotal-display').html(ArsolProposalQuotation.formatPrice(productSubtotal));
            $('#onetime-fee-subtotal-display').html(ArsolProposalQuotation.formatPrice(onetimeFeeSubtotal));
            $('#shipping-subtotal-display').html(ArsolProposalQuotation.formatPrice(shippingSubtotal));

            // Calculate average monthly totals separately for each section
            var constants = arsol_proposal_quotation_vars.calculation_constants;
            
            // Product section recurring totals
            var productDailyCost = 0;
            var hasProductRecurring = false;
            $.each(productRecurringTotals, function(key, data) {
                hasProductRecurring = true;
                var dailyCost = ArsolProposalQuotation.getDailyCost(data.total, data.interval, data.period);
                productDailyCost += dailyCost;
            });
            var productAverageMonthlyTotal = productDailyCost * constants.days_in_month;
            
            // Recurring fee section totals
            var recurringFeeDailyCost = 0;
            var hasRecurringFees = false;
            $.each(recurringFeeRecurringTotals, function(key, data) {
                hasRecurringFees = true;
                var dailyCost = ArsolProposalQuotation.getDailyCost(data.total, data.interval, data.period);
                recurringFeeDailyCost += dailyCost;
            });
            var recurringFeeAverageMonthlyTotal = recurringFeeDailyCost * constants.days_in_month;
            
            // Combined totals for grand total
            var totalDailyCost = productDailyCost + recurringFeeDailyCost;
            var averageMonthlyTotal = totalDailyCost * constants.days_in_month;
            var averageYearlyTotal = totalDailyCost * constants.days_in_year;
            var hasRecurring = hasProductRecurring || hasRecurringFees;

            // Update section recurring displays
            if (hasProductRecurring) {
                $('#product-avg-monthly-display').html(ArsolProposalQuotation.formatPrice(productAverageMonthlyTotal) + ' /mo');
            } else {
                $('#product-avg-monthly-display').html(ArsolProposalQuotation.formatPrice(0));
            }
            
            if (hasRecurringFees) {
                $('#recurring-fee-avg-monthly-display').html(ArsolProposalQuotation.formatPrice(recurringFeeAverageMonthlyTotal) + ' /mo');
            } else {
                $('#recurring-fee-avg-monthly-display').html(ArsolProposalQuotation.formatPrice(0));
            }

            // Update main totals
            $('#one-time-total-display').html(ArsolProposalQuotation.formatPrice(oneTimeTotal));
            $('#average-monthly-total-display').html(ArsolProposalQuotation.formatPrice(averageYearlyTotal) + (hasRecurring ? ' /yr' : ''));

            // Update hidden inputs for form submission
            $('#line_items_one_time_total').val(oneTimeTotal.toFixed(2));
            $('#line_items_recurring_totals').val(JSON.stringify(recurringTotals));

            // Update summary if it exists
            this.updateSummary();

            this.calculating = false;
        },
        
        updateSummary: function() {
            // Copy existing totals from quotation displays instead of recalculating
            // This prevents NaN errors and ensures consistency
            
            // Copy product totals
            var productSubtotalText = $('#product-subtotal-display').text();
            var productRecurringText = $('#product-avg-monthly-display').text();
            $('#summary-product-subtotal-display').html($('#product-subtotal-display').html());
            $('#summary-product-recurring-display').html($('#product-avg-monthly-display').html());
            
            // Copy fee totals
            $('#summary-onetime-fee-display').html($('#onetime-fee-subtotal-display').html());
            $('#summary-recurring-fee-display').html($('#recurring-fee-avg-monthly-display').html());
            
            // Copy shipping total
            $('#summary-shipping-display').html($('#shipping-subtotal-display').html());
            
            // Copy main totals
            $('#summary-one-time-total-display').html($('#one-time-total-display').html());
            $('#summary-avg-yearly-total-display').html($('#average-monthly-total-display').html());
            
            // Show/hide rows based on whether the original displays have meaningful values
            // Check if product subtotal is greater than $0.00
            var hasProductSubtotal = productSubtotalText && !productSubtotalText.includes('$0.00');
            var hasProductRecurring = productRecurringText && !productRecurringText.includes('$0.00') && productRecurringText.trim() !== '';
            
            if (hasProductSubtotal || hasProductRecurring) {
                $('#products-row').show();
                $('#products-onetime').toggle(hasProductSubtotal);
                $('#products-recurring').toggle(hasProductRecurring);
            } else {
                $('#products-row').hide();
            }
            
            // Show/hide other rows based on display content
            var onetimeFeeText = $('#onetime-fee-subtotal-display').text();
            $('#onetime-fees-row').toggle(onetimeFeeText && !onetimeFeeText.includes('$0.00'));
            
            var recurringFeeText = $('#recurring-fee-avg-monthly-display').text();
            var hasRecurringFees = recurringFeeText && !recurringFeeText.includes('$0.00') && recurringFeeText.trim() !== '';
            $('#recurring-fees-row').toggle(hasRecurringFees);
            
            if (hasRecurringFees) {
                // Add start date if available (find the earliest start date from recurring fees)
                var earliestStartDate = null;
                $('#recurring-fee-lines-body tr.arsol-line-item').each(function() {
                    var startDate = $(this).find('.arsol-start-date-input').val();
                    if (startDate) {
                        var date = new Date(startDate);
                        if (!earliestStartDate || date < earliestStartDate) {
                            earliestStartDate = date;
                        }
                    }
                });
                
                if (earliestStartDate) {
                    var formattedDate = earliestStartDate.toLocaleDateString();
                    $('#summary-recurring-start-date').text(' (starts ' + formattedDate + ')');
                } else {
                    $('#summary-recurring-start-date').text('');
                }
            }
            
            var shippingText = $('#shipping-subtotal-display').text();
            $('#shipping-row').toggle(shippingText && !shippingText.includes('$0.00'));
            
            // Show/hide totals section rows
            var oneTimeTotalText = $('#one-time-total-display').text();
            var hasOneTimeTotal = oneTimeTotalText && !oneTimeTotalText.includes('$0.00');
            $('#onetime-total-row').toggle(hasOneTimeTotal);
            
            var yearlyTotalText = $('#average-monthly-total-display').text();
            var hasYearlyTotal = yearlyTotalText && !yearlyTotalText.includes('$0.00') && yearlyTotalText.trim() !== '';
            $('#yearly-total-row').toggle(hasYearlyTotal);
            
            // Show/hide the entire totals row only if at least one total is meaningful
            $('#totals-row').toggle(hasOneTimeTotal || hasYearlyTotal);
            
            // Show empty state if no quotation data is available
            var hasQuotationData = (hasProductSubtotal || hasProductRecurring) || 
                                   (onetimeFeeText && !onetimeFeeText.includes('$0.00')) ||
                                   hasRecurringFees ||
                                   (shippingText && !shippingText.includes('$0.00')) ||
                                   hasOneTimeTotal || hasYearlyTotal;
            $('#quotation-empty-state').toggle(!hasQuotationData);
        },

        formatPrice: function(price) {
            var currencySymbol = arsol_proposal_quotation_vars.currency_symbol;
            var formattedPrice = Number(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            return '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">' + currencySymbol + '</span>' + formattedPrice + '</bdi></span>';
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Initialize all systems
        ArsolProposal.init();
        
        // Initialize quotation system if it exists
        if ($('#proposal_quotation_builder').length > 0) {
            ArsolProposalQuotation.init();
        }
        
        // Initialize budget system if it exists
        if ($('#proposal_budget_builder').length > 0) {
            ArsolBudget.init();
        }
    });

})(jQuery); 