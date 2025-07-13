# CRUD Complete Reference

## Overview

The Arsol Projects for Woo plugin implements a comprehensive CRUD (Create, Read, Update, Delete) system using WordPress-native patterns with WooCommerce-inspired entity methods. This system provides a clean, consistent API for managing projects, proposals, and requests.

## Core Principles

### 1. Array-Based Data Storage
All core plugin data is stored as arrays in single meta fields, following WooCommerce patterns:
- **Budget data**: Stored as complete arrays with onetime/recurring structures
- **Quotation data**: Stored as complete arrays with line items and totals
- **Request data**: Stored as complete arrays with budget and requirements
- **Workflow data**: Stored as complete arrays with stage and conversion information

### 2. Entity-Specific Method Names
All getter/setter methods use entity-specific naming for clarity:
- `get_proposal_budget()` / `set_proposal_budget()`
- `get_quotation_line_items()` / `set_quotation_line_items()`
- `get_request_budget()` / `set_request_budget()`

### 3. No Backward Compatibility
The plugin uses a clean slate approach with no legacy meta key support:
- No individual meta key access for core data
- No migration from old data structures
- No deprecated methods or backward compatibility layers

## Entity Classes

### Arsol_PFW_Proposal

#### Core Properties
- `post_author`: Creator (admin) ID
- `customer_id`: Customer ID (stored in meta)
- `costing_type`: 'budget', 'quotation', or 'none'
- `start_date`: Project start date
- `due_date`: Project due date
- `project_lead`: Project lead user ID

#### Budget Methods
```php
// Complete budget data
$proposal->get_proposal_budget() : array
$proposal->set_proposal_budget(array $data) : void

// Individual budget components
$proposal->get_budget_onetime_amount() : array
$proposal->set_budget_onetime_amount(array $data) : void
$proposal->get_budget_recurring_amount() : array
$proposal->set_budget_recurring_amount(array $data) : void
$proposal->get_budget_notes() : string
$proposal->set_budget_notes(string $notes) : void
```

#### Quotation Methods
```php
// Complete quotation data
$proposal->get_proposal_quotation() : array
$proposal->set_proposal_quotation(array $data) : void

// Individual quotation components
$proposal->get_quotation_line_items() : array
$proposal->set_quotation_line_items(array $items) : void
$proposal->get_quotation_onetime_total() : float
$proposal->set_quotation_onetime_total(float $total) : void
$proposal->get_quotation_recurring_totals() : array
$proposal->set_quotation_recurring_totals(array $totals) : void
$proposal->get_quotation_currency() : string
$proposal->set_quotation_currency(string $currency) : void
$proposal->get_quotation_currency_symbol() : string
$proposal->set_quotation_currency_symbol(string $symbol) : void
$proposal->get_quotation_notes() : string
$proposal->set_quotation_notes(string $notes) : void
```

#### Request Methods
```php
// Complete request data
$proposal->get_original_request() : array
$proposal->set_original_request(array $data) : void

// Individual request components
$proposal->get_request_details() : string
$proposal->set_request_details(string $details) : void
$proposal->get_request_title() : string
$proposal->set_request_title(string $title) : void
$proposal->get_request_date() : string
$proposal->set_request_date(string $date) : void
$proposal->get_request_budget() : array
$proposal->set_request_budget(array $budget) : void
$proposal->get_request_start_date() : string
$proposal->set_request_start_date(string $date) : void
$proposal->get_request_due_date() : string
$proposal->set_request_due_date(string $date) : void
$proposal->get_request_attachments() : array
$proposal->set_request_attachments(array $attachments) : void
```

#### WooCommerce Integration Methods
```php
// Complete WooCommerce data
$proposal->get_woocommerce_integration() : array
$proposal->set_woocommerce_integration(array $data) : void

// Individual WooCommerce components
$proposal->get_woocommerce_order_id() : int
$proposal->set_woocommerce_order_id(int $order_id) : void
$proposal->get_woocommerce_subscription_id() : int
$proposal->set_woocommerce_subscription_id(int $subscription_id) : void
$proposal->get_woocommerce_created_via() : string
$proposal->set_woocommerce_created_via(string $method) : void
```

