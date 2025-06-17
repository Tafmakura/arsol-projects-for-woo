# Arsol Proposal Admin JavaScript Rules - Complete Documentation

This document serves as a backup of all rules and functionality in the `arsol-pfw-admin-proposal.js` file before simplification.

## 1. Core Dependencies
- jQuery (required)
- wp-util (WordPress template system)
- underscore (debounce functionality with fallback)
- selectWoo (enhanced product search)
- arsol_budget_vars (currency formatting)
- arsol_pfw_proposal_quotation_vars (AJAX endpoints and data)

## 2. ArsolProposal Object
**Purpose**: Main proposal type toggling and form cleanup

**Key Methods**:
- `toggleCostProposalSections()`: Show/hide metaboxes based on proposal type
- `presaveCleanup()`: Clean empty sections before form submission
- `cleanupEmptyProposalSections()`: Auto-set type to 'none' if no content

**Toggle Rules**:
- `budget` → Show `#arsol_budget_estimates_metabox`
- `quotation` → Show `#arsol_proposal_quotation_metabox`
- `none` → Hide both

## 3. ArsolBudget Object
**Purpose**: Real-time budget calculations

**Key Methods**:
- `updateBudgetTotals()`: Calculate one-time and recurring totals
- `formatPrice()`: WooCommerce-compatible currency formatting

**Calculation Logic**:
- One-time: First `.js-amount-input` value
- Recurring: `.recurring-budget-amount-input` with billing period
- Format billing cycles: `/2mo`, `/yr`, `/wk`, `/day`

## 4. ArsolProposalQuotation Object
**Purpose**: Complex quotation system with dynamic line items

**Core Properties**:
- `calculating`: Prevents recursive calculations
- `line_item_id`: Incremental ID counter

**Key Methods**:
- `addLineItem()` / `removeLineItem()`: Dynamic row management
- `renderRow()`: Create rows from WordPress templates
- `initProductSearch()`: SelectWoo AJAX product search
- `productChanged()`: Handle product selection and subscription detection
- `calculateTotals()`: Real-time total calculations
- `loadExistingItems()`: Load saved data from PHP

**Template Types**:
- `arsol-product-line-item`
- `arsol-onetime-fee-line-item`
- `arsol-recurring-fee-line-item`
- `arsol-shipping-fee-line-item`

## 5. Subscription Product Handling
**Detection**: `product_type === 'subscription' || 'subscription_variation'`

**Subscription Rules**:
- Show start date field
- Store billing metadata (interval, period, sign_up_fee)
- Display recurring price format: "$99.00 /mo"
- Add sign-up fees to one-time total
- Apply `arsol-subscription-product` CSS class

## 6. AJAX Integration
- Product search: `arsol_proposal_quotation_ajax_search_products`
- Product details: `arsol_proposal_quotation_ajax_get_product_details`
- Nonce verification required
- Response format: `{success: true, data: {}}`

## 7. Performance Optimizations
- Debouncing: 300ms delay on calculations
- Event delegation for dynamic content
- Calculation flags to prevent recursion

## 8. Key Selectors
- `#cost_proposal_type`: Main proposal type dropdown
- `#proposal_quotation_builder`: Quotation container
- `.js-amount-input`: Budget amount inputs
- `.arsol-quantity-input`: Product quantities
- `.arsol-price-input`: Product prices

This documentation preserves all rules before simplification. 