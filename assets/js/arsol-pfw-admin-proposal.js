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

    // Basic Budget Calculations
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
            
            $('.budget-total-display').text(currency + amount.toFixed(2));
        }
    };

    // Basic Quotation System
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
            
            $(container).append(template(data));
            this.calculate();
        },

        removeItem: function(e) {
            e.preventDefault();
            $(e.target).closest('tr').remove();
            this.calculate();
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
                        $row.find('.arsol-price-input').val(response.data.regular_price);
                        $row.find('.arsol-sale-price-input').val(response.data.sale_price);
                        ArsolQuotation.calculate();
                    }
                });
            }
        },

        calculate: function() {
            var total = 0;
            
            // Calculate product totals
            $('#product-lines-body tr').each(function() {
                var qty = parseFloat($(this).find('.arsol-quantity-input').val()) || 0;
                var price = parseFloat($(this).find('.arsol-price-input').val()) || 0;
                total += qty * price;
            });
            
            // Calculate fee totals
            $('.arsol-amount-input').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            
            var currency = (typeof arsol_pfw_proposal_quotation_vars !== 'undefined') ? 
                          arsol_pfw_proposal_quotation_vars.currency_symbol : '$';
            
            $('#one-time-total-display').text(currency + total.toFixed(2));
        },

        loadExisting: function() {
            if (typeof arsol_pfw_proposal_quotation_vars !== 'undefined' && 
                arsol_pfw_proposal_quotation_vars.line_items) {
                var items = arsol_pfw_proposal_quotation_vars.line_items;
                
                // Load existing items
                if (items.products) {
                    for (var id in items.products) {
                        this.itemId = Math.max(this.itemId, parseInt(id));
                    }
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