#### Workflow Methods
```php
// Complete workflow data
$proposal->get_workflow_data() : array
$proposal->set_workflow_data(array $data) : void

// Individual workflow components
$proposal->get_workflow_status() : string
$proposal->set_workflow_status(string $status) : void
$proposal->get_workflow_type() : string
$proposal->set_workflow_type(string $type) : void
$proposal->get_conversion_step() : string
$proposal->set_conversion_step(string $step) : void
$proposal->get_conversion_created_ids() : array
$proposal->set_conversion_created_ids(array $ids) : void
$proposal->get_conversion_rollback_reason() : string
$proposal->set_conversion_rollback_reason(string $reason) : void
```

### Arsol_PFW_Project

#### Core Properties
- `post_author`: Creator (admin) ID
- `customer_id`: Customer ID (stored in meta)
- `start_date`: Project start date
- `due_date`: Project due date
- `project_lead`: Project lead user ID

#### Budget Methods
```php
// Complete budget data
$project->get_project_budget() : array
$project->set_project_budget(array $data) : void

// Individual budget components
$project->get_budget_onetime_amount() : array
$project->set_budget_onetime_amount(array $data) : void
$project->get_budget_recurring_amount() : array
$project->set_budget_recurring_amount(array $data) : void
$project->get_budget_notes() : string
$project->set_budget_notes(string $notes) : void
```

#### Quotation Methods
```php
// Complete quotation data
$project->get_project_quotation() : array
$project->set_project_quotation(array $data) : void

// Individual quotation components
$project->get_quotation_line_items() : array
$project->set_quotation_line_items(array $items) : void
$project->get_quotation_onetime_total() : float
$project->set_quotation_onetime_total(float $total) : void
$project->get_quotation_recurring_totals() : array
$project->set_quotation_recurring_totals(array $totals) : void
$project->get_quotation_currency() : string
$project->set_quotation_currency(string $currency) : void
$project->get_quotation_currency_symbol() : string
$project->set_quotation_currency_symbol(string $symbol) : void
$project->get_quotation_notes() : string
$project->set_quotation_notes(string $notes) : void
```

### Arsol_PFW_Request

#### Core Properties
- `post_author`: Creator (admin) ID
- `customer_id`: Customer ID (stored in meta)
- `costing_type`: 'budget', 'quotation', or 'none'
- `start_date`: Requested start date
- `due_date`: Requested due date
- `project_lead`: Project lead user ID

#### Budget Methods
```php
// Complete budget data
$request->get_request_budget() : array
$request->set_request_budget(array $data) : void

// Individual budget components
$request->get_budget_onetime_amount() : array
$request->set_budget_onetime_amount(array $data) : void
$request->get_budget_recurring_amount() : array
$request->set_budget_recurring_amount(array $data) : void
$request->get_budget_notes() : string
$request->set_budget_notes(string $notes) : void
```

#### Quotation Methods
```php
// Complete quotation data
$request->get_request_quotation() : array
$request->set_request_quotation(array $data) : void

// Individual quotation components
$request->get_quotation_line_items() : array
$request->set_quotation_line_items(array $items) : void
$request->get_quotation_onetime_total() : float
$request->set_quotation_onetime_total(float $total) : void
$request->get_quotation_recurring_totals() : array
$request->set_quotation_recurring_totals(array $totals) : void
$request->get_quotation_currency() : string
$request->set_quotation_currency(string $currency) : void
$request->get_quotation_currency_symbol() : string
$request->set_quotation_currency_symbol(string $symbol) : void
$request->get_quotation_notes() : string
$request->set_quotation_notes(string $notes) : void
```

## Data Store Classes

### Arsol_PFW_Data_Store_Proposal

