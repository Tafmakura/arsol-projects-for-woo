jQuery(document).ready(function($) {
    'use strict';

    function initUserSelectDropdowns() {
        $('.arsol-user-select2').filter(':not(.enhanced)').each(function() {
            var $this = $(this);
            var placeholder = $this.data('placeholder') || $this.find('option:first').text() || 'Select project lead';
            
            var select2_args = {
                placeholder: placeholder,
                allowClear: true,
                width: 'resolve',
                minimumInputLength: 1
            };
            
            if (typeof $.fn.selectWoo !== 'undefined') {
                $this.selectWoo(select2_args).addClass('enhanced');
            } else if (typeof $.fn.select2 !== 'undefined') {
                $this.select2(select2_args).addClass('enhanced');
            }
        });
    }

    function initProjectLeadAjaxSearch() {
        $('.arsol-project-lead-search').filter(':not(.enhanced)').each(function() {
            var $this = $(this);
            
            var select2_args = {
                allowClear: $this.data('allow_clear') ? true : false,
                placeholder: $this.data('placeholder') || 'Search for project lead...',
                minimumInputLength: 1,
                width: 'resolve',
                ajax: {
                    url: ajaxurl,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            term: params.term,
                            action: $this.data('action') || 'arsol_json_search_project_leads',
                            security: $this.data('security'),
                            limit: $this.data('limit') || 20
                        };
                    },
                    processResults: function(data) {
                        var terms = [];
                        if (data) {
                            $.each(data, function(id, text) {
                                terms.push({ id: id, text: text });
                            });
                        }
                        return { results: terms };
                    },
                    cache: true
                }
            };
            
            if (typeof $.fn.selectWoo !== 'undefined') {
                $this.selectWoo(select2_args).addClass('enhanced');
            } else if (typeof $.fn.select2 !== 'undefined') {
                $this.select2(select2_args).addClass('enhanced');
            }
        });
    }

    function initWooCommerceEnhancedDropdowns() {
        $(':input.wc-enhanced-select, :input.wc-product-search, :input.wc-customer-search').filter(':not(.enhanced)').each(function() {
            var select2_args = {
                minimumResultsForSearch: 10,
                allowClear: $(this).data('allow_clear') ? true : false,
                placeholder: $(this).data('placeholder') || $(this).attr('placeholder') || ''
            };

            if (typeof $.fn.selectWoo !== 'undefined') {
                $(this).selectWoo(select2_args).addClass('enhanced');
            } else if (typeof $.fn.select2 !== 'undefined') {
                $(this).select2(select2_args).addClass('enhanced');
            }
        });
    }

    function initAdminTableFilters() {
        setTimeout(function() {
            $('.wc-customer-search').filter(':not(.enhanced)').each(function() {
                var $this = $(this);
                
                var select2_args = {
                    allowClear: $this.data('allow_clear') ? true : false,
                    placeholder: $this.data('placeholder') || 'Search for a customer...',
                    minimumInputLength: 3,
                    width: 'resolve',
                    ajax: {
                        url: wc_enhanced_select_params.ajax_url,
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                term: params.term,
                                action: $this.data('action') || 'woocommerce_json_search_customers',
                                security: $this.data('security') || wc_enhanced_select_params.search_customers_nonce,
                                exclude: $this.data('exclude'),
                                include: $this.data('include'),
                                limit: $this.data('limit') || 20
                            };
                        },
                        processResults: function(data) {
                            var terms = [];
                            if (data) {
                                $.each(data, function(id, text) {
                                    terms.push({ id: id, text: text });
                                });
                            }
                            return { results: terms };
                        },
                        cache: true
                    }
                };
                
                if (typeof $.fn.selectWoo !== 'undefined') {
                    $this.selectWoo(select2_args).addClass('enhanced');
                } else if (typeof $.fn.select2 !== 'undefined') {
                    $this.select2(select2_args).addClass('enhanced');
                }
            });
        }, 100);
    }
    
    function initAllDropdowns() {
        initWooCommerceEnhancedDropdowns();
        initUserSelectDropdowns();
        initProjectLeadAjaxSearch();
        initAdminTableFilters();
    }
    
    initAllDropdowns();
    
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function(mutations) {
            var shouldReinit = false;
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) {
                            var $node = $(node);
                            if ($node.is('.wc-enhanced-select, .wc-customer-search, .arsol-user-select2, .arsol-project-lead-search') || 
                                $node.find('.wc-enhanced-select, .wc-customer-search, .arsol-user-select2, .arsol-project-lead-search').length) {
                                shouldReinit = true;
                            }
                        }
                    });
                }
            });
            if (shouldReinit) {
                setTimeout(initAllDropdowns, 100);
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
}); 