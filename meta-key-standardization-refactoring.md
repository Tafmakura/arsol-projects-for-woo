# Meta Key Standardization & Content Flow Refactoring

## Overview
This document outlines the comprehensive refactoring of meta keys and content flow across all three CPTs (Project Requests, Project Proposals, Active Projects) to create a standardized, logical, and maintainable system.

## 🎯 Objectives

1. **Standardize all meta keys** with consistent `_arsol_pfw_` prefixes
2. **Clean up content flow** to eliminate duplication and create clear separation
3. **Implement type-aware data handling** for Budget vs Quotation proposals
4. **Create logical naming conventions** that reflect data context and source
5. **Preserve historical data** while avoiding unnecessary duplication

## 📋 Phase 1: Content Flow Restructuring

### Current Problems
- Request content gets duplicated in proposals (`post_content` + meta field)
- Proposal-prefixed meta keys remain in projects instead of being renamed
- No clear separation between original request data and proposal responses

### Solution: Clean Content Flow

#### Request → Proposal Conversion
```php
// BEFORE: Duplicated content
$proposal_args = array(
    'post_content' => $request_post->post_content,  // ❌ Creates duplication
);
update_post_meta($proposal_id, '_arsol_pfw_proposal_request_details', $request_post->post_content);

// AFTER: Clean separation
$proposal_args = array(
    'post_content' => '',  // ✅ Empty slate for proposal writing
);
update_post_meta($proposal_id, '_arsol_pfw_proposal_request_details', $request_post->post_content);
```

#### Proposal → Project Conversion
```php
// NEW: Preserve proposal content in project meta
update_post_meta($project_id, '_arsol_pfw_project_proposal_details', $proposal_post->post_content);

// NEW: Rename request data with project context
$request_meta_mapping = array(
    '_arsol_pfw_proposal_request_details' => '_arsol_pfw_project_request_details',
    '_arsol_pfw_proposal_request_title' => '_arsol_pfw_project_request_title',
    '_arsol_pfw_proposal_request_date' => '_arsol_pfw_project_request_date',
    '_arsol_pfw_proposal_request_budget' => '_arsol_pfw_project_request_budget',
    '_arsol_pfw_proposal_request_start_date' => '_arsol_pfw_project_request_start_date',
    '_arsol_pfw_proposal_request_delivery_date' => '_arsol_pfw_project_request_delivery_date',
    '_arsol_pfw_proposal_request_attachments' => '_arsol_pfw_project_request_attachments',
);

// NEW: Rename proposal data with project context
$proposal_meta_mapping = array(
    '_arsol_pfw_proposal_start_date' => '_arsol_pfw_project_proposal_start_date',
    '_arsol_pfw_proposal_delivery_date' => '_arsol_pfw_project_proposal_delivery_date',
    '_arsol_pfw_proposal_notes' => '_arsol_pfw_project_proposal_notes',
    '_arsol_pfw_proposal_timeline' => '_arsol_pfw_project_proposal_timeline',
    '_arsol_pfw_proposal_costing_type' => '_arsol_pfw_project_proposal_costing_type',
);
```

## 📊 Phase 2: Type-Aware Data Handling

### Budget Proposals → Projects
Preserve ALL budget details with project context (no working meta duplication):

```php
$budget_meta_mapping = array(
    '_arsol_pfw_proposal_budget_onetime_amount' => '_arsol_pfw_project_proposal_budget_onetime_amount',
    '_arsol_pfw_proposal_budget_onetime_amount_details' => '_arsol_pfw_project_proposal_budget_onetime_amount_details',
    '_arsol_pfw_proposal_budget_recurring_amount' => '_arsol_pfw_project_proposal_budget_recurring_amount',
    '_arsol_pfw_proposal_budget_recurring_amount_details' => '_arsol_pfw_project_proposal_budget_recurring_amount_details',
    '_arsol_pfw_proposal_budget_recurring_amount_billing_interval' => '_arsol_pfw_project_proposal_budget_recurring_amount_billing_interval',
    '_arsol_pfw_proposal_budget_recurring_amount_billing_period' => '_arsol_pfw_project_proposal_budget_recurring_amount_billing_period',
    '_arsol_pfw_proposal_budget_recurring_billing_start_date' => '_arsol_pfw_project_proposal_budget_recurring_billing_start_date',
);
```

