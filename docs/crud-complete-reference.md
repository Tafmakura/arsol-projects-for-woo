# CRUD Complete Reference

## Overview

This document provides a complete reference for the CRUD (Create, Read, Update, Delete) operations in Arsol Projects for WooCommerce. The system follows WooCommerce patterns with array-based data structures and entity-specific naming conventions.

## Current Implementation Status

### ✅ **Implemented (Current)**
- Array-based complex data structures
- **Entity-specific naming convention** (WooCommerce-aligned)
- Individual field methods for common operations
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

## Entity Classes

### Arsol_PFW_Proposal

#### Constructor
```php
$proposal = new Arsol_PFW_Proposal($proposal_id);
$proposal = new Arsol_PFW_Proposal($post_object);
$proposal = new Arsol_PFW_Proposal(); // Empty instance
```

#### Creation
```php
$proposal = Arsol_PFW_Proposal::create(array(
    'post_title' => 'Proposal Title',
    'post_content' => 'Proposal content',
    'post_author' => 123, // Creator ID
    'meta_input' => array(
        '_arsol_pfw_proposal_customer_id' => 456,
        '_arsol_pfw_proposal_description' => 'Description',
        '_arsol_pfw_proposal_timeline' => '2 weeks',
        '_arsol_pfw_proposal_project_lead' => 789,
        '_arsol_pfw_proposal_start_date' => '2024-01-15',
        '_arsol_pfw_proposal_delivery_date' => '2024-01-30',
        '_arsol_pfw_proposal_expiration_date' => '2024-02-15',
        '_arsol_pfw_proposal_costing_type' => 'fixed',
        '_arsol_pfw_proposal_parent_project_id' => 101,
        '_arsol_pfw_proposal_due_date' => '2024-01-25',
        '_arsol_pfw_proposal_customer_notice' => 'Customer notice',
        '_arsol_pfw_proposal_secondary_status' => 'pending_review',
        '_arsol_pfw_proposal_budget_data' => array(
            'onetime' => array(
                'amount' => 1000.00,
                'currency' => 'USD',
                'details' => 'One-time setup fee'
            ),
            'recurring' => array(
                'amount' => 500.00,
                'currency' => 'USD',
                'frequency' => 'monthly',
                'details' => 'Monthly maintenance'
            ),
            'notes' => 'Budget notes here',
            'type' => 'mixed'
        ),
        '_arsol_pfw_proposal_quotation_data' => array(
            'line_items' => array(
                array(
                    'name' => 'Design Services',
                    'quantity' => 1,
                    'price' => 500.00,
                    'total' => 500.00
                )
            ),
            'currency' => 'USD',
            'totals' => array(
                'subtotal' => 500.00,
                'tax' => 50.00,
                'total' => 550.00
            ),
            'notes' => 'Quotation notes here'
        ),
        '_arsol_pfw_proposal_original_request_data' => array(
            'request_id' => 123,
            'budget' => array(
                'min' => 1000,
                'max' => 5000,
                'currency' => 'USD'
            ),
            'start_date' => '2024-01-15',
            'delivery_date' => '2024-03-15',
            'title' => 'Original Request Title',
            'content' => 'Original request content',
            'attachments' => array(456, 789)
        ),
        '_arsol_pfw_proposal_woocommerce_data' => array(
            'order_id' => 456,
            'subscription_id' => 789,
            'conversion_date' => '2024-01-20 10:30:00',
            'payment_method' => 'stripe',
            'billing_address' => array(
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com'
            )
        ),
        '_arsol_pfw_proposal_workflow_data' => array(
            'rejection_reason' => 'Budget too high',
            'conversion_type' => 'manual',
            'workflow_started' => '2024-01-15 09:00:00',
            'approval_date' => '2024-01-20 14:30:00',
            'approver_id' => 123,
            'notes' => 'Workflow notes here'
        )
    ),
    'tax_input' => array(
        'arsol-pfw-proposal-stage' => array('draft')
    )
));
```

#### Reading Data

##### Simple Fields (Individual Meta)
```php
// Direct getter methods
$description = $proposal->get_description();
$timeline = $proposal->get_timeline();
$project_lead = $proposal->get_project_lead();
$start_date = $proposal->get_start_date();
$delivery_date = $proposal->get_delivery_date();
$expiration_date = $proposal->get_expiration_date();
$costing_type = $proposal->get_costing_type();
$parent_project_id = $proposal->get_parent_project_id();
$due_date = $proposal->get_due_date();
$customer_notice = $proposal->get_customer_notice();
$secondary_status = $proposal->get_secondary_status();
```

##### Complex Fields (Array-Based)

**Budget Data (Entity-Specific Methods - Recommended):**
```php
// Get complete proposal budget
$budget_data = $proposal->get_proposal_budget();

// Individual field access (recommended)
$onetime_amount = $proposal->get_budget_onetime_amount();
$recurring_amount = $proposal->get_budget_recurring_amount();
$budget_notes = $proposal->get_budget_notes();
$budget_type = $proposal->get_budget_type();

// Generic field access (for custom fields)
$custom_field = $proposal->get_budget_field('custom_field');
```

**Quotation Data (Entity-Specific Methods - Recommended):**
```php
// Get complete proposal quotation
$quotation_data = $proposal->get_proposal_quotation();

// Individual field access (recommended)
$currency = $proposal->get_quotation_currency();
$totals = $proposal->get_quotation_total();
$notes = $proposal->get_quotation_notes();
$line_items = $proposal->get_quotation_line_items();

// Generic field access (for custom fields)
$custom_field = $proposal->get_quotation_field('custom_field');
```

