<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
    return;
}

$proposal_id = $post->ID;
$cost_proposal_type = get_post_meta($proposal_id, '_arsol_pfw_proposal_costing_type', true);
$expiration_date = get_post_meta($proposal_id, '_arsol_pfw_proposal_expiration_date', true);
?>

<!-- Cost Proposal Type Guidance -->
<div class="arsol-pfw-show-if-proposal-cost-type-is-none" style="display: none;">
    <div class="cost-proposal-guidance">
        <h4><?php _e('💡 Cost Proposal Type Guidance', 'arsol-pfw'); ?></h4>
        <p><strong><?php _e('Budget:', 'arsol-pfw'); ?></strong> <?php _e('Use when providing estimated costs or budget ranges. Good for initial planning and rough estimates.', 'arsol-pfw'); ?></p>
        <p><strong><?php _e('Quotation:', 'arsol-pfw'); ?></strong> <?php _e('Use when providing exact pricing with specific products, services, and terms. This becomes a binding proposal when accepted.', 'arsol-pfw'); ?></p>
    </div>
</div>

<!-- Budget Summary Template -->
<div id="budget-summary-template" class="form-field-wide arsol-pfw-show-if-proposal-cost-type-is-budget" style="display: none;">
    <h4><?php _e('📊 Budget Summary', 'arsol-pfw'); ?></h4>
    </br>
    <!-- Empty state message for budget -->
    <div id="budget-empty-state" class="summary-empty-state" style="display: none;">
        <p><?php _e('Your budget proposal is empty', 'arsol-pfw'); ?></p>
    </div>
    
    <p class="summary-row" id="budget-onetime-row" style="display: none;">
        <label><strong><?php _e('One-Time Budget:', 'arsol-pfw'); ?></strong></label>
        <?php _e('Total:', 'arsol-pfw'); ?> <span id="summary-budget-onetime-display"><?php echo wc_price(0); ?></span>
    </p>
    
    <p class="summary-row" id="budget-recurring-row" style="display: none;">
        <label><strong><?php _e('Recurring Budget:', 'arsol-pfw'); ?></strong></label>
        <?php _e('Average Monthly Total:', 'arsol-pfw'); ?> 
        <span id="summary-budget-recurring-display"><?php echo wc_price(0); ?></span><span id="summary-budget-billing-period">/mo</span>
        <span id="summary-budget-start-date"></span>
    </p>
</div>

<!-- Quotation Summary Template -->
<div id="quotation-summary-template" class="form-field-wide arsol-pfw-show-if-proposal-cost-type-is-quotation" style="display: none;">
    <h4><?php _e('📋 Quotation Summary', 'arsol-pfw'); ?></h4>
    </br>
    <!-- Empty state message for quotation -->
    <div id="quotation-empty-state" class="summary-empty-state" style="display: none;">
        <p><?php _e('Your quotation is empty', 'arsol-pfw'); ?></p>
    </div>
    
    <p class="summary-row" id="products-row" style="display: none;">
        <label><strong><?php _e('Products:', 'arsol-pfw'); ?></strong></label>
        <span id="products-onetime" style="display: none;">
            <?php _e('Sub Total:', 'arsol-pfw'); ?> <span id="summary-product-subtotal-display"><?php echo wc_price(0); ?></span>
        </span>
        <span id="products-recurring" style="display: none;">
            <?php _e('Average Monthly Sub Total:', 'arsol-pfw'); ?> <span id="summary-product-recurring-display"><?php echo wc_price(0); ?></span>
        </span>
    </p>
    
    <p class="summary-row" id="onetime-fees-row" style="display: none;">
        <label><strong><?php _e('One-Time Fees:', 'arsol-pfw'); ?></strong></label>
        <?php _e('Sub Total:', 'arsol-pfw'); ?> <span id="summary-onetime-fee-display"><?php echo wc_price(0); ?></span>
    </p>
    
    <p class="summary-row" id="recurring-fees-row" style="display: none;">
        <label><strong><?php _e('Recurring Fees:', 'arsol-pfw'); ?></strong></label>
        <?php _e('Average Monthly Sub Total:', 'arsol-pfw'); ?> 
        <span id="summary-recurring-fee-display"><?php echo wc_price(0); ?></span>
        <span id="summary-recurring-start-date"></span>
    </p>
    
    <p class="summary-row" id="shipping-row" style="display: none;">
        <label><strong><?php _e('Shipping:', 'arsol-pfw'); ?></strong></label>
        <?php _e('Sub Total:', 'arsol-pfw'); ?> <span id="summary-shipping-display"><?php echo wc_price(0); ?></span>
    </p>
    
    <p class="summary-row" id="totals-row">
        <label><strong><?php _e('Grand Totals:', 'arsol-pfw'); ?></strong></label>
        <span id="onetime-total-row">
            <?php _e('One-Time Total:', 'arsol-pfw'); ?> <span id="summary-one-time-total-display"><?php echo wc_price(0); ?></span>
        </span>
        <span id="yearly-total-row" style="display: none;">
            <?php _e('Avg Recurring Total:', 'arsol-pfw'); ?> <span id="summary-avg-yearly-total-display"><?php echo wc_price(0); ?></span>
        </span>
    </p>
</div>

<?php /* Expiration Date removed from admin display - still shows on frontend */ ?> 