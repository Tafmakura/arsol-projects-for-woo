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
                // First run all pre-save cleanup tasks (only on submit, not on real-time validation)
                ArsolProposal.presaveCleanup();
                // Then run validation to show inline error messages
                ArsolProposal.validateProposal();
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
                // Check if user has added any line items
                var hasAnyLineItems = $('.product-line-item, .recurring-fee-line-item, .onetime-fee-line-item').length > 0;
                
                if (hasAnyLineItems) {
                    // Only validate if user has started adding line items
                    var hasValidItem = false;
                    $('.product-line-item, .recurring-fee-line-item, .onetime-fee-line-item').each(function() {
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

            // Button disabling removed - now uses HTML5 validation with inline error messages
            // Auto-cleanup moved to form submission to prevent overly sensitive behavior
            // $('.arsol-confirm-conversion, #publish').prop('disabled', !isValid);
            
            return isValid;
        },

        // Pre-save cleanup function - runs all cleanup tasks before saving
        presaveCleanup: function() {
            // Clean up empty proposal sections
            this.cleanupEmptyProposalSections();
            
            // Add other pre-save cleanup tasks here as needed
            // this.cleanupOtherStuff();
        },

        // Clean up empty proposal sections (moved from cleanupEmptySections)
        cleanupEmptyProposalSections: function() {
            var proposalType = $('#cost_proposal_type').val();
            
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
                var hasAnyLineItems = $('.product-line-item, .recurring-fee-line-item, .onetime-fee-line-item').length > 0;
                
                if (!hasAnyLineItems) {
                    // Auto-cleanup: set type to 'none' if no line items exist on save
                    $('#cost_proposal_type').val('none');
                    console.log('Pre-save cleanup: Empty quotation section changed to "none"');
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
            var debouncedValidate = _.debounce(this.updateAddButtonStates.bind(this), 300);
            
            $builder.on('input change', '.arsol-quantity-input, .arsol-sale-price-input, .arsol-price-input, .arsol-amount-input, .arsol-billing-select', function() {
                debouncedCalculate();
                debouncedValidate();
            });
            
            // Also validate on description changes
            $builder.on('input change', '.arsol-description-input', debouncedValidate);
            
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
                if(data.product_id) {
                     this.fetchProductDetails($newRow, data.product_id);
                }
            }
        },

        initSelect2: function($row) {
            $row.find('.arsol-description-input').select2({
                ajax: {
                    url: arsol_proposal_quotation_vars.ajax_url,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            action: 'arsol_proposal_quotation_ajax_search_products',
                            nonce: arsol_proposal_quotation_vars.nonce,
                            search: params.term,
                        };
                    },
                    processResults: function(data) { return { results: data.data }; },
                    cache: true
                },
                placeholder: 'Search for a product...',
                minimumInputLength: 1
            });
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
                            $row.find('.arsol-date-input').show();
                            // Store subscription billing data on the row for calculations
                            $row.data('billing-interval', data.billing_interval || 1);
                            $row.data('billing-period', data.billing_period || 'month');
                            $row.data('is-subscription', true);
                            console.log('Set subscription data on row:', {
                                billingInterval: $row.data('billing-interval'),
                                billingPeriod: $row.data('billing-period'),
                                isSubscription: $row.data('is-subscription')
                            });
                        } else {
                            $row.find('.arsol-date-input').hide();
                            $row.removeData('billing-interval billing-period is-subscription');
                        }
                        
                        ArsolProposalQuotation.toggleStartDateColumn();
                        ArsolProposalQuotation.calculateTotals();
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
            this.updateAddButtonStates();
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
            } else {
                description = $lastRow.find('.arsol-description-input').val();
                amount = $lastRow.find('.arsol-amount-input').val();
            }
            
            return description && description.trim() && amount && parseFloat(amount) > 0;
        },

        updateAddButtonStates: function() {
            var self = this;
            
            $('.add-line-item').each(function() {
                var type = $(this).data('type');
                var isValid = self.validateLastLineItem(type);
                $(this).prop('disabled', !isValid);
            });
        },

        removeLineItem: function(e) {
            e.preventDefault();
            $(e.currentTarget).closest('tr').remove();
            this.calculateTotals();
            this.toggleStartDateColumn();
            this.updateAddButtonStates();
        },

        productChanged: function(e) {
            var $select = $(e.currentTarget);
            var productId = $select.val();
            var $row = $select.closest('tr');

            if (productId) {
                this.fetchProductDetails($row, productId);
            } else {
                $row.find('.arsol-price-input, .arsol-sale-price-input').val('');
                $row.find('.arsol-date-input').hide();
                $row.removeData('billing-interval billing-period is-subscription');
                this.toggleStartDateColumn();
                this.calculateTotals();
            }
        },

        toggleStartDateColumn: function() {
            var hasSubscriptions = false;
            $('#product-lines-body tr.arsol-line-item').each(function() {
                var $startDateInput = $(this).find('.arsol-date-input');
                if ($startDateInput.is(':visible')) {
                    hasSubscriptions = true;
                    return false;
                }
            });

            var hasRecurringFees = $('#recurring-fee-lines-body tr.arsol-line-item').length > 0;

            if (hasSubscriptions || hasRecurringFees) {
                $('.arsol-date-column').show();
            } else {
                $('.arsol-date-column').hide();
            }
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
            $('#average-monthly-total-display').html(ArsolProposalQuotation.formatPrice(averageMonthlyTotal) + (hasRecurring ? ' /mo' : ''));

            // Update hidden inputs for form submission
            $('#line_items_one_time_total').val(oneTimeTotal.toFixed(2));
            $('#line_items_recurring_totals').val(JSON.stringify(recurringTotals));

            this.calculating = false;
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