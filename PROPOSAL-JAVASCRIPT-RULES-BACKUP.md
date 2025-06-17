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
```html
<span class="woocommerce-Price-amount amount">
    <bdi><span class="woocommerce-Price-currencySymbol">$</span>99.00</bdi>
</span>
```

#### `updateBudgetTotals()`
- **One-time Budget**: First `.js-amount-input` value
- **Recurring Budget**: `.recurring-budget-amount-input` value
- **Billing Period Logic**:
  - Reads interval and period from form
  - Formats as `/2mo`, `/yr`, `/wk`, `/day`
  - Updates multiple display elements

## 4. ArsolProposalQuotation Object (Complex Quotation System)

### Purpose
Dynamic line item management, AJAX product search, real-time calculations.

### Properties
- `calculating`: Prevents recursive calculations
- `line_item_id`: Incremental ID counter

### Initialization

#### `init()`
- **Templates**: Loads WordPress templates for each line item type
  - `wp.template('arsol-product-line-item')`
  - `wp.template('arsol-onetime-fee-line-item')`
  - `wp.template('arsol-recurring-fee-line-item')`
  - `wp.template('arsol-shipping-fee-line-item')`
- Binds events, loads existing data, calculates totals

### Event Management

#### `bindEvents()`
- **Add/Remove**: Event delegation on `#proposal_quotation_builder`
- **Product Selection**: Handles product dropdown changes
- **Debounced Calculations**: 300ms delay on input changes
- **Section-Specific Events**: Different selectors for each section

### Line Item Management

#### `addLineItem(e)`
- **Flow**: Increment ID → Render row → Calculate totals
- **Data**: `{ id: ++this.line_item_id }`

#### `removeLineItem(e)`
- **Flow**: Remove closest `tr` → Calculate totals

#### `renderRow(type, data)`
- **Type Mapping**:
  - `product` → `#product-lines-body`
  - `onetime-fee` → `#onetime-fee-lines-body`
  - `recurring-fee` → `#recurring-fee-lines-body`
  - `shipping-fee` → `#shipping-lines-body`
- **Post-Render**: Initialize product search for product rows
- **Subscription Handling**: Show/hide date fields based on product type

### Product Search System

#### `initProductSearch($select)`
- **Technology**: SelectWoo with AJAX
- **Endpoint**: `arsol_proposal_quotation_ajax_search_products`
- **Config**:
  - Minimum input: 2 characters
  - Delay: 250ms
  - Placeholder: "Search for products..."

#### `productChanged(e)`
- **Trigger**: Product selection from dropdown
- **AJAX Call**: `arsol_proposal_quotation_ajax_get_product_details`
- **Data Handling**:
  - Regular products: Set price fields
  - **Subscription Products**:
    - Show start date field
    - Store billing metadata in hidden fields
    - Add `arsol-subscription-product` class
    - Handle sign-up fees

### Data Loading

#### `loadExistingItems()`
- **Sources**: `arsol_pfw_proposal_quotation_vars.line_items`
- **Categories**: products, one_time_fees, recurring_fees, shipping_fees
- **ID Management**: Maintains highest ID for new items

### Calculation Engine

#### `calculateTotals()`
- **Recursion Protection**: `calculating` flag
- **Product Calculation Logic**:
  - **Regular Products**: Quantity × Price → One-time total
  - **Subscription Products**: 
    - Show billing cycle in subtotal
    - Add sign-up fees to one-time total
    - Format: "$99.00 /mo"
- **Fee Calculations**: 
  - One-time fees → One-time total
  - Recurring fees → Show with billing cycle
  - Shipping → One-time total
- **Display Updates**: Multiple total elements updated

#### `formatPrice(price)`
- **Currency**: `arsol_pfw_proposal_quotation_vars.currency_symbol`
- **Format**: Same as budget formatting
- **Usage**: All price displays

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

### Billing Period Formatting
```javascript
var periodText = period === 'month' ? 'mo' : 
                period === 'year' ? 'yr' : 
                period === 'week' ? 'wk' : 
                period === 'day' ? 'day' : period;
var intervalText = interval > 1 ? interval : '';
var billingText = '/' + intervalText + periodText;
```

## 6. Form Field Selectors

### Budget System
- `.js-amount-input`: Amount inputs
- `.js-billing-input`: Billing configuration
- `.recurring-budget-amount-input`: Recurring amount
- `.billing-interval`, `.billing-period`: Billing settings

### Quotation System
- `.arsol-quantity-input`: Product quantities
- `.arsol-price-input`: Regular prices
- `.arsol-sale-price-input`: Sale prices
- `.arsol-amount-input`: Fee amounts
- `.arsol-billing-select`: Billing dropdowns
- `.arsol-description-input`: Product/fee descriptions

### Display Elements
- `#budget-onetime-total-display`: One-time budget total
- `#budget-recurring-total-display`: Recurring budget total
- `#one-time-total-display`: Quotation one-time total
- `#product-subtotal-display`: Product section subtotal

## 7. WordPress Integration

### Template System
- Uses `wp.template()` for dynamic row generation
- Templates defined in PHP with `tmpl-` prefix
- Data passed via `{{ data.field }}` syntax

### AJAX Integration
- Endpoints: WordPress admin-ajax.php
- Nonce verification required
- Response format: `{success: true, data: {}}`

### Localization Variables
- `arsol_budget_vars`: Currency symbols
- `arsol_pfw_proposal_quotation_vars`: AJAX URLs, nonces, existing data

## 8. Performance Optimizations

### Debouncing
- 300ms delay on calculation triggers
- Prevents excessive calculations during typing

### Event Delegation
- Events bound to containers, not individual elements
- Works with dynamically added content

### Calculation Flags
- `calculating` flag prevents recursive calculations
- Ensures single calculation per trigger

## 9. Error Handling

### Graceful Degradation
- Fallback implementations for missing dependencies
- Safe type checking before accessing objects
- Default values for calculations

### User Feedback
- Visual indicators for subscription products
- Immediate calculation updates
- Form cleanup before submission

## 10. Integration Points

### PHP Dependencies
- Product search AJAX handler
- Product details AJAX handler
- Meta box rendering
- Template output
- Localization data

### CSS Dependencies
- `.hidden-start-date`: Hide date fields
- `.arsol-subscription-product`: Subscription styling
- WooCommerce price styling classes

This documentation captures all the rules and functionality before simplification. 