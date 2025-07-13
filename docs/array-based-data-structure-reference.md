# Array-Based Data Structure Reference

## Overview

This document outlines the array-based data structure approach implemented in Arsol Projects for WooCommerce. This approach follows WooCommerce patterns for handling complex data structures while maintaining clean, consistent APIs.

## Current Implementation Status

### ✅ **Implemented (Current)**
- Array-based complex data structures
- **Entity-specific naming convention** (WooCommerce-aligned)
- Individual field methods for common operations
- Field-specific access methods (`get_budget_field`, `set_budget_field`)
- Data store with complex meta key support
- Comprehensive getter/setter methods

### 🔄 **Next Steps**
- Implement same pattern for Project entity
- Implement same pattern for Request entity
- Update all templates and handlers to use new API
- Create migration scripts for existing data
- Update global functions to use new methods

## Core Principles

### 1. Array-Based Complex Data
Complex data structures (like budgets, quotations, workflow data) are stored as complete arrays in single meta fields, following WooCommerce patterns.

### 2. Entity-Specific Naming
Methods use entity-specific names that clearly indicate what they operate on, following WooCommerce patterns.

### 3. Individual Field Access
Common fields have dedicated getter/setter methods for easy access.

## Data Structure Types

### Simple Meta Keys (Individual Fields)
These are stored as individual meta fields and have direct getter/setter methods:

```php
// Simple fields
'description' => '_arsol_pfw_proposal_description',
'timeline' => '_arsol_pfw_proposal_timeline',
'project_lead' => '_arsol_pfw_proposal_project_lead',
'start_date' => '_arsol_pfw_proposal_start_date',
'delivery_date' => '_arsol_pfw_proposal_delivery_date',
'expiration_date' => '_arsol_pfw_proposal_expiration_date',
'costing_type' => '_arsol_pfw_proposal_costing_type',
'parent_project_id' => '_arsol_pfw_proposal_parent_project_id',
'due_date' => '_arsol_pfw_proposal_due_date',
'customer_notice' => '_arsol_pfw_proposal_customer_notice',
'secondary_status' => '_arsol_pfw_proposal_secondary_status',
```

### Complex Meta Keys (Array-Based)
These are stored as complete arrays in single meta fields:

```php
// Complex data structures
'budget_data' => '_arsol_pfw_proposal_budget_data',
'quotation_data' => '_arsol_pfw_proposal_quotation_data',
'original_request_data' => '_arsol_pfw_proposal_original_request_data',
'woocommerce_data' => '_arsol_pfw_proposal_woocommerce_data',
'workflow_data' => '_arsol_pfw_proposal_workflow_data',
```

## Current API Usage Examples

### Budget Data Structure

```php
// Get complete proposal budget
$budget_data = $proposal->get_proposal_budget();
// Returns: array(
//     'onetime' => array(
//         'amount' => 1000.00,
//         'currency' => 'USD',
//         'details' => 'One-time setup fee'
//     ),
//     'recurring' => array(
//         'amount' => 500.00,
//         'currency' => 'USD',
//         'frequency' => 'monthly',
//         'details' => 'Monthly maintenance'
//     ),
//     'notes' => 'Budget notes here',
//     'type' => 'mixed'
// )

// Set complete proposal budget
$proposal->set_proposal_budget($budget_data);

// Individual field access (recommended)
$onetime_amount = $proposal->get_budget_onetime_amount();
$recurring_amount = $proposal->get_budget_recurring_amount();
$budget_notes = $proposal->get_budget_notes();
$budget_type = $proposal->get_budget_type();

// Set individual fields (recommended)
$proposal->set_budget_onetime_amount(array('amount' => 2000.00, 'currency' => 'USD'));
$proposal->set_budget_notes('Updated budget notes');
$proposal->set_budget_type('mixed');

// Generic field access (for custom fields)
$custom_field = $proposal->get_budget_field('custom_field');
$proposal->set_budget_field('custom_field', $value);
```

### Quotation Data Structure

```php
// Get complete proposal quotation
$quotation_data = $proposal->get_proposal_quotation();
// Returns: array(
//     'line_items' => array(
//         array(
//             'name' => 'Design Services',
//             'quantity' => 1,
//             'price' => 500.00,
//             'total' => 500.00
//         ),
//         array(
//             'name' => 'Development',
//             'quantity' => 10,
//             'price' => 100.00,
//             'total' => 1000.00
//         )
//     ),
//     'currency' => 'USD',
//     'totals' => array(
//         'subtotal' => 1500.00,
//         'tax' => 150.00,
//         'total' => 1650.00
//     ),
//     'notes' => 'Quotation notes here'
// )

// Set complete proposal quotation
$proposal->set_proposal_quotation($quotation_data);

// Individual field access (recommended)
$currency = $proposal->get_quotation_currency();
$totals = $proposal->get_quotation_total();
$notes = $proposal->get_quotation_notes();
$line_items = $proposal->get_quotation_line_items();

// Set individual fields (recommended)
$proposal->set_quotation_currency('EUR');
$proposal->set_quotation_total(array('subtotal' => 1500.00, 'total' => 1650.00));
$proposal->set_quotation_notes('Updated quotation notes');

// Generic field access (for custom fields)
$custom_field = $proposal->get_quotation_field('custom_field');
$proposal->set_quotation_field('custom_field', $value);
```

