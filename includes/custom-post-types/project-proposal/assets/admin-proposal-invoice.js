// JS for Arsol Proposal Invoice Metabox
(function($) {
    'use strict';

    var ArsolProposalInvoice = {
        init: function() {
            this.product_template = wp.template('arsol-product-line-item');
            this.fee_template = wp.template('arsol-onetime-fee-line-item');
            this.line_item_id = 0; // A single counter for all line items to ensure uniqueness
            this.bindEvents();
            this.loadExistingItems();
        },

        bindEvents: function() {
            var builder = $('#proposal_invoice_builder');
            builder.on('click', '.add-line-item', this.addLineItem.bind(this));
            builder.on('click', '.remove-line-item', this.removeLineItem.bind(this));
            builder.on('change', '.product-select', this.productChanged.bind(this));
            builder.on('input', '.quantity-input, .sale-price-input, .fee-amount-input', this.calculateTotals.bind(this));
        },
        
        loadExistingItems: function() {
            var self = this;
            var items = arsol_proposal_invoice_vars.line_items;

            // Load products
            if (items && items.products) {
                $.each(items.products, function(id, itemData) {
                    self.line_item_id++;
                    itemData.id = self.line_item_id;
                    var $newRow = $(self.product_template(itemData));
                    $('#product-lines-body').append($newRow);
                    self.initSelect2($newRow);
                });
            }
            
            // Load fees
            if (items && items.one_time_fees) {
                 $.each(items.one_time_fees, function(id, itemData) {
                    self.line_item_id++;
                    itemData.id = self.line_item_id;
                    var $newRow = $(self.fee_template(itemData));
                    $('#onetime-fee-lines-body').append($newRow);
                });
            }

            this.calculateTotals();
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
                    processResults: function(data) {
                        return { results: data.data };
                    },
                    cache: true
                },
                placeholder: 'Search for a product...',
                minimumInputLength: 1
            });
        },

        addLineItem: function(e) {
            e.preventDefault();
            this.line_item_id++;
            var templateData = { id: this.line_item_id };
            var itemType = $(e.currentTarget).data('type');

            if (itemType === 'product') {
                var $newRow = $(this.product_template(templateData));
                $('#product-lines-body').append($newRow);
                this.initSelect2($newRow);
            } else if (itemType === 'onetime-fee') {
                var $newRow = $(this.fee_template(templateData));
                $('#onetime-fee-lines-body').append($newRow);
            }
        },

        removeLineItem: function(e) {
            e.preventDefault();
            $(e.currentTarget).closest('.line-item').remove();
            this.calculateTotals();
        },

        productChanged: function(e) {
            var self = this;
            var $select = $(e.currentTarget);
            var $row = $select.closest('.line-item');
            var productId = $select.val();

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
                        $row.find('.price-input').val(response.data.price);
                        $row.find('.sale-price-input').val(response.data.sale_price);
                        self.calculateTotals();
                    }
                }
            });
        },
        
        calculateTotals: function() {
            var grandTotal = 0;
            var currencySymbol = arsol_proposal_invoice_vars.currency_symbol;

            $('#product-lines-body .line-item').each(function() {
                var $row = $(this);
                var quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                var salePrice = parseFloat($row.find('.sale-price-input').val());
                var price = parseFloat($row.find('.price-input').val()) || 0;
                
                var unitPrice = !isNaN(salePrice) && salePrice > 0 ? salePrice : price;
                var subtotal = quantity * unitPrice;
                
                grandTotal += subtotal;
                
                var formattedPrice = subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                var priceHtml = '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">' + currencySymbol + '</span>' + formattedPrice + '</bdi></span>';

                $row.find('.subtotal-display').html(priceHtml);
            });

            $('#onetime-fee-lines-body .line-item').each(function() {
                var $row = $(this);
                var amount = parseFloat($row.find('.fee-amount-input').val()) || 0;
                grandTotal += amount;
            });
            
            var formattedGrandPrice = grandTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            var grandTotalHtml = '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">' + currencySymbol + '</span>' + formattedGrandPrice + '</bdi></span>';

            $('#grand-total-display').html(grandTotalHtml);
            $('#line_items_grand_total').val(grandTotal.toFixed(2));
        }
    };

    $(function() {
        ArsolProposalInvoice.init();
    });

})(jQuery); 