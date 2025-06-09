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
            var $builder = $('#proposal_invoice_builder');
            
            // Use event delegation for better performance with dynamic content
            $builder
                .on('click', '.add-line-item', this.addLineItem.bind(this))
                .on('click', '.remove-line-item', this.removeLineItem.bind(this))
                .on('change', '.product-select', this.productChanged.bind(this))
                .on('change', '.shipping-method-select-ui', this.shippingMethodChanged.bind(this));
            
            // Use jQuery's one() method for input events with debouncing (WordPress pattern)
            var debouncedCalculate = _.debounce(this.calculateTotals.bind(this), 300);
            $builder.on('input change', '.quantity-input, .sale-price-input, .price-input, .fee-amount-input, .billing-interval, .billing-period', debouncedCalculate);
            
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
            this.toggleStartDateColumn();
        },

        removeLineItem: function(e) {
            e.preventDefault();
            $(e.currentTarget).closest('.line-item').remove();
            this.calculateTotals();
            this.toggleStartDateColumn();
        },

        toggleStartDateColumn: function() {
            // Use jQuery's :data() selector for more efficient checking
            var $subscriptionRows = $('#product-lines-body .line-item').filter(function() {
                return $(this).data('is-subscription') === true;
            });
            
            var hasSubscriptions = $subscriptionRows.length > 0;
            
            // Use jQuery's toggle() method for cleaner show/hide logic
            $('#product-line-items .start-date-column').toggle(hasSubscriptions);
            
            // Trigger a custom event for extensibility (WordPress pattern)
            $(document).trigger('arsol:start-date-column-toggled', [hasSubscriptions]);
        },
        
        productChanged: function(e) {
            var $row = $(e.currentTarget).closest('.line-item');
            var productId = $(e.currentTarget).val();
            this.fetchProductDetails($row, productId);
        },

        fetchProductDetails: function($row, productId) {
             var self = this;
             if (!productId) {
                 // Clear all data when no product selected
                 $row.data('is-subscription', false)
                    .data('sign-up-fee', 0)
                    .data('billing-interval', '')
                    .data('billing-period', '');
                 $row.find('.start-date-input').hide().val('');
                 self.toggleStartDateColumn();
                 return;
             }

             // Use WordPress AJAX patterns with proper loading state
             $row.addClass('arsol-loading');
             
             var ajaxData = {
                 action: 'arsol_proposal_invoice_ajax_get_product_details',
                 nonce: arsol_proposal_invoice_vars.nonce,
                 product_id: productId
             };

             $.ajax({
                 url: arsol_proposal_invoice_vars.ajax_url,
                 type: 'POST',
                 data: ajaxData,
                 beforeSend: function() {
                     // Disable form controls during loading
                     $row.find('input, select').prop('disabled', true);
                 },
                 success: function(response) {
                     if (response.success && response.data) {
                         var data = response.data;
                         
                         // Use jQuery's efficient chaining for form updates
                         $row.find('.price-input').val(data.regular_price || '')
                             .end()
                             .find('.sale-price-input').val(data.sale_price || '');
                         
                         // Store data using jQuery data() method efficiently
                         $row.data({
                             'is-subscription': !!data.is_subscription,
                             'sign-up-fee': parseFloat(data.sign_up_fee) || 0,
                             'billing-interval': data.billing_interval || '',
                             'billing-period': data.billing_period || ''
                         });
                         
                         // Use jQuery's toggle() for cleaner conditional display
                         $row.find('.start-date-input').toggle(!!data.is_subscription).val(data.is_subscription ? $row.find('.start-date-input').val() : '');
                         
                         self.calculateTotals();
                         self.toggleStartDateColumn();
                     } else {
                         // Handle error state
                         console.warn('Failed to fetch product details:', response);
                     }
                 },
                 error: function(xhr, status, error) {
                     console.error('AJAX error fetching product details:', error);
                 },
                 complete: function() {
                     // Re-enable form controls and remove loading state
                     $row.removeClass('arsol-loading')
                         .find('input, select').prop('disabled', false);
                 }
             });
        },
        
        calculateTotals: function() {
            var self = this;
            var grandOneTimeTotal = 0;
            var grandTotalDailyCost = 0;
            var currencySymbol = arsol_proposal_invoice_vars.currency_symbol;

            // Cache frequently used jQuery objects (WordPress performance pattern)
            var $productRows = $('#product-lines-body .line-item');
            var $oneTimeRows = $('#onetime-fee-lines-body .line-item');
            var $recurringRows = $('#recurring-fee-lines-body .line-item');
            var $shippingRows = $('#shipping-lines-body .line-item');

            var formatPrice = function(price) {
                var formattedPrice = Number(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                return '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">' + currencySymbol + '</span>' + formattedPrice + '</bdi></span>';
            };

            // --- Products ---
            var productsOneTimeSubtotal = 0;
            var productsDailyCost = 0;
            
            $productRows.each(function() {
                var $row = $(this);
                var quantity = Math.max(0, parseFloat($row.find('.quantity-input').val()) || 0);
                var isSubscription = !!$row.data('is-subscription');

                // Show/hide start date input for individual rows
                $row.find('.start-date-input').toggle(isSubscription);

                var salePrice = parseFloat($row.find('.sale-price-input').val()) || 0;
                var regularPrice = parseFloat($row.find('.price-input').val()) || 0;
                var unitPrice = salePrice > 0 ? salePrice : regularPrice;
                var itemTotal = unitPrice * quantity;

                if (isSubscription) {
                    var signUpFee = parseFloat($row.data('sign-up-fee')) || 0;
                    productsOneTimeSubtotal += signUpFee * quantity;

                    var interval = parseInt($row.data('billing-interval')) || 1;
                    var period = $row.data('billing-period');
                    
                    if (period) {
                        productsDailyCost += self.getDailyCost(itemTotal, interval, period);
                        // Display subscription subtotal with billing period
                        var periodDisplay = period === 'month' ? 'mo' : (period === 'year' ? 'yr' : (period === 'week' ? 'wk' : (period === 'day' ? 'day' : period.charAt(0))));
                        var intervalText = interval > 1 ? interval : '';
                        $row.find('.subtotal-display').html(formatPrice(itemTotal) + ' /' + intervalText + periodDisplay);
                    }
                } else {
                    productsOneTimeSubtotal += itemTotal;
                    $row.find('.subtotal-display').html(formatPrice(itemTotal));
                }
            });

            var productsMonthlyTotal = productsDailyCost * arsol_proposal_invoice_vars.calculation_constants.days_in_month;
            $('#product-subtotal-display').html(formatPrice(productsOneTimeSubtotal));
            $('#product-avg-monthly-display').html(formatPrice(productsMonthlyTotal) + ' /mo');
            grandOneTimeTotal += productsOneTimeSubtotal;
            grandTotalDailyCost += productsDailyCost;

            // --- One-Time Fees ---
            var oneTimeFeesSubtotal = 0;
            $oneTimeRows.each(function() {
                var $row = $(this);
                var amount = parseFloat($row.find('.fee-amount-input').val()) || 0;
                oneTimeFeesSubtotal += amount;
                $row.find('.subtotal-display').html(formatPrice(amount));
            });
            $('#onetime-fee-subtotal-display').html(formatPrice(oneTimeFeesSubtotal));
            grandOneTimeTotal += oneTimeFeesSubtotal;
            
            // --- Recurring Fees ---
            var recurringFeesDailyCost = 0;
            $recurringRows.each(function() {
                var $row = $(this);
                var amount = parseFloat($row.find('.fee-amount-input').val()) || 0;
                var interval = parseInt($row.find('.billing-interval').val()) || 1;
                var period = $row.find('.billing-period').val();
               
                if (interval && period && amount > 0) {
                    recurringFeesDailyCost += self.getDailyCost(amount, interval, period);
                    // Display recurring fee subtotal with billing period
                    var periodDisplay = period === 'month' ? 'mo' : (period === 'year' ? 'yr' : (period === 'week' ? 'wk' : (period === 'day' ? 'day' : period.charAt(0))));
                    var intervalText = interval > 1 ? interval : '';
                    $row.find('.subtotal-display').html(formatPrice(amount) + ' /' + intervalText + periodDisplay);
                } else {
                    $row.find('.subtotal-display').html(formatPrice(amount));
                }
            });
            var recurringFeesMonthlyTotal = recurringFeesDailyCost * arsol_proposal_invoice_vars.calculation_constants.days_in_month;
            $('#recurring-fee-avg-monthly-display').html(formatPrice(recurringFeesMonthlyTotal) + ' /mo');
            grandTotalDailyCost += recurringFeesDailyCost;

            // --- Shipping ---
            var shippingSubtotal = 0;
            $shippingRows.each(function() {
                var $row = $(this);
                var amount = parseFloat($row.find('.fee-amount-input').val()) || 0;
                shippingSubtotal += amount;
                $row.find('.subtotal-display').html(formatPrice(amount));
            });
            $('#shipping-subtotal-display').html(formatPrice(shippingSubtotal));
            grandOneTimeTotal += shippingSubtotal;

            // --- Display Grand Totals ---
            var grandAnnualTotal = grandTotalDailyCost * arsol_proposal_invoice_vars.calculation_constants.days_in_year;
            $('#one-time-total-display').html(formatPrice(grandOneTimeTotal));
            $('#average-monthly-total-display').html(formatPrice(grandAnnualTotal) + ' /yr');

            // Update hidden inputs (WordPress form pattern)
            $('#line_items_one_time_total').val(grandOneTimeTotal.toFixed(2));
            $('#line_items_recurring_totals').val('');
            
            // Ensure column visibility and trigger custom event
            this.toggleStartDateColumn();
            $(document).trigger('arsol:totals-calculated', [grandOneTimeTotal, grandTotalDailyCost]);
        }
    };

    // Use WordPress standard document ready pattern with extensibility
    $(document).ready(function($) {
        // Initialize the invoice builder
        ArsolProposalInvoice.init();
        
        // Trigger WordPress-style initialization event for extensibility
        $(document).trigger('arsol:invoice-builder-ready', [ArsolProposalInvoice]);
        
        // Add WordPress admin-style accessibility improvements
        $('.add-line-item').attr('aria-label', function() {
            return 'Add ' + $(this).data('type').replace('-', ' ') + ' line item';
        });
        
        $('.remove-line-item').attr('aria-label', 'Remove line item');
    });

})(jQuery);