### Original Request Data Structure

```php
// Get complete original request
$request_data = $proposal->get_original_request();
// Returns: array(
//     'request_id' => 123,
//     'budget' => array(
//         'min' => 1000,
//         'max' => 5000,
//         'currency' => 'USD'
//     ),
//     'start_date' => '2024-01-15',
//     'delivery_date' => '2024-03-15',
//     'title' => 'Original Request Title',
//     'content' => 'Original request content',
//     'attachments' => array(456, 789)
// )

// Set complete original request
$proposal->set_original_request($request_data);

// Individual field access (recommended)
$original_budget = $proposal->get_original_request_budget();
$original_start_date = $proposal->get_original_request_start_date();
$original_title = $proposal->get_original_request_title();

// Set individual fields (recommended)
$proposal->set_original_request_budget(array('min' => 2000, 'max' => 8000));
$proposal->set_original_request_start_date('2024-02-01');
$proposal->set_original_request_title('Updated Request Title');

// Generic field access (for custom fields)
$custom_field = $proposal->get_original_request_field('custom_field');
$proposal->set_original_request_field('custom_field', $value);
```

### WooCommerce Integration Data Structure

```php
// Get complete WooCommerce integration
$woocommerce_data = $proposal->get_woocommerce_integration();
// Returns: array(
//     'order_id' => 456,
//     'subscription_id' => 789,
//     'conversion_date' => '2024-01-20 10:30:00',
//     'payment_method' => 'stripe',
//     'billing_address' => array(
//         'first_name' => 'John',
//         'last_name' => 'Doe',
//         'email' => 'john@example.com'
//     )
// )

// Set complete WooCommerce integration
$proposal->set_woocommerce_integration($woocommerce_data);

// Individual field access (recommended)
$order_id = $proposal->get_woocommerce_order_id();
$subscription_id = $proposal->get_woocommerce_subscription_id();
$conversion_date = $proposal->get_woocommerce_conversion_date();

// Set individual fields (recommended)
$proposal->set_woocommerce_order_id(999);
$proposal->set_woocommerce_subscription_id(888);
$proposal->set_woocommerce_conversion_date('2024-01-25 15:30:00');

// Generic field access (for custom fields)
$custom_field = $proposal->get_woocommerce_field('custom_field');
$proposal->set_woocommerce_field('custom_field', $value);
```

### Workflow History Data Structure

```php
// Get complete workflow history
$workflow_data = $proposal->get_workflow_history();
// Returns: array(
//     'rejection_reason' => 'Budget too high',
//     'conversion_type' => 'manual',
//     'workflow_started' => '2024-01-15 09:00:00',
//     'approval_date' => '2024-01-20 14:30:00',
//     'approver_id' => 123,
//     'notes' => 'Workflow notes here'
// )

// Set complete workflow history
$proposal->set_workflow_history($workflow_data);

// Individual field access (recommended)
$rejection_reason = $proposal->get_workflow_rejection_reason();
$approval_date = $proposal->get_workflow_approval_date();
$approver_id = $proposal->get_workflow_approver_id();

// Set individual fields (recommended)
$proposal->set_workflow_rejection_reason('Timeline too short');
$proposal->set_workflow_approval_date('2024-01-25 16:00:00');
$proposal->set_workflow_approver_id(456);

// Generic field access (for custom fields)
$custom_field = $proposal->get_workflow_field('custom_field');
$proposal->set_workflow_field('custom_field', $value);
```

## WooCommerce Alignment

### WooCommerce Pattern
```php
// WooCommerce uses entity-specific names
$order->get_billing_address()      // Not get_billing_data()
$order->set_billing_address()      // Not set_billing_data()
$order->get_billing_first_name()   // Individual field access
$order->set_billing_first_name()   // Individual field access
```

### Our Implementation
```php
// Entity-specific methods (matches WooCommerce)
$proposal->get_proposal_budget()           // Instead of get_budget_data()
$proposal->set_proposal_budget()           // Instead of set_budget_data()
$proposal->get_proposal_quotation()        // Instead of get_quotation_data()
$proposal->set_proposal_quotation()        // Instead of set_quotation_data()

// Individual field methods (matches WooCommerce)
$proposal->get_budget_onetime_amount();    // Direct field access
$proposal->set_budget_onetime_amount();    // Direct field access
$proposal->get_quotation_currency();       // Direct field access
$proposal->set_quotation_currency();       // Direct field access
```

