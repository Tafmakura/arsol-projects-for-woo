# Meta Key Standardization Summary

## Overview
This document summarizes the meta key standardization changes implemented in the Arsol Projects for Woo plugin.

## ✅ Completed Changes

### 1. Data Store Updates
- **Proposal Data Store**: Updated to use new meta key naming convention
  - `_arsol_pfw_proposal_project_lead` → `_arsol_pfw_proposed_project_lead`
  - `_arsol_pfw_proposed_start_date` → `_arsol_pfw_proposed_project_start_date`
  - `_arsol_pfw_proposed_due_date` → `_arsol_pfw_proposed_project_due_date`
  - `_arsol_pfw_proposal_parent_project_id` → `_arsol_pfw_parent_project_id`
  - Budget consolidated to `_arsol_pfw_proposed_project_budget_line_items` (array)
  - Quotation consolidated to `_arsol_pfw_proposed_project_quotation_line_items` (array)

- **Project Data Store**: Updated to inherit array structures
  - Added complex meta keys for inherited proposal data
  - `_arsol_pfw_proposed_project_budget_line_items` (inherited from proposal)
  - `_arsol_pfw_proposed_project_quotation_line_items` (inherited from proposal)

- **Request Data Store**: Updated to use standardized naming
  - `_arsol_pfw_request_parent_project_id` → `_arsol_pfw_parent_project_id`
  - Removed timeline fields

### 2. Entity Class Updates
- **Proposal Entity**: Updated budget methods to use array structure
  - `get_budget_onetime_amount()` now reads from array structure
  - `set_budget_onetime_amount()` now writes to array structure
  - `get_budget_recurring_amount()` now reads from array structure
  - `set_budget_recurring_amount()` now writes to array structure

### 3. Conversion Handler Updates
- **Request to Proposal**: Updated meta key mappings
  - `_arsol_pfw_proposed_start_date` → `_arsol_pfw_proposed_project_start_date`
  - `_arsol_pfw_proposed_due_date` → `_arsol_pfw_proposed_project_due_date`
  - `_arsol_pfw_proposal_project_lead` → `_arsol_pfw_proposed_project_lead`
  - `_arsol_pfw_parent_request_id` → `_arsol_pfw_request_id`

- **Proposal to Project**: Updated meta key mappings
  - `_arsol_pfw_proposal_project_lead` → `_arsol_pfw_proposed_project_lead`
  - `_arsol_pfw_proposed_due_date` → `_arsol_pfw_proposed_project_due_date`

### 4. Template Updates
- **Admin Request Template**: Removed timeline and priority fields
- **Email Templates**: Removed timeline references
  - `email-admin-new-request.php`: Removed timeline and priority
  - `email-proposal-ready.php`: Removed timeline

### 5. Documentation Updates
- **Meta Keys Reference**: Updated to reflect new naming convention
- **Data Access Patterns**: Updated to show array-based structures
- **Migration Status**: Updated to show completed changes

## 🔄 In Progress

### 1. Admin Handler Updates
- **Proposal Admin Handler**: Need to update meta key references
- **Project Admin Handler**: Need to update meta key references
- **Request Admin Handler**: Need to update meta key references

### 2. Frontend Template Updates
- **Sidebar Meta Class**: Partially updated, need to complete
- **Admin List Classes**: Need to update meta key references
- **UI Components**: Need to update meta key references

### 3. WooCommerce Integration Updates
- **Biller Class**: Need to update meta key references
- **Integration Class**: Need to update meta key references
- **Email Templates**: Need to update meta key references

## ❌ Still To Do

### 1. Admin List Classes
- Update `class-arsol-pfw-cpt-proposal-admin-list.php`
- Update `class-arsol-pfw-cpt-project-admin-list.php`
- Update `class-arsol-pfw-cpt-request-admin-list.php`

### 2. UI Components
- Update `section-edit-project-header-column-2.php`
- Update `section-edit-proposal-header-column-3.php`
- Update `subsection-edit-project-proposal-details.php`

### 3. WooCommerce Integration
- Update `class-arsol-pfw-wc-biller-invoice.php`
- Update `class-arsol-pfw-wc-integration.php`
- Update `class-arsol-pfw-wc-logs.php`

### 4. Conversion Classes
- Update `class-arsol-pfw-cpt-proposal-conversion.php`
- Update `class-arsol-pfw-cpt-request-conversion.php`

### 5. Setup Classes
- Update `class-arsol-pfw-cpt-proposal-setup.php`
- Update `class-arsol-pfw-cpt-request-setup.php`

## 🎯 Key Changes Made

### 1. Meta Key Naming Convention
- **Proposal Fields**: Added "project" prefix for key fields
  - `_arsol_pfw_proposed_project_start_date`
  - `_arsol_pfw_proposed_project_due_date`
  - `_arsol_pfw_proposed_project_lead`

- **Request Fields**: Added "project" prefix for key fields
  - `_arsol_pfw_requested_project_budget`
  - `_arsol_pfw_requested_project_start_date`
  - `_arsol_pfw_requested_project_due_date`

- **Inherited Data**: Removed redundant prefixes
  - `_arsol_pfw_proposal_request_id` → `_arsol_pfw_request_id`
  - `_arsol_pfw_proposal_request_details` → `_arsol_pfw_request_details`

### 2. Budget Structure Changes
- **Proposal Budgets**: Consolidated to array structure
  - `_arsol_pfw_proposed_project_budget_line_items` (array)
  - Contains onetime, recurring, notes, type

- **Project Budgets**: Inherit array structure from proposals
  - Same array structure as proposals
  - No individual budget fields

### 3. Removed Fields
- **Timeline Fields**: Removed from all CPTs
  - `_arsol_pfw_proposal_timeline`
  - `_arsol_pfw_request_timeline`
  - `_arsol_pfw_project_timeline`

- **Priority Fields**: Removed from all CPTs
  - `_arsol_pfw_request_priority`

- **Redundant Flags**: Removed unnecessary flags
  - `_arsol_pfw_is_project_tied_proposal` (replaced by `_arsol_pfw_parent_project_id`)

## 📋 Next Steps

1. **Complete Admin Handler Updates**: Update all admin save/read methods
2. **Complete Frontend Updates**: Update all template and component files
3. **Complete WooCommerce Updates**: Update all integration files
4. **Test All Changes**: Verify functionality across all components
5. **Update Documentation**: Complete all documentation updates

## 🚨 Important Notes

- **No Backward Compatibility**: Changes are breaking changes
- **No Migration Script**: Existing data will need manual migration
- **Array-Based Budgets**: All budget data now uses array structures
- **Consistent Naming**: All meta keys follow standardized naming convention 