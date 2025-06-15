// JS for Arsol Proposal Admin (Invoice & Budget sections)
(function($) {
    'use strict';

    var ArsolProposalInvoice = {
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
            var constants = arsol_proposal_invoice_vars.calculation_constants;

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
            var initialTotal = arsol_proposal_invoice_vars.average_monthly_total_formatted;
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
            var $builder = $('#proposal_invoice_builder');
            
            // Use event delegation for better performance with dynamic content
            $builder
                .on('click', '.add-line-item', this.addLineItem.bind(this))
                .on('click', '.remove-line-item', this.removeLineItem.bind(this))
                .on('change', '.arsol-description-input', this.productChanged.bind(this))
                .on('change', '.arsol-select-full', this.shippingMethodChanged.bind(this));
            
            // Use jQuery's one() method for input events with debouncing (WordPress pattern)
            var debouncedCalculate = _.debounce(this.calculateTotals.bind(this), 300);
            $builder.on('input change', '.arsol-quantity-input, .arsol-sale-price-input, .arsol-price-input, .arsol-amount-input, .arsol-billing-select', debouncedCalculate);
            
            // Add WordPress-style custom event triggers for extensibility
            $(document).trigger('arsol:invoice-events-bound', [$builder]);
        },
        
        loadExistingItems: function() {
            var self = this;
            var items = arsol_proposal_invoice_vars.line_items;

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
                    url: arsol_proposal_invoice_vars.ajax_url,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            action: 'arsol_proposal_invoice_ajax_search_products',
                            nonce: arsol_proposal_invoice_vars.nonce,
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
                url: arsol_proposal_invoice_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'arsol_proposal_invoice_ajax_get_product_details',
                    nonce: arsol_proposal_invoice_vars.nonce,
                    product_id: productId
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        $row.find('.arsol-price-input').val(data.regular_price);
                        $row.find('.arsol-sale-price-input').val(data.sale_price);
                        $row.find('.product-sub-text').html(data.sub_text);
                        
                        // Check if product is subscription type (backward compatible)
                        var isSubscription = data.product_type && (data.product_type === 'subscription' || data.product_type === 'subscription_variation');
                        if (isSubscription) {
                            $row.find('.arsol-date-input').show();
                            // Store subscription billing data on the row for calculations
                            $row.data('billing-interval', data.billing_interval || 1);
                            $row.data('billing-period', data.billing_period || 'month');
                            $row.data('is-subscription', true);
                        } else {
                            $row.find('.arsol-date-input').hide();
                            $row.removeData('billing-interval billing-period is-subscription');
                        }
                        
                        ArsolProposalInvoice.toggleStartDateColumn();
                        ArsolProposalInvoice.calculateTotals();
                    }
                }
            });
        },

        addLineItem: function(e) {
            e.preventDefault();
            var type = $(e.currentTarget).data('type');
            this.renderRow(type, {});
            this.toggleStartDateColumn();
        },

        removeLineItem: function(e) {
            e.preventDefault();
            $(e.currentTarget).closest('tr').remove();
            this.calculateTotals();
            this.toggleStartDateColumn();
        },

        productChanged: function(e) {
            var $select = $(e.currentTarget);
            var productId = $select.val();
            var $row = $select.closest('tr');

            if (productId) {
                this.fetchProductDetails($row, productId);
            } else {
                $row.find('.arsol-price-input, .arsol-sale-price-input').val('');
                $row.find('.product-sub-text').html('');
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
                
                $(this).find('.arsol-subtotal-column').html(ArsolProposalInvoice.formatPrice(subtotal));
                
                var $startDateInput = $(this).find('.arsol-date-input');
                if ($startDateInput.is(':visible') && $(this).data('is-subscription')) {
                    // This is a subscription product
                    var interval = parseInt($(this).data('billing-interval')) || 1;
                    var period = $(this).data('billing-period') || 'month';
                    
                    // Create billing text for display
                    var periodText = period === 'month' ? 'mo' : (period === 'year' ? 'yr' : (period === 'week' ? 'wk' : (period === 'day' ? 'day' : period)));
                    var intervalText = interval > 1 ? interval : '';
                    var billingText = '/' + intervalText + periodText;
                    
                    $(this).find('.arsol-subtotal-column').html(ArsolProposalInvoice.formatPrice(subtotal) + ' ' + billingText);
                    
                    // Add to recurring totals
                    ArsolProposalInvoice.updateRecurringTotals(recurringTotals, interval, period, subtotal);
                    ArsolProposalInvoice.updateRecurringTotals(productRecurringTotals, interval, period, subtotal);
                } else {
                    // This is a one-time product
                    oneTimeTotal += subtotal;
                    productSubtotal += subtotal;
                }
            });

            // Calculate one-time fee totals
            $('#onetime-fee-lines-body tr.arsol-line-item').each(function() {
                var amount = parseFloat($(this).find('.arsol-amount-input').val()) || 0;
                $(this).find('.arsol-subtotal-column').html(ArsolProposalInvoice.formatPrice(amount));
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
                
                $(this).find('.arsol-subtotal-column').html(ArsolProposalInvoice.formatPrice(amount) + ' ' + billingText);
                
                ArsolProposalInvoice.updateRecurringTotals(recurringTotals, interval, period, amount);
                ArsolProposalInvoice.updateRecurringTotals(recurringFeeRecurringTotals, interval, period, amount);
                recurringFeeSubtotal += amount;
            });

            // Calculate shipping totals
            $('#shipping-lines-body tr.arsol-line-item').each(function() {
                var amount = parseFloat($(this).find('.arsol-amount-input').val()) || 0;
                $(this).find('.arsol-subtotal-column').html(ArsolProposalInvoice.formatPrice(amount));
                oneTimeTotal += amount;
                shippingSubtotal += amount;
            });

            // Update section subtotals
            $('#product-subtotal-display').html(ArsolProposalInvoice.formatPrice(productSubtotal));
            $('#onetime-fee-subtotal-display').html(ArsolProposalInvoice.formatPrice(onetimeFeeSubtotal));
            $('#shipping-subtotal-display').html(ArsolProposalInvoice.formatPrice(shippingSubtotal));

            // Calculate average monthly totals separately for each section
            var constants = arsol_proposal_invoice_vars.calculation_constants;
            
            // Product section recurring totals
            var productDailyCost = 0;
            var hasProductRecurring = false;
            $.each(productRecurringTotals, function(key, data) {
                hasProductRecurring = true;
                var dailyCost = ArsolProposalInvoice.getDailyCost(data.total, data.interval, data.period);
                productDailyCost += dailyCost;
            });
            var productAverageMonthlyTotal = productDailyCost * constants.days_in_month;
            
            // Recurring fee section totals
            var recurringFeeDailyCost = 0;
            var hasRecurringFees = false;
            $.each(recurringFeeRecurringTotals, function(key, data) {
                hasRecurringFees = true;
                var dailyCost = ArsolProposalInvoice.getDailyCost(data.total, data.interval, data.period);
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
                $('#product-avg-monthly-display').html(ArsolProposalInvoice.formatPrice(productAverageMonthlyTotal) + ' /mo');
            } else {
                $('#product-avg-monthly-display').html(ArsolProposalInvoice.formatPrice(0));
            }
            
            if (hasRecurringFees) {
                $('#recurring-fee-avg-monthly-display').html(ArsolProposalInvoice.formatPrice(recurringFeeAverageMonthlyTotal) + ' /mo');
            } else {
                $('#recurring-fee-avg-monthly-display').html(ArsolProposalInvoice.formatPrice(0));
            }

            // Update main totals
            $('#one-time-total-display').html(ArsolProposalInvoice.formatPrice(oneTimeTotal));
            $('#average-monthly-total-display').html(ArsolProposalInvoice.formatPrice(averageYearlyTotal) + (hasRecurring ? ' /yr' : ''));

            // Update hidden inputs for form submission
            $('#line_items_one_time_total').val(oneTimeTotal.toFixed(2));
            $('#line_items_recurring_totals').val(JSON.stringify(recurringTotals));

            this.calculating = false;
        },

        formatPrice: function(price) {
            var currencySymbol = arsol_proposal_invoice_vars.currency_symbol;
            var formattedPrice = Number(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            return '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">' + currencySymbol + '</span>' + formattedPrice + '</bdi></span>';
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        ArsolProposalInvoice.init();
    });

})(jQuery); 