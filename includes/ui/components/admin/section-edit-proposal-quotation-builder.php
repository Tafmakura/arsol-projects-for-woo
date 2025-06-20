<?php
/**
 * Admin Template: Quotation Builder Section
 *
 * This template contains the quotation builder interface with sections for:
 * - Products & Services
 * - One-Time Fees
 * - Recurring Fees
 * - Shipping Fees
 * - Summary
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the current post
global $post;

if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
    return;
}
?>

<!-- Products & Services Section -->
<div class="line-items-container">
    <div class="arsol-section-header">
        <h3><?php _e('Products & Services', 'arsol-pfw'); ?></h3>
        <button type="button" class="button button-secondary add-line-item" data-type="product" id="add-product-btn">
            <?php _e('Add Product/Service', 'arsol-pfw'); ?>
        </button>
    </div>
    
    <table class="widefat" id="product-lines">
        <thead>
            <tr>
                <th class="arsol-description-column"><?php _e('Product/Service', 'arsol-pfw'); ?></th>
                <th class="arsol-date-column"><?php _e('Start Date', 'arsol-pfw'); ?></th>
                <th class="arsol-quantity-column"><?php _e('Qty', 'arsol-pfw'); ?></th>
                <th class="arsol-price-column"><?php _e('Price', 'arsol-pfw'); ?></th>
                <th class="arsol-sale-price-column"><?php _e('Sale Price', 'arsol-pfw'); ?></th>
                <th class="arsol-subtotal-column"><?php _e('Subtotal', 'arsol-pfw'); ?></th>
                <th class="arsol-actions-column"><?php _e('Actions', 'arsol-pfw'); ?></th>
            </tr>
        </thead>
        <tbody id="product-lines-body">
            <!-- Products will be added here via JavaScript -->
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="arsol-subtotal-label"><?php _e('Products Subtotal:', 'arsol-pfw'); ?></td>
                <td class="arsol-subtotal-amount">
                    <span id="product-subtotal-display"><?php echo wc_price(0); ?></span>
                </td>
                <td></td>
            </tr>
            <tr class="recurring-total-row" style="display: none;">
                <td colspan="5" class="arsol-subtotal-label"><?php _e('Avg. Monthly (Subscriptions):', 'arsol-pfw'); ?></td>
                <td class="arsol-subtotal-amount">
                    <span id="product-avg-monthly-display"><?php echo wc_price(0); ?></span>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- One-Time Fees Section -->
<div class="line-items-container">
    <div class="arsol-section-header">
        <h3><?php _e('One-Time Fees', 'arsol-pfw'); ?></h3>
        <button type="button" class="button button-secondary add-line-item" data-type="onetime-fee" id="add-onetime-fee-btn">
            <?php _e('Add One-Time Fee', 'arsol-pfw'); ?>
        </button>
    </div>
    
    <table class="widefat" id="onetime-fee-lines">
        <thead>
            <tr>
                <th class="arsol-description-column"><?php _e('Description', 'arsol-pfw'); ?></th>
                <th class="arsol-amount-column"><?php _e('Amount', 'arsol-pfw'); ?></th>
                <th class="arsol-taxable-column"><?php _e('Tax Class', 'arsol-pfw'); ?></th>
                <th class="arsol-subtotal-column"><?php _e('Subtotal', 'arsol-pfw'); ?></th>
                <th class="arsol-actions-column"><?php _e('Actions', 'arsol-pfw'); ?></th>
            </tr>
        </thead>
        <tbody id="onetime-fee-lines-body">
            <!-- One-time fees will be added here via JavaScript -->
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="arsol-subtotal-label"><?php _e('One-Time Fees Subtotal:', 'arsol-pfw'); ?></td>
                <td class="arsol-subtotal-amount">
                    <span id="onetime-fee-subtotal-display"><?php echo wc_price(0); ?></span>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- Recurring Fees Section -->
<div class="line-items-container">
    <div class="arsol-section-header">
        <h3><?php _e('Recurring Fees', 'arsol-pfw'); ?></h3>
        <button type="button" class="button button-secondary add-line-item" data-type="recurring-fee" id="add-recurring-fee-btn">
            <?php _e('Add Recurring Fee', 'arsol-pfw'); ?>
        </button>
    </div>
    
    <table class="widefat" id="recurring-fee-lines">
        <thead>
            <tr>
                <th class="arsol-description-column"><?php _e('Description', 'arsol-pfw'); ?></th>
                <th class="arsol-date-column"><?php _e('Start Date', 'arsol-pfw'); ?></th>
                <th class="arsol-amount-column"><?php _e('Amount', 'arsol-pfw'); ?></th>
                <th class="arsol-billing-cycle-column"><?php _e('Billing Cycle', 'arsol-pfw'); ?></th>
                <th class="arsol-taxable-column"><?php _e('Tax Class', 'arsol-pfw'); ?></th>
                <th class="arsol-subtotal-column"><?php _e('Subtotal', 'arsol-pfw'); ?></th>
                <th class="arsol-actions-column"><?php _e('Actions', 'arsol-pfw'); ?></th>
            </tr>
        </thead>
        <tbody id="recurring-fee-lines-body">
            <!-- Recurring fees will be added here via JavaScript -->
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="arsol-subtotal-label"><?php _e('Avg. Monthly (Recurring):', 'arsol-pfw'); ?></td>
                <td class="arsol-subtotal-amount">
                    <span id="recurring-fee-avg-monthly-display"><?php echo wc_price(0); ?></span>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- Shipping Fees Section -->
<div class="line-items-container">
    <div class="arsol-section-header">
        <h3><?php _e('Shipping Fees', 'arsol-pfw'); ?></h3>
        <button type="button" class="button button-secondary add-line-item" data-type="shipping-fee" id="add-shipping-fee-btn">
            <?php _e('Add Shipping Fee', 'arsol-pfw'); ?>
        </button>
    </div>
    
    <table class="widefat" id="shipping-lines">
        <thead>
            <tr>
                <th class="arsol-description-column"><?php _e('Description', 'arsol-pfw'); ?></th>
                <th class="arsol-shipping-class-column"><?php _e('Shipping Class', 'arsol-pfw'); ?></th>
                <th class="arsol-amount-column"><?php _e('Amount', 'arsol-pfw'); ?></th>
                <th class="arsol-taxable-column"><?php _e('Tax Class', 'arsol-pfw'); ?></th>
                <th class="arsol-subtotal-column"><?php _e('Subtotal', 'arsol-pfw'); ?></th>
                <th class="arsol-actions-column"><?php _e('Actions', 'arsol-pfw'); ?></th>
            </tr>
        </thead>
        <tbody id="shipping-lines-body">
            <!-- Shipping fees will be added here via JavaScript -->
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="arsol-subtotal-label"><?php _e('Shipping Subtotal:', 'arsol-pfw'); ?></td>
                <td class="arsol-subtotal-amount">
                    <span id="shipping-subtotal-display"><?php echo wc_price(0); ?></span>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- Quotation Summary Section -->
<div class="line-items-container arsol-quotation-summary">
    <h3><?php _e('Quotation Summary', 'arsol-pfw'); ?></h3>
    
    <div id="quotation-empty-state" class="arsol-empty-state">
        <p><?php _e('No quotation items added yet. Use the buttons above to add products, services, or fees.', 'arsol-pfw'); ?></p>
    </div>
    
    <table class="widefat arsol-summary-table" id="quotation-summary">
        <tbody>
            <tr id="products-row" style="display: none;">
                <td class="arsol-summary-label"><?php _e('Products & Services:', 'arsol-pfw'); ?></td>
                <td class="arsol-summary-amount">
                    <div id="products-onetime">
                        <?php _e('One-time:', 'arsol-pfw'); ?> <span id="summary-product-subtotal-display"><?php echo wc_price(0); ?></span>
                    </div>
                    <div id="products-recurring">
                        <?php _e('Recurring:', 'arsol-pfw'); ?> <span id="summary-product-recurring-display"><?php echo wc_price(0); ?></span>
                    </div>
                </td>
            </tr>
            <tr id="onetime-fees-row" style="display: none;">
                <td class="arsol-summary-label"><?php _e('One-Time Fees:', 'arsol-pfw'); ?></td>
                <td class="arsol-summary-amount">
                    <span id="summary-onetime-fee-display"><?php echo wc_price(0); ?></span>
                </td>
            </tr>
            <tr id="recurring-fees-row" style="display: none;">
                <td class="arsol-summary-label"><?php _e('Recurring Fees:', 'arsol-pfw'); ?></td>
                <td class="arsol-summary-amount">
                    <span id="summary-recurring-fee-display"><?php echo wc_price(0); ?></span>
                    <span id="summary-recurring-start-date"></span>
                </td>
            </tr>
            <tr id="shipping-row" style="display: none;">
                <td class="arsol-summary-label"><?php _e('Shipping:', 'arsol-pfw'); ?></td>
                <td class="arsol-summary-amount">
                    <span id="summary-shipping-display"><?php echo wc_price(0); ?></span>
                </td>
            </tr>
        </tbody>
        <tfoot id="totals-row" style="display: none;">
            <tr id="onetime-total-row">
                <td class="arsol-total-label"><strong><?php _e('One-Time Total:', 'arsol-pfw'); ?></strong></td>
                <td class="arsol-total-amount">
                    <strong><span id="summary-one-time-total-display"><?php echo wc_price(0); ?></span></strong>
                </td>
            </tr>
            <tr id="yearly-total-row">
                <td class="arsol-total-label"><strong><?php _e('Annual Total:', 'arsol-pfw'); ?></strong></td>
                <td class="arsol-total-amount">
                    <strong><span id="summary-avg-yearly-total-display"><?php echo wc_price(0); ?></span></strong>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- Hidden inputs for form submission -->
<input type="hidden" id="line_items_one_time_total" name="line_items_one_time_total" value="0">
<input type="hidden" id="line_items_recurring_totals" name="line_items_recurring_totals" value="{}"> 