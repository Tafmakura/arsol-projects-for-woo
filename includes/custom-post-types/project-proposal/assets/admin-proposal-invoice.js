// JS for Arsol Proposal Invoice Metabox
(function($) {
    'use strict';

    var ArsolProposalInvoice = {
        init: function() {
            this.product_template = wp.template('arsol-product-line-item');
            this.onetime_fee_template = wp.template('arsol-onetime-fee-line-item');
            this.recurring_fee_template = wp.template('arsol-recurring-fee-line-item');
            this.line_item_id = 0;
            this.bindEvents();
            this.loadExistingItems();
        },

        bindEvents: function() {
            var builder = $('#proposal_invoice_builder');
            builder.on('click', '.add-line-item', this.addLineItem.bind(this));
            builder.on('click', '.remove-line-item', this.removeLineItem.bind(this));
            builder.on('change', '.product-select', this.productChanged.bind(this));
            builder.on('input change', '.quantity-input, .sale-price-input, .price-input, .fee-amount-input, .billing-interval, .billing-period', this.calculateTotals.bind(this));
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
            } else {
                return;
            }
            
            var $newRow = $(template(data));
            $(container).append($newRow);
            if (type === 'product') {
                this.initSelect2($newRow);
                // After rendering an existing product, re-set its data from the fields
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
                         // Set visual fields
                         $row.find('.price-input').val(data.regular_price);
                         $row.find('.sale-price-input').val(data.sale_price);
                         $row.find('.product-sub-text').html(data.sub_text);
                         
                         // Store data for calculation
                         $row.data('is-subscription', data.is_subscription);
                         $row.data('recurring-amount', data.recurring_amount);
                         
                         self.calculateTotals();
                     }
                 }
             });
        },
        
        calculateTotals: function() {
            var oneTimeTotal = 0;
            var recurringTotal = 0;
            var currencySymbol = arsol_proposal_invoice_vars.currency_symbol;
            
            var formatPrice = function(price) {
                var formattedPrice = price.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                return '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">' + currencySymbol + '</span>' + formattedPrice + '</bdi></span>';
            };

            // Product Totals
            $('#product-lines-body .line-item').each(function() {
                var $row = $(this);
                var quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                var isSubscription = $row.data('is-subscription');
                
                // One-time part of the product (regular price or sign-up fee)
                var salePrice = parseFloat($row.find('.sale-price-input').val());
                var price = parseFloat($row.find('.price-input').val()) || 0;
                var oneTimeUnitPrice = !isNaN(salePrice) && salePrice > 0 ? salePrice : price;
                var oneTimeSubtotal = quantity * oneTimeUnitPrice;
                
                oneTimeTotal += oneTimeSubtotal;
                
                if (isSubscription) {
                    var recurringAmount = parseFloat($row.data('recurring-amount')) || 0;
                    recurringTotal += quantity * recurringAmount;
                }
                
                $row.find('.subtotal-display').html(formatPrice(oneTimeSubtotal));
            });
            
            // One-Time Fee Totals
            $('#onetime-fee-lines-body .line-item').each(function() {
                var $row = $(this);
                var amount = parseFloat($row.find('.fee-amount-input').val()) || 0;
                oneTimeTotal += amount;
                $row.find('.subtotal-display').html(formatPrice(amount));
            });

            // Recurring Fee Totals
            $('#recurring-fee-lines-body .line-item').each(function() {
                var $row = $(this);
                var amount = parseFloat($row.find('.fee-amount-input').val()) || 0;
                recurringTotal += amount;
                var interval = $row.find('.billing-interval option:selected').text();
                var period = $row.find('.billing-period option:selected').text();
                $row.find('.subtotal-display').html(formatPrice(amount) + ' / ' + period);
            });
            
            $('#one-time-total-display').html(formatPrice(oneTimeTotal));
            $('#recurring-total-display').html(formatPrice(recurringTotal));
            $('#line_items_one_time_total').val(oneTimeTotal.toFixed(2));
            $('#line_items_recurring_total').val(recurringTotal.toFixed(2));
        }
    };

    $(function() {
        ArsolProposalInvoice.init();
    });

})(jQuery);