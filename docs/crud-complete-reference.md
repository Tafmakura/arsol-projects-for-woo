# CRUD Complete Reference

## Overview

The Arsol Projects for Woo plugin implements a comprehensive CRUD (Create, Read, Update, Delete) system that follows WordPress and WooCommerce best practices. The system provides both entity methods (primary approach) and individual meta key access (WooCommerce pattern) for maximum flexibility and compatibility.

## Data Access Patterns

### 1. Entity Methods (Primary Approach)
Entity methods provide a clean, object-oriented interface for accessing and modifying data. These methods handle data validation, formatting, and ensure data consistency.

**Examples:**
```php
// Get complete budget data as array
$budget_data = $proposal->get_proposal_budget();

// Set complete budget data as array
$proposal->set_proposal_budget($budget_data);

// Get individual fields from array data
$onetime_amount = $proposal->get_budget_onetime_amount();
$recurring_amount = $proposal->get_budget_recurring_amount();
$budget_notes = $proposal->get_budget_notes();
```

### 2. Individual Meta Key Access (WooCommerce Pattern)
Following WooCommerce's pattern, the plugin also supports direct meta key access for individual fields. This provides flexibility for developers and maintains backward compatibility.

**Examples:**
```php
// Get individual budget meta field
$onetime_amount = $proposal->get_budget_meta('onetime_amount');
$recurring_amount = $proposal->get_budget_meta('recurring_amount');

// Set individual budget meta field
$proposal->set_budget_meta('onetime_amount', $amount);
$proposal->set_budget_meta('recurring_amount', $recurring_data);

// Direct WordPress meta access for custom fields
$custom_field = get_post_meta($proposal->get_id(), '_custom_field', true);
```

### 3. Generic Meta Access
For custom meta fields not managed by the plugin, developers can use WordPress's native `get_post_meta()` and `update_post_meta()` functions.

**Examples:**
```php
// Get custom meta field
$custom_data = get_post_meta($proposal->get_id(), '_my_custom_field', true);

// Set custom meta field
update_post_meta($proposal->get_id(), '_my_custom_field', $value);
```

## Proposal Entity Methods

### Budget Data Methods

#### Complete Budget Data
```php
// Get complete budget structure
$budget_data = $proposal->get_proposal_budget();
// Returns: array with 'onetime', 'recurring', 'notes', 'type' keys

// Set complete budget structure
$proposal->set_proposal_budget($budget_data);
```

#### Individual Budget Fields
```php
// One-time budget
$onetime_data = $proposal->get_budget_onetime_amount();
$proposal->set_budget_onetime_amount($onetime_data);

// Recurring budget
$recurring_data = $proposal->get_budget_recurring_amount();
$proposal->set_budget_recurring_amount($recurring_data);

// Budget notes
$notes = $proposal->get_budget_notes();
$proposal->set_budget_notes($notes);

// Budget type
$type = $proposal->get_budget_type();
$proposal->set_budget_type($type);
```

#### Individual Meta Access (WooCommerce Pattern)
```php
// Get individual budget meta fields
$amount = $proposal->get_budget_meta('onetime_amount');
$details = $proposal->get_budget_meta('onetime_details');
$currency = $proposal->get_budget_meta('currency');

// Set individual budget meta fields
$proposal->set_budget_meta('onetime_amount', $amount);
$proposal->set_budget_meta('onetime_details', $details);
$proposal->set_budget_meta('currency', $currency);
```

### Quotation Data Methods

#### Complete Quotation Data
```php
// Get complete quotation structure
$quotation_data = $proposal->get_proposal_quotation();
// Returns: array with 'line_items', 'totals', 'currency', 'notes' keys

// Set complete quotation structure
$proposal->set_proposal_quotation($quotation_data);
```

