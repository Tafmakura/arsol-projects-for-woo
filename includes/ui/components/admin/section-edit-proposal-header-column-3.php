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

// Only show summary for budget or quotation types
if ($cost_proposal_type === 'budget' || $cost_proposal_type === 'quotation') :
?>

<div class="arsol-totals-container arsol-proposal-summary">
    <?php if ($cost_proposal_type === 'budget') : 
        // Get budget data
        $budget_data = get_post_meta($proposal_id, '_proposal_budget', true);
        $recurring_budget_data = get_post_meta($proposal_id, '_proposal_recurring_budget', true);
        $billing_interval = get_post_meta($proposal_id, '_proposal_billing_interval', true) ?: '1';
        $billing_period = get_post_meta($proposal_id, '_proposal_billing_period', true) ?: 'month';
        $recurring_start_date = get_post_meta($proposal_id, '_proposal_recurring_start_date', true);
        
        $budget_amount = !empty($budget_data['amount']) ? floatval($budget_data['amount']) : 0;
        $recurring_budget_amount = !empty($recurring_budget_data['amount']) ? floatval($recurring_budget_data['amount']) : 0;
        
        // Format billing period
        $period_display = $billing_period === 'month' ? 'mo' : ($billing_period === 'year' ? 'yr' : ($billing_period === 'week' ? 'wk' : ($billing_period === 'day' ? 'day' : $billing_period)));
        $interval_text = $billing_interval > 1 ? $billing_interval : '';
        $billing_text = '/' . $interval_text . $period_display;
        
        // Format start date
        $start_date_text = !empty($recurring_start_date) ? date_i18n(get_option('date_format'), strtotime($recurring_start_date)) : '';
    ?>
    
    <h4><?php _e('📊 BUDGET SUMMARY', 'arsol-pfw'); ?></h4>
    
    <div class="arsol-totals-right">
        <table class="arsol-totals-table">
            <?php if ($budget_amount > 0) : ?>
            <tr class="arsol-total-row">
                <td colspan="2"><strong><?php _e('One-Time Budget:', 'arsol-pfw'); ?></strong></td>
            </tr>
            <tr class="arsol-total-row">
                <td class="arsol-total-label"><?php _e('Total:', 'arsol-pfw'); ?></td>
                <td class="arsol-total-amount">
                    <span class="js-total-display" id="summary-budget-onetime-display">
                        <?php echo wc_price($budget_amount); ?>
                    </span>
                </td>
            </tr>
            <?php endif; ?>
            
            <?php if ($recurring_budget_amount > 0) : ?>
            <tr class="arsol-total-row">
                <td colspan="2"><strong><?php _e('Recurring Budget:', 'arsol-pfw'); ?></strong></td>
            </tr>
            <tr class="arsol-total-row">
                <td class="arsol-total-label"><?php _e('Average Monthly Total:', 'arsol-pfw'); ?></td>
                <td class="arsol-total-amount">
                    <span class="js-total-display" id="summary-budget-recurring-display">
                        <?php echo wc_price($recurring_budget_amount); ?>
                    </span>
                    <span class="arsol-billing-period" id="summary-budget-billing-period"><?php echo esc_html($billing_text); ?></span>
                    <?php if (!empty($start_date_text)) : ?>
                        <br><small><?php printf(__('starting on %s', 'arsol-pfw'), esc_html($start_date_text)); ?></small>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    
    <?php elseif ($cost_proposal_type === 'quotation') : 
        // Get quotation data
        $line_items = get_post_meta($proposal_id, '_arsol_proposal_quotation_line_items', true);
        $one_time_total = get_post_meta($proposal_id, '_arsol_proposal_one_time_total', true) ?: 0;
        $recurring_totals = get_post_meta($proposal_id, '_arsol_proposal_recurring_totals_grouped', true) ?: array();
        
        // Calculate section subtotals
        $product_subtotal = 0;
        $product_recurring_subtotal = 0;
        $onetime_fee_subtotal = 0;
        $recurring_fee_subtotal = 0;
        $shipping_subtotal = 0;
        $earliest_start_date = null;
        
        // Calculate product subtotals
        if (!empty($line_items['products'])) {
            foreach ($line_items['products'] as $item) {
                $quantity = floatval($item['quantity'] ?? 0);
                $sale_price = !empty($item['sale_price']) ? floatval($item['sale_price']) : null;
                $regular_price = floatval($item['regular_price'] ?? 0);
                $price = $sale_price !== null && $sale_price > 0 ? $sale_price : $regular_price;
                $subtotal = $quantity * $price;
                
                // Check if subscription
                $product_type = $item['product_type'] ?? '';
                if (in_array($product_type, ['subscription', 'subscription_variation'])) {
                    $product_recurring_subtotal += $subtotal;
                    // Track earliest start date
                    if (!empty($item['start_date'])) {
                        $start_date = strtotime($item['start_date']);
                        if ($earliest_start_date === null || $start_date < $earliest_start_date) {
                            $earliest_start_date = $start_date;
                        }
                    }
                } else {
                    $product_subtotal += $subtotal;
                }
            }
        }
        
        // Calculate fee subtotals
        if (!empty($line_items['one_time_fees'])) {
            foreach ($line_items['one_time_fees'] as $item) {
                $onetime_fee_subtotal += floatval($item['amount'] ?? 0);
            }
        }
        
        if (!empty($line_items['recurring_fees'])) {
            foreach ($line_items['recurring_fees'] as $item) {
                $recurring_fee_subtotal += floatval($item['amount'] ?? 0);
                // Track earliest start date
                if (!empty($item['start_date'])) {
                    $start_date = strtotime($item['start_date']);
                    if ($earliest_start_date === null || $start_date < $earliest_start_date) {
                        $earliest_start_date = $start_date;
                    }
                }
            }
        }
        
        if (!empty($line_items['shipping_fees'])) {
            foreach ($line_items['shipping_fees'] as $item) {
                $shipping_subtotal += floatval($item['amount'] ?? 0);
            }
        }
        
        // Calculate average yearly total from recurring totals
        $total_daily_cost = 0;
        if (!empty($recurring_totals)) {
            foreach ($recurring_totals as $key => $data) {
                $interval = intval($data['interval'] ?? 1);
                $period = $data['period'] ?? 'month';
                $amount = floatval($data['total'] ?? 0);
                
                // Convert to daily cost
                $days_in_period = 1;
                switch ($period) {
                    case 'day': $days_in_period = 1; break;
                    case 'week': $days_in_period = 7; break;
                    case 'month': $days_in_period = 30.44; break; // Average days in month
                    case 'year': $days_in_period = 365.25; break; // Account for leap years
                }
                
                $total_days_in_cycle = $days_in_period * $interval;
                if ($total_days_in_cycle > 0) {
                    $total_daily_cost += $amount / $total_days_in_cycle;
                }
            }
        }
        
        $average_yearly_total = $total_daily_cost * 365.25;
        $average_monthly_total = ($product_recurring_subtotal + $recurring_fee_subtotal);
        
        // Format start date
        $start_date_text = $earliest_start_date ? date_i18n(get_option('date_format'), $earliest_start_date) : '';
    ?>
    
    <h4><?php _e('📋 QUOTATION SUMMARY', 'arsol-pfw'); ?></h4>
    
    <div class="arsol-totals-right">
        <table class="arsol-totals-table">
            <?php if ($product_subtotal > 0 || $product_recurring_subtotal > 0) : ?>
            <tr class="arsol-total-row">
                <td colspan="2"><strong><?php _e('Products:', 'arsol-pfw'); ?></strong></td>
            </tr>
            <?php if ($product_subtotal > 0) : ?>
            <tr class="arsol-total-row">
                <td class="arsol-total-label"><?php _e('Sub Total:', 'arsol-pfw'); ?></td>
                <td class="arsol-total-amount">
                    <span id="summary-product-subtotal-display"><?php echo wc_price($product_subtotal); ?></span>
                </td>
            </tr>
            <?php endif; ?>
            <?php if ($product_recurring_subtotal > 0) : ?>
            <tr class="arsol-total-row">
                <td class="arsol-total-label"><?php _e('Average Monthly Sub Total:', 'arsol-pfw'); ?></td>
                <td class="arsol-total-amount">
                    <span id="summary-product-recurring-display"><?php echo wc_price($product_recurring_subtotal); ?></span>
                </td>
            </tr>
            <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($onetime_fee_subtotal > 0) : ?>
            <tr class="arsol-total-row">
                <td colspan="2"><strong><?php _e('One-Time Fees:', 'arsol-pfw'); ?></strong></td>
            </tr>
            <tr class="arsol-total-row">
                <td class="arsol-total-label"><?php _e('Sub Total:', 'arsol-pfw'); ?></td>
                <td class="arsol-total-amount">
                    <span id="summary-onetime-fee-display"><?php echo wc_price($onetime_fee_subtotal); ?></span>
                </td>
            </tr>
            <?php endif; ?>
            
            <?php if ($recurring_fee_subtotal > 0) : ?>
            <tr class="arsol-total-row">
                <td colspan="2"><strong><?php _e('Recurring Fees:', 'arsol-pfw'); ?></strong></td>
            </tr>
            <tr class="arsol-total-row">
                <td class="arsol-total-label"><?php _e('Average Monthly Sub Total:', 'arsol-pfw'); ?></td>
                <td class="arsol-total-amount">
                    <span id="summary-recurring-fee-display"><?php echo wc_price($recurring_fee_subtotal); ?></span>
                    <?php if (!empty($start_date_text)) : ?>
                        <br><small><?php printf(__('starting on %s', 'arsol-pfw'), esc_html($start_date_text)); ?></small>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
            
            <?php if ($shipping_subtotal > 0) : ?>
            <tr class="arsol-total-row">
                <td colspan="2"><strong><?php _e('Shipping:', 'arsol-pfw'); ?></strong></td>
            </tr>
            <tr class="arsol-total-row">
                <td class="arsol-total-label"><?php _e('Sub Total:', 'arsol-pfw'); ?></td>
                <td class="arsol-total-amount">
                    <span id="summary-shipping-display"><?php echo wc_price($shipping_subtotal); ?></span>
                </td>
            </tr>
            <?php endif; ?>
            
            <!-- Grand Totals -->
            <tr class="arsol-total-row">
                <td colspan="2"><strong><?php _e('TOTALS', 'arsol-pfw'); ?></strong></td>
            </tr>
            <tr class="arsol-total-row">
                <td class="arsol-total-label"><?php _e('One-Time Total:', 'arsol-pfw'); ?></td>
                <td class="arsol-total-amount">
                    <span id="summary-one-time-total-display"><?php echo wc_price($one_time_total); ?></span>
                </td>
            </tr>
            <?php if ($average_yearly_total > 0) : ?>
            <tr class="arsol-total-row">
                <td class="arsol-total-label"><?php _e('Avg Yearly Total:', 'arsol-pfw'); ?></td>
                <td class="arsol-total-amount">
                    <span id="summary-avg-yearly-total-display"><?php echo wc_price($average_yearly_total); ?></span>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    
    <?php endif; ?>
</div>

<?php endif; ?>

<?php if (!empty($expiration_date)) : ?>
<p class="form-field form-field-wide">
    <label><strong><?php _e('Expiration Date:', 'arsol-pfw'); ?></strong></label>
    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($expiration_date))); ?>
</p>
<?php endif; ?> 