// JS for Arsol Proposal Admin - Minimal Implementation
(function($) {
    'use strict';

    // Basic dependency checks
    if (typeof $ === 'undefined') return;
    if (typeof wp === 'undefined' || typeof wp.template !== 'function') return;

    // Minimal Proposal Toggle System
    var ArsolProposal = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Toggle sections on dropdown change
            $('#cost_proposal_type').on('change', this.toggleSections);
            
            // Initial toggle
            this.toggleSections();
            
            // Basic cleanup on form submit
            $('form#post').on('submit', this.cleanupBeforeSave);
        },

        toggleSections: function() {
            var type = $('#cost_proposal_type').val();
            
            // Hide all sections first
            $('#arsol_budget_estimates_metabox, #arsol_proposal_quotation_metabox').hide();
            
            // Show relevant section
            if (type === 'budget') {
                $('#arsol_budget_estimates_metabox').show();
            } else if (type === 'quotation') {
                $('#arsol_proposal_quotation_metabox').show();
            }
        },

        cleanupBeforeSave: function() {
            var type = $('#cost_proposal_type').val();
            
            // Auto-cleanup empty sections
            if (type === 'budget' && !$('input[name="proposal_budget"]').val()) {
                $('#cost_proposal_type').val('none');
            } else if (type === 'quotation' && $('.arsol-line-item').length === 0) {
                $('#cost_proposal_type').val('none');
            }
        }
    };

    // Basic Budget Calculations with Billing Period Formatting
    var ArsolBudget = {
        init: function() {
            this.bindEvents();
            this.updateTotals();
        },

        bindEvents: function() {
            $('.js-amount-input, .js-billing-input').on('input change', this.updateTotals.bind(this));
        },

        updateTotals: function() {
            var amount = parseFloat($('.js-amount-input').first().val()) || 0;
            var currency = (typeof arsol_budget_vars !== 'undefined') ? arsol_budget_vars.currency_symbol : '$';
            
            // Basic budget total
            $('.budget-total-display').text(currency + amount.toFixed(2));
            
            // Handle recurring budget with billing period
            var recurringAmount = parseFloat($('.recurring-budget-amount-input').val()) || 0;
            if (recurringAmount > 0) {
                var billingText = this.formatBillingPeriod();
                $('#budget-recurring-total-display').html(currency + recurringAmount.toFixed(2) + billingText);
            }
        },

        formatBillingPeriod: function() {
            var interval = parseInt($('.billing-interval').val()) || 1;
            var period = $('.billing-period').val() || 'month';
            
            // Simple period formatting
            var periodMap = {
                'month': 'mo',
                'year': 'yr', 
                'week': 'wk',
                'day': 'day'
            };
            
            var periodText = periodMap[period] || period;
            var intervalText = interval > 1 ? interval : '';
            
            return ' <span class="billing-cycle">/' + intervalText + periodText + '</span>';
        }
    };

    // Enhanced Quotation System with Subscription Support
    var ArsolQuotation = {
        itemId: 0,

        init: function() {
            this.bindEvents();
            this.loadExisting();
        },

        bindEvents: function() {
            // Add/remove line items
            $(document).on('click', '.add-line-item', this.addItem.bind(this));
            $(document).on('click', '.remove-line-item', this.removeItem.bind(this));
            
            // Basic calculations
            $(document).on('input change', '.arsol-quantity-input, .arsol-price-input, .arsol-amount-input', this.calculate.bind(this));
            
            // Product selection
            $(document).on('change', '.arsol-description-input', this.productChanged.bind(this));
        },

        addItem: function(e) {
            e.preventDefault();
            var type = $(e.target).data('type');
            var template = wp.template('arsol-' + type + '-line-item');
            var data = { id: ++this.itemId };
            
            var container = '#' + type.replace('-', '-lines-') + 'body';
            if (type === 'product') container = '#product-lines-body';
            
            var $newRow = $(template(data));
            $(container).append($newRow);
            
            // Initialize SelectWoo for product rows
            if (type === 'product') {
                this.initProductSearch($newRow.find('.arsol-description-input'));
            }
            
            this.calculate();
        },

        removeItem: function(e) {
            e.preventDefault();
            $(e.target).closest('tr').remove();
            this.calculate();
        },

        // SelectWoo Product Search Integration
        initProductSearch: function($select) {
            if (typeof $.fn.selectWoo !== 'function') return;
            
            $select.selectWoo({
                ajax: {
                    url: arsol_pfw_proposal_quotation_vars.ajax_url,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            action: 'arsol_proposal_quotation_ajax_search_products',
                            nonce: arsol_pfw_proposal_quotation_vars.nonce
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.success ? data.data : []
                        };
                    }
                },
                minimumInputLength: 2,
                placeholder: 'Search for products...'
            });
        },

        productChanged: function(e) {
            var $select = $(e.target);
            var productId = $select.val();
            
            if (productId && typeof arsol_pfw_proposal_quotation_vars !== 'undefined') {
                $.post(arsol_pfw_proposal_quotation_vars.ajax_url, {
                    action: 'arsol_proposal_quotation_ajax_get_product_details',
                    product_id: productId,
                    nonce: arsol_pfw_proposal_quotation_vars.nonce
                }, function(response) {
                    if (response.success) {
                        var $row = $select.closest('tr');
                        var product = response.data;
                        
                        // Set basic product info
                        $row.find('.arsol-price-input').val(product.regular_price || '');
                        $row.find('.arsol-sale-price-input').val(product.sale_price || '');
                        
                        // Advanced Subscription Product Handling
                        ArsolQuotation.handleSubscriptionProduct($row, product);
                        
                        ArsolQuotation.calculate();
                    }
                });
            }
        },

        // Clean Subscription Product Handling
        handleSubscriptionProduct: function($row, product) {
            var isSubscription = product.product_type === 'subscription' || 
                               product.product_type === 'subscription_variation';
            
            if (isSubscription) {
                // Add subscription styling
                $row.addClass('arsol-subscription-product');
                
                // Show start date field
                $row.find('.arsol-start-date-field').show();
                
                // Store subscription metadata
                $row.find('input[name*="[billing_interval]"]').val(product.billing_interval || '1');
                $row.find('input[name*="[billing_period]"]').val(product.billing_period || 'month');
                $row.find('input[name*="[sign_up_fee]"]').val(product.sign_up_fee || '0');
                $row.find('input[name*="[product_type]"]').val(product.product_type);
                
            } else {
                // Regular product - hide subscription fields
                $row.removeClass('arsol-subscription-product');
                $row.find('.arsol-start-date-field').hide();
            }
        },

        calculate: function() {
            var oneTimeTotal = 0;
            var recurringTotal = 0;
            
            // Calculate product totals with subscription support
            $('#product-lines-body tr').each(function() {
                var $row = $(this);
                var qty = parseFloat($row.find('.arsol-quantity-input').val()) || 0;
                var price = parseFloat($row.find('.arsol-price-input').val()) || 0;
                var signUpFee = parseFloat($row.find('input[name*="[sign_up_fee]"]').val()) || 0;
                var isSubscription = $row.hasClass('arsol-subscription-product');
                
                var lineTotal = qty * price;
                
                if (isSubscription) {
                    // Subscription: recurring price + one-time sign-up fee
                    recurringTotal += lineTotal;
                    oneTimeTotal += (qty * signUpFee);
                    
                    // Format subtotal with billing period
                    var billingText = ArsolQuotation.getSubscriptionBillingText($row);
                    $row.find('.arsol-subtotal-column').html(
                        ArsolQuotation.formatPrice(lineTotal) + billingText
                    );
                } else {
                    // Regular product: one-time total
                    oneTimeTotal += lineTotal;
                    $row.find('.arsol-subtotal-column').text(ArsolQuotation.formatPrice(lineTotal));
                }
            });
            
            // Calculate fee totals
            $('.arsol-amount-input').each(function() {
                oneTimeTotal += parseFloat($(this).val()) || 0;
            });
            
            // Update displays
            $('#one-time-total-display').text(this.formatPrice(oneTimeTotal));
            if (recurringTotal > 0) {
                $('#recurring-total-display').text(this.formatPrice(recurringTotal) + '/mo');
            }
        },

        // Simple Billing Period Formatting for Subscriptions
        getSubscriptionBillingText: function($row) {
            var interval = $row.find('input[name*="[billing_interval]"]').val() || '1';
            var period = $row.find('input[name*="[billing_period]"]').val() || 'month';
            
            var periodMap = {
                'month': 'mo',
                'year': 'yr',
                'week': 'wk', 
                'day': 'day'
            };
            
            var periodText = periodMap[period] || period;
            var intervalText = parseInt(interval) > 1 ? interval : '';
            
            return ' <span class="billing-cycle">/' + intervalText + periodText + '</span>';
        },

        formatPrice: function(price) {
            var currency = (typeof arsol_pfw_proposal_quotation_vars !== 'undefined') ? 
                          arsol_pfw_proposal_quotation_vars.currency_symbol : '$';
            return currency + parseFloat(price || 0).toFixed(2);
        },

        loadExisting: function() {
            if (typeof arsol_pfw_proposal_quotation_vars !== 'undefined' && 
                arsol_pfw_proposal_quotation_vars.line_items) {
                var items = arsol_pfw_proposal_quotation_vars.line_items;
                
                // Load existing items and initialize SelectWoo
                if (items.products) {
                    for (var id in items.products) {
                        this.itemId = Math.max(this.itemId, parseInt(id));
                    }
                    
                    // Initialize SelectWoo for existing product selects
                    $('.arsol-description-input').each(function() {
                        ArsolQuotation.initProductSearch($(this));
                    });
                }
                
                this.calculate();
            }
        }
    };

    // Initialize when ready
    $(document).ready(function() {
        ArsolProposal.init();
        
        if ($('#proposal_budget_builder').length) {
            ArsolBudget.init();
        }
        
        if ($('#proposal_quotation_builder').length) {
            ArsolQuotation.init();
        }
    });

})(jQuery); 