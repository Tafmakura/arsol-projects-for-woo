// JS for Arsol Proposal Invoice Metabox
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
            var $row = $select.closest('.line-item');
            var $input = $row.find('.shipping-method-input');
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
            var builder = $('#proposal_invoice_builder');
            builder.on('click', '.add-line-item', this.addLineItem.bind(this));
            builder.on('click', '.remove-line-item', this.removeLineItem.bind(this));
            builder.on('change', '.product-select', this.productChanged.bind(this));
            builder.on('change', '.shipping-method-select-ui', this.shippingMethodChanged.bind(this));
            // Debounce the calculation to prevent firing on every single key press
            builder.on('input change', '.quantity-input, .sale-price-input, .price-input, .fee-amount-input, .billing-interval, .billing-period', _.debounce(this.calculateTotals.bind(this), 300));
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
                var $select = $newRow.find('.shipping-method-select-ui');
                var $input = $newRow.find('.shipping-method-input');
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
            $row.find('.product-select').select2({
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

        addLineItem: function(e) {
            e.preventDefault();
            this.renderRow($(e.currentTarget).data('type'), {});
        },

        removeLineItem: function(e) {
            e.preventDefault();
            $(e.currentTarget).closest('.line-item').remove();
            this.calculateTotals();
        },
        
        productChanged: function(e) {
            var $row = $(e.currentTarget).closest('.line-item');
            var productId = $(e.currentTarget).val();
            this.fetchProductDetails($row, productId);
        },

        fetchProductDetails: function($row, productId) {
             var self = this;
             if (!productId) return;

             $.ajax({
                 url: arsol_proposal_invoice_vars.ajax_url,
                 type: 'POST',
                 data: {
                     action: 'arsol_proposal_invoice_ajax_get_product_details',
                     nonce: arsol_proposal_invoice_vars.nonce,
                     product_id: productId,
                 },
                 success: function(response) {
                     if (response.success) {
                         var data = response.data;
                         $row.find('.price-input').val(data.regular_price);
                         $row.find('.sale-price-input').val(data.sale_price);
                         
                         $row.data('is-subscription', data.is_subscription);
                         $row.data('sign-up-fee', data.sign_up_fee || 0);
                         
                         // Store these for calculation, but also set the UI
                         $row.data('billing-interval', data.billing_interval);
                         $row.data('billing-period', data.billing_period);
                         
                         if (data.is_subscription) {
                             $row.find('.start-date-column').show();
                             var $billingCol = $row.find('.billing-cycle-column');
                             $billingCol.show();
                             $billingCol.find('.billing_cycle_interval').val(data.billing_interval);
                             $billingCol.find('.billing_cycle_period').val(data.billing_period);
                         } else {
                             $row.find('.start-date-column').hide();
                             $row.find('.billing-cycle-column').hide();
                         }
                         
                         self.calculateTotals();
                     }
                 }
             });
        },
        
        calculateTotals: function() {
            var self = this;
            var grandOneTimeTotal = 0;
            var grandTotalDailyCost = 0;
            var currencySymbol = arsol_proposal_invoice_vars.currency_symbol;

            var formatPrice = function(price) {
                var formattedPrice = Number(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                return '<span class="woocommerce-Price-amount amount"><bdi>' + currencySymbol + formattedPrice + '</bdi></span>';
            };

            // --- Products ---
            var productsOneTimeSubtotal = 0;
            var productsDailyCost = 0;
            $('#product-lines-body .line-item').each(function() {
                var $row = $(this);
                var quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                var isSubscription = $row.data('is-subscription');
                var salePrice = parseFloat($row.find('.sale-price-input').val());
                var regularPrice = parseFloat($row.find('.price-input').val()) || 0;
                var unitPrice = !isNaN(salePrice) ? salePrice : regularPrice;
                var itemTotal = unitPrice * quantity;

                if (isSubscription) {
                    var signUpFee = parseFloat($row.data('sign-up-fee')) || 0;
                    productsOneTimeSubtotal += signUpFee * quantity;

                    var interval = $row.find('.billing-cycle-column .billing_cycle_interval').val();
                    var period = $row.find('.billing-cycle-column .billing_cycle_period').val();
                    
                    if (period && interval) {
                        productsDailyCost += self.getDailyCost(itemTotal, interval, period);
                    }
                     $row.find('.subtotal-display').html(formatPrice(itemTotal));
                } else {
                    productsOneTimeSubtotal += itemTotal;
                    $row.find('.subtotal-display').html(formatPrice(itemTotal));
                }
            });
            var productsMonthlyTotal = productsDailyCost * (arsol_proposal_invoice_vars.calculation_constants.days_in_month || 30.4375);
            $('#product-subtotal-display').html(formatPrice(productsOneTimeSubtotal));
            $('#product-avg-monthly-display').html(formatPrice(productsMonthlyTotal) + ' /mo');
            grandOneTimeTotal += productsOneTimeSubtotal;
            grandTotalDailyCost += productsDailyCost;

            // --- One-Time Fees ---
            var oneTimeFeesSubtotal = 0;
            $('#onetime-fee-lines-body .line-item').each(function() {
                var amount = parseFloat($(this).find('.fee-amount-input').val()) || 0;
                oneTimeFeesSubtotal += amount;
                $(this).find('.subtotal-display').html(formatPrice(amount));
            });
            $('#onetime-fee-subtotal-display').html(formatPrice(oneTimeFeesSubtotal));
            grandOneTimeTotal += oneTimeFeesSubtotal;
            
            // --- Recurring Fees ---
            var recurringFeesDailyCost = 0;
            $('#recurring-fee-lines-body .line-item').each(function() {
                var $row = $(this);
                var amount = parseFloat($row.find('.fee-amount-input').val()) || 0;
                var interval = $row.find('.billing-cycle-column .billing_cycle_interval').val();
                var period = $row.find('.billing-cycle-column .billing_cycle_period').val();
               
                if (interval && period && amount > 0) {
                    var dailyCost = self.getDailyCost(amount, interval, period);
                    recurringFeesDailyCost += dailyCost;
                    var monthlyCost = dailyCost * (arsol_proposal_invoice_vars.calculation_constants.days_in_month || 30.4375);
                    $row.find('.subtotal-display').html(formatPrice(monthlyCost) + ' /mo');
                } else {
                    $row.find('.subtotal-display').html(formatPrice(0));
                }
            });
            var recurringFeesMonthlyTotal = recurringFeesDailyCost * (arsol_proposal_invoice_vars.calculation_constants.days_in_month || 30.4375);
            $('#recurring-fee-avg-monthly-display').html(formatPrice(recurringFeesMonthlyTotal) + ' /mo');
            grandTotalDailyCost += recurringFeesDailyCost;

            // --- Shipping ---
            var shippingSubtotal = 0;
            $('#shipping-lines-body .line-item').each(function() {
                 var amount = parseFloat($(this).find('.fee-amount-input').val()) || 0;
                shippingSubtotal += amount;
                $(this).find('.subtotal-display').html(formatPrice(amount));
            });
            $('#shipping-subtotal-display').html(formatPrice(shippingSubtotal));
            grandOneTimeTotal += shippingSubtotal;

            // --- Display Grand Totals ---
            var grandMonthlyTotal = grandTotalDailyCost * (arsol_proposal_invoice_vars.calculation_constants.days_in_month || 30.4375);
            $('#one-time-total-display').html(formatPrice(grandOneTimeTotal));
            $('#average-monthly-total-display').html(formatPrice(grandMonthlyTotal) + ' /mo');

            // --- UPDATE HIDDEN INPUTS FOR SAVING ---
            $('#line_items_one_time_total').val(grandOneTimeTotal.toFixed(2));
            var recurringTotalsForSave = {
                products: { monthly: productsMonthlyTotal.toFixed(2) },
                recurring_fees: { monthly: recurringFeesMonthlyTotal.toFixed(2) },
                grand_total: { monthly: grandMonthlyTotal.toFixed(2) }
            };
            $('#line_items_recurring_totals').val(JSON.stringify(recurringTotalsForSave));
        }
    };

    $(document).ready(function() {
        ArsolProposalInvoice.init();
    });

})(jQuery);