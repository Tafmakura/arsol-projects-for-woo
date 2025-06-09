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

        getCycleKey: function(interval, period) {
            return period + '_' + interval;
        },

        getCycleLabel: function(interval, period) {
            var periodLabel = period;
            interval = parseInt(interval);

            if (interval > 1) {
                periodLabel += 's';
                return '/ ' + interval + ' ' + periodLabel;
            }
            return '/ ' + periodLabel;
        },

        setInitialAverageTotal: function() {
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
                         $row.data('billing-interval', data.billing_interval);
                         $row.data('billing-period', data.billing_period);
                         
                         self.calculateTotals();
                     }
                 }
             });
        },
        
        calculateTotals: function() {
            var self = this;
            if (self.calculating) return; // Prevent simultaneous calculations

            var oneTimeTotal = 0;
            var recurringTotals = {};
            var recurringItemsForAjax = [];
            var currencySymbol = arsol_proposal_invoice_vars.currency_symbol;

            var formatPrice = function(price) {
                var formattedPrice = Number(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                return '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">' + currencySymbol + '</span>' + formattedPrice + '</bdi></span>';
            };

            // Products
            $('#product-lines-body .line-item').each(function() {
                var $row = $(this);
                var quantity = parseFloat($row.find('.quantity-input').val()) || 1;
                var isSubscription = $row.data('is-subscription');

                var salePrice = parseFloat($row.find('.sale-price-input').val());
                var regularPrice = parseFloat($row.find('.price-input').val()) || 0;
                var unitPrice = !isNaN(salePrice) && salePrice > 0 ? salePrice : regularPrice;
                var itemTotal = unitPrice * quantity;

                if (isSubscription) {
                    var signUpFee = parseFloat($row.data('sign-up-fee')) || 0;
                    oneTimeTotal += signUpFee;

                    var interval = parseInt($row.data('billing-interval')) || 1;
                    var period = $row.data('billing-period');
                    
                    var subtotalDisplay = formatPrice(itemTotal);

                    if (interval && period) {
                        subtotalDisplay += ' ' + self.getCycleLabel(interval, period);
                        self.updateRecurringTotals(recurringTotals, interval, period, itemTotal);
                        recurringItemsForAjax.push({
                            price: unitPrice,
                            interval: interval,
                            period: period,
                            quantity: quantity
                        });
                    }
                    
                    if (signUpFee > 0) {
                        subtotalDisplay += ' (+ ' + formatPrice(signUpFee) + ' sign-up)';
                    }
                    $row.find('.subtotal-display').html(subtotalDisplay);

                } else {
                    oneTimeTotal += itemTotal;
                    $row.find('.subtotal-display').html(formatPrice(itemTotal));
                }
            });

            // One-Time Fees
            $('#onetime-fee-lines-body .line-item').each(function() {
                var amount = parseFloat($(this).find('.fee-amount-input').val()) || 0;
                oneTimeTotal += amount;
                $(this).find('.subtotal-display').html(formatPrice(amount));
            });

            // Recurring Fees
            $('#recurring-fee-lines-body .line-item').each(function() {
                var amount = parseFloat($(this).find('.fee-amount-input').val()) || 0;
                var interval = parseInt($(this).find('.billing-interval').val()) || 1;
                var period = $(this).find('.billing-period').val();
               
                if (interval && period) {
                     self.updateRecurringTotals(recurringTotals, interval, period, amount);
                     
                     var subtotalText = formatPrice(amount) + ' ' + self.getCycleLabel(interval, period);
                     $(this).find('.subtotal-display').html(subtotalText);

                     recurringItemsForAjax.push({
                         price: amount,
                         interval: interval,
                         period: period,
                         quantity: 1
                     });
                } else {
                     $(this).find('.subtotal-display').html(formatPrice(amount));
                }
            });
            
            // Shipping
            $('#shipping-lines-body .line-item').each(function() {
                var amount = parseFloat($(this).find('.fee-amount-input').val()) || 0;
                oneTimeTotal += amount;
                $(this).find('.subtotal-display').html(formatPrice(amount));
            });

            // --- AJAX call for average monthly total ---
            self.calculating = true;
            $.ajax({
                url: arsol_proposal_invoice_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'arsol_calculate_average_monthly_total',
                    nonce: arsol_proposal_invoice_vars.nonce,
                    recurring_items: recurringItemsForAjax
                },
                success: function(response) {
                    if (response.success) {
                        $('#average-monthly-total-display').html(response.data.formatted);
                    } else {
                        $('#average-monthly-total-display').html('<span class="error" style="color:red;">Error</span>');
                        console.error("AJAX error (success:false):", response.data.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#average-monthly-total-display').html('<span class="error" style="color:red;">Error</span>');
                    console.error("Fatal AJAX error:", textStatus, errorThrown);
                    console.error("Response:", jqXHR.responseText);
                },
                complete: function() {
                    self.calculating = false; // Reset the flag
                }
            });
            // --- End AJAX call ---


            // Display Totals (except average monthly, which is handled by AJAX)
            $('#one-time-total-display').html(formatPrice(oneTimeTotal));
            
            // Render recurring totals
            var $recurringDisplay = $('#recurring-totals-display');
            var recurringHtml = [];
            if (Object.keys(recurringTotals).length > 0) {
                 $.each(recurringTotals, function(key, data) {
                     var label = self.getCycleLabel(data.interval, data.period);
                     recurringHtml.push(formatPrice(data.total) + ' ' + label);
                 });
                 $recurringDisplay.html(recurringHtml.join('<br>'));
            } else {
                $recurringDisplay.html(formatPrice(0));
            }

            // Update hidden inputs
            $('#line_items_one_time_total').val(oneTimeTotal.toFixed(2));
            $('#line_items_recurring_totals').val(JSON.stringify(recurringTotals));
        }
    };

    $(function() {
        ArsolProposalInvoice.init();
    });

})(jQuery);