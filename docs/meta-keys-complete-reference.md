# Arsol Projects for Woo - Meta Keys Complete Reference

**Version**: 1.0  
**Date**: 2024  
**Purpose**: Complete documentation of all meta keys used in the Arsol Projects for Woo plugin

---

## Table of Contents

1. [Overview](#overview)
2. [Meta Key Standardization](#meta-key-standardization)
3. [Project Request Meta Keys](#project-request-meta-keys)
4. [Project Proposal Meta Keys](#project-proposal-meta-keys)
5. [Active Project Meta Keys](#active-project-meta-keys)
6. [WooCommerce Integration Meta Keys](#woocommerce-integration-meta-keys)
7. [Legacy Meta Keys](#legacy-meta-keys)
8. [Migration Reference](#migration-reference)

---

## Overview

The Arsol Projects for Woo plugin uses a comprehensive meta key system to store custom data across three main custom post types (CPTs) and their integration with WooCommerce orders and subscriptions.

### Naming Convention

All meta keys follow the standardized prefix pattern:
```
_arsol_pfw_{context}_{specific_field}
```

Where:
- `_arsol_pfw_` = Plugin prefix (consistent across all keys)
- `{context}` = Data context (`request`, `proposal`, `project`)
- `{specific_field}` = Descriptive field name

### Meta Key Categories

| Category | Count | Prefix Pattern | Purpose |
|----------|-------|----------------|---------|
| **Request Keys** | 5 | `_arsol_pfw_request_*` | Project request data |
| **Proposal Keys** | 25+ | `_arsol_pfw_proposal_*` | Proposal details & quotations |
| **Project Keys** | 10+ | `_arsol_pfw_project_*` | Active project management |
| **Integration Keys** | 3 | Various | WooCommerce integration |

---

## Meta Key Standardization

### Current Status

- ✅ **Proposal Meta Keys**: Fully standardized (25+ keys)
- 🔄 **Project Meta Keys**: Partially standardized (ongoing migration)
- 🔄 **Request Meta Keys**: Partially standardized (ongoing migration)
- ❌ **Legacy Keys**: Being phased out

### Standardization Benefits

1. **Consistent Naming**: Easy to understand and maintain
2. **Namespace Protection**: Prevents conflicts with other plugins
3. **Logical Grouping**: Related fields grouped by context
4. **Future-Proof**: Scalable naming structure

---

## Project Request Meta Keys

*Total: 5 standardized keys*

### Core Request Data

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_request_budget` | Array | Customer budget information | `{'amount': 1000, 'currency': 'USD'}` |
| `_arsol_pfw_request_start_date` | String | Requested start date | `'2024-01-15'` |
| `_arsol_pfw_request_delivery_date` | String | Requested delivery date | `'2024-03-15'` |

### Admin Feedback Fields

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_request_onhold_feedback` | String | Admin feedback when on hold | Reason for hold status |
| `_arsol_pfw_request_underreview_feedback` | String | Admin notes during review | Review progress notes |

### Implementation Example

```php
// Saving request budget
$budget_data = array(
    'amount' => floatval($_POST['budget_amount']),
    'currency' => get_woocommerce_currency()
);
update_post_meta($request_id, '_arsol_pfw_request_budget', $budget_data);

// Saving dates
update_post_meta($request_id, '_arsol_pfw_request_start_date', sanitize_text_field($_POST['start_date']));
update_post_meta($request_id, '_arsol_pfw_request_delivery_date', sanitize_text_field($_POST['delivery_date']));
```

---

## Project Proposal Meta Keys

*Total: 25+ standardized keys*

### Core Proposal Data

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_proposal_costing_type` | String | Proposal type | `'budget'` or `'quotation'` |
| `_arsol_pfw_proposal_secondary_status` | String | Internal status | `'ready_for_review'`, `'processing'` |
| `_arsol_pfw_proposal_project_lead` | String | Assigned project lead | User ID |
| `_arsol_pfw_proposal_customer_id` | String | Customer ID | User ID |

### Timeline & Dates

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_proposal_start_date` | String | Proposed start date | `'2024-01-15'` |
| `_arsol_pfw_proposal_delivery_date` | String | Proposed delivery date | `'2024-03-15'` |
| `_arsol_pfw_proposal_expiration_date` | String | Proposal expiration | `'2024-01-01'` |

### Budget-Type Proposals

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_proposal_budget_onetime_amount` | Array | One-time amount | `{'amount': 5000, 'currency': 'USD'}` |
| `_arsol_pfw_proposal_budget_onetime_amount_details` | String | One-time description | Text description |
| `_arsol_pfw_proposal_budget_recurring_amount` | Array | Recurring amount | `{'amount': 100, 'currency': 'USD'}` |
| `_arsol_pfw_proposal_budget_recurring_amount_details` | String | Recurring description | Text description |
| `_arsol_pfw_proposal_budget_recurring_amount_billing_interval` | String | Billing interval | `'1'`, `'2'`, `'3'` |
| `_arsol_pfw_proposal_budget_recurring_amount_billing_period` | String | Billing period | `'month'`, `'year'` |
| `_arsol_pfw_proposal_budget_recurring_billing_start_date` | String | Billing start date | `'2024-01-15'` |

### Quotation-Type Proposals

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_proposal_quotation_line_items` | Array | Detailed line items | Complex array structure |
| `_arsol_pfw_proposal_quotation_onetime_total` | String | Total one-time amount | `'5000.00'` |
| `_arsol_pfw_proposal_quotation_recurring_totals_grouped` | Array | Grouped recurring totals | Array by billing period |
| `_arsol_pfw_proposal_quotation_currency` | String | Quotation currency | `'USD'`, `'EUR'` |
| `_arsol_pfw_proposal_quotation_currency_symbol` | String | Currency symbol | `'$'`, `'€'` |

### Additional Proposal Fields

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_proposal_notes` | String | Proposal notes | HTML content |
| `_arsol_pfw_proposal_timeline` | String | Project timeline | Text description |
| `_arsol_pfw_proposal_attachments` | Array | File attachments | Array of file IDs |

### Original Request Data (Inherited)

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_proposal_request_id` | String | Original request ID | Post ID |
| `_arsol_pfw_proposal_request_date` | String | Original request date | `'2024-01-01'` |
| `_arsol_pfw_proposal_request_title` | String | Original request title | Text |
| `_arsol_pfw_proposal_request_details` | String | Original request content | HTML content |
| `_arsol_pfw_proposal_request_budget` | Array | Original budget | `{'amount': 1000, 'currency': 'USD'}` |
| `_arsol_pfw_proposal_request_start_date` | String | Original start date | `'2024-01-15'` |
| `_arsol_pfw_proposal_request_delivery_date` | String | Original delivery date | `'2024-03-15'` |
| `_arsol_pfw_proposal_request_attachments` | Array | Original attachments | Array of file IDs |

### Implementation Example

```php
// Saving proposal costing type
update_post_meta($proposal_id, '_arsol_pfw_proposal_costing_type', 'budget');

// Saving budget data
$budget_data = array(
    'amount' => floatval($_POST['onetime_amount']),
    'currency' => get_woocommerce_currency()
);
update_post_meta($proposal_id, '_arsol_pfw_proposal_budget_onetime_amount', $budget_data);

// Saving quotation line items
$line_items = array(
    array(
        'description' => 'Web Development',
        'quantity' => 1,
        'rate' => 5000,
        'total' => 5000,
        'type' => 'onetime'
    )
);
update_post_meta($proposal_id, '_arsol_pfw_proposal_quotation_line_items', $line_items);
```

---

## Active Project Meta Keys

*Total: 10+ standardized keys*

### Core Project Data

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_project_lead` | String | Project lead | User ID |
| `_arsol_pfw_project_start_date` | String | Project start date | `'2024-01-15'` |
| `_arsol_pfw_project_due_date` | String | Project due date | `'2024-03-15'` |
| `_arsol_pfw_project_proposal_id` | String | Source proposal ID | Post ID |

### WooCommerce Integration

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_project_woocommerce_order_id` | String | Created order ID | WC Order ID |
| `_arsol_pfw_project_woocommerce_subscription_id` | String | Created subscription ID | WC Subscription ID |
| `_arsol_pfw_project_order_creation_note` | String | Order creation success note | Success message |
| `_arsol_pfw_project_order_creation_error` | String | Order creation error | Error message |

### Billing Configuration

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_project_billing_interval` | String | Billing interval | `'1'`, `'2'`, `'3'` |
| `_arsol_pfw_project_billing_period` | String | Billing period | `'month'`, `'year'` |
| `_arsol_pfw_project_recurring_start_date` | String | Recurring billing start | `'2024-01-15'` |

### Inherited Data from Proposal

During proposal → project conversion, these fields are created:

#### From Request (via Proposal)

| Meta Key | Type | Description |
|----------|------|-------------|
| `_arsol_pfw_project_request_details` | String | Original request content |
| `_arsol_pfw_project_request_title` | String | Original request title |
| `_arsol_pfw_project_request_date` | String | Original request date |
| `_arsol_pfw_project_request_budget` | Array | Original request budget |
| `_arsol_pfw_project_request_start_date` | String | Original requested start date |
| `_arsol_pfw_project_request_delivery_date` | String | Original requested delivery |
| `_arsol_pfw_project_request_attachments` | Array | Original request attachments |

#### From Proposal

| Meta Key | Type | Description |
|----------|------|-------------|
| `_arsol_pfw_project_proposal_details` | String | Proposal content |
| `_arsol_pfw_project_proposal_start_date` | String | Proposed start date |
| `_arsol_pfw_project_proposal_delivery_date` | String | Proposed delivery date |
| `_arsol_pfw_project_proposal_notes` | String | Proposal notes |
| `_arsol_pfw_project_proposal_timeline` | String | Proposal timeline |
| `_arsol_pfw_project_proposal_costing_type` | String | Budget or quotation type |

#### Budget-Specific (if proposal was budget type)

| Meta Key | Type | Description |
|----------|------|-------------|
| `_arsol_pfw_project_proposal_budget_onetime_amount` | Array | One-time amount |
| `_arsol_pfw_project_proposal_budget_recurring_amount` | Array | Recurring amount |
| `_arsol_pfw_project_proposal_budget_*` | Various | All budget fields |

#### Quotation-Specific (if proposal was quotation type)

| Meta Key | Type | Description |
|----------|------|-------------|
| `_arsol_pfw_project_proposal_quotation_line_items` | Array | Line items |
| `_arsol_pfw_project_proposal_quotation_onetime_total` | String | Total one-time |
| `_arsol_pfw_project_proposal_quotation_*` | Various | All quotation fields |

### Implementation Example

```php
// Core project data
update_post_meta($project_id, '_arsol_pfw_project_lead', $lead_user_id);
update_post_meta($project_id, '_arsol_pfw_project_start_date', current_time('mysql'));

// WooCommerce integration
update_post_meta($project_id, '_arsol_pfw_project_woocommerce_order_id', $order_id);
update_post_meta($project_id, '_arsol_pfw_project_order_creation_note', 'Order created successfully');

// Billing configuration
update_post_meta($project_id, '_arsol_pfw_project_billing_interval', '1');
update_post_meta($project_id, '_arsol_pfw_project_billing_period', 'month');
```

---

## WooCommerce Integration Meta Keys

### Order Meta Keys

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `ARSOL_PROJECT_META_KEY` | String | Links order to project | Project post ID |
| `_arsol_project_id` | String | Project ID (legacy) | Project post ID |

**Constant Definition:**
```php
define('ARSOL_PROJECT_META_KEY', 'arsol-pfw/parent-project-id');
```

### Usage in WooCommerce

```php
// Save project ID to order
$project_id = 123;
update_post_meta($order->get_id(), ARSOL_PROJECT_META_KEY, $project_id);

// Retrieve project from order
$project_id = get_post_meta($order_id, ARSOL_PROJECT_META_KEY, true);
```

---

## Legacy Meta Keys

### Being Phased Out

These older meta keys are being replaced with standardized versions:

| Legacy Key | New Standardized Key | Status |
|------------|---------------------|---------|
| `_project_lead` | `_arsol_pfw_project_lead` | 🔄 Migrating |
| `_project_start_date` | `_arsol_pfw_project_start_date` | 🔄 Migrating |
| `_project_due_date` | `_arsol_pfw_project_due_date` | 🔄 Migrating |
| `_project_budget` | `_arsol_pfw_project_proposal_budget_*` | 🔄 Migrating |
| `_project_recurring_budget` | `_arsol_pfw_project_proposal_budget_recurring_*` | 🔄 Migrating |
| `_request_budget` | `_arsol_pfw_request_budget` | 🔄 Migrating |
| `_request_start_date` | `_arsol_pfw_request_start_date` | 🔄 Migrating |
| `_request_delivery_date` | `_arsol_pfw_request_delivery_date` | 🔄 Migrating |

### Legacy Proposal Keys (Already Migrated)

These were successfully migrated to standardized format:

| Legacy Key | New Standardized Key |
|------------|---------------------|
| `_cost_proposal_type` | `_arsol_pfw_proposal_costing_type` |
| `_proposal_secondary_status` | `_arsol_pfw_proposal_secondary_status` |
| `_proposal_start_date` | `_arsol_pfw_proposal_start_date` |
| `_proposal_budget` | `_arsol_pfw_proposal_budget_onetime_amount` |
| `_proposal_notes` | `_arsol_pfw_proposal_notes` |

---

## Migration Reference

### Migration Strategy

1. **Gradual Migration**: Both old and new keys work during transition
2. **Data Preservation**: Original data maintained during migration
3. **Fallback Logic**: Code checks both old and new keys
4. **Clean Data Flow**: New conversions use only standardized keys

### Migration Functions

```php
// Example migration function
function migrate_project_meta_keys($project_id) {
    $legacy_mappings = array(
        '_project_lead' => '_arsol_pfw_project_lead',
        '_project_start_date' => '_arsol_pfw_project_start_date',
        '_project_due_date' => '_arsol_pfw_project_due_date'
    );
    
    foreach ($legacy_mappings as $old_key => $new_key) {
        $value = get_post_meta($project_id, $old_key, true);
        if (!empty($value)) {
            update_post_meta($project_id, $new_key, $value);
            // Optionally delete old key after migration
            // delete_post_meta($project_id, $old_key);
        }
    }
}
```

### Fallback Reading

```php
// Function to read meta with fallback to legacy keys
function get_project_meta_with_fallback($project_id, $standard_key, $legacy_key) {
    $value = get_post_meta($project_id, $standard_key, true);
    
    if (empty($value)) {
        $value = get_post_meta($project_id, $legacy_key, true);
    }
    
    return $value;
}

// Usage example
$project_lead = get_project_meta_with_fallback(
    $project_id, 
    '_arsol_pfw_project_lead', 
    '_project_lead'
);
```

---

## Data Flow Summary

### Request → Proposal → Project

```
1. REQUEST CREATION
   ├── _arsol_pfw_request_budget
   ├── _arsol_pfw_request_start_date
   └── _arsol_pfw_request_delivery_date

2. PROPOSAL CREATION (inherits request data)
   ├── _arsol_pfw_proposal_request_* (inherited)
   ├── _arsol_pfw_proposal_costing_type
   ├── _arsol_pfw_proposal_budget_* OR _arsol_pfw_proposal_quotation_*
   └── _arsol_pfw_proposal_notes

3. PROJECT CREATION (inherits all previous data)
   ├── _arsol_pfw_project_request_* (from request via proposal)
   ├── _arsol_pfw_project_proposal_* (from proposal)
   ├── _arsol_pfw_project_lead
   ├── _arsol_pfw_project_woocommerce_order_id
   └── _arsol_pfw_project_billing_*
```

This comprehensive meta key system ensures data integrity and traceability throughout the complete project lifecycle while maintaining backward compatibility during the standardization process. 