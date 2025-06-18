<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
    return;
}

$proposal_id = $post->ID;
$cost_proposal_type = get_post_meta($proposal_id, '_cost_proposal_type', true);
$expiration_date = get_post_meta($proposal_id, '_proposal_expiration_date', true);
?>

<!-- Proposal Summary Container (initially hidden, controlled by JavaScript) -->
<div class="arsol-proposal-summary" id="proposal-summary-container" style="display: none;">
    
    <!-- Budget Summary Template -->
    <div id="budget-summary-template" style="display: none;">
        <h4><?php _e('📊 BUDGET SUMMARY', 'arsol-pfw'); ?></h4>
        
        <!-- Empty state message for budget -->
        <div id="budget-empty-state" class="summary-empty-state" style="display: none;">
            <p><?php _e('No budget data available', 'arsol-pfw'); ?></p>
        </div>
        
        <div class="summary-row" id="budget-onetime-row" style="display: none;">
            <div class="summary-heading"><strong><?php _e('One-Time Budget:', 'arsol-pfw'); ?></strong></div>
            <div class="summary-content">
                <span><?php _e('Total:', 'arsol-pfw'); ?></span> <span id="summary-budget-onetime-display"><?php echo wc_price(0); ?></span>
            </div>
        </div>
        
        <div class="summary-row" id="budget-recurring-row" style="display: none;">
            <div class="summary-heading"><strong><?php _e('Recurring Budget:', 'arsol-pfw'); ?></strong></div>
            <div class="summary-content">
                <span><?php _e('Average Monthly Total:', 'arsol-pfw'); ?></span> 
                <span id="summary-budget-recurring-display"><?php echo wc_price(0); ?></span><span id="summary-budget-billing-period">/mo</span>
                <span id="summary-budget-start-date"></span>
            </div>
        </div>
    </div>
    
    <!-- Quotation Summary Template -->
    <div id="quotation-summary-template" style="display: none;">
        <h4><?php _e('📋 QUOTATION SUMMARY', 'arsol-pfw'); ?></h4>
        
        <!-- Empty state message for quotation -->
        <div id="quotation-empty-state" class="summary-empty-state" style="display: none;">
            <p><?php _e('No quotation data available', 'arsol-pfw'); ?></p>
        </div>
        
        <div class="summary-row" id="products-row" style="display: none;">
            <div class="summary-heading"><strong><?php _e('Products:', 'arsol-pfw'); ?></strong></div>
            <div class="summary-content">
                <div id="products-onetime" style="display: none;">
                    <span><?php _e('Sub Total:', 'arsol-pfw'); ?></span> <span id="summary-product-subtotal-display"><?php echo wc_price(0); ?></span>
                </div>
                <div id="products-recurring" style="display: none;">
                    <span><?php _e('Average Monthly Sub Total:', 'arsol-pfw'); ?></span> <span id="summary-product-recurring-display"><?php echo wc_price(0); ?></span>
                </div>
            </div>
        </div>
        
        <div class="summary-row" id="onetime-fees-row" style="display: none;">
            <div class="summary-heading"><strong><?php _e('One-Time Fees:', 'arsol-pfw'); ?></strong></div>
            <div class="summary-content">
                <span><?php _e('Sub Total:', 'arsol-pfw'); ?></span> <span id="summary-onetime-fee-display"><?php echo wc_price(0); ?></span>
            </div>
        </div>
        
        <div class="summary-row" id="recurring-fees-row" style="display: none;">
            <div class="summary-heading"><strong><?php _e('Recurring Fees:', 'arsol-pfw'); ?></strong></div>
            <div class="summary-content">
                <span><?php _e('Average Monthly Sub Total:', 'arsol-pfw'); ?></span> 
                <span id="summary-recurring-fee-display"><?php echo wc_price(0); ?></span>
                <span id="summary-recurring-start-date"></span>
            </div>
        </div>
        
        <div class="summary-row" id="shipping-row" style="display: none;">
            <div class="summary-heading"><strong><?php _e('Shipping:', 'arsol-pfw'); ?></strong></div>
            <div class="summary-content">
                <span><?php _e('Sub Total:', 'arsol-pfw'); ?></span> <span id="summary-shipping-display"><?php echo wc_price(0); ?></span>
            </div>
        </div>
        
        <div class="summary-row" id="totals-row">
            <div class="summary-heading"><strong><?php _e('TOTALS', 'arsol-pfw'); ?></strong></div>
            <div class="summary-content">
                <div id="onetime-total-row">
                    <span><?php _e('One-Time Total:', 'arsol-pfw'); ?></span> <span id="summary-one-time-total-display"><?php echo wc_price(0); ?></span>
                </div>
                <div id="yearly-total-row" style="display: none;">
                    <span><?php _e('Avg Yearly Total:', 'arsol-pfw'); ?></span> <span id="summary-avg-yearly-total-display"><?php echo wc_price(0); ?></span>
                </div>
            </div>
        </div>
    </div>
    
</div>

<?php if (!empty($expiration_date)) : ?>
<p class="form-field form-field-wide">
    <label><strong><?php _e('Expiration Date:', 'arsol-pfw'); ?></strong></label>
    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($expiration_date))); ?>
</p>
<?php endif; ?> 