jQuery(document).ready(function($) {
    'use strict';
    
    /**
     * Arsol Projects For Woo - Smart Conditional Visibility System
     * Works across all admin pages with CSS class-based conditional logic
     * 
     * Class naming pattern: arsol-pfw-{action}-if-{field-name}-is-{option-value}
     * Example: arsol-pfw-show-if-user-project-permissions-is-user-specific
     */
    var ArsolConditionalVisibility = {
        
        /**
         * Initialize the conditional visibility system
         */
        init: function() {
            this.initializeFieldMappings();
            this.bindEvents();
            this.updateAllConditionalVisibility();
        },

        /**
         * Dynamically discover all conditional elements and their field mappings
         */
        initializeFieldMappings: function() {
            this.fieldMappings = {};
            
            // Find all elements with conditional classes
            $('[class*="arsol-pfw-show-if-"], [class*="arsol-pfw-hide-if-"]').each(function() {
                var $element = $(this);
                var classes = $element.attr('class').split(' ');
                
                classes.forEach(function(className) {
                    var mapping = ArsolConditionalVisibility.parseConditionalClass(className);
                    if (mapping) {
                        // Validate that the field actually exists in the DOM
                        var fieldExists = false;
                        var selector = mapping.fieldId.startsWith('.') ? mapping.fieldId : '#' + mapping.fieldId;
                        
                        if ($(selector).length > 0) {
                            fieldExists = true;
                        }
                        
                        if (!fieldExists) {
                            console.log('ArsolConditionalVisibility: Field not found for class "' + className + '" - looking for field: ' + selector);
                            return; // Skip this mapping - fail quietly
                        }
                        
                        // Store the mapping for this field
                        if (!ArsolConditionalVisibility.fieldMappings[mapping.fieldId]) {
                            // Build selector based on whether it's an ID or class
                            var finalSelector = mapping.fieldId.startsWith('.') ? mapping.fieldId : '#' + mapping.fieldId;
                            
                            ArsolConditionalVisibility.fieldMappings[mapping.fieldId] = {
                                selector: finalSelector,
                                conditions: []
                            };
                        }
                        
                        // Add this condition to the field mapping
                        ArsolConditionalVisibility.fieldMappings[mapping.fieldId].conditions.push({
                            action: mapping.action,
                            value: mapping.value,
                            className: className
                        });
                    }
                });
            });
            
            console.log('ArsolConditionalVisibility: Successfully discovered field mappings:', this.fieldMappings);
        },

        /**
         * Parse a conditional class name and extract components
         * @param {string} className - The CSS class name to parse
         * @returns {object|null} - Parsed components or null if not a conditional class
         */
        parseConditionalClass: function(className) {
            // Pattern: arsol-pfw-{action}-if-{field-name}-is-{option-value}
            if (!className.startsWith('arsol-pfw-') || !className.includes('-if-') || !className.includes('-is-')) {
                return null; // Not a conditional class - ignore silently
            }
            
            var parts = className.split('-');
            var actionIndex = parts.indexOf('pfw') + 1; // Position after 'pfw'
            var ifIndex = parts.indexOf('if');
            var isIndex = parts.indexOf('is');
            
            // Validate class structure
            if (actionIndex === 0 || ifIndex === -1 || isIndex === -1) {
                console.log('ArsolConditionalVisibility: Malformed class structure "' + className + '" - missing required parts');
                return null; // Fail quietly
            }
            
            // Validate action position
            if (actionIndex >= ifIndex) {
                console.log('ArsolConditionalVisibility: Malformed class "' + className + '" - action must come before "if"');
                return null; // Fail quietly
            }
            
            // Validate if/is order
            if (ifIndex >= isIndex) {
                console.log('ArsolConditionalVisibility: Malformed class "' + className + '" - "if" must come before "is"');
                return null; // Fail quietly
            }
            
            // Extract components
            var action = parts[actionIndex]; // 'show' or 'hide'
            var fieldParts = parts.slice(ifIndex + 1, isIndex);
            var valueParts = parts.slice(isIndex + 1);
            
            // Validate action
            if (action !== 'show' && action !== 'hide') {
                console.log('ArsolConditionalVisibility: Invalid action "' + action + '" in class "' + className + '" - must be "show" or "hide"');
                return null; // Fail quietly
            }
            
            // Validate field name
            if (fieldParts.length === 0) {
                console.log('ArsolConditionalVisibility: Missing field name in class "' + className + '"');
                return null; // Fail quietly
            }
            
            // Validate value
            if (valueParts.length === 0) {
                console.log('ArsolConditionalVisibility: Missing value in class "' + className + '"');
                return null; // Fail quietly
            }
            
            var fieldName = fieldParts.join('-');
            var value = valueParts.join('-');
            
            // Convert field name to likely field ID
            var fieldId = this.convertFieldNameToId(fieldName);
            
            return {
                action: action,
                fieldName: fieldName,
                fieldId: fieldId,
                value: value,
                originalClass: className
            };
        },

        /**
         * Convert field name from class to field ID
         * @param {string} fieldName - Field name from class (e.g., 'arsol-pfw-status' or 'red-fire-field')
         * @returns {string} - Exact field ID (e.g., 'arsol-pfw-status' or 'red-fire-field')
         */
        convertFieldNameToId: function(fieldName) {
            // EXPLICIT STANDARD: CSS class field name must match exact field ID
            // No automatic prefixing, no conversion - what you write is what you get
            
            // Check if field exists as ID
            if ($('#' + fieldName).length > 0) {
                return fieldName;
            }
            
            // Check if field exists as class selector
            if ($('.' + fieldName).length > 0) {
                return '.' + fieldName;
            }
            
            // Return as-is even if not found (for debugging and future fields)
            return fieldName;
        },

        /**
         * Bind events to all discovered conditional fields
         */
        bindEvents: function() {
            var self = this;
            
            // Bind to each discovered field automatically
            Object.keys(this.fieldMappings).forEach(function(fieldId) {
                var mapping = self.fieldMappings[fieldId];
                
                $(document).on('change', mapping.selector, function() {
                    self.updateConditionalVisibilityForField(fieldId);
                });
            });
            
            // No hardcoded selectors - system is purely auto-discovery based!
            // Fields are only monitored if they have conditional classes in the DOM
        },

        /**
         * Update conditional visibility for a specific field
         * @param {string} fieldId - The field ID to update visibility for
         */
        updateConditionalVisibilityForField: function(fieldId) {
            var mapping = this.fieldMappings[fieldId];
            if (!mapping) {
                console.log('ArsolConditionalVisibility: No mapping found for field "' + fieldId + '"');
                return; // Fail quietly
            }
            
            var $field = $(mapping.selector);
            if ($field.length === 0) {
                console.log('ArsolConditionalVisibility: Field "' + mapping.selector + '" not found in DOM');
                return; // Fail quietly
            }
            
            // Handle both select fields and checkboxes
            var currentValue;
            if ($field.attr('type') === 'checkbox') {
                currentValue = $field.is(':checked') ? 'checked' : 'unchecked';
            } else {
                currentValue = $field.val();
            }
            
            console.log('ArsolConditionalVisibility: Updating field', fieldId, 'value:', currentValue);
            
            // Process each condition for this field
            mapping.conditions.forEach(function(condition) {
                var $elements = $('.' + condition.className);
                
                if ($elements.length === 0) {
                    console.log('ArsolConditionalVisibility: No elements found with class "' + condition.className + '"');
                    return; // Continue to next condition
                }
                
                var shouldShow = false;
                
                if (condition.action === 'show') {
                    shouldShow = (currentValue === condition.value);
                } else if (condition.action === 'hide') {
                    shouldShow = (currentValue !== condition.value);
                }
                
                // Apply visibility: jQuery show/hide (preserves original display types automatically)
                if (shouldShow) {
                    $elements.show();
                } else {
                    $elements.hide();
                }
            });
        },

        /**
         * Update all conditional visibility on page load
         */
        updateAllConditionalVisibility: function() {
            var self = this;
            
            Object.keys(this.fieldMappings).forEach(function(fieldId) {
                self.updateConditionalVisibilityForField(fieldId);
            });
        },

        /**
         * Refresh the system - useful when DOM is modified
         */
        refresh: function() {
            this.initializeFieldMappings();
            this.bindEvents();
            this.updateAllConditionalVisibility();
        }
    };

    // Initialize conditional visibility system
    ArsolConditionalVisibility.init();
    
    // Expose to global scope for debugging
    window.ArsolConditionalVisibility = ArsolConditionalVisibility;
    
    // ========================================
    // DROPDOWN ENHANCEMENT SYSTEM
    // ========================================
    
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

    function initUnifiedUserSearch() {
        $('.arsol-pfw-user-search').filter(':not(.enhanced)').each(function() {
            var $this = $(this);
            var searchType = $this.data('search-type') || 'users';
            
            var select2_args = {
                allowClear: $this.data('allow_clear') ? true : false,
                placeholder: $this.data('placeholder') || 'Search for user...',
                minimumInputLength: 1,
                width: 'resolve',
                ajax: {
                    url: ajaxurl,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            term: params.term,
                            action: $this.data('action') || 'arsol_pfw_ajax_search_users',
                            search_type: searchType,
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
        initUnifiedUserSearch();
        initAdminTableFilters();
    }
    
    // Initialize dropdown enhancements
    initAllDropdowns();
    
    // ========================================
    // MUTATION OBSERVER FOR DYNAMIC CONTENT
    // ========================================
    
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function(mutations) {
            var shouldReinitDropdowns = false;
            var shouldRefreshConditionals = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) {
                            var $node = $(node);
                            
                            // Check for dropdown elements
                            if ($node.is('.wc-enhanced-select, .wc-customer-search, .arsol-user-select2, .arsol-pfw-user-search') || 
                                $node.find('.wc-enhanced-select, .wc-customer-search, .arsol-user-select2, .arsol-pfw-user-search').length) {
                                shouldReinitDropdowns = true;
                            }
                            
                            // Check for conditional elements
                            if ($node.is('[class*="arsol-pfw-show-if-"], [class*="arsol-pfw-hide-if-"]') || 
                                $node.find('[class*="arsol-pfw-show-if-"], [class*="arsol-pfw-hide-if-"]').length) {
                                shouldRefreshConditionals = true;
                            }
                        }
                    });
                }
            });
            
            if (shouldReinitDropdowns) {
                setTimeout(initAllDropdowns, 100);
            }
            
            if (shouldRefreshConditionals) {
                setTimeout(function() {
                    ArsolConditionalVisibility.refresh();
                }, 100);
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    // Also refresh conditional visibility on AJAX complete
    $(document).ajaxComplete(function() {
        setTimeout(function() {
            ArsolConditionalVisibility.refresh();
        }, 100);
    });
}); 