#### Meta Key Mappings
```php
protected $meta_keys = array(
    // Core data
    '_arsol_pfw_customer_id' => 'customer_id',
    '_arsol_pfw_proposal_costing_type' => 'costing_type',
    '_arsol_pfw_proposal_start_date' => 'start_date',
    '_arsol_pfw_proposal_due_date' => 'due_date',
    '_arsol_pfw_proposal_project_lead' => 'project_lead',
    '_arsol_pfw_proposal_expiration_date' => 'expiration_date',
    '_arsol_pfw_proposal_customer_notice' => 'customer_notice',
    
    // Array-based data structures
    '_arsol_pfw_proposal_budget' => 'proposal_budget',
    '_arsol_pfw_proposal_quotation' => 'proposal_quotation',
    '_arsol_pfw_original_request' => 'original_request',
    '_arsol_pfw_woocommerce_integration' => 'woocommerce_integration',
    '_arsol_pfw_workflow_data' => 'workflow_data',
);
```

### Arsol_PFW_Data_Store_Project

#### Meta Key Mappings
```php
protected $meta_keys = array(
    // Core data
    '_arsol_pfw_customer_id' => 'customer_id',
    '_arsol_pfw_project_start_date' => 'start_date',
    '_arsol_pfw_project_due_date' => 'due_date',
    '_arsol_pfw_project_lead' => 'project_lead',
    '_arsol_pfw_project_customer_notice' => 'customer_notice',
    
    // Array-based data structures
    '_arsol_pfw_project_budget' => 'project_budget',
    '_arsol_pfw_project_quotation' => 'project_quotation',
    '_arsol_pfw_woocommerce_integration' => 'woocommerce_integration',
    '_arsol_pfw_workflow_data' => 'workflow_data',
);
```

### Arsol_PFW_Data_Store_Request

#### Meta Key Mappings
```php
protected $meta_keys = array(
    // Core data
    '_arsol_pfw_customer_id' => 'customer_id',
    '_arsol_pfw_request_costing_type' => 'costing_type',
    '_arsol_pfw_request_start_date' => 'start_date',
    '_arsol_pfw_request_due_date' => 'due_date',
    '_arsol_pfw_request_project_lead' => 'project_lead',
    '_arsol_pfw_request_customer_notice' => 'customer_notice',
    
    // Array-based data structures
    '_arsol_pfw_request_budget' => 'request_budget',
    '_arsol_pfw_request_quotation' => 'request_quotation',
    '_arsol_pfw_woocommerce_integration' => 'woocommerce_integration',
    '_arsol_pfw_workflow_data' => 'workflow_data',
);
```

## Usage Examples

### Creating a Proposal with Budget
```php
$proposal = new Arsol_PFW_Proposal();
$proposal->set_title('Website Redesign Proposal');
$proposal->set_customer_id(123);
$proposal->set_costing_type('budget');

// Set budget data as complete array
$budget_data = array(
    'onetime' => array(
        'amount' => 5000.00,
        'currency' => 'USD',
        'details' => 'Complete website redesign including responsive design'
    ),
    'recurring' => array(
        'amount' => 500.00,
        'currency' => 'USD',
        'details' => 'Monthly maintenance and updates',
        'billing_interval' => '1',
        'billing_period' => 'month',
        'start_date' => '2024-01-01'
    ),
    'notes' => 'All prices include tax and delivery',
    'type' => 'mixed'
);

$proposal->set_proposal_budget($budget_data);
$proposal->save();
```

### Creating a Proposal with Quotation
```php
$proposal = new Arsol_PFW_Proposal();
$proposal->set_title('E-commerce Platform Proposal');
$proposal->set_customer_id(456);
$proposal->set_costing_type('quotation');

// Set quotation data as complete array
$quotation_data = array(
    'line_items' => array(
        'products' => array(
            array(
                'product_id' => 789,
                'description' => 'WooCommerce Premium',
                'quantity' => 1,
                'regular_price' => 299.00,
                'sale_price' => 249.00
            )
        ),
        'one_time_fees' => array(
            array(
                'description' => 'Custom theme development',
                'amount' => 2000.00,
                'tax_class' => 'standard'
            )
        ),
        'recurring_fees' => array(
            array(
                'description' => 'Monthly hosting and support',
                'amount' => 99.00,
                'interval' => '1',
                'period' => 'month'
            )
        )
    ),
    'onetime_total' => 2299.00,
    'recurring_totals' => array(
        'monthly' => 99.00,
        'yearly' => 1188.00
    ),
    'currency' => 'USD',
    'currency_symbol' => '$',
    'notes' => 'All prices include setup and configuration'
);

$proposal->set_proposal_quotation($quotation_data);
$proposal->save();
```

