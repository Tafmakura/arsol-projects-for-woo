# Arsol Projects for Woo - Meta Keys Complete Reference

**Version**: 2.0  
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
7. [Data Access Patterns](#data-access-patterns)
8. [Migration Status](#migration-status)

---

## Overview

The Arsol Projects for Woo plugin uses a comprehensive meta key system with standardized naming conventions across three main custom post types (CPTs):
- **Projects** (`arsol-pfw-project`)
- **Proposals** (`arsol-pfw-proposal`) 
- **Requests** (`arsol-pfw-request`)

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
| **Request Keys** | 6 | `_arsol_pfw_request_*` | Project request data |
| **Proposal Keys** | 15+ | `_arsol_pfw_proposal_*` | Proposal details & quotations |
| **Project Keys** | 8+ | `_arsol_pfw_project_*` | Active project management |
| **Integration Keys** | 3 | Various | WooCommerce integration |

---

## Meta Key Standardization

### Current Status

- ✅ **Proposal Meta Keys**: Fully standardized with array-based structures
- ✅ **Project Meta Keys**: Standardized with inherited array structures
- ✅ **Request Meta Keys**: Standardized with simplified structure
- ✅ **Timeline Fields**: Removed from all CPTs
- ✅ **Priority Fields**: Removed from all CPTs

### Standardization Benefits

1. **Consistent Naming**: Easy to understand and maintain
2. **Namespace Protection**: Prevents conflicts with other plugins
3. **Logical Grouping**: Related fields grouped by context
4. **Array-Based Structures**: Complex data stored as arrays (WooCommerce pattern)
5. **Future-Proof**: Scalable naming structure

---

## Project Request Meta Keys

*Total: 6 standardized keys*

### Core Request Data

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_requested_budget` | Array | Customer budget information | `{'amount': 1000, 'currency': 'USD'}` |
| `_arsol_pfw_requested_start_date` | String | Requested start date | `'2024-01-15'` |
| `_arsol_pfw_requested_due_date` | String | Requested delivery date | `'2024-03-15'` |
| `_arsol_pfw_request_description` | String | Request description | Text content |
| `_arsol_pfw_request_project_lead` | String | Assigned project lead | User ID |
| `_arsol_pfw_parent_project_id` | String | Parent project ID | Post ID |

### Implementation Example

```php
// Saving request budget
$budget_data = array(
    'amount' => floatval($_POST['budget_amount']),
    'currency' => get_woocommerce_currency()
);
update_post_meta($request_id, '_arsol_pfw_requested_budget', $budget_data);

// Saving dates
update_post_meta($request_id, '_arsol_pfw_requested_start_date', sanitize_text_field($_POST['start_date']));
update_post_meta($request_id, '_arsol_pfw_requested_due_date', sanitize_text_field($_POST['due_date']));
```

---

## Project Proposal Meta Keys

*Total: 15+ standardized keys*

### Core Proposal Data (Simple Meta Keys)

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_proposal_description` | String | Proposal description | Text content |
| `_arsol_pfw_proposed_project_lead` | String | Assigned project lead | User ID |
| `_arsol_pfw_proposed_project_start_date` | String | Proposed start date | `'2024-01-15'` |
| `_arsol_pfw_proposed_project_due_date` | String | Proposed due date | `'2024-03-15'` |
| `_arsol_pfw_proposal_expiration_date` | String | Proposal expiration date | `'2024-02-15'` |
| `_arsol_pfw_proposal_costing_type` | String | Proposal type | `'budget'` or `'quotation'` |
| `_arsol_pfw_parent_project_id` | String | Parent project ID | Post ID |
| `_arsol_pfw_proposal_customer_notice` | String | Customer notice | HTML content |
| `_arsol_pfw_proposal_secondary_status` | String | Internal status | `'ready_for_review'`, `'processing'` |

### Complex Data Structure Meta Keys (Array-Based)

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_proposed_project_budget_line_items` | Array | Complete budget data structure | Array of budget items |
| `_arsol_pfw_proposed_project_quotation_line_items` | Array | Complete quotation data structure | Array of quotation items |
| `_arsol_pfw_proposal_original_request_data` | Array | Original request data structure | Inherited request data |
| `_arsol_pfw_proposal_woocommerce_data` | Array | WooCommerce integration data | Order/subscription data |
| `_arsol_pfw_proposal_workflow_data` | Array | Workflow management data | Approval/rejection data |

### Inherited from Request (Original Request Data)

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_request_id` | String | Original request ID | Post ID |
| `_arsol_pfw_request_details` | String | Original request content | HTML content |
| `_arsol_pfw_requested_project_budget` | Array | Original request budget | `{'amount': 1000, 'currency': 'USD'}` |
| `_arsol_pfw_requested_project_start_date` | String | Original requested start date | `'2024-01-15'` |
| `_arsol_pfw_requested_project_due_date` | String | Original requested due date | `'2024-03-15'` |
| `_arsol_pfw_request_attachments` | Array | Original request attachments | Array of file IDs |

### Implementation Example

```php
// Saving proposal costing type
update_post_meta($proposal_id, '_arsol_pfw_proposal_costing_type', 'budget');

// Saving budget data as array
$budget_data = array(
    'onetime' => array(
        'amount' => 5000,
        'currency' => 'USD',
        'description' => 'One-time development'
    ),
    'recurring' => array(
        'amount' => 500,
        'currency' => 'USD',
        'period' => 'month',
        'description' => 'Monthly maintenance'
    ),
    'notes' => 'Budget includes all development and maintenance costs'
);
update_post_meta($proposal_id, '_arsol_pfw_proposed_project_budget_line_items', $budget_data);

// Saving quotation line items
$quotation_data = array(
    'line_items' => array(
    array(
        'description' => 'Web Development',
        'quantity' => 1,
        'rate' => 5000,
        'total' => 5000,
        'type' => 'onetime'
        )
    ),
    'currency' => 'USD',
    'totals' => array(
        'onetime' => 5000,
        'recurring' => 0
    )
);
update_post_meta($proposal_id, '_arsol_pfw_proposed_project_quotation_line_items', $quotation_data);
```

---

## Active Project Meta Keys

*Total: 8+ standardized keys*

### Core Project Data (Simple Meta Keys)

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_project_budget` | Array | Project budget information | `{'amount': 1000, 'currency': 'USD'}` |
| `_arsol_pfw_project_due_date` | String | Project due date | `'2024-03-15'` |
| `_arsol_pfw_project_description` | String | Project description | Text content |
| `_arsol_pfw_project_start_date` | String | Project start date | `'2024-01-15'` |
| `_arsol_pfw_project_lead` | String | Assigned project lead | User ID |
| `_arsol_pfw_project_customer_notice` | String | Customer notice | HTML content |

### Complex Data Structure Meta Keys (Inherited from Proposal)

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_proposed_project_budget_line_items` | Array | Inherited budget data structure | Array of budget items |
| `_arsol_pfw_proposed_project_quotation_line_items` | Array | Inherited quotation data structure | Array of quotation items |
| `_arsol_pfw_project_woocommerce_data` | Array | WooCommerce integration data | Order/subscription data |
| `_arsol_pfw_project_workflow_data` | Array | Workflow management data | Project status data |

### Inherited from Proposal (No "project" prefix)

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_proposal_id` | String | Original proposal ID | Post ID |
| `_arsol_pfw_request_id` | String | Original request ID | Post ID |

### Implementation Example

```php
// Core project data
update_post_meta($project_id, '_arsol_pfw_project_lead', $lead_user_id);
update_post_meta($project_id, '_arsol_pfw_project_start_date', current_time('mysql'));

// Inherit proposal budget data
$proposal_budget = get_post_meta($proposal_id, '_arsol_pfw_proposed_project_budget_line_items', true);
update_post_meta($project_id, '_arsol_pfw_proposed_project_budget_line_items', $proposal_budget);

// WooCommerce integration
update_post_meta($project_id, '_arsol_pfw_project_woocommerce_order_id', $order_id);
```

---

## WooCommerce Integration Meta Keys

### Order Meta Keys

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `ARSOL_PROJECT_META_KEY` | String | Links order to project | Project post ID |
| `_arsol_pfw_project_id` | String | Project ID in orders | Project post ID |
| `_arsol_pfw_proposal_id` | String | Proposal ID in orders | Proposal post ID |
| `_arsol_pfw_conversion_date` | String | Date of conversion to order | `'2024-01-15 10:30:00'` |

### Subscription Meta Keys

| Meta Key | Type | Description | Usage |
|----------|------|-------------|-------|
| `_arsol_pfw_proposal_id` | String | Proposal ID in subscriptions | Proposal post ID |
| `_arsol_pfw_conversion_date` | String | Date of conversion to subscription | `'2024-01-15 10:30:00'` |

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

## Data Access Patterns

### 1. Entity Methods (Primary Approach)
Entity methods provide a clean, object-oriented interface for accessing and modifying data.

**Examples:**
```php
// Get complete budget data as array
$budget_data = $proposal->get_proposal_budget();

// Set complete budget data as array
$proposal->set_proposal_budget($budget_data);

// Get individual fields from array data
$onetime_amount = $proposal->get_budget_onetime_amount();
$project_lead = $proposal->get_project_lead();
```

### 2. Individual Meta Key Access (WooCommerce Pattern)
Following WooCommerce's pattern, the plugin also supports direct meta key access for individual fields.

**Examples:**
```php
// Get individual budget meta field
$onetime_amount = $proposal->get_budget_meta('onetime');
$project_lead = $proposal->get_meta('_arsol_pfw_proposed_project_lead');

// Set individual budget meta field
$proposal->set_budget_meta('onetime', $amount);
$proposal->set_meta('_arsol_pfw_proposed_project_lead', $lead_id);
```

### 3. Generic Meta Access
For custom meta fields not managed by the plugin, developers can use WordPress's native functions.

**Examples:**
```php
// Get custom meta field
$custom_field = get_post_meta($proposal->get_id(), '_custom_field', true);

// Set custom meta field
update_post_meta($proposal->get_id(), '_custom_field', $value);
```

---

## Migration Status

### ✅ Completed Migrations

1. **Proposal Meta Keys**: Fully standardized with array-based structures
2. **Project Meta Keys**: Standardized with inherited array structures
3. **Request Meta Keys**: Standardized with simplified structure
4. **Timeline Fields**: Removed from all CPTs
5. **Priority Fields**: Removed from all CPTs
6. **Budget Consolidation**: Proposal budgets now use array structure
7. **Meta Key Naming**: Consistent naming across all CPTs

### 🔄 In Progress

1. **Template Updates**: Updating all templates to use new meta keys
2. **Admin Handler Updates**: Updating admin save/read methods
3. **Conversion Handler Updates**: Updating conversion logic
4. **Documentation Updates**: Updating all documentation

### ❌ Removed Features

1. **Timeline Fields**: No longer supported in any CPT
2. **Priority Fields**: No longer supported in any CPT
3. **Legacy Budget Fields**: Individual budget fields replaced with arrays
4. **Redundant Flags**: Removed `_arsol_pfw_is_project_tied_proposal`

### Benefits of New Structure

1. **Consistency**: All CPTs follow the same naming patterns
2. **Simplicity**: Removed unnecessary fields (timeline, priority)
3. **Performance**: Array-based structures are more efficient
4. **Maintainability**: Cleaner, more organized meta key structure
5. **WooCommerce Alignment**: Follows WooCommerce patterns for complex data 