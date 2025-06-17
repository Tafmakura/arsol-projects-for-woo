// JS for Arsol Proposal Admin (Invoice & Budget sections)
(function($) {
    'use strict';



    // Dependency checks
    if (typeof $ === 'undefined') {
        console.error('Arsol Proposal Admin: jQuery is not loaded');
        return;
    }

    // Check for WordPress template function
    if (typeof wp === 'undefined' || typeof wp.template !== 'function') {
        console.error('Arsol Proposal Admin: WordPress template function is not available');
        return;
    }

    // Check for required localized variables
    if (typeof arsol_budget_vars === 'undefined') {
        console.error('Arsol Proposal Admin: arsol_budget_vars is not defined');
    }

    if (typeof arsol_pfw_proposal_quotation_vars === 'undefined') {
        console.error('Arsol Proposal Admin: arsol_pfw_proposal_quotation_vars is not defined');
    }

    // Safe debounce function that falls back if underscore is not available
    function safeDebounce(func, delay) {
        if (typeof _ !== 'undefined' && typeof _.debounce === 'function') {
            return _.debounce(func, delay);
        }
        
        // Fallback debounce implementation
        var timeoutId;
        return function() {
            var context = this;
            var args = arguments;
            clearTimeout(timeoutId);
            timeoutId = setTimeout(function() {
                func.apply(context, args);
            }, delay);
        };
    }

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
            });

            // Add cleanup on form submission
            $('form#post').on('submit', function(e) {
                console.log('Form submission started');
                ArsolProposal.presaveCleanup();
                console.log('Pre-save cleanup completed');
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
            // No validation needed - removed
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

    // Quotation System
    var ArsolProposalQuotation = {
        calculating: false,
        line_item_id: 0,

        init: function() {
            this.product_template = wp.template('arsol-product-line-item');
            this.onetime_fee_template = wp.template('arsol-onetime-fee-line-item');
            this.recurring_fee_template = wp.template('arsol-recurring-fee-line-item');
            this.shipping_fee_template = wp.template('arsol-shipping-fee-line-item');
            this.bindEvents();
            this.loadExistingItems();
            this.calculateTotals();
        },

        bindEvents: function() {
            var $builder = $('#proposal_quotation_builder');
            
            // Use event delegation for better performance with dynamic content
            $builder
                .on('click', '.add-line-item', this.addLineItem.bind(this))
                .on('click', '.remove-line-item', this.removeLineItem.bind(this))
                .on('change', '.arsol-product-item select.arsol-description-input', this.productChanged.bind(this));
            
            // Use jQuery's debounced input events for calculations
            var debouncedCalculate = safeDebounce(this.calculateTotals.bind(this), 300);
            
            // Products & Services section
            $builder.on('input change', '#product-lines-body .arsol-quantity-input, #product-lines-body .arsol-sale-price-input, #product-lines-body .arsol-price-input, #product-lines-body .arsol-description-input', function() {
                debouncedCalculate();
            });
            
            // Recurring Fees section
            $builder.on('input change', '#recurring-fee-lines-body .arsol-amount-input, #recurring-fee-lines-body .arsol-billing-select, #recurring-fee-lines-body .arsol-description-input', function() {
                debouncedCalculate();
            });
            
            // One-Time Fees section
            $builder.on('input change', '#onetime-fee-lines-body .arsol-amount-input, #onetime-fee-lines-body .arsol-description-input', function() {
                debouncedCalculate();
            });
            
            // Shipping Fees section
            $builder.on('input change', '#shipping-lines-body .arsol-amount-input, #shipping-lines-body .arsol-description-input', function() {
                debouncedCalculate();
            });
        },

        addLineItem: function(e) {
            e.preventDefault();
            var type = $(e.target).data('type');
            var data = { id: ++this.line_item_id };
            this.renderRow(type, data);
            this.calculateTotals();
        },

        removeLineItem: function(e) {
            e.preventDefault();
            $(e.target).closest('tr').remove();
            this.calculateTotals();
        },

        renderRow: function(type, data) {
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

            // Initialize product search if it's a product row
            if (type === 'product') {
                this.initProductSearch($newRow.find('select.arsol-description-input'));
                
                // Handle subscription product styling for existing items
                if (data.product_type && (data.product_type === 'subscription' || data.product_type === 'subscription_variation')) {
                    $newRow.addClass('arsol-subscription-product');
                    $newRow.find('.arsol-date-input').removeClass('hidden-start-date').show();
                } else {
                    $newRow.find('.arsol-date-input').addClass('hidden-start-date').hide();
                }
            }
        },

        initProductSearch: function($select) {
            if (typeof $select.selectWoo === 'function') {
                $select.selectWoo({
                    ajax: {
                        url: arsol_pfw_proposal_quotation_vars.ajax_url,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term,
                                action: 'arsol_proposal_quotation_ajax_search_products',
                                nonce: arsol_pfw_proposal_quotation_vars.nonce
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data.success ? data.data : []
                            };
                        }
                    },
                    minimumInputLength: 2,
                    placeholder: 'Search for products...',
                    allowClear: true
                });
            }
        },

        productChanged: function(e) {
            var $select = $(e.target);
            var $row = $select.closest('tr');
            var productId = $select.val();

            if (productId) {
                // Get product details via AJAX
                $.post(arsol_pfw_proposal_quotation_vars.ajax_url, {
                    action: 'arsol_proposal_quotation_ajax_get_product_details',
                    product_id: productId,
                    nonce: arsol_pfw_proposal_quotation_vars.nonce
                }, function(response) {
                    if (response.success) {
                        var product = response.data;
                        
                        // Set basic product info
                        $row.find('.arsol-price-input').val(product.regular_price);
                        $row.find('.arsol-sale-price-input').val(product.sale_price);
                        $row.find('input[name*="[product_type]"]').val(product.product_type);
                        
                        // Handle subscription products
                        var isSubscription = product.product_type === 'subscription' || product.product_type === 'subscription_variation';
                        
                        if (isSubscription) {
                            // Show start date field for subscription products
                            $row.find('.arsol-date-input').removeClass('hidden-start-date').show();
                            
                            // Store subscription meta data in hidden fields if they exist
                            $row.find('input[name*="[billing_interval]"]').val(product.billing_interval || 1);
                            $row.find('input[name*="[billing_period]"]').val(product.billing_period || 'month');
                            $row.find('input[name*="[sign_up_fee]"]').val(product.sign_up_fee || 0);
                            
                            // Add subscription indicator to the row
                            $row.addClass('arsol-subscription-product');
                        } else {
                            // Hide start date field for non-subscription products
                            $row.find('.arsol-date-input').addClass('hidden-start-date').hide();
                            $row.removeClass('arsol-subscription-product');
                        }
                        
                        ArsolProposalQuotation.calculateTotals();
                    }
                });
            }
        },

        loadExistingItems: function() {
            var self = this;
            var items = arsol_pfw_proposal_quotation_vars.line_items;

            if (items && items.products) {
                $.each(items.products, function(id, itemData) { 
                    self.line_item_id = Math.max(self.line_item_id, parseInt(id) || 0);
                    self.renderRow('product', itemData); 
                });
            }
            if (items && items.one_time_fees) {
                $.each(items.one_time_fees, function(id, itemData) { 
                    self.line_item_id = Math.max(self.line_item_id, parseInt(id) || 0);
                    self.renderRow('onetime-fee', itemData); 
                });
            }
            if (items && items.recurring_fees) {
                $.each(items.recurring_fees, function(id, itemData) { 
                    self.line_item_id = Math.max(self.line_item_id, parseInt(id) || 0);
                    self.renderRow('recurring-fee', itemData); 
                });
            }
            if (items && items.shipping_fees) {
                $.each(items.shipping_fees, function(id, itemData) { 
                    self.line_item_id = Math.max(self.line_item_id, parseInt(id) || 0);
                    self.renderRow('shipping-fee', itemData); 
                });
            }
        },

        calculateTotals: function() {
            if (this.calculating) {
                return;
            }
            this.calculating = true;

            var oneTimeTotal = 0;
            var recurringTotals = {};

            // Calculate product totals
            $('#product-lines-body tr.arsol-line-item').each(function() {
                var $row = $(this);
                var quantity = parseFloat($row.find('.arsol-quantity-input').val()) || 0;
                var salePrice = parseFloat($row.find('.arsol-sale-price-input').val());
                var regularPrice = parseFloat($row.find('.arsol-price-input').val());
                var price = !isNaN(salePrice) && salePrice > 0 ? salePrice : regularPrice;
                price = isNaN(price) ? 0 : price;
                var subtotal = quantity * price;
                
                // Check if this is a subscription product
                var productType = $row.find('input[name*="[product_type]"]').val();
                var isSubscription = productType === 'subscription' || productType === 'subscription_variation';
                
                if (isSubscription) {
                    // For subscription products, show recurring price format
                    var billingInterval = $row.find('input[name*="[billing_interval]"]').val() || 1;
                    var billingPeriod = $row.find('input[name*="[billing_period]"]').val() || 'month';
                    
                    var periodText = billingPeriod === 'month' ? 'mo' : (billingPeriod === 'year' ? 'yr' : (billingPeriod === 'week' ? 'wk' : (billingPeriod === 'day' ? 'day' : billingPeriod)));
                    var intervalText = billingInterval > 1 ? billingInterval : '';
                    var billingText = '/' + intervalText + periodText;
                    
                    $row.find('.arsol-subtotal-column').html(ArsolProposalQuotation.formatPrice(subtotal) + ' ' + billingText);
                    
                    // Add sign-up fee to one-time total if it exists
                    var signUpFee = parseFloat($row.find('input[name*="[sign_up_fee]"]').val()) || 0;
                    oneTimeTotal += signUpFee * quantity;
                } else {
                    // For regular products, show one-time price
                    $row.find('.arsol-subtotal-column').html(ArsolProposalQuotation.formatPrice(subtotal));
                    oneTimeTotal += subtotal;
                }
            });

            // Calculate one-time fee totals
            $('#onetime-fee-lines-body tr.arsol-line-item').each(function() {
                var amount = parseFloat($(this).find('.arsol-amount-input').val()) || 0;
                $(this).find('.arsol-subtotal-column').html(ArsolProposalQuotation.formatPrice(amount));
                oneTimeTotal += amount;
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
            });

            // Calculate shipping totals
            $('#shipping-lines-body tr.arsol-line-item').each(function() {
                var amount = parseFloat($(this).find('.arsol-amount-input').val()) || 0;
                $(this).find('.arsol-subtotal-column').html(ArsolProposalQuotation.formatPrice(amount));
                oneTimeTotal += amount;
            });

            // Update section subtotals
            $('#product-subtotal-display').html(ArsolProposalQuotation.formatPrice(oneTimeTotal));
            $('#onetime-fee-subtotal-display').html(ArsolProposalQuotation.formatPrice(0)); // Will be calculated above
            $('#shipping-subtotal-display').html(ArsolProposalQuotation.formatPrice(0)); // Will be calculated above

            // Update main totals
            $('#one-time-total-display').html(ArsolProposalQuotation.formatPrice(oneTimeTotal));

            this.calculating = false;
        },

        formatPrice: function(price) {
            var currencySymbol = arsol_pfw_proposal_quotation_vars.currency_symbol;
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