### Quotation Proposals → Projects
Preserve quotation details with key totals in quotation context:

```php
$quotation_meta_mapping = array(
    '_arsol_pfw_proposal_quotation_line_items' => '_arsol_pfw_project_proposal_quotation_line_items',
    '_arsol_pfw_proposal_quotation_onetime_total' => '_arsol_pfw_project_proposal_quotation_onetime_total',
    '_arsol_pfw_proposal_quotation_recurring_totals_grouped' => '_arsol_pfw_project_proposal_quotation_recurring_average_total',
    '_arsol_pfw_proposal_quotation_currency' => '_arsol_pfw_project_proposal_quotation_currency',
    '_arsol_pfw_proposal_quotation_currency_symbol' => '_arsol_pfw_project_proposal_quotation_currency_symbol',
);
```

**Key Changes:**
- ❌ Remove duplication to generic `_project_budget` fields
- ✅ Maintain data in proper context (budget vs quotation)
- ✅ Include delivery dates for both types
- ✅ Preserve all historical details

## 🔧 Phase 3: Core Meta Key Standardization

### Active Projects Meta Keys (10 total)
```
Current                                    → Standardized
_project_lead                             → _arsol_pfw_project_lead
_project_start_date                       → _arsol_pfw_project_start_date
_project_due_date                         → _arsol_pfw_project_due_date
_project_woocommerce_order_id             → _arsol_pfw_project_woocommerce_order_id
_project_woocommerce_subscription_id      → _arsol_pfw_project_woocommerce_subscription_id
_project_order_creation_note              → _arsol_pfw_project_order_creation_note
_project_order_creation_error             → _arsol_pfw_project_order_creation_error
_project_billing_interval                 → _arsol_pfw_project_billing_interval
_project_billing_period                   → _arsol_pfw_project_billing_period
_project_recurring_start_date             → _arsol_pfw_project_recurring_start_date

// REMOVE (no longer needed due to clean data flow):
_project_budget                           → ❌ DELETED
_project_recurring_budget                 → ❌ DELETED
```

### Project Requests Meta Keys (3 total)
```
Current                    → Standardized
_request_budget           → _arsol_pfw_request_budget
_request_start_date       → _arsol_pfw_request_start_date
_request_delivery_date    → _arsol_pfw_request_delivery_date
```

### Project Proposals Meta Keys
✅ **Already standardized** (25 keys with `_arsol_pfw_proposal_` prefix)

## 📁 Implementation Impact

### Files Requiring Updates

#### Phase 1: Content Flow (5 files)
- `includes/workflow/class-workflow-handler.php` - Conversion logic
- `includes/ui/components/admin/section-edit-proposal-header-column-2.php` - Request details display
- `includes/ui/components/admin/section-edit-active-header-column-2.php` - Project proposal details
- `includes/ui/components/admin/section-edit-active-header.php` - Project summary logic
- `includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-setup.php` - Request details section

#### Phase 2: Type-Aware Handling (2 files)
- `includes/workflow/class-workflow-handler.php` - Enhanced conversion logic
- `includes/ui/components/admin/section-edit-active-header-column-2.php` - Display logic updates

#### Phase 3: Core Standardization (~19 files)
- **Active Projects**: ~11 files (admin classes, templates, frontend components)
- **Project Requests**: ~8 files (admin classes, templates, frontend components)

## 🎯 Final Meta Key Structure