#### Individual Quotation Fields
```php
// Line items
$line_items = $proposal->get_quotation_line_items();
$proposal->set_quotation_line_items($line_items);

// Currency
$currency = $proposal->get_quotation_currency();
$proposal->set_quotation_currency($currency);

// Total amounts
$totals = $proposal->get_quotation_total();
$proposal->set_quotation_total($totals);

// Notes
$notes = $proposal->get_quotation_notes();
$proposal->set_quotation_notes($notes);
```

#### Individual Meta Access (WooCommerce Pattern)
```php
// Get individual quotation meta fields
$line_items = $proposal->get_quotation_meta('line_items');
$onetime_total = $proposal->get_quotation_meta('onetime_total');
$currency = $proposal->get_quotation_meta('currency');

// Set individual quotation meta fields
$proposal->set_quotation_meta('line_items', $line_items);
$proposal->set_quotation_meta('onetime_total', $total);
$proposal->set_quotation_meta('currency', $currency);
```

### Original Request Data Methods

#### Complete Request Data
```php
// Get complete original request structure
$request_data = $proposal->get_original_request();
// Returns: array with 'title', 'budget', 'start_date', 'details' keys

// Set complete original request structure
$proposal->set_original_request($request_data);
```

#### Individual Request Fields
```php
// Request title
$title = $proposal->get_original_request_title();
$proposal->set_original_request_title($title);

// Request budget
$budget = $proposal->get_original_request_budget();
$proposal->set_original_request_budget($budget);

// Start date
$start_date = $proposal->get_original_request_start_date();
$proposal->set_original_request_start_date($start_date);
```

#### Individual Meta Access (WooCommerce Pattern)
```php
// Get individual request meta fields
$title = $proposal->get_request_meta('title');
$budget = $proposal->get_request_meta('budget');
$details = $proposal->get_request_meta('details');

// Set individual request meta fields
$proposal->set_request_meta('title', $title);
$proposal->set_request_meta('budget', $budget);
$proposal->set_request_meta('details', $details);
```

### WooCommerce Integration Methods

#### Complete WooCommerce Data
```php
// Get complete WooCommerce integration structure
$woocommerce_data = $proposal->get_woocommerce_integration();
// Returns: array with 'order_id', 'subscription_id', 'conversion_date' keys

// Set complete WooCommerce integration structure
$proposal->set_woocommerce_integration($woocommerce_data);
```

#### Individual WooCommerce Fields
```php
// Order ID
$order_id = $proposal->get_woocommerce_order_id();
$proposal->set_woocommerce_order_id($order_id);

// Subscription ID
$subscription_id = $proposal->get_woocommerce_subscription_id();
$proposal->set_woocommerce_subscription_id($subscription_id);

// Conversion date
$conversion_date = $proposal->get_woocommerce_conversion_date();
$proposal->set_woocommerce_conversion_date($conversion_date);
```

#### Individual Meta Access (WooCommerce Pattern)
```php
// Get individual WooCommerce meta fields
$order_id = $proposal->get_woocommerce_meta('order_id');
$subscription_id = $proposal->get_woocommerce_meta('subscription_id');
$created_via = $proposal->get_woocommerce_meta('created_via');

// Set individual WooCommerce meta fields
$proposal->set_woocommerce_meta('order_id', $order_id);
$proposal->set_woocommerce_meta('subscription_id', $subscription_id);
$proposal->set_woocommerce_meta('created_via', $created_via);
```

### Workflow History Methods

#### Complete Workflow Data
```php
// Get complete workflow history structure
$workflow_data = $proposal->get_workflow_history();
// Returns: array with 'status', 'type', 'step', 'approval_date' keys

// Set complete workflow history structure
$proposal->set_workflow_history($workflow_data);
```

#### Individual Workflow Fields
```php
// Rejection reason
$reason = $proposal->get_workflow_rejection_reason();
$proposal->set_workflow_rejection_reason($reason);

// Approval date
$approval_date = $proposal->get_workflow_approval_date();
$proposal->set_workflow_approval_date($approval_date);

// Approver ID
$approver_id = $proposal->get_workflow_approver_id();
$proposal->set_workflow_approver_id($approver_id);
```

