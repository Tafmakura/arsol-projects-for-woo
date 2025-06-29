# WordPress Plugin Meta Key Standardization Operation

## Project Overview
**RENAMING OPERATION ONLY**: Standardize all project proposal meta keys in the WordPress/WooCommerce plugin to use consistent `_arsol_pfw_` prefixing with logical hierarchical organization. 

**Key Constraints:**
- **RENAME ONLY** - No file deletion or creation unless absolutely necessary
- **NO backward compatibility** or migration required
- **NO data migration** - Clean break from legacy naming
- Focus on updating existing code references only

## Meta Key Mapping (25 Total)

### Core Settings (2 fields)
```
_cost_proposal_type → _arsol_pfw_proposal_costing_type
_proposal_secondary_status → _arsol_pfw_proposal_secondary_status
```

### Dates (3 fields)
```
_proposal_start_date → _arsol_pfw_proposal_start_date
_proposal_delivery_date → _arsol_pfw_proposal_delivery_date
_proposal_expiration_date → _arsol_pfw_proposal_expiration_date
```

### Budget Fields (6 fields)
```
_proposal_budget → _arsol_pfw_proposal_budget_onetime_amount
_proposal_budget_details → _arsol_pfw_proposal_budget_onetime_amount_details
_proposal_recurring_budget → _arsol_pfw_proposal_budget_recurring_amount
_proposal_recurring_budget_details → _arsol_pfw_proposal_budget_recurring_amount_details
_proposal_billing_interval → _arsol_pfw_proposal_budget_recurring_amount_billing_interval
_proposal_billing_period → _arsol_pfw_proposal_budget_recurring_amount_billing_period
_proposal_recurring_start_date → _arsol_pfw_proposal_budget_recurring_billing_start_date
```

### Quotation Fields (5 fields)
```
_arsol_proposal_quotation_line_items → _arsol_pfw_proposal_quotation_line_items
_arsol_proposal_one_time_total → _arsol_pfw_proposal_quotation_onetime_total
_arsol_proposal_recurring_totals_grouped → _arsol_pfw_proposal_quotation_recurring_totals_grouped
_arsol_proposal_currency → _arsol_pfw_proposal_quotation_currency
_arsol_proposal_currency_symbol → _arsol_pfw_proposal_quotation_currency_symbol
```

### Content Fields (3 fields)
```
_arsol_proposal_notes → _arsol_pfw_proposal_notes
_proposal_timeline → _arsol_pfw_proposal_timeline
_proposal_attachments → _arsol_pfw_proposal_attachments
```

### Customer Field (1 field)
```
_proposal_customer_id → _arsol_pfw_proposal_customer_id
```

### Original Request Data (3 fields)
```
_original_request_date → _arsol_pfw_proposal_request_date
_original_request_title → _arsol_pfw_proposal_request_title
_original_request_content → _arsol_pfw_proposal_request_details
```

### Project Reference (1 field)
```
_original_proposal_id → _arsol_pfw_project_original_proposal_id
```

## File Impact Analysis

### JavaScript Files (1 file - MAJOR changes)
- `assets/js/arsol-pfw-admin-cpt-proposal.js` - Update all selectors, form names, and meta key references

### CSS Files (1 file - MINOR changes)
- `assets/css/arsol-pfw-admin.css` - Update metabox IDs and related class names

### PHP Files (21+ files)

#### Core Admin Classes (MAJOR changes needed)
1. `includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal.php`
2. `includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal-budget.php`
3. `includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal-quotation.php`
4. `includes/workflow/class-workflow-handler.php`
5. `includes/classes/class-woocommerce-logs.php`
6. `includes/classes/class-woocommerce-biller-invoice.php`

#### UI Components (MAJOR changes needed)
7. `includes/ui/components/admin/section-edit-proposal-header.php`
8. `includes/ui/components/admin/section-edit-proposal-header-column-1.php`
9. `includes/ui/components/admin/section-edit-proposal-header-column-3.php`

#### UI Components (MINOR changes needed)
10. `includes/ui/components/admin/section-edit-proposal-header-column-2.php`
11. `includes/ui/components/admin/section-edit-active-header.php`
12. `includes/ui/components/admin/section-edit-active-header-column-2.php`

#### Other Classes (MINOR changes needed)
13. `includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposals.php`
14. `includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-setup.php`
15. `includes/custom-post-types/project-proposal/class-project-proposal-cpt-frontend-handler.php`
16. `includes/ui/templates/frontend/page-project-proposal.php`
17. `includes/classes/class-assets.php`
18. `includes/classes/class-woocommerce-mailer.php`
19. `includes/custom-post-types/project/class-project-cpt-admin-setup.php`
20. `docs/sidebar-hooks-examples.php`