### Project Requests (3 keys)
```
_arsol_pfw_request_budget
_arsol_pfw_request_start_date  
_arsol_pfw_request_delivery_date
```

### Project Proposals (25 keys - already standardized)
```
_arsol_pfw_proposal_* (all existing keys)
```

### Active Projects (10 core + inherited keys)
**Core Project Keys:**
```
_arsol_pfw_project_lead
_arsol_pfw_project_start_date
_arsol_pfw_project_due_date
_arsol_pfw_project_woocommerce_order_id
_arsol_pfw_project_woocommerce_subscription_id
_arsol_pfw_project_order_creation_note
_arsol_pfw_project_order_creation_error
_arsol_pfw_project_billing_interval
_arsol_pfw_project_billing_period
_arsol_pfw_project_recurring_start_date
```

**Inherited Request Data:**
```
_arsol_pfw_project_request_details
_arsol_pfw_project_request_title
_arsol_pfw_project_request_date
_arsol_pfw_project_request_budget
_arsol_pfw_project_request_start_date
_arsol_pfw_project_request_delivery_date
_arsol_pfw_project_request_attachments
```

**Inherited Proposal Data:**
```
_arsol_pfw_project_proposal_details
_arsol_pfw_project_proposal_start_date
_arsol_pfw_project_proposal_delivery_date
_arsol_pfw_project_proposal_notes
_arsol_pfw_project_proposal_timeline
_arsol_pfw_project_proposal_costing_type

// Budget-specific:
_arsol_pfw_project_proposal_budget_onetime_amount
_arsol_pfw_project_proposal_budget_onetime_amount_details
_arsol_pfw_project_proposal_budget_recurring_amount
_arsol_pfw_project_proposal_budget_recurring_amount_details
_arsol_pfw_project_proposal_budget_recurring_amount_billing_interval
_arsol_pfw_project_proposal_budget_recurring_amount_billing_period
_arsol_pfw_project_proposal_budget_recurring_billing_start_date

// Quotation-specific:
_arsol_pfw_project_proposal_quotation_line_items
_arsol_pfw_project_proposal_quotation_onetime_total
_arsol_pfw_project_proposal_quotation_recurring_average_total
_arsol_pfw_project_proposal_quotation_currency
_arsol_pfw_project_proposal_quotation_currency_symbol
```

## ✅ Benefits

1. **Clean Content Flow**: No duplication, clear separation of concerns
2. **Logical Naming**: Context-appropriate meta keys reflecting data source
3. **Type-Aware Processing**: Budget vs Quotation data handled appropriately  
4. **Historical Preservation**: All original data maintained but properly contextualized
5. **Consistent Standards**: All meta keys follow `_arsol_pfw_` prefix pattern
6. **Conflict Prevention**: Reduced risk of conflicts with other plugins
7. **Easier Maintenance**: Predictable naming makes code easier to understand
8. **Future-Proofing**: Consistent structure for new features

## ⚠️ Implementation Notes

- **No Backward Compatibility**: Clean break approach for better long-term maintainability
- **Database Cleanup**: Old meta keys will remain but become unused
- **Form Field Updates**: HTML form field names need updating to match new meta keys
- **JavaScript Updates**: Any JS referencing form fields requires updates
- **Testing Required**: Verify all CRUD operations work correctly after changes

## 🚀 Execution Plan

1. **Phase 1**: Implement content flow restructuring
2. **Phase 2**: Add type-aware data handling  
3. **Phase 3**: Standardize core meta keys for Active Projects
4. **Phase 4**: Standardize core meta keys for Project Requests
5. **Testing**: Comprehensive testing of all conversion flows
6. **Documentation**: Update any developer documentation

---

**Total Meta Keys After Standardization:**
- **Project Requests**: 3 keys
- **Project Proposals**: 25 keys (already done)
- **Active Projects**: ~35 keys (10 core + 25 inherited)
- **Grand Total**: ~63 standardized meta keys across all CPTs 