# Project Proposals - Complete Reference

## Table of Contents

1. [Overview](#overview)
2. [Proposal Types](#proposal-types)
3. [Normal Proposals](#normal-proposals)
4. [Project-Tied Proposals](#project-tied-proposals)
5. [Cost Proposal Types](#cost-proposal-types)
6. [Field Reference](#field-reference)
7. [Status Management](#status-management)
8. [Conversion System](#conversion-system)
9. [Meta Keys Reference](#meta-keys-reference)
10. [JavaScript Integration](#javascript-integration)
11. [Template Structure](#template-structure)
12. [Hooks and Filters](#hooks-and-filters)
13. [Best Practices](#best-practices)

---

## Overview

The Arsol Projects for WooCommerce plugin supports two distinct types of project proposals:

- **Normal Proposals**: Independent proposals that can be converted to projects
- **Project-Tied Proposals**: Proposals linked to existing projects with inherited field values

This reference provides comprehensive documentation for both types, their differences, and implementation details.

---

## Proposal Types

### Normal Proposals

Normal proposals are standalone entities that can be created independently and later converted to projects.

**Characteristics:**
- All fields are editable
- Can be converted to projects (when approved)
- Independent lifecycle management
- Full user control over all proposal details

### Project-Tied Proposals

Project-tied proposals are linked to existing projects and inherit specific field values from their parent project.

**Characteristics:**
- Certain fields are locked and inherited from parent project
- Cannot be converted to projects (already tied to one)
- Restricted field editing for consistency
- Automatic synchronization with parent project data

---

## Normal Proposals

### Creation Process

Normal proposals can be created through:

1. **Direct Creation**: Admin → Proposals → Add New
2. **Request Conversion**: Converting approved project requests
3. **Manual Entry**: Direct form submission

### Editable Fields

All fields in normal proposals are fully editable:

- **Customer**: Searchable customer selection
- **Project Lead**: Searchable project lead selection  
- **Cost Proposal Type**: None, Budget, or Quotation
- **Proposal Status**: Processing, Approved, Rejected, etc.
- **Dates**: Start, delivery, and expiration dates
- **Content**: Full proposal description and details

### Lifecycle Management

```
Draft → Published → Approved → Converted to Project
  ↓        ↓          ↓
Save    Update    Convert
```

### Conversion to Project

When a normal proposal is approved, it can be converted to a project:

**Requirements:**
- Proposal status must be "Approved"
- Proposal must be published
- User must have appropriate permissions

**Conversion Process:**
1. Creates new project with proposal data
2. Transfers all proposal content
3. Creates WooCommerce orders/subscriptions (for quotations)
4. Permanently deletes original proposal

**Warning Message:**
> "This action will create a new project based on this proposal and permanently delete the original proposal. The proposal status must be set to "Approved" before conversion. Orders and invoices will be created for quotation proposals. This action cannot be undone."

---

## Project-Tied Proposals

### Creation Process

Project-tied proposals are created from existing projects:

1. **From Project**: Project edit screen → "Create Proposal" button
2. **URL Parameter**: `post-new.php?post_type=arsol-pfw-proposal&parent_project=123`
3. **Automatic Linking**: Via `_arsol_pfw_parent_project_id` meta key

### Locked Fields

The following fields are automatically inherited and locked:

#### Customer Field
- **Source**: `$parent_project->post_author`
- **Display**: Disabled select with customer name
- **Submission**: Hidden input ensures value is saved

```html
<select class="arsol-disabled-select" disabled>
    <option selected>Customer Name (email@example.com)</option>
</select>
<input type="hidden" name="customer_id" value="123">
```

#### Project Lead Field
- **Source**: `get_post_meta($parent_project_id, '_arsol_pfw_project_lead', true)`
- **Display**: Disabled select with lead name and email
- **Fallback**: "No project lead assigned" if none set

```html
<select class="arsol-disabled-select" disabled>
    <option selected>Lead Name (lead@example.com)</option>
</select>
<input type="hidden" name="proposal_project_lead" value="456">
```

#### Cost Proposal Type Field
- **Value**: Always "Quotation"
- **Display**: Disabled select without Select2 enhancement
- **JavaScript**: Maintains same ID for conditional logic compatibility

```html
<select id="arsol_pfw_proposal_costing_type" class="arsol-disabled-select" disabled>
    <option value="quotation" selected>Quotation</option>
</select>
<input type="hidden" name="arsol_pfw_proposal_costing_type" value="quotation">
```

### Editable Fields

The following fields remain editable in project-tied proposals:

- **Proposal Title**: Can be customized
- **Proposal Status**: Full status management
- **Dates**: Start, delivery, and expiration dates
- **Content**: Proposal description and details
- **Quotation Details**: Line items, pricing, etc.

### Parent Project Relationship

#### Header Display
```
Parent Project: [Project Title]
```

#### Informational Message
> "This proposal is tied to a parent project. The Customer, Project Lead, and Cost Proposal Type fields are automatically set from the parent project and cannot be modified. Changes to these values must be made in the parent project."

#### View Project Button
- **Function**: Opens parent project in new tab
- **Style**: Secondary button without conversion functionality
- **Target**: `_blank` with `rel="noopener noreferrer"`

### Meta Data Persistence

Project-tied proposals maintain their relationship through:

```php
// Parent project ID
update_post_meta($post_id, '_arsol_pfw_parent_project_id', $parent_project_id);

// Project-tied flag
update_post_meta($post_id, '_arsol_pfw_is_project_tied_proposal', 1);
```

### URL Parameter Preservation

JavaScript ensures URL parameters are maintained during form submission:

```javascript
// Preserve parent_project parameter
var parentProjectId = 123;
var currentAction = $form.attr('action') || '';
var separator = currentAction.indexOf('?') !== -1 ? '&' : '?';
$form.attr('action', currentAction + separator + 'parent_project=' + parentProjectId);
```

---

## Cost Proposal Types

### None
- **Purpose**: Placeholder for proposals without cost details
- **Features**: Basic proposal information only
- **Meta Boxes**: General settings only

### Budget
- **Purpose**: High-level budget estimates
- **Features**: 
  - One-time budget amounts
  - Recurring budget amounts with billing cycles
  - Budget descriptions and details
- **Meta Boxes**: Budget Estimates section

### Quotation
- **Purpose**: Detailed line-item pricing
- **Features**:
  - Product line items
  - One-time fees
  - Recurring fees
  - Shipping fees
  - Automatic total calculations
- **Meta Boxes**: Quotation Details section

---

## Field Reference

### Core Fields

| Field | Type | Normal Proposals | Project-Tied Proposals |
|-------|------|------------------|------------------------|
| `post_title` | Text | Editable | Editable |
| `post_content` | WYSIWYG | Editable | Editable |
| `post_author` | User ID | Editable | Locked (from parent) |
| `post_status` | Status | Editable | Editable |

### Meta Fields

| Meta Key | Description | Normal | Project-Tied |
|----------|-------------|---------|--------------|
| `_arsol_pfw_proposal_project_lead` | Project lead user ID | Editable | Locked |
| `_arsol_pfw_proposal_costing_type` | Cost proposal type | Editable | Locked (quotation) |
| `_arsol_pfw_proposed_start_date` | Proposed start date | Editable | Editable |
| `_arsol_pfw_proposed_due_date` | Proposed due date | Editable | Editable |
| `_arsol_pfw_proposal_expiration_date` | Proposal expiration | Editable | Editable |
| `_arsol_pfw_parent_project_id` | Parent project ID | N/A | Auto-set |
| `_arsol_pfw_is_project_tied_proposal` | Project-tied flag | N/A | Auto-set (1) |

### Budget Fields (Budget Type Only)

| Meta Key | Description | Type |
|----------|-------------|------|
| `_arsol_pfw_proposal_budget_onetime_amount` | One-time budget | Array (amount, currency) |
| `_arsol_pfw_proposal_budget_onetime_amount_details` | One-time budget description | Text |
| `_arsol_pfw_proposal_budget_recurring_amount` | Recurring budget | Array (amount, currency) |
| `_arsol_pfw_proposal_budget_recurring_amount_details` | Recurring budget description | Text |
| `_arsol_pfw_proposal_budget_recurring_amount_billing_interval` | Billing interval | Text (1, 2, 3, etc.) |
| `_arsol_pfw_proposal_budget_recurring_amount_billing_period` | Billing period | Text (day, week, month, year) |
| `_arsol_pfw_proposal_budget_recurring_billing_start_date` | Billing start date | Date |

### Quotation Fields (Quotation Type Only)

| Meta Key | Description | Type |
|----------|-------------|------|
| `_arsol_pfw_proposal_quotation_line_items` | All line items | Array |
| `_arsol_pfw_proposal_quotation_onetime_total` | One-time total | Decimal |
| `_arsol_pfw_proposal_quotation_recurring_totals_grouped` | Recurring totals | Array |

---

## Status Management

### Proposal Statuses

Proposals use the `arsol-proposal-status` taxonomy:

- **processing**: Default status for new proposals
- **approved**: Ready for conversion (normal) or finalized (project-tied)
- **rejected**: Declined proposals
- **on-hold**: Temporarily paused proposals
- **revision-requested**: Requires modifications

### Status Transitions

```
processing → approved → converted (normal proposals)
processing → approved → finalized (project-tied proposals)
processing → rejected → archived
processing → on-hold → processing
any → revision-requested → processing
```

### Status-Based Permissions

- **Conversion**: Only approved, published proposals can be converted
- **Editing**: All statuses allow editing (except locked fields in project-tied)
- **Deletion**: Admin permissions required

---

## Conversion System

### Normal Proposal Conversion

#### Prerequisites
```php
$is_not_published = $post->post_status !== 'publish';
$is_not_approved = $current_proposal_status !== 'approved';
$is_disabled = $is_not_published || $is_not_approved;
```

#### Conversion Process
1. **Validation**: Check status and permissions
2. **Project Creation**: Create new project post
3. **Data Transfer**: Copy proposal data to project
4. **Order Creation**: Generate WooCommerce orders (quotations)
5. **Cleanup**: Delete original proposal

#### Conversion URL
```php
$convert_url = admin_url('admin-post.php?action=arsol_convert_to_project&proposal_id=' . $post->ID);
$convert_url = wp_nonce_url($convert_url, 'arsol_convert_to_project_nonce');
```

### Project-Tied Proposals

Project-tied proposals **cannot** be converted as they're already linked to existing projects. Instead, they show a "View Project" button.

---

## Meta Keys Reference

### Core Proposal Meta Keys

```php
// Basic proposal information
'_arsol_pfw_proposal_project_lead'        // User ID of project lead
'_arsol_pfw_proposal_costing_type'        // none|budget|quotation
'_arsol_pfw_proposed_start_date'          // Y-m-d format
'_arsol_pfw_proposed_due_date'            // Y-m-d format
'_arsol_pfw_proposal_expiration_date'     // Y-m-d format
'_arsol_pfw_proposal_notes'               // Additional notes

// Project relationship
'_arsol_pfw_parent_project_id'            // Parent project ID (project-tied only)
'_arsol_pfw_is_project_tied_proposal'     // 1 for project-tied proposals

// Request relationship (if created from request)
'_arsol_pfw_proposal_request_id'          // Original request ID
'_arsol_pfw_proposal_request_budget'      // Request budget data
'_arsol_pfw_proposal_request_start_date'  // Request start date
'_arsol_pfw_proposed_due_date'            // Y-m-d format
```

### Budget Meta Keys

```php
// One-time budget
'_arsol_pfw_proposal_budget_onetime_amount' // Array: {amount, currency}
'_arsol_pfw_proposal_budget_onetime_amount_details' // Description

// Recurring budget
'_arsol_pfw_proposal_budget_recurring_amount' // Array: {amount, currency}
'_arsol_pfw_proposal_budget_recurring_amount_details' // Description
'_arsol_pfw_proposal_budget_recurring_amount_billing_interval' // 1,2,3...
'_arsol_pfw_proposal_budget_recurring_amount_billing_period' // day,week,month,year
'_arsol_pfw_proposal_budget_recurring_billing_start_date' // Y-m-d
```

### Quotation Meta Keys

```php
// Line items and totals
'_arsol_pfw_proposal_quotation_line_items' // Array of all line items
'_arsol_pfw_proposal_quotation_onetime_total' // Calculated one-time total
'_arsol_pfw_proposal_quotation_recurring_totals_grouped' // Recurring totals by period
```

---

## JavaScript Integration

### Conditional Logic Implementation

Our proposal system uses a **smart conditional visibility system** based on CSS classes and auto-discovery JavaScript:

#### CSS Class Pattern
```css
/* Pattern: arsol-pfw-{action}-if-{field-name}-is-{value} */
.arsol-pfw-show-if-arsol_pfw_proposal_costing_type-is-budget { /* Budget metabox */ }
.arsol-pfw-show-if-arsol_pfw_proposal_costing_type-is-quotation { /* Quotation metabox */ }
```

#### Implementation
The system automatically:
1. **Scans for CSS classes** matching the pattern `arsol-pfw-{action}-if-{field-name}-is-{value}`
2. **Auto-discovers field mappings** without hardcoded JavaScript
3. **Binds to field changes** and updates visibility in real-time
4. **Works with any field ID and value** - completely dynamic

#### Benefits
- ✅ **Zero configuration** - just add CSS classes
- ✅ **Auto-discovery** of field relationships
- ✅ **Dynamic** - works with any field/value combination
- ✅ **Performance** - minimal JavaScript overhead
- ✅ **Maintainable** - no hardcoded mappings to update

#### Example Usage
```html
<!-- Metabox will automatically show/hide based on proposal costing type -->
<div class="postbox arsol-pfw-show-if-arsol_pfw_proposal_costing_type-is-budget">
    <h2>Budget Estimation</h2>
    <!-- Budget content -->
</div>

<div class="postbox arsol-pfw-show-if-arsol_pfw_proposal_costing_type-is-quotation">
    <h2>Quotation Builder</h2>
    <!-- Quotation content -->
</div>
```

#### Metabox Integration
Metaboxes use WordPress `postbox_classes` filters to apply conditional CSS classes:

```php
// Budget metabox
add_filter('postbox_classes_arsol-pfw-proposal_arsol_budget_estimates_metabox', 
    array($this, 'add_budget_metabox_classes'));

public function add_budget_metabox_classes($classes) {
    $classes[] = 'arsol-pfw-show-if-arsol_pfw_proposal_costing_type-is-budget';
    return $classes;
}
```

---

## Template Structure

### Main Header Template
**File**: `includes/ui/components/admin/section-edit-proposal-header.php`

**Purpose**: Container for proposal header with project relationship display

```php
// Project relationship display
if ($is_project_tied && $parent_project_data) {
    echo '<p class="order_number">';
    printf(__('Parent Project: %s', 'arsol-pfw'), esc_html($parent_project_data['title']));
    echo '</p>';
}
```

### Column 1: General Settings
**File**: `includes/ui/components/admin/section-edit-proposal-header-column-1.php`

**Contains**:
- Date fields (start, delivery, expiration)
- Customer field (editable/locked)
- Project lead field (editable/locked)
- Proposal status
- Cost proposal type (editable/locked)

### Column 2: Request Details (Optional)
**File**: `includes/ui/components/admin/section-edit-proposal-header-column-2.php`

**Contains**: Original request data (if proposal was created from request)

### Column 3: Proposal Summary
**File**: `includes/ui/components/admin/section-edit-proposal-header-column-3.php`

**Contains**: Proposal summary and review status

### Meta Box Templates

#### Budget Meta Box
**File**: Budget estimation forms and calculations

#### Quotation Meta Box  
**File**: Line item management and total calculations

---

## Hooks and Filters

### Action Hooks

```php
// Proposal lifecycle
do_action('arsol_pfw_proposal_created', $proposal_id);
do_action('arsol_pfw_proposal_updated', $proposal_id);
do_action('arsol_pfw_proposal_status_changed', $proposal_id, $old_status, $new_status);

// Project-tied proposals
do_action('arsol_pfw_project_tied_proposal_created', $proposal_id, $parent_project_id);
do_action('arsol_pfw_project_tied_proposal_updated', $proposal_id, $parent_project_id);

// Conversion
do_action('arsol_pfw_proposal_converting', $proposal_id);
do_action('arsol_pfw_proposal_converted', $proposal_id, $project_id);
```

### Filter Hooks

```php
// Field values
$customer_id = apply_filters('arsol_pfw_proposal_customer_id', $customer_id, $proposal_id);
$project_lead = apply_filters('arsol_pfw_proposal_project_lead', $project_lead, $proposal_id);

// Project-tied overrides
$locked_fields = apply_filters('arsol_pfw_project_tied_locked_fields', $locked_fields, $proposal_id);
$parent_data = apply_filters('arsol_pfw_project_tied_parent_data', $parent_data, $proposal_id);

// Conversion
$conversion_data = apply_filters('arsol_pfw_proposal_conversion_data', $conversion_data, $proposal_id);
```

---

## Best Practices

### For Normal Proposals

1. **Status Management**: Always set appropriate status before conversion
2. **Validation**: Ensure all required fields are completed
3. **Documentation**: Provide clear proposal descriptions
4. **Review Process**: Implement approval workflows

### For Project-Tied Proposals

1. **Parent Project Setup**: Ensure parent project has complete data
2. **Field Consistency**: Don't attempt to override locked fields
3. **Communication**: Use proposal content for project-specific details
4. **Status Tracking**: Monitor proposal status independently of project

### Development Guidelines

1. **Field Detection**: Always check `is_project_tied_proposal()` before field modifications
2. **JavaScript Compatibility**: Maintain element IDs for conditional logic
3. **Meta Key Consistency**: Use proper naming conventions
4. **Hook Usage**: Implement appropriate action/filter hooks

### CSS Styling

1. **Disabled Fields**: Use `.arsol-disabled-select` class consistently
2. **Visual Indicators**: Clearly show locked vs editable fields
3. **Responsive Design**: Ensure forms work on all screen sizes
4. **Accessibility**: Maintain proper ARIA labels and descriptions

---

## Troubleshooting

### Common Issues

#### Project-Tied Fields Not Locking
**Cause**: Missing parent project ID or incorrect meta keys
**Solution**: Verify `_arsol_pfw_parent_project_id` meta exists and is valid

#### JavaScript Conditional Logic Not Working
**Cause**: Element ID mismatch or Select2 interference
**Solution**: Ensure disabled fields maintain same ID without `wc-enhanced-select` class

#### URL Parameter Lost on Save
**Cause**: Missing JavaScript URL preservation
**Solution**: Verify JavaScript is properly preserving `parent_project` parameter

#### Conversion Button Disabled
**Cause**: Proposal not published or not approved status
**Solution**: Check proposal status and publication state

### Debug Information

```php
// Check proposal type
$is_project_tied = get_post_meta($proposal_id, '_arsol_pfw_parent_project_id', true);
echo $is_project_tied ? 'Project-Tied' : 'Normal';

// Check parent project data
$parent_project_id = get_post_meta($proposal_id, '_arsol_pfw_parent_project_id', true);
$parent_project = get_post($parent_project_id);
var_dump($parent_project);

// Check field values
$customer_id = get_post($proposal_id)->post_author;
$project_lead = get_post_meta($proposal_id, '_arsol_pfw_proposal_project_lead', true);
$cost_type = get_post_meta($proposal_id, '_arsol_pfw_proposal_costing_type', true);
```

---

## Conclusion

This reference provides comprehensive documentation for both normal and project-tied proposals in the Arsol Projects for WooCommerce plugin. Understanding the differences between these proposal types is crucial for proper implementation and user experience.

Key takeaways:
- **Normal proposals** offer full flexibility and conversion capabilities
- **Project-tied proposals** provide consistency through field inheritance
- **Proper field locking** ensures data integrity in project-tied scenarios
- **JavaScript compatibility** maintains conditional logic functionality
- **Clear user communication** explains field restrictions and relationships

For additional technical details, refer to the related documentation files in this directory.