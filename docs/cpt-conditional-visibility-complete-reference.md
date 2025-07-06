# Conditional Visibility System - Complete Reference

## Overview

The **Smart Conditional Visibility System** provides dynamic show/hide functionality for admin elements based on form field values. It automatically discovers conditional elements in the DOM and binds appropriate events - no JavaScript configuration required.

## Key Features

- ✅ **Zero Configuration**: Just add CSS classes, everything else is automatic
- ✅ **Auto-Discovery**: Scans DOM for conditional classes and binds events automatically  
- ✅ **Universal**: Works with any field ID pattern (ID selectors, class selectors)
- ✅ **Robust**: Validates classes and fails gracefully with helpful debugging
- ✅ **Performance**: Only monitors fields that have conditional elements
- ✅ **Future-Proof**: New conditional elements work instantly

---

## CSS Class Naming Pattern

### Basic Pattern
```
arsol-pfw-{action}-if-{field-name}-is-{value}
```

### Components
- **`arsol-pfw-`**: Required namespace prefix
- **`{action}`**: `show` or `hide`
- **`if`**: Required separator
- **`{field-name}`**: Exact field ID (without # or .)
- **`is`**: Required separator  
- **`{value}`**: Field value to match

---

## Examples

### Standard Examples

```html
<!-- Field -->
<select id="proposal-stage">
    <option value="processing">Processing</option>
    <option value="approved">Approved</option>
    <option value="rejected">Rejected</option>
</select>

<!-- Conditional Elements -->
<div class="arsol-pfw-show-if-proposal-stage-is-processing">
    Shows when proposal-stage equals "processing"
</div>

<div class="arsol-pfw-hide-if-proposal-stage-is-rejected">
    Hidden when proposal-stage equals "rejected"
</div>

<div class="arsol-pfw-show-if-proposal-stage-is-approved">
    Shows when proposal-stage equals "approved"
</div>
```

### Complex Field Names

```html
<!-- Field with complex ID -->
<input id="my-custom-billing-field" type="text">

<!-- Conditional Element -->
<div class="arsol-pfw-show-if-my-custom-billing-field-is-monthly">
    Shows when my-custom-billing-field equals "monthly"
</div>
```

### Multiple Values

```html
<!-- Field -->
<select id="user-role">
    <option value="admin">Admin</option>
    <option value="editor">Editor</option>
    <option value="subscriber">Subscriber</option>
</select>

<!-- Multiple conditional elements for same field -->
<div class="arsol-pfw-show-if-user-role-is-admin">
    Admin-only content
</div>

<div class="arsol-pfw-show-if-user-role-is-editor">
    Editor-only content  
</div>

<div class="arsol-pfw-hide-if-user-role-is-subscriber">
    Hidden from subscribers
</div>
```

### Legacy Field IDs

```html
<!-- Legacy field with underscores -->
<select id="proposal_stage">
    <option value="processing">Processing</option>
    <option value="approved">Approved</option>
</select>

<!-- Use exact field ID in class -->
<div class="arsol-pfw-show-if-proposal_stage-is-approved">
    Shows when proposal_stage equals "approved"
</div>
```

### Class-Based Fields

```html
<!-- Field using class selector -->
<select class="billing-period">
    <option value="month">Monthly</option>
    <option value="year">Yearly</option>
</select>

<!-- Use class name in conditional class -->
<div class="arsol-pfw-show-if-billing-period-is-year">
    Shows when .billing-period equals "year"
</div>
```

### General Settings Example

```html
<!-- Frontend Permissions field (kebab-case ID with arsol-pfw prefix) -->
<select id="arsol-pfw-user-project-permissions">
    <option value="none">None</option>
    <option value="request">Users can request projects</option>
    <option value="create">Users can create projects</option>
    <option value="user_specific">Set per user</option>
</select>

<!-- New User Permissions field (shown only when "Set per user" is selected) -->
<div class="arsol-pfw-show-if-arsol-pfw-user-project-permissions-is-user_specific">
    <label for="arsol-pfw-default-user-permission">New User Permissions</label>
    <select id="arsol-pfw-default-user-permission">
        <option value="none">None</option>
        <option value="request">Can request projects</option>
        <option value="create">Can create projects</option>
    </select>
    <p class="description">Default permission level assigned to new users</p>
</div>
```

**Key Points:**
- ✅ **Field ID:** `arsol-pfw-user-project-permissions` (kebab-case with arsol-pfw prefix)
- ✅ **CSS Class:** `arsol-pfw-show-if-arsol-pfw-user-project-permissions-is-user_specific` (matches field ID exactly)
- ✅ **WordPress Standards:** Follows WordPress CSS naming conventions
- ✅ **Consistent Prefix:** All elements use `arsol-pfw-` prefix for proper namespacing

### WordPress Standards Implementation

```html
<!-- ✅ CORRECT: WordPress kebab-case with prefix -->
<select id="arsol-pfw-user-project-permissions">
<div class="arsol-pfw-show-if-arsol-pfw-user-project-permissions-is-user_specific">

<!-- ❌ INCORRECT: Mixed naming conventions -->
<select id="user_project_permissions">
<div class="arsol-pfw-show-if-user_project_permissions-is-user_specific">
```

### ⚠️ **Critical Rule: Value Matching**

The **value part** of the CSS class must match the field value **exactly** - do NOT convert to kebab-case:

```html
<!-- Field with option value -->
<select id="arsol-pfw-user-project-permissions">
    <option value="user_specific">Set per user</option>  <!-- Value has underscore -->
</select>

<!-- ✅ CORRECT: Value matches exactly -->
<div class="arsol-pfw-show-if-arsol-pfw-user-project-permissions-is-user_specific">
    <!-- CSS class uses exact value: user_specific -->
</div>

<!-- ❌ INCORRECT: Value converted to kebab-case -->
<div class="arsol-pfw-show-if-arsol-pfw-user-project-permissions-is-user-specific">
    <!-- This would NOT work - value doesn't match -->
</div>
```

### CSS Class Naming Rules

| **Part** | **Format** | **Example** |
|---|---|---|
| Prefix | kebab-case | `arsol-pfw-` |
| Action | kebab-case | `show-if-` |
| Field ID | kebab-case | `arsol-pfw-user-project-permissions-` |
| Value | **EXACT MATCH** | `user_specific` (not `user-specific`) |

**Final Class:** `arsol-pfw-show-if-arsol-pfw-user-project-permissions-is-user_specific`

---

## Field ID Requirements

### Rule: CSS Class Field Name = Exact Field ID

The field name in your CSS class **must exactly match** your field ID:

| **Field Element** | **CSS Class Field Name** | **Result** |
|---|---|---|
| `id="status"` | `arsol-pfw-show-if-status-is-active` | ✅ Matches |
| `id="my-field"` | `arsol-pfw-show-if-my-field-is-yes` | ✅ Matches |
| `id="proposal_stage"` | `arsol-pfw-show-if-proposal_stage-is-approved` | ✅ Matches |
| `class="billing-period"` | `arsol-pfw-show-if-billing-period-is-month` | ✅ Matches |

### No Automatic Conversion

The system does **NOT** perform automatic conversions:

```html
❌ <select id="my-field">
❌ <div class="arsol-pfw-show-if-my_field-is-active"> <!-- Wrong: underscores don't auto-convert -->

✅ <select id="my-field">  
✅ <div class="arsol-pfw-show-if-my-field-is-active"> <!-- Correct: exact match -->
```

---

## Supported Actions

### Show Action
Shows element when field value matches:
```html
<div class="arsol-pfw-show-if-status-is-active">
    Visible when status = "active", hidden otherwise
</div>
```

### Hide Action  
Hides element when field value matches:
```html
<div class="arsol-pfw-hide-if-status-is-disabled">
    Hidden when status = "disabled", visible otherwise
</div>
```

---

## Multiple Conditions

### Same Element, Multiple Conditions
```html
<div class="arsol-pfw-show-if-status-is-active arsol-pfw-show-if-type-is-premium">
    Shows when: status = "active" AND type = "premium"
    (Both conditions must be true)
</div>
```

### Same Field, Different Values
```html
<div class="arsol-pfw-show-if-status-is-active">Active content</div>
<div class="arsol-pfw-show-if-status-is-pending">Pending content</div>  
<div class="arsol-pfw-show-if-status-is-rejected">Rejected content</div>
```

### Mixed Actions
```html
<div class="arsol-pfw-show-if-type-is-premium arsol-pfw-hide-if-status-is-disabled">
    Shows when: type = "premium" AND status ≠ "disabled"
</div>
```

---

## Validation & Error Handling

### The system validates all classes and fails gracefully:

### Valid Class Patterns
```html
✅ arsol-pfw-show-if-field-is-value
✅ arsol-pfw-hide-if-my-field-name-is-active  
✅ arsol-pfw-show-if-complex-field-name-is-complex-value
```

### Invalid Class Patterns
```html
❌ arsol-pfw-show-field-is-value         (Missing '-if-')
❌ arsol-pfw-show-if-field               (Missing '-is-' and value)
❌ arsol-pfw-invalid-if-field-is-value   (Invalid action)
❌ arsol-pfw-show-if-is-value            (Missing field name)  
❌ arsol-pfw-show-if-field-is-           (Missing value)
```

### Console Output for Invalid Classes
```javascript
// Malformed structure
ArsolConditionalVisibility: Malformed class structure "arsol-pfw-show-field-is-value" - missing required parts

// Invalid action
ArsolConditionalVisibility: Invalid action "invalid" in class "arsol-pfw-invalid-if-field-is-value" - must be "show" or "hide"

// Missing field
ArsolConditionalVisibility: Field not found for class "arsol-pfw-show-if-nonexistent-is-value" - looking for field: #nonexistent

// Missing elements
ArsolConditionalVisibility: No elements found with class "arsol-pfw-show-if-field-is-value"
```

---

## Debugging

### Enable Console Logging
The system automatically logs helpful information to the browser console:

### Successful Initialization
```javascript
ArsolConditionalVisibility: Successfully discovered field mappings: {
  "proposal-stage": {
    selector: "#proposal-stage", 
    conditions: [
      {action: "show", value: "processing", className: "arsol-pfw-show-if-proposal-stage-is-processing"}
    ]
  }
}
```

### Field Updates
```javascript
ArsolConditionalVisibility: Updating field proposal-stage value: processing
```

### Common Issues & Solutions

| **Issue** | **Console Message** | **Solution** |
|---|---|---|
| Field not found | `Field not found for class... looking for field: #my-field` | Check field ID exists in DOM |
| Malformed class | `Malformed class structure...` | Fix CSS class pattern |
| No elements | `No elements found with class...` | Check CSS class exists on elements |
| Invalid action | `Invalid action "xyz"... must be "show" or "hide"` | Use only `show` or `hide` |

---

## Performance

### Optimized Event Binding
- Only binds events to fields that have conditional elements
- Uses event delegation for efficiency  
- No hardcoded field monitoring

### Auto-Discovery Process
1. Scans DOM for `arsol-pfw-show-if-*` and `arsol-pfw-hide-if-*` classes
2. Extracts field names from classes
3. Validates field existence in DOM
4. Binds change events only to discovered fields
5. Updates visibility in real-time

---

## Integration

### File Location
The smart conditional visibility system is implemented in:
```
assets/js/arsol-pfw-admin.js
```

### Auto-Loading
The system is automatically loaded on all admin pages:
- Settings pages
- User profile pages
- Project edit screens
- Proposal edit screens  
- Request edit screens
- Any admin page where conditional logic is needed

### Initialization
```javascript
// Automatically initializes on document ready
$(document).ready(function() {
    ArsolConditionalVisibility.init();
});

// Also refreshes after AJAX calls and DOM mutations
$(document).ajaxComplete(function() {
    setTimeout(function() {
        ArsolConditionalVisibility.refresh();
    }, 100);
});

// MutationObserver automatically detects new conditional elements
// and refreshes the system when needed
```

---

## API Reference

### Global Object
```javascript
window.ArsolConditionalVisibility
```

### Methods

#### `init()`
Initialize the system (called automatically)
```javascript
ArsolConditionalVisibility.init();
```

#### `refresh()`
Re-scan DOM and rebuild mappings (useful after AJAX content updates)
```javascript
ArsolConditionalVisibility.refresh();
```

#### `updateConditionalVisibilityForField(fieldId)`
Manually trigger visibility update for specific field
```javascript
ArsolConditionalVisibility.updateConditionalVisibilityForField('proposal-stage');
```

### Properties

#### `fieldMappings`
Object containing discovered field mappings
```javascript
console.log(ArsolConditionalVisibility.fieldMappings);
```

---

## Best Practices

### 1. Use Descriptive Field Names
```html
✅ id="proposal-stage"        (Clear purpose)
❌ id="ps"                    (Unclear abbreviation)
```

### 2. Use Semantic Values
```html
✅ value="processing"         (Descriptive)
❌ value="1"                  (Unclear meaning)
```

### 3. Group Related Conditions
```html
<!-- Group conditions logically -->
<div class="proposal-section arsol-pfw-show-if-proposal-stage-is-processing">
    <h3>Processing Stage</h3>
    <!-- Processing-specific content -->
</div>

<div class="proposal-section arsol-pfw-show-if-proposal-stage-is-approved">  
    <h3>Approved Stage</h3>
    <!-- Approval-specific content -->
</div>
```

### 4. Test Edge Cases
- Empty field values
- Special characters in values
- Dynamic content updates
- AJAX form submissions

### 5. Use Browser Developer Tools
- Check console for validation messages
- Inspect element classes for typos
- Verify field IDs match exactly

---

## Migration from Hardcoded Systems

### Old Hardcoded Approach
```javascript
// ❌ Old: Hardcoded in JavaScript
$('#proposal-stage').on('change', function() {
    if ($(this).val() === 'processing') {
        $('.processing-content').show();
    } else {
        $('.processing-content').hide(); 
    }
});
```

### New Conditional Class Approach
```html
<!-- ✅ New: Just add CSS class -->
<div class="processing-content arsol-pfw-show-if-proposal-stage-is-processing">
    Processing content
</div>
```

### Migration Steps
1. **Identify** hardcoded conditional logic in JavaScript
2. **Replace** with CSS classes on target elements
3. **Remove** hardcoded JavaScript event handlers
4. **Test** functionality with browser console open
5. **Verify** no console errors or warnings

---

## Troubleshooting

### Element Not Showing/Hiding

1. **Check field ID**: Does `#field-name` exist in DOM?
2. **Check CSS class**: Is pattern `arsol-pfw-show-if-field-name-is-value` correct?
3. **Check field value**: Does field actually have the expected value?
4. **Check console**: Any validation errors logged?

### Console Validation
```javascript
// Check if field exists
console.log($('#my-field').length); // Should be > 0

// Check field value  
console.log($('#my-field').val()); // Should match expected value

// Check conditional elements
console.log($('.arsol-pfw-show-if-my-field-is-value').length); // Should be > 0

// Check system state
console.log(ArsolConditionalVisibility.fieldMappings);
```

### Performance Issues
- Use browser Performance tab to check for excessive event handlers
- Verify only necessary fields are being monitored
- Check for duplicate conditional classes

---

## Browser Support

- **Chrome**: ✅ All versions
- **Firefox**: ✅ All versions  
- **Safari**: ✅ All versions
- **Edge**: ✅ All versions
- **IE**: ❌ Not supported (uses modern JavaScript)

---

## Version History

### v1.0.0 - Smart Conditional System
- Auto-discovery based on CSS classes
- No hardcoded field IDs in JavaScript
- Comprehensive validation and error handling
- Supports any field ID pattern
- Graceful failure with debugging output

---

This completes the comprehensive reference for the Smart Conditional Visibility System. 