### Benefits of Entity-Specific Naming

1. **✅ WooCommerce Alignment**: Matches `get_billing_address()` pattern exactly
2. **✅ Clearer Intent**: `get_proposal_budget()` vs `get_budget_data()` is more descriptive
3. **✅ Better API**: More intuitive for developers
4. **✅ Future-Proof**: Easier to add other budget types later (e.g., `get_request_budget()`)

## Migration Strategy

### From Old Individual Meta Fields

**Old Approach:**
```php
// Individual meta fields
$budget = get_post_meta($proposal_id, '_arsol_pfw_proposal_budget', true);
$budget_notes = get_post_meta($proposal_id, '_arsol_pfw_proposal_budget_notes', true);
$quotation = get_post_meta($proposal_id, '_arsol_pfw_proposal_quotation', true);
$quotation_notes = get_post_meta($proposal_id, '_arsol_pfw_proposal_quotation_notes', true);
```

**Current Approach (Recommended):**
```php
// Entity-specific methods
$budget_data = $proposal->get_proposal_budget();
$quotation_data = $proposal->get_proposal_quotation();

// Individual field methods
$budget_notes = $proposal->get_budget_notes();
$quotation_notes = $proposal->get_quotation_notes();
```

### Data Migration Script

```php
// Example migration script for existing proposals
function migrate_proposal_data_to_array_structure() {
    $proposals = get_posts(array(
        'post_type' => 'arsol-pfw-proposal',
        'posts_per_page' => -1,
        'post_status' => 'any'
    ));
    
    foreach ($proposals as $post) {
        $proposal = new Arsol_PFW_Proposal($post->ID);
        
        // Migrate budget data
        $old_budget = get_post_meta($post->ID, '_arsol_pfw_proposal_budget', true);
        $old_budget_notes = get_post_meta($post->ID, '_arsol_pfw_proposal_budget_notes', true);
        
        if ($old_budget || $old_budget_notes) {
            $budget_data = array(
                'legacy_budget' => $old_budget,
                'notes' => $old_budget_notes
            );
            $proposal->set_proposal_budget($budget_data);
        }
        
        // Migrate quotation data
        $old_quotation = get_post_meta($post->ID, '_arsol_pfw_proposal_quotation', true);
        $old_quotation_notes = get_post_meta($post->ID, '_arsol_pfw_proposal_quotation_notes', true);
        
        if ($old_quotation || $old_quotation_notes) {
            $quotation_data = array(
                'legacy_quotation' => $old_quotation,
                'notes' => $old_quotation_notes
            );
            $proposal->set_proposal_quotation($quotation_data);
        }
        
        $proposal->save();
    }
}
```

## Benefits

### 1. Clean API
- Consistent getter/setter methods
- Clear separation between simple and complex data
- Intuitive field access patterns
- Entity-specific naming for clarity

### 2. Performance
- Fewer database queries for complex data
- Atomic updates of related data
- Better caching opportunities

### 3. Extensibility
- Easy to add new fields to complex structures
- No need to create new meta keys for related data
- Flexible data schemas

### 4. WooCommerce Alignment
- Follows WooCommerce patterns for complex data
- Consistent with WordPress/WooCommerce best practices
- Familiar to WooCommerce developers

## Implementation Status

### ✅ Completed
- Proposal entity with array-based data structures
- **Entity-specific naming convention implemented**
- Individual field methods for common operations
- Data store with complex meta key support
- Comprehensive getter/setter methods

### 🔄 Next Steps
- Implement same pattern for Project entity
- Implement same pattern for Request entity
- Update all templates and handlers to use new API
- Create migration scripts for existing data
- Update global functions to use new methods

## Best Practices

### 1. Use Entity-Specific Methods (Recommended)
```php
// ✅ Good - Entity-specific methods
$budget_data = $proposal->get_proposal_budget();
$proposal->set_budget_notes('New notes');
```

### 2. Use Individual Field Methods for Common Fields
```php
// ✅ Good - Individual field methods
$notes = $proposal->get_budget_notes();
$proposal->set_budget_notes('Updated notes');

// ✅ Good - Generic field methods for custom fields
$custom_field = $proposal->get_budget_field('custom_field');
$proposal->set_budget_field('custom_field', $value);
```

### 3. Use Complete Data Methods for Bulk Operations
```php
// ✅ Good for bulk operations
$budget_data = $proposal->get_proposal_budget();
$budget_data['onetime']['amount'] = 2000.00;
$budget_data['recurring']['amount'] = 750.00;
$proposal->set_proposal_budget($budget_data);
```

### 4. Handle Empty Data Gracefully
```php
// ✅ Good
$budget_data = $proposal->get_proposal_budget() ?: array();
$notes = $proposal->get_budget_notes() ?: '';

// ❌ Avoid
$budget_data = $proposal->get_proposal_budget();
if (!$budget_data) {
    $budget_data = array();
}
``` 