**Original Request Data (Entity-Specific Methods - Recommended):**
```php
// Get complete original request
$request_data = $proposal->get_original_request();

// Individual field access (recommended)
$original_budget = $proposal->get_original_request_budget();
$original_start_date = $proposal->get_original_request_start_date();
$original_title = $proposal->get_original_request_title();

// Generic field access (for custom fields)
$custom_field = $proposal->get_original_request_field('custom_field');
```

**WooCommerce Integration Data (Entity-Specific Methods - Recommended):**
```php
// Get complete WooCommerce integration
$woocommerce_data = $proposal->get_woocommerce_integration();

// Individual field access (recommended)
$order_id = $proposal->get_woocommerce_order_id();
$subscription_id = $proposal->get_woocommerce_subscription_id();
$conversion_date = $proposal->get_woocommerce_conversion_date();

// Generic field access (for custom fields)
$custom_field = $proposal->get_woocommerce_field('custom_field');
```

**Workflow History Data (Entity-Specific Methods - Recommended):**
```php
// Get complete workflow history
$workflow_data = $proposal->get_workflow_history();

// Individual field access (recommended)
$rejection_reason = $proposal->get_workflow_rejection_reason();
$approval_date = $proposal->get_workflow_approval_date();
$approver_id = $proposal->get_workflow_approver_id();

// Generic field access (for custom fields)
$custom_field = $proposal->get_workflow_field('custom_field');
```

#### Updating Data

##### Simple Fields (Individual Meta)
```php
// Direct setter methods
$proposal->set_description('Updated description');
$proposal->set_timeline('3 weeks');
$proposal->set_project_lead(789);
$proposal->set_start_date('2024-02-01');
$proposal->set_delivery_date('2024-02-15');
$proposal->set_expiration_date('2024-03-01');
$proposal->set_costing_type('hourly');
$proposal->set_parent_project_id(202);
$proposal->set_due_date('2024-02-10');
$proposal->set_customer_notice('Updated customer notice');
$proposal->set_secondary_status('approved');
```

##### Complex Fields (Array-Based)

**Budget Data (Entity-Specific Methods - Recommended):**
```php
// Set complete proposal budget
$proposal->set_proposal_budget($budget_data);

// Individual field access (recommended)
$proposal->set_budget_onetime_amount(array('amount' => 2000.00, 'currency' => 'USD'));
$proposal->set_budget_recurring_amount(array('amount' => 750.00, 'currency' => 'USD'));
$proposal->set_budget_notes('Updated budget notes');
$proposal->set_budget_type('mixed');

// Generic field access (for custom fields)
$proposal->set_budget_field('custom_field', $value);
```

**Quotation Data (Entity-Specific Methods - Recommended):**
```php
// Set complete proposal quotation
$proposal->set_proposal_quotation($quotation_data);

// Individual field access (recommended)
$proposal->set_quotation_currency('EUR');
$proposal->set_quotation_total(array('subtotal' => 1500.00, 'total' => 1650.00));
$proposal->set_quotation_notes('Updated quotation notes');
$proposal->set_quotation_line_items($line_items);

// Generic field access (for custom fields)
$proposal->set_quotation_field('custom_field', $value);
```

**Original Request Data (Entity-Specific Methods - Recommended):**
```php
// Set complete original request
$proposal->set_original_request($request_data);

// Individual field access (recommended)
$proposal->set_original_request_budget(array('min' => 2000, 'max' => 8000));
$proposal->set_original_request_start_date('2024-02-01');
$proposal->set_original_request_title('Updated Request Title');

// Generic field access (for custom fields)
$proposal->set_original_request_field('custom_field', $value);
```

**WooCommerce Integration Data (Entity-Specific Methods - Recommended):**
```php
// Set complete WooCommerce integration
$proposal->set_woocommerce_integration($woocommerce_data);

// Individual field access (recommended)
$proposal->set_woocommerce_order_id(999);
$proposal->set_woocommerce_subscription_id(888);
$proposal->set_woocommerce_conversion_date('2024-01-25 15:30:00');

// Generic field access (for custom fields)
$proposal->set_woocommerce_field('custom_field', $value);
```

**Workflow History Data (Entity-Specific Methods - Recommended):**
```php
// Set complete workflow history
$proposal->set_workflow_history($workflow_data);

// Individual field access (recommended)
$proposal->set_workflow_rejection_reason('Timeline too short');
$proposal->set_workflow_approval_date('2024-01-25 16:00:00');
$proposal->set_workflow_approver_id(456);

// Generic field access (for custom fields)
$proposal->set_workflow_field('custom_field', $value);
```

#### Saving Changes
```php
// Save all changes
$result = $proposal->save();

if (is_wp_error($result)) {
    // Handle error
    $error_message = $result->get_error_message();
} else {
    // Success
    $proposal_id = $proposal->get_id();
}
```

#### Deleting
```php
// Delete proposal
$result = $proposal->delete();

if ($result) {
    // Successfully deleted
} else {
    // Failed to delete
}
```

#### Generic Meta Access
```php
// Get any meta value
$value = $proposal->get_meta('_custom_meta_key');

// Set any meta value
$proposal->set_meta('_custom_meta_key', 'custom_value');

// Delete meta value
$proposal->delete_meta('_custom_meta_key');
```

## Data Store Pattern

### Proposal Data Store
```php
class Proposal_Data_Store {
    // Meta key mappings
    private $meta_keys = array(
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
        
        // Complex data structures
        'budget_data' => '_arsol_pfw_proposal_budget_data',
        'quotation_data' => '_arsol_pfw_proposal_quotation_data',
        'original_request_data' => '_arsol_pfw_proposal_original_request_data',
        'woocommerce_data' => '_arsol_pfw_proposal_woocommerce_data',
        'workflow_data' => '_arsol_pfw_proposal_workflow_data',
    );
}
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