## HTML Form Elements to Update

### Form Field Names (12+ fields)
```
proposal_budget_details → arsol_pfw_proposal_budget_onetime_amount_details
proposal_recurring_budget_details → arsol_pfw_proposal_budget_recurring_amount_details
proposal_billing_interval → arsol_pfw_proposal_budget_recurring_amount_billing_interval
proposal_billing_period → arsol_pfw_proposal_budget_recurring_amount_billing_period
proposal_recurring_start_date → arsol_pfw_proposal_budget_recurring_billing_start_date
proposal_start_date → arsol_pfw_proposal_start_date
proposal_delivery_date → arsol_pfw_proposal_delivery_date
proposal_expiration_date → arsol_pfw_proposal_expiration_date
arsol_proposal_notes → arsol_pfw_proposal_notes
cost_proposal_type → arsol_pfw_proposal_costing_type
line_items_one_time_total → arsol_pfw_proposal_quotation_onetime_total
(and related form fields)
```

### Element IDs (15+ elements)
Update all HTML element IDs that reference the old meta key names.

### CSS Classes (6+ classes)
Update CSS classes that reference proposal-specific styling.

## Implementation Requirements

### 🔄 **RENAMING OPERATIONS ONLY**

### 1. Meta Key Operations (RENAME ONLY)
- **FIND & REPLACE** ALL `get_post_meta()` calls with old meta keys
- **FIND & REPLACE** ALL `update_post_meta()` calls with old meta keys
- **FIND & REPLACE** ALL `delete_post_meta()` calls with old meta keys
- **FIND & REPLACE** ALL `$_POST` array key references

### 2. JavaScript Updates (RENAME ONLY)
- **FIND & REPLACE** ALL DOM selectors (`#`, `.`, `[name=""]`)
- **FIND & REPLACE** ALL form field references
- **FIND & REPLACE** ALL AJAX data keys
- **FIND & REPLACE** ALL event handlers

### 3. CSS Updates (RENAME ONLY)
- **FIND & REPLACE** metabox IDs
- **FIND & REPLACE** form-related class names
- **MAINTAIN** existing styling functionality

### 4. PHP Form Processing (RENAME ONLY)
- **FIND & REPLACE** `$_POST` key checks
- **FIND & REPLACE** `sanitize_*()` function calls
- **UPDATE** validation logic references only
- **UPDATE** nonce field names if referenced

### 5. Database Operations (RENAME ONLY)
- **FIND & REPLACE** meta key arrays in workflow handler
- **FIND & REPLACE** meta copying logic
- **FIND & REPLACE** deletion logic

## Critical Notes

### 🔄 **RENAMING OPERATION CONSTRAINTS**
- **NO FILE CREATION** - Do not create new files unless absolutely necessary
- **NO FILE DELETION** - Do not delete existing files
- **NO STRUCTURAL CHANGES** - Maintain existing file structure and organization
- **CONTENT UPDATES ONLY** - Focus solely on updating references within existing files

### ⚠️ Breaking Changes
- **NO backward compatibility**
- **NO data migration** 
- All existing proposal data will become inaccessible until database is manually updated
- This is an intentional clean break from legacy naming

### 🎯 Naming Consistency
- All meta keys MUST start with `_arsol_pfw_`
- Use clear hierarchical structure: `category_subcategory_field`
- Maintain logical grouping (budget vs quotation vs core fields)
- Use consistent terminology (onetime vs recurring)

### 🔍 Testing Requirements
- Test ALL form submissions
- Test ALL meta data retrieval
- Test proposal creation workflow
- Test proposal-to-project conversion
- Test WooCommerce billing operations
- Test frontend proposal display

## Execution Order

1. **Phase 1**: Update all PHP meta key operations
2. **Phase 2**: Update JavaScript selectors and form handling  
3. **Phase 3**: Update CSS classes and IDs
4. **Phase 4**: Update HTML form elements and names
5. **Phase 5**: Test all functionality end-to-end

## Validation Checklist

- [ ] All 25 meta keys updated across all files
- [ ] All JavaScript selectors updated
- [ ] All CSS references updated  
- [ ] All HTML form names updated
- [ ] All PHP `$_POST` references updated
- [ ] All database operations tested
- [ ] All admin forms functional
- [ ] All frontend displays functional
- [ ] All workflow conversions functional
- [ ] All WooCommerce integrations functional

---

**Total Scope**: 25 meta keys × ~48 files = Comprehensive codebase update required 