#### Individual Meta Access (WooCommerce Pattern)
```php
// Get individual workflow meta fields
$status = $proposal->get_workflow_meta('status');
$type = $proposal->get_workflow_meta('type');
$step = $proposal->get_workflow_meta('step');

// Set individual workflow meta fields
$proposal->set_workflow_meta('status', $status);
$proposal->set_workflow_meta('type', $type);
$proposal->set_workflow_meta('step', $step);
```

## Data Structure Examples

### Budget Data Structure
```php
$budget_data = array(
    'onetime' => array(
        'amount' => '1000.00',
        'currency' => 'USD',
        'details' => 'Initial setup and configuration'
    ),
    'recurring' => array(
        'amount' => '500.00',
        'currency' => 'USD',
        'details' => 'Monthly maintenance and support',
        'billing_interval' => '1',
        'billing_period' => 'month',
        'start_date' => '2024-01-01'
    ),
    'notes' => 'Budget includes all necessary components',
    'type' => 'mixed'
);
```

### Quotation Data Structure
```php
$quotation_data = array(
    'line_items' => array(
        array(
            'name' => 'Web Development',
            'quantity' => 1,
            'unit_price' => '5000.00',
            'total' => '5000.00'
        ),
        array(
            'name' => 'Design Services',
            'quantity' => 1,
            'unit_price' => '2000.00',
            'total' => '2000.00'
        )
    ),
    'totals' => array(
        'subtotal' => '7000.00',
        'tax' => '700.00',
        'total' => '7700.00'
    ),
    'currency' => 'USD',
    'notes' => 'Quotation valid for 30 days'
);
```

## Best Practices

### 1. Use Entity Methods for Core Data
For core plugin data, prefer entity methods as they provide:
- Data validation and formatting
- Consistent data structure
- Better performance through caching
- Clear API documentation

### 2. Use Individual Meta Access for Flexibility
Use individual meta access when:
- You need to access specific fields without loading entire structures
- Working with legacy code that expects individual meta keys
- Implementing custom functionality that requires direct meta access

### 3. Use Generic Meta Access for Custom Data
For custom meta fields not managed by the plugin:
- Use `get_post_meta()` and `update_post_meta()`
- Follow WordPress naming conventions
- Implement proper data sanitization

### 4. Data Consistency
When using individual meta access, the plugin automatically updates the cached data structures to maintain consistency between both access patterns.

## Migration Guide

### From Legacy Individual Meta Keys
If you have code using legacy individual meta keys, you can:

1. **Keep using legacy methods** (backward compatibility maintained)
2. **Migrate to entity methods** (recommended for new code)
3. **Use individual meta access** (WooCommerce pattern)

### Example Migration
```php
// Old way (still works)
$onetime_amount = get_post_meta($proposal_id, '_arsol_pfw_proposal_budget_onetime_amount', true);

// New way - Entity method (recommended)
$onetime_data = $proposal->get_budget_onetime_amount();

// New way - Individual meta access (WooCommerce pattern)
$onetime_amount = $proposal->get_budget_meta('onetime_amount');
```

## Performance Considerations

### Entity Methods
- Cache data in memory for the duration of the object lifecycle
- Provide better performance for repeated access to the same data
- Automatically handle data validation and formatting

### Individual Meta Access
- Direct database access for each call
- Better for one-time access to specific fields
- No memory overhead for unused data

### Generic Meta Access
- Direct WordPress meta access
- No plugin overhead
- Suitable for custom fields and extensions

## Error Handling

All methods include proper error handling:
- Invalid field names return `null` or empty arrays
- Database errors are logged
- Data validation prevents invalid data storage

## Conclusion

The plugin's CRUD system provides maximum flexibility while maintaining data consistency and following WordPress and WooCommerce best practices. Choose the access pattern that best fits your specific use case, with entity methods being the recommended approach for core plugin data. 