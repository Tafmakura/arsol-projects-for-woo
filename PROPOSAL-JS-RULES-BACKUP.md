# Arsol Proposal Admin JavaScript Rules - Complete Documentation

## Overview
This document serves as a backup of all rules and functionality in the `arsol-pfw-admin-proposal.js` file before simplification.

## 1. Dependency Management

### Required Dependencies
- **jQuery**: Core functionality
- **wp-util**: WordPress template system 
- **underscore**: Debounce functionality (with fallback)
- **selectWoo**: Enhanced product search
- **arsol_budget_vars**: Currency formatting
- **arsol_pfw_proposal_quotation_vars**: AJAX endpoints and data

### Dependency Checks
```javascript
// Check jQuery availability
if (typeof $ === 'undefined') return;

// Check WordPress template function  
if (typeof wp === 'undefined' || typeof wp.template !== 'function') return;

// Safe debounce fallback if underscore unavailable
function safeDebounce(func, delay) {
    if (typeof _ !== 'undefined' && typeof _.debounce === 'function') {
        return _.debounce(func, delay);
    }
    // Custom fallback implementation
}
```

## 2. ArsolProposal Object (Main System)

### Purpose
Handles proposal type toggling, form cleanup, and initialization.

### Methods

#### `init()`
- Initializes event bindings
- Runs initial setup
- No page detection (handled by PHP)

#### `bindEvents()`
- **Toggle Event**: `#cost_proposal_type` change triggers section visibility
- **Form Submission**: Runs cleanup before save
- Uses event delegation for performance

#### `toggleCostProposalSections()`
- **Rule**: Hide all metaboxes first
- **Show Logic**:
  - `budget` → Show `#arsol_budget_estimates_metabox`
  - `quotation` → Show `#arsol_proposal_quotation_metabox`
  - `none` → Hide both

#### `presaveCleanup()`
- Orchestrates cleanup before form submission
- Calls `cleanupEmptyProposalSections()`

#### `cleanupEmptyProposalSections()`
- **Budget Cleanup Rule**: If no budget amount, details, or recurring budget → Set type to 'none'
- **Quotation Cleanup Rule**: If no line items exist → Set type to 'none'
- **Timing**: Only runs on form submission, not during user interaction

## 3. ArsolBudget Object (Budget Calculations)

### Purpose
Real-time budget total calculations and formatting.

### Methods

#### `init()`
- Binds events to budget input fields
- Calculates initial totals

#### `bindEvents()`
- **Input Events**: `.js-amount-input` on input
- **Billing Events**: `.js-billing-input` on change
- Uses `bind(this)` for context preservation

#### `formatPrice(price)`
- **Currency**: Uses `arsol_budget_vars.currency_symbol` (fallback: '$')
- **Format**: Number with 2 decimals, thousand separators
- **Output**: WooCommerce-compatible HTML structure

#### `updateBudgetTotals()`
- **One-time Budget**: First `.js-amount-input` value
- **Recurring Budget**: `.recurring-budget-amount-input` value
- **Billing Period Logic**: Reads interval and period from form, formats as `/2mo`, `/yr`, `/wk`, `/day`

## 4. ArsolProposalQuotation Object (Complex Quotation System)

### Purpose
Dynamic line item management, AJAX product search, real-time calculations.

### Properties
- `calculating`: Prevents recursive calculations
- `line_item_id`: Incremental ID counter

### Methods

#### `init()`
- Loads WordPress templates for each line item type
- Binds events, loads existing data, calculates totals

#### `addLineItem(e)` / `removeLineItem(e)`
- Add/remove table rows dynamically
- Recalculate totals after changes

#### `renderRow(type, data)`
- Type mapping to different table sections
- Initialize product search for product rows
- Handle subscription product styling

#### `initProductSearch($select)`
- SelectWoo with AJAX product search
- Minimum 2 characters, 250ms delay

#### `productChanged(e)`
- AJAX call to get product details
- Handle subscription vs regular products
- Show/hide date fields, store metadata

#### `loadExistingItems()`
- Load saved line items from PHP data
- Maintain ID sequence for new items

#### `calculateTotals()`
- Calculate totals for all sections
- Handle subscription billing cycles
- Update multiple display elements

## 5. Subscription Product Rules

### Detection Logic
```javascript
var isSubscription = product.product_type === 'subscription' || 
                    product.product_type === 'subscription_variation';
```

### Subscription-Specific Handling
1. **Date Fields**: Show start date input
2. **Metadata Storage**: billing_interval, billing_period, sign_up_fee
3. **Calculation**: Recurring price display + sign-up fees in one-time total
4. **Visual Indicators**: CSS class `arsol-subscription-product`

## 6. Key Selectors and Elements

### Proposal Toggle
- `#cost_proposal_type`: Main dropdown
- `#arsol_budget_estimates_metabox`: Budget section
- `#arsol_proposal_quotation_metabox`: Quotation section

### Budget System
- `.js-amount-input`: Amount inputs
- `.js-billing-input`: Billing configuration
- `.recurring-budget-amount-input`: Recurring amount

### Quotation System
- `#proposal_quotation_builder`: Main container
- `.arsol-quantity-input`: Product quantities
- `.arsol-price-input`: Regular prices
- `.arsol-amount-input`: Fee amounts

### Display Elements
- `#budget-onetime-total-display`: One-time budget total
- `#one-time-total-display`: Quotation one-time total
- `#product-subtotal-display`: Product section subtotal

## 7. WordPress Integration

### Template System
- Uses `wp.template()` for dynamic row generation
- Templates: `arsol-product-line-item`, `arsol-onetime-fee-line-item`, etc.

### AJAX Integration
- `arsol_proposal_quotation_ajax_search_products`: Product search
- `arsol_proposal_quotation_ajax_get_product_details`: Product details

### Localization Variables
- `arsol_budget_vars`: Currency symbols
- `arsol_pfw_proposal_quotation_vars`: AJAX URLs, nonces, existing data

## 8. Performance Features

### Debouncing
- 300ms delay on calculation triggers
- Prevents excessive calculations during typing

### Event Delegation
- Events bound to containers for dynamic content

### Calculation Protection
- `calculating` flag prevents recursive calculations

This documentation captures all the rules and functionality before simplification. 