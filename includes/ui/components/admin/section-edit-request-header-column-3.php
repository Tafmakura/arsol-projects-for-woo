<?php
/**
 * Admin Template: Request - Status & Workflow Actions (Column 3)
 *
 * This template displays request status, customer information, and workflow actions.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the current post
global $post;

if (!$post || $post->post_type !== 'arsol-pfw-request') {
    return;
}

$request_id = $post->ID;

// Get request status
$request_statuses = wp_get_object_terms($request_id, 'arsol-request-status');
$current_status = !empty($request_statuses) ? $request_statuses[0] : null;

// Get customer information
$customer_id = $post->post_author;
$customer = get_userdata($customer_id);
$customer_orders_count = 0;
$customer_total_spent = 0;

if ($customer) {
    // Get customer's order history
    $customer_orders = wc_get_orders(array(
        'customer' => $customer_id,
        'status' => array('wc-completed', 'wc-processing'),
        'limit' => -1,
    ));
    $customer_orders_count = count($customer_orders);
    
    // Calculate total spent
    foreach ($customer_orders as $order) {
        $customer_total_spent += $order->get_total();
    }
}

// Get request urgency/priority
$priority = get_post_meta($request_id, '_request_priority', true);
$urgency_level = get_post_meta($request_id, '_request_urgency', true);

// Check if request can be converted to proposal
$can_convert = $current_status && $current_status->slug === 'under-review';

// Get request age
$request_date = get_the_date('Y-m-d H:i:s', $request_id);
$request_age_days = floor((time() - strtotime($request_date)) / (60 * 60 * 24));
?>

<div class="arsol-request-status-workflow">
    
    <!-- Request Status -->
    <div class="form-field form-field-wide">
        <label><?php _e('Request Status:', 'arsol-pfw'); ?></label>
        <span class="arsol-status-badge <?php echo $current_status ? 'request-' . esc_attr($current_status->slug) : 'request-unknown'; ?>">
            <?php echo $current_status ? esc_html($current_status->name) : __('No Status', 'arsol-pfw'); ?>
        </span>
    </div>

    <!-- Request Age -->
    <div class="form-field form-field-wide">
        <label><?php _e('Request Age:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-age <?php echo $request_age_days > 7 ? 'old-request' : ''; ?>">
            <?php 
            if ($request_age_days == 0) {
                _e('Today', 'arsol-pfw');
            } elseif ($request_age_days == 1) {
                _e('1 day ago', 'arsol-pfw');
            } else {
                printf(__('%d days ago', 'arsol-pfw'), $request_age_days);
            }
            ?>
            <?php if ($request_age_days > 7): ?>
                <small class="arsol-old-request-warning"><?php _e('(Needs attention)', 'arsol-pfw'); ?></small>
            <?php endif; ?>
        </span>
    </div>

    <!-- Customer Information -->
    <?php if ($customer): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Customer:', 'arsol-pfw'); ?></label>
        <div class="arsol-customer-info">
            <div class="arsol-customer-name">
                <strong><?php echo esc_html($customer->display_name); ?></strong>
                <small><?php echo esc_html($customer->user_email); ?></small>
            </div>
            <div class="arsol-customer-stats">
                <span class="arsol-customer-stat">
                    <strong><?php echo intval($customer_orders_count); ?></strong> <?php _e('orders', 'arsol-pfw'); ?>
                </span>
                <span class="arsol-customer-stat">
                    <strong><?php echo wc_price($customer_total_spent); ?></strong> <?php _e('spent', 'arsol-pfw'); ?>
                </span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Priority & Urgency -->
    <?php if (!empty($priority) || !empty($urgency_level)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Priority Level:', 'arsol-pfw'); ?></label>
        <div class="arsol-priority-indicators">
            <?php if (!empty($priority)): ?>
                <span class="arsol-priority priority-<?php echo esc_attr(strtolower($priority)); ?>">
                    <?php echo esc_html($priority); ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($urgency_level)): ?>
                <span class="arsol-urgency urgency-<?php echo esc_attr(strtolower($urgency_level)); ?>">
                    <?php echo esc_html($urgency_level); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Workflow Actions -->
    <?php if (current_user_can('edit_post', $request_id)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Actions:', 'arsol-pfw'); ?></label>
        <div class="arsol-workflow-actions">
            
            <!-- Convert to Proposal -->
            <?php if ($can_convert && current_user_can('publish_posts')): ?>
            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=arsol_convert_to_proposal&request_id=' . $request_id), 'arsol_convert_to_proposal_nonce'); ?>" 
               class="button button-primary arsol-convert-proposal">
                <?php _e('Convert to Proposal', 'arsol-pfw'); ?>
            </a>
            <?php endif; ?>
            
            <!-- Update Status -->
            <button type="button" class="button button-secondary arsol-update-request-status" data-request-id="<?php echo esc_attr($request_id); ?>">
                <?php _e('Update Status', 'arsol-pfw'); ?>
            </button>
            
            <!-- Contact Customer -->
            <?php if ($customer): ?>
            <a href="mailto:<?php echo esc_attr($customer->user_email); ?>?subject=<?php echo urlencode('Regarding your project request: ' . get_the_title($request_id)); ?>" 
               class="button button-secondary arsol-contact-customer">
                <?php _e('Contact Customer', 'arsol-pfw'); ?>
            </a>
            <?php endif; ?>
            
            <!-- View Customer Orders -->
            <?php if ($customer_orders_count > 0): ?>
            <a href="<?php echo admin_url('edit.php?post_type=shop_order&_customer_user=' . $customer_id); ?>" 
               class="button button-secondary arsol-view-customer-orders">
                <?php _e('View Customer Orders', 'arsol-pfw'); ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Request Statistics -->
    <div class="form-field form-field-wide">
        <label><?php _e('Request Stats:', 'arsol-pfw'); ?></label>
        <div class="arsol-request-stats">
            <div class="arsol-stat-item">
                <span class="arsol-stat-label"><?php _e('Submitted:', 'arsol-pfw'); ?></span>
                <span class="arsol-stat-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($request_date))); ?></span>
            </div>
            <div class="arsol-stat-item">
                <span class="arsol-stat-label"><?php _e('Last Modified:', 'arsol-pfw'); ?></span>
                <span class="arsol-stat-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($post->post_modified))); ?></span>
            </div>
        </div>
    </div>

    <!-- Customer View Link -->
    <div class="form-field form-field-wide">
        <label><?php _e('Customer View:', 'arsol-pfw'); ?></label>
        <div class="arsol-customer-view">
            <?php 
            $customer_request_url = wc_get_account_endpoint_url('project-view-request') . $request_id . '/';
            ?>
            <a href="<?php echo esc_url($customer_request_url); ?>" target="_blank" class="arsol-customer-link">
                <?php _e('View as Customer', 'arsol-pfw'); ?> ↗
            </a>
        </div>
    </div>

</div>
