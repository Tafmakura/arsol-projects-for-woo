/**
 * Arsol Projects for WooCommerce - Admin Phases Settings
 * 
 * Handles stage selection with fresh AJAX loading
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize stage selectors
     */
    function initStageSelectors() {
        $('.arsol-stages-select2').each(function() {
            var $select = $(this);
            var taxonomy = $select.data('taxonomy');
            var selectedIds = $select.data('selected') || [];

            // Initialize selectWoo with AJAX for fresh data
            $select.selectWoo({
                placeholder: arsolPhases.strings.placeholder,
                allowClear: true,
                width: '100%',
                ajax: {
                    url: arsolPhases.ajaxurl,
                    dataType: 'json',
                    delay: 0,
                    data: function(params) {
                        return {
                            action: 'arsol_load_taxonomy_terms',
                            taxonomy: taxonomy,
                            nonce: arsolPhases.nonce,
                            security: arsolPhases.nonce
                        };
                    },
                    processResults: function(data) {
                        if (!data.success) {
                            return { results: [] };
                        }

                        // Mark previously selected items as selected
                        if (data.data && data.data.results) {
                            data.data.results.forEach(function(item) {
                                if (selectedIds.includes(parseInt(item.id))) {
                                    item.selected = true;
                                }
                            });
                            return { results: data.data.results };
                        }
                        
                        return { results: [] };
                    },
                    cache: false
                },
                minimumInputLength: 0,
                language: {
                    loadingMore: function() {
                        return arsolPhases.strings.loading;
                    },
                    noResults: function() {
                        return arsolPhases.strings.noResults;
                    }
                }
            });

            // Pre-populate with selected values if any
            if (selectedIds.length > 0) {
                $select.trigger('select2:open');
                $select.trigger('select2:close');
            }
        });
    }

    /**
     * Document ready
     */
    $(document).ready(function() {
        initStageSelectors();
    });

})(jQuery);