### Reading Proposal Data
```php
$proposal = new Arsol_PFW_Proposal(123);

// Get complete budget data
$budget = $proposal->get_proposal_budget();
$onetime_amount = $budget['onetime']['amount'] ?? 0;
$recurring_amount = $budget['recurring']['amount'] ?? 0;

// Get individual budget components
$onetime_data = $proposal->get_budget_onetime_amount();
$recurring_data = $proposal->get_budget_recurring_amount();

// Get complete quotation data
$quotation = $proposal->get_proposal_quotation();
$line_items = $quotation['line_items'] ?? array();
$onetime_total = $quotation['onetime_total'] ?? 0;

// Get individual quotation components
$products = $proposal->get_quotation_line_items()['products'] ?? array();
$onetime_total = $proposal->get_quotation_onetime_total();
```

### Updating Individual Fields
```php
$proposal = new Arsol_PFW_Proposal(123);

// Update individual budget field
$onetime_data = $proposal->get_budget_onetime_amount();
$onetime_data['amount'] = 6000.00;
$proposal->set_budget_onetime_amount($onetime_data);

// Update individual quotation field
$line_items = $proposal->get_quotation_line_items();
$line_items['one_time_fees'][0]['amount'] = 2500.00;
$proposal->set_quotation_line_items($line_items);

$proposal->save();
```

## Best Practices

### 1. Always Use Entity Methods
```php
// ✅ Correct - Use entity methods
$proposal = new Arsol_PFW_Proposal(123);
$budget = $proposal->get_proposal_budget();

// ❌ Incorrect - Direct meta access for core data
$budget = get_post_meta(123, '_arsol_pfw_proposal_budget_onetime_amount', true);
```

### 2. Use Complete Array Operations
```php
// ✅ Correct - Set complete data structure
$proposal->set_proposal_budget($complete_budget_array);

// ✅ Correct - Update individual fields
$onetime_data = $proposal->get_budget_onetime_amount();
$onetime_data['amount'] = 5000.00;
$proposal->set_budget_onetime_amount($onetime_data);
```

### 3. Handle Missing Data Gracefully
```php
// ✅ Correct - Use null coalescing
$amount = $proposal->get_budget_onetime_amount()['amount'] ?? 0;
$details = $proposal->get_budget_onetime_amount()['details'] ?? '';

// ✅ Correct - Check for empty arrays
$line_items = $proposal->get_quotation_line_items() ?: array();
$products = $line_items['products'] ?? array();
```

### 4. Save After Changes
```php
// ✅ Correct - Always save after changes
$proposal->set_proposal_budget($new_budget);
$proposal->save();

// ❌ Incorrect - Changes not persisted
$proposal->set_proposal_budget($new_budget);
// Missing save() call
```

## Migration Notes

### No Backward Compatibility
The plugin implements a clean slate approach:
- No support for old individual meta keys
- No migration from legacy data structures
- No deprecated methods or backward compatibility layers

### Custom Meta Fields
Developers can still use `get_post_meta()` for custom fields:
```php
// ✅ Correct - Custom meta fields
$custom_field = get_post_meta($proposal_id, '_my_custom_field', true);
update_post_meta($proposal_id, '_my_custom_field', $value);

// ❌ Incorrect - Core plugin data
$budget = get_post_meta($proposal_id, '_arsol_pfw_proposal_budget_onetime_amount', true);
```

## Integration with WooCommerce

The plugin follows WooCommerce patterns for data storage and retrieval:
- Array-based line items (similar to WC orders)
- Array-based addresses (similar to WC customer addresses)
- Entity-specific method names (similar to WC products, orders)
- Single meta field storage for complex data structures

This ensures consistency with WooCommerce development patterns and makes the plugin familiar to WooCommerce developers. 