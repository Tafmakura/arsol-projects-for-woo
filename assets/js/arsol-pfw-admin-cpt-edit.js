/**
 * Arsol Projects For Woo - Smart Conditional Visibility System
 * Works across all CPT edit screens (projects, proposals, requests)
 * 
 * Class naming pattern: arsol-pfw-{action}-if-{field-name}-is-{option-value}
 * Example: arsol-pfw-show-if-proposal-cost-type-is-budget
 */

(function($) {
    'use strict';

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
            
            var currentValue = $field.val();
            
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
                
                // Apply visibility: display: none to hide, remove display property to show (preserves original display)
                if (shouldShow) {
                    // Remove only the display property, preserving other inline styles
                    $elements.each(function() {
                        var $el = $(this);
                        var style = $el.attr('style');
                        if (style) {
                            // Remove display property but keep other styles
                            style = style.replace(/display\s*:\s*[^;]+;?\s*/gi, '');
                            if (style.trim()) {
                                $el.attr('style', style);
                            } else {
                                $el.removeAttr('style');
                            }
                        }
                    });
                } else {
                    $elements.css('display', 'none');
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

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        ArsolConditionalVisibility.init();
        
        // Also initialize on AJAX complete in case new elements are added
        $(document).ajaxComplete(function() {
            setTimeout(function() {
                ArsolConditionalVisibility.refresh();
            }, 100);
        });
    });

    // Expose to global scope for debugging
    window.ArsolConditionalVisibility = ArsolConditionalVisibility;

})(jQuery);
