<?php
/**
 * Admin Template: Edit Proposal Header - Summary Column
 *
 * Variables passed from parent template:
 * $proposal (Arsol_PFW_Proposal object) - The proposal entity instance
 *
 * @package Arsol_Projects_For_Woo
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure we have the proposal entity instance
if (!isset($proposal) || !is_object($proposal)) {
    return;
}

$proposal_id = $proposal->get_id();
$proposal_costing_type = $proposal->get_costing_type();
$expiration_date = $proposal->get_proposal_expiration_date();
?>

<!-- Proposal Costing Type Guidance -->
<div class="arsol-pfw-show-if-arsol_pfw_proposal_costing_type-is-none">
    <div class="cost-proposal-guidance">
        <h4><?php _e('💡 Proposal Costing Type Guidance', 'arsol-pfw'); ?></h4>
        <p><?php _e('Choose a proposal costing type to provide pricing estimates:', 'arsol-pfw'); ?></p>
        <ul>
            <li><strong><?php _e('Budget:', 'arsol-pfw'); ?></strong> <?php _e('Provide rough cost estimates and timelines', 'arsol-pfw'); ?></li>
            <li><strong><?php _e('Quotation:', 'arsol-pfw'); ?></strong> <?php _e('Create detailed pricing with products and services', 'arsol-pfw'); ?></li>
        </ul>
    </div>
</div>

<!-- Budget Summary Template -->
<div id="budget-summary-template" class="arsol-pfw-show-if-arsol_pfw_proposal_costing_type-is-budget">
    <h4><?php _e('📊 Budget Summary', 'arsol-pfw'); ?></h4>
    </br>
    <!-- Empty state message for budget -->
    <div id="budget-empty-state" class="summary-empty-state">
        <p><?php _e('No budget data available', 'arsol-pfw'); ?></p>
    </div>
    
    <p class="summary-row" id="budget-onetime-row">
        <span class="arsol-pfw-meta-label"><?php _e('One-time Budget:', 'arsol-pfw'); ?></span>
        <span id="summary-budget-onetime-display">$0.00</span>
    </p>
    
    <p class="summary-row" id="budget-recurring-row">
        <span class="arsol-pfw-meta-label"><?php _e('Recurring Budget:', 'arsol-pfw'); ?></span>
        <span id="summary-budget-recurring-display">$0.00</span>
        <span id="summary-budget-billing-period">/mo</span>
        <span id="summary-budget-start-date"></span>
    </p>
</div>

<!-- Quotation Summary Template -->
<div id="quotation-summary-template" class="arsol-pfw-show-if-arsol_pfw_proposal_costing_type-is-quotation">
    <h4><?php _e('📋 Quotation Summary', 'arsol-pfw'); ?></h4>
    </br>
    <!-- Empty state message for quotation -->
    <div id="quotation-empty-state" class="summary-empty-state">
        <p><?php _e('No quotation data available', 'arsol-pfw'); ?></p>
    </div>
    
    <!-- Products & Services One-time Row -->
    <p class="summary-row" id="products-onetime-row">
        <span class="arsol-pfw-meta-label"><?php _e('Products & Services:', 'arsol-pfw'); ?></span>
        <span id="summary-product-subtotal-display">$0.00</span>
    </p>
    
    <!-- Products & Services Recurring Row -->
    <p class="summary-row" id="products-recurring-row">
        <span class="arsol-pfw-meta-label"><?php _e('Products & Services (Recurring):', 'arsol-pfw'); ?></span>
        <span id="summary-product-recurring-display">$0.00</span>
    </p>
    
    <!-- One-time Fees Row -->
    <p class="summary-row" id="onetime-fees-row">
        <span class="arsol-pfw-meta-label"><?php _e('One-time Fees:', 'arsol-pfw'); ?></span>
        <span id="summary-onetime-fee-display">$0.00</span>
    </p>
    
    <!-- Recurring Fees Row -->
    <p class="summary-row" id="recurring-fees-row">
        <span class="arsol-pfw-meta-label"><?php _e('Recurring Fees:', 'arsol-pfw'); ?></span>
        <span id="summary-recurring-fee-display">$0.00</span>
        <span id="summary-recurring-start-date"></span>
    </p>
    
    <!-- Shipping Row -->
    <p class="summary-row" id="shipping-row">
        <span class="arsol-pfw-meta-label"><?php _e('Shipping:', 'arsol-pfw'); ?></span>
        <span id="summary-shipping-display">$0.00</span>
    </p>
    
    <!-- One-time Total Row -->
    <p class="summary-row" id="onetime-total-row">
        <span class="arsol-pfw-meta-label"><?php _e('One-time Total:', 'arsol-pfw'); ?></span>
        <span id="summary-onetime-total-display">$0.00</span>
    </p>
    
    <!-- Average Yearly Total Row -->
    <p class="summary-row" id="yearly-total-row">
        <span class="arsol-pfw-meta-label"><?php _e('Average Yearly:', 'arsol-pfw'); ?></span>
        <span id="summary-avg-yearly-total-display">$0.00</span>
    </p>
</div>

<?php /* Expiration Date removed from admin display - still shows on frontend */ ?> 