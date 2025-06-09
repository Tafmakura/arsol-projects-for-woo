// JS for Arsol Proposal Invoice Metabox
(function($) {
    'use strict';

    var ArsolProposalInvoice = {
        init: function() {
            console.log('Arsol Proposal Invoice: Initializing...');
            this.product_template = wp.template('arsol-product-line-item');
            this.onetime_fee_template = wp.template('arsol-onetime-fee-line-item');
            this.recurring_fee_template = wp.template('arsol-recurring-fee-line-item');
            this.line_item_id = 0;
            this.bindEvents();
            this.loadExistingItems();
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

        updateRecurringTotals: function(recurringTotals, interval, period, amount) {
            if (interval && period && amount > 0) {
                var cycleKey = this.getCycleKey(interval, period);
                if (!recurringTotals[cycleKey]) {
                    recurringTotals[cycleKey] = { total: 0, interval: interval, period: period };
                }
                recurringTotals[cycleKey].total += amount;
            }
        },

        bindEvents: function() {
            var builder = $('#proposal_invoice_builder');
            builder.on('click', '.add-line-item', this.addLineItem.bind(this));
            builder.on('click', '.remove-line-item', this.removeLineItem.bind(this));
            builder.on('change', '.product-select', this.productChanged.bind(this));
            builder.on('input change', '.quantity-input, .sale-price-input, .price-input, .fee-amount-input, .billing-interval, .billing-period', this.calculateTotals.bind(this));
        },
        
        loadExistingItems: function() {
            console.log('Arsol Proposal Invoice: Loading existing items...', arsol_proposal_invoice_vars.line_items);
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
            console.log('Arsol Proposal Invoice: Finished loading items.');
            this.calculateTotals();
        },

        renderRow: function(type, data) {
            console.log('Arsol Proposal Invoice: Rendering row...', {type: type, data: data});
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
            } else {
                console.error('Arsol Proposal Invoice: Unknown row type for rendering:', type);
                return;
            }
            
            var $newRow = $(template(data));
            $(container).append($newRow);
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
             console.log('Arsol Proposal Invoice: Fetching details for product ID:', productId);
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
                     console.log('Arsol Proposal Invoice: AJAX success response:', response);
                     if (response.success) {
                         var data = response.data;
                         $row.find('.price-input').val(data.regular_price);
                         $row.find('.sale-price-input').val(data.sale_price);
                         $row.find('.product-sub-text').html(data.sub_text);
                         
                         $row.data('is-subscription', data.is_subscription);
                         $row.data('sign-up-fee', data.sign_up_fee || 0);
                         $row.data('billing-interval', data.billing_interval);
                         $row.data('billing-period', data.billing_period);
                         
                         self.calculateTotals();
                     } else {
                         console.error('Arsol Proposal Invoice: AJAX call was successful but API returned an error:', response.data);
                     }
                 },
                 error: function(xhr, status, error) {
                     console.error('Arsol Proposal Invoice: AJAX request failed!', {
                         status: status,
                         error: error,
                         xhr: xhr
                     });
                 }
             });
        },
        
        calculateTotals: function() {
            console.log('Arsol Proposal Invoice: Calculating totals...');
            var oneTimeTotal = 0;
            var recurringTotals = {}; // Use an object to group by billing cycle
            var currencySymbol = arsol_proposal_invoice_vars.currency_symbol;
            
            var formatPrice = function(price) {
                var formattedPrice = Number(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                return '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">' + currencySymbol + '</span>' + formattedPrice + '</bdi></span>';
            };
            
            $('#product-lines-body .line-item').each(function() {
                var $row = $(this);
                var quantity = parseFloat($row.find('.quantity-input').val()) || 1;
                var isSubscription = $row.data('is-subscription');
                
                var salePrice = parseFloat($row.find('.sale-price-input').val());
                var regularPrice = parseFloat($row.find('.price-input').val()) || 0;
                var unitPrice = !isNaN(salePrice) && salePrice > 0 ? salePrice : regularPrice;
                
                if (isSubscription) {
                    var signUpFee = parseFloat($row.data('sign-up-fee')) || 0;
                    oneTimeTotal += quantity * signUpFee;
                    
                    var recurringAmount = unitPrice;
                    var interval = $row.data('billing-interval');
                    var period = $row.data('billing-period');
                    
                    var subtotalDisplay = formatPrice(quantity * recurringAmount);

                    if (interval && period) {
                        subtotalDisplay += ' ' + this.getCycleLabel(interval, period);
                        this.updateRecurringTotals(recurringTotals, interval, period, quantity * recurringAmount);
                    }
                    
                    if (signUpFee > 0) {
                        subtotalDisplay += ' (+ ' + formatPrice(quantity * signUpFee) + ' sign-up)';
                    }
                    $row.find('.subtotal-display').html(subtotalDisplay);

                } else {
                    var oneTimeSubtotal = quantity * unitPrice;
                    oneTimeTotal += oneTimeSubtotal;
                    $row.find('.subtotal-display').html(formatPrice(oneTimeSubtotal));
                }
            });
            
            $('#onetime-fee-lines-body .line-item').each(function() {
                var $row = $(this);
                var amount = parseFloat($row.find('.fee-amount-input').val()) || 0;
                oneTimeTotal += amount;
                $row.find('.subtotal-display').html(formatPrice(amount));
            });

            $('#recurring-fee-lines-body .line-item').each(function() {
                var $row = $(this);
                var amount = parseFloat($row.find('.fee-amount-input').val()) || 0;
                var interval = $row.find('.billing-interval').val();
                var period = $row.find('.billing-period').val();
               
                if (interval && period) {
                     this.updateRecurringTotals(recurringTotals, interval, period, amount);
                     
                     // Use the helper to show the full cycle in the subtotal
                     var subtotalText = formatPrice(amount) + ' ' + this.getCycleLabel(interval, period);
                     $row.find('.subtotal-display').html(subtotalText);
                } else {
                    // Fallback for when cycle isn't fully defined
                    $row.find('.subtotal-display').html(formatPrice(amount));
                }
            });
            
            $('#one-time-total-display').html(formatPrice(oneTimeTotal));
            $('#line_items_one_time_total').val(oneTimeTotal.toFixed(2));
            
            // Render recurring totals
            var $recurringDisplay = $('#recurring-totals-display');
            var recurringHtml = [];

            if (Object.keys(recurringTotals).length > 0) {
                 $.each(recurringTotals, function(key, data) {
                     var label = this.getCycleLabel(data.interval, data.period);
                     recurringHtml.push(formatPrice(data.total) + ' ' + label);
                 });
                 $recurringDisplay.html(recurringHtml.join('<br>'));
            } else {
                $recurringDisplay.html(formatPrice(0));
            }
            
            $('#line_items_recurring_totals').val(JSON.stringify(recurringTotals));
        }
    };

    $(function() {
        ArsolProposalInvoice.init();
    });

})(jQuery);