<?php
/**
 * Admin Template: Proposal - Review Status & Actions (Column 3)
 *
 * This template displays proposal review status, approval information, and action buttons.
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

$proposal_id = $post->ID;

// Get proposal review status
$review_statuses = wp_get_object_terms($proposal_id, 'arsol-review-status');
$current_review_status = !empty($review_statuses) ? $review_statuses[0] : null;

// Get proposal meta data
$proposal_budget = get_post_meta($proposal_id, '_proposal_budget', true);
$proposal_expiration_date = get_post_meta($proposal_id, '_proposal_expiration_date', true);
$proposal_delivery_date = get_post_meta($proposal_id, '_proposal_delivery_date', true);
$proposal_status = $post->post_status;

// Get proposal totals from invoice data
$invoice_data = get_post_meta($proposal_id, '_proposal_invoice_data', true);
$total_amount = 0;
if (!empty($invoice_data['products'])) {
    foreach ($invoice_data['products'] as $product) {
        $total_amount += floatval($product['total']);
    }
}
if (!empty($invoice_data['recurring_fees'])) {
    foreach ($invoice_data['recurring_fees'] as $fee) {
        $total_amount += floatval($fee['amount']);
    }
}

// Check if proposal is near expiration
$is_near_expiration = false;
$days_until_expiration = 0;
if (!empty($proposal_expiration_date)) {
    $expiration_date = new DateTime($proposal_expiration_date);
    $today = new DateTime();
    $days_until_expiration = $today->diff($expiration_date)->days;
    $is_near_expiration = $days_until_expiration <= 7 && $expiration_date > $today;
}
?>

<div class="arsol-proposal-review-actions">
    
    <!-- Review Status -->
    <div class="form-field form-field-wide">
        <label><?php _e('Review Status:', 'arsol-pfw'); ?></label>
        <span class="arsol-status-badge <?php echo $current_review_status ? 'review-' . esc_attr($current_review_status->slug) : 'review-unknown'; ?>">
            <?php echo $current_review_status ? esc_html($current_review_status->name) : __('No Review Status', 'arsol-pfw'); ?>
        </span>
    </div>

    <!-- Proposal Status -->
    <div class="form-field form-field-wide">
        <label><?php _e('Proposal Status:', 'arsol-pfw'); ?></label>
        <span class="arsol-proposal-status status-<?php echo esc_attr($proposal_status); ?>">
            <?php echo ucfirst(str_replace('-', ' ', $proposal_status)); ?>
        </span>
    </div>

    <!-- Total Value -->
    <?php if ($total_amount > 0): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Total Value:', 'arsol-pfw'); ?></label>
        <span class="arsol-total-value">
            <?php echo wc_price($total_amount); ?>
        </span>
    </div>
    <?php endif; ?>

    <!-- Expiration Alert -->
    <?php if (!empty($proposal_expiration_date)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Expires:', 'arsol-pfw'); ?></label>
        <span class="arsol-expiration-date <?php echo $is_near_expiration ? 'near-expiration' : ''; ?>">
            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($proposal_expiration_date))); ?>
            <?php if ($is_near_expiration): ?>
                <small class="arsol-expiration-warning"><?php printf(__('(%d days left)', 'arsol-pfw'), $days_until_expiration); ?></small>
            <?php endif; ?>
        </span>
    </div>
    <?php endif; ?>

    <!-- Expected Delivery -->
    <?php if (!empty($proposal_delivery_date)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Expected Delivery:', 'arsol-pfw'); ?></label>
        <span class="arsol-delivery-date">
            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($proposal_delivery_date))); ?>
        </span>
    </div>
    <?php endif; ?>

    <!-- Review Actions -->
    <?php if ($proposal_status === 'publish' && current_user_can('publish_posts')): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Proposal Actions:', 'arsol-pfw'); ?></label>
        <div class="arsol-proposal-actions">
            
            <!-- Convert to Project -->
            <?php if ($current_review_status && $current_review_status->slug === 'approved'): ?>
            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=arsol_convert_to_project&proposal_id=' . $proposal_id), 'arsol_convert_to_project_nonce'); ?>" 
               class="button button-primary arsol-convert-project">
                <?php _e('Convert to Project', 'arsol-pfw'); ?>
            </a>
            <?php endif; ?>
            
            <!-- Send to Customer -->
            <button type="button" class="button button-secondary arsol-send-proposal" data-proposal-id="<?php echo esc_attr($proposal_id); ?>">
                <?php _e('Send to Customer', 'arsol-pfw'); ?>
            </button>
            
            <!-- Preview Proposal -->
            <button type="button" class="button button-secondary arsol-preview-proposal" data-proposal-id="<?php echo esc_attr($proposal_id); ?>">
                <?php _e('Preview', 'arsol-pfw'); ?>
            </button>
            
            <!-- Duplicate Proposal -->
            <button type="button" class="button button-secondary arsol-duplicate-proposal" data-proposal-id="<?php echo esc_attr($proposal_id); ?>">
                <?php _e('Duplicate', 'arsol-pfw'); ?>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Customer Status -->
    <?php if ($proposal_status === 'publish'): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Customer View:', 'arsol-pfw'); ?></label>
        <div class="arsol-customer-status">
            <?php 
            $customer_url = wc_get_account_endpoint_url('project-view-proposal') . $proposal_id . '/';
            ?>
            <a href="<?php echo esc_url($customer_url); ?>" target="_blank" class="arsol-customer-link">
                <?php _e('View as Customer', 'arsol-pfw'); ?> ↗
            </a>
        </div>
    </div>
    <?php endif; ?>

</div>
