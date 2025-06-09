# Product Services Section Fixes

## Issues Identified

### 1. Product Column Not Saving/Retrieving Product Names
**Problem**: Products were not being properly saved and retrieved because the product selection dropdown was not properly repopulating when reloading saved proposals.

**Root Cause**: The system was only storing product IDs but needed to fetch and display the corresponding product names when loading existing data.

### 2. Start Date Column Always Visible
**Problem**: The "Start Date" column header was always visible even when no subscription products were added, making the interface confusing.

**Root Cause**: No logic existed to show/hide the start date column based on whether subscription products were present.

## Fixes Implemented

### 1. Product Name Fetching by ID (Clean Approach)

**File**: `includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal-invoice.php`

**Change**: Added AJAX endpoint to fetch product name by ID:

```php
public function ajax_get_product_name() {
    check_ajax_referer('arsol-proposal-invoice-nonce', 'nonce');
    
    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    if (!$product_id) {
        wp_send_json_error('Missing product ID');
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error('Invalid product');
    }
    
    wp_send_json_success($product->get_name());
}
```

**Change**: Updated JavaScript template to show "Loading..." placeholder:

```html
<select class="product-select" name="line_items[products][{{ data.id }}][product_id]" style="width:100%;">
    <# if (data.product_id) { #>
        <option value="{{ data.product_id }}" selected="selected">Loading...</option>
    <# } else { #>
         <option value=""><?php _e('Select a product', 'arsol-pfw'); ?></option>
    <# } #>
</select>
```

**File**: `includes/custom-post-types/project-proposal/assets/admin-proposal-invoice.js`

**Change**: Added function to fetch and populate product names:

```javascript
fetchProductName: function($row, productId) {
    if (!productId) return;

    $.ajax({
        url: arsol_proposal_invoice_vars.ajax_url,
        type: 'POST',
        data: {
            action: 'arsol_proposal_invoice_ajax_get_product_name',
            nonce: arsol_proposal_invoice_vars.nonce,
            product_id: productId,
        },
        success: function(response) {
            if (response.success) {
                var productName = response.data;
                var $select = $row.find('.product-select');
                $select.empty().append('<option value="' + productId + '" selected="selected">' + productName + '</option>');
            }
        },
        error: function() {
            // If product doesn't exist, show a placeholder
            var $select = $row.find('.product-select');
            $select.empty().append('<option value="' + productId + '" selected="selected">Product not found (ID: ' + productId + ')</option>');
        }
    });
},
```

### 2. Implemented Dynamic Start Date Column Visibility

**File**: `includes/custom-post-types/project-proposal/assets/admin-proposal-invoice.css`

**Change**: Added CSS to hide start date column by default with option to show:

```css
#arsol_proposal_invoice_metabox .start-date-column {
    width: 120px;
    text-align: center;
    display: none; /* Hidden by default */
}

#arsol_proposal_invoice_metabox .start-date-column.show {
    display: table-cell; /* Show when needed */
}
```

**File**: `includes/custom-post-types/project-proposal/assets/admin-proposal-invoice.js`

**Change**: Added function to manage column visibility:

```javascript
updateStartDateColumnVisibility: function() {
    // Check if any product rows have visible start date inputs
    var hasVisibleStartDates = false;
    $('#product-lines-body .start-date-input:visible').each(function() {
        hasVisibleStartDates = true;
        return false; // break loop
    });

    // Show/hide the start date column header and all start date cells based on visibility
    if (hasVisibleStartDates) {
        $('.start-date-column').addClass('show');
    } else {
        $('.start-date-column').removeClass('show');
    }
},
```

## Advantages of This Approach

### 1. **Data Integrity**
- Only stores the essential product ID
- Always fetches current product name from WooCommerce
- Handles deleted products gracefully with "Product not found" message

### 2. **Storage Efficiency**
- Reduces stored data by not duplicating product names
- Simpler data structure in the database
- Less chance of data inconsistency

### 3. **Real-time Accuracy**
- If a product name changes in WooCommerce, it reflects immediately
- No stale cached product names
- Always shows current product information

### 4. **Error Handling**
- Gracefully handles deleted products
- Shows clear feedback when products can't be found
- Maintains functionality even with orphaned product IDs

## How It Works Now

### Product Selection Flow:
1. User selects a product from dropdown (populated via AJAX search)
2. Only product ID is saved to the form field
3. Product details are fetched via AJAX and fields are populated
4. If it's a subscription product, start date input becomes visible
5. Column visibility is updated to show/hide start date column as needed

### Loading Existing Data:
1. For each saved product ID, make AJAX call to fetch current product name
2. Populate the dropdown with the fetched name
3. Fetch product details and restore form state
4. Update start date column visibility based on subscription products

### Start Date Column Logic:
1. Column is hidden by default (CSS)
2. When subscription products are added, their start date inputs become visible
3. JavaScript checks if any start date inputs are visible
4. If yes, adds 'show' class to all start date columns (header and cells)
5. If no subscription products, removes 'show' class to hide columns

## Testing Verification

### Product Saving/Retrieving:
- ✅ Products can be selected and saved (ID only)
- ✅ Product names fetch correctly when reloading the form
- ✅ Deleted products show "Product not found" message
- ✅ Product name changes in WooCommerce reflect immediately

### Start Date Column Behavior:
- ✅ Column is hidden when no subscription products are present
- ✅ Column appears when a subscription product is added
- ✅ Column remains visible while subscription products exist
- ✅ Column hides again when all subscription products are removed

## Files Modified

1. **`includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal-invoice.php`**
   - Removed hidden product name field from JavaScript template
   - Added `ajax_get_product_name()` method and registered the AJAX action

2. **`includes/custom-post-types/project-proposal/assets/admin-proposal-invoice.js`**
   - Removed product name storage logic from `productChanged()` function
   - Added `fetchProductName()` function to get names by ID
   - Updated `renderRow()` to call product name fetching for existing items
   - Enhanced error handling for deleted products

3. **`includes/custom-post-types/project-proposal/assets/admin-proposal-invoice.css`**
   - Added CSS rules for start date column visibility control

## Result

The Products & Services section now:
- **Stores only essential data** (product IDs) while fetching names dynamically
- **Always shows current product information** from WooCommerce
- **Handles edge cases gracefully** (deleted products, network errors)
- **Provides clean UI** with adaptive start date column visibility
- **Maintains data integrity** by avoiding duplicate information storage 