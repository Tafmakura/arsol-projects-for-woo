<?php
/**
 * Admin Template: Edit Project/Proposal Details Subsection
 *
 * Variables passed from parent template:
 * $proposal (Arsol_PFW_Proposal object) - The proposal entity instance
 * $request_id - Source request ID
 * $request_budget - Request budget data
 * $request_start_date - Request start date
 * $request_delivery_date - Request delivery date
 * $has_request_data - Whether request data exists
 *
 * @package Arsol_Projects_For_Woo
 */

if (!defined('ABSPATH')) {
    exit;
}

// Determine post type and get appropriate meta keys
$post_type = get_post_type();
$post_id = get_the_ID();

if ($post_type === 'arsol-pfw-proposal') {
    // For proposals, get request data from proposal meta
    $request_id = get_post_meta($post_id, '_arsol_pfw_proposal_request_id', true);
    $request_budget = get_post_meta($post_id, '_arsol_pfw_proposal_request_budget', true);
    $request_start_date = get_post_meta($post_id, '_arsol_pfw_proposal_request_start_date', true);
    $request_delivery_date = get_post_meta($post_id, '_arsol_pfw_proposal_request_delivery_date', true);
} else {
    // For projects, get request data from project meta
    $request_id = get_post_meta($post_id, '_arsol_pfw_project_request_id', true);
    $request_budget = get_post_meta($post_id, '_arsol_pfw_project_request_budget', true);
    $request_start_date = get_post_meta($post_id, '_arsol_pfw_project_request_start_date', true);
    $request_delivery_date = get_post_meta($post_id, '_arsol_pfw_project_request_delivery_date', true);
}

$has_request_data = $request_id || $request_budget || $request_start_date || $request_delivery_date;
?>

<?php if ($has_request_data): ?>
    <div class="arsol-request-details">
        <h4><?php _e('Original Request Details', 'arsol-pfw'); ?></h4>
        
        <table class="widefat">
            <tr>
                <th><?php _e('Request ID:', 'arsol-pfw'); ?></th>
                <td><?php echo !empty($request_id) ? esc_html($request_id) : '<em>' . __('Not provided', 'arsol-pfw') . '</em>'; ?></td>
            </tr>
            <tr>
                <th><?php _e('Start Date:', 'arsol-pfw'); ?></th>
                <td><?php echo !empty($request_start_date) ? esc_html(date_i18n(get_option('date_format'), strtotime($request_start_date))) : '<em>' . __('Not provided', 'arsol-pfw') . '</em>'; ?></td>
            </tr>
            <tr>
                <th><?php _e('Delivery Date:', 'arsol-pfw'); ?></th>
                <td><?php echo !empty($request_delivery_date) ? esc_html(date_i18n(get_option('date_format'), strtotime($request_delivery_date))) : '<em>' . __('Not provided', 'arsol-pfw') . '</em>'; ?></td>
            </tr>
            <tr>
                <th><?php _e('Budget:', 'arsol-pfw'); ?></th>
                <td>
                    <?php if (!empty($request_budget)): ?>
                        <?php if (is_array($request_budget) && isset($request_budget['amount'])): ?>
                            <?php 
                            $currency = isset($request_budget['currency']) ? $request_budget['currency'] : get_woocommerce_currency();
                            echo wc_price($request_budget['amount'], array('currency' => $currency));
                            ?>
                        <?php else: ?>
                            <?php echo wc_price($request_budget); ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <em><?php _e('Not provided', 'arsol-pfw'); ?></em>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
<?php else: ?>
    <p><?php _e('No original request data available.', 'arsol-pfw'); ?></p>
<?php endif; ?> 