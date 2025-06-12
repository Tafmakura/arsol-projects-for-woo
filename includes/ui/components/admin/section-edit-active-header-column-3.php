<?php
/**
 * Admin Template: Active Project - Status & Actions (Column 3)
 *
 * This template displays project status, progress, and action buttons for active projects.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the current post
global $post;

if (!$post || $post->post_type !== 'arsol-project') {
    return;
}

$project_id = $post->ID;

// Get project status
$project_statuses = wp_get_object_terms($project_id, 'arsol-project-status');
$current_status = !empty($project_statuses) ? $project_statuses[0] : null;

// Get project meta data
$project_budget = get_post_meta($project_id, '_project_budget', true);
$project_due_date = get_post_meta($project_id, '_project_due_date', true);
$project_start_date = get_post_meta($project_id, '_proposal_start_date', true);
$completion_percentage = get_post_meta($project_id, '_project_completion_percentage', true);

// Get associated orders
$project_orders = get_post_meta($project_id, '_project_orders', true);
$order_count = is_array($project_orders) ? count($project_orders) : 0;

// Get associated subscriptions
$project_subscriptions = get_post_meta($project_id, '_project_subscriptions', true);
$subscription_count = is_array($project_subscriptions) ? count($project_subscriptions) : 0;
?>

<div class="arsol-project-status-actions">
    
    <!-- Project Status -->
    <div class="form-field form-field-wide">
        <label><?php _e('Project Status:', 'arsol-pfw'); ?></label>
        <span class="arsol-status-badge <?php echo $current_status ? 'status-' . esc_attr($current_status->slug) : 'status-unknown'; ?>">
            <?php echo $current_status ? esc_html($current_status->name) : __('No Status', 'arsol-pfw'); ?>
        </span>
    </div>

    <!-- Progress Bar -->
    <?php if (!empty($completion_percentage)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Progress:', 'arsol-pfw'); ?></label>
        <div class="arsol-progress-container">
            <div class="arsol-progress-bar">
                <div class="arsol-progress-fill" style="width: <?php echo intval($completion_percentage); ?>%"></div>
            </div>
            <span class="arsol-progress-text"><?php echo intval($completion_percentage); ?>%</span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Due Date Alert -->
    <?php if (!empty($project_due_date)): ?>
    <?php 
    $due_date = new DateTime($project_due_date);
    $today = new DateTime();
    $days_remaining = $today->diff($due_date)->days;
    $is_overdue = $due_date < $today;
    ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Due Date:', 'arsol-pfw'); ?></label>
        <span class="arsol-due-date <?php echo $is_overdue ? 'overdue' : ($days_remaining <= 7 ? 'urgent' : ''); ?>">
            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($project_due_date))); ?>
            <?php if ($is_overdue): ?>
                <small class="arsol-overdue-text"><?php _e('(Overdue)', 'arsol-pfw'); ?></small>
            <?php elseif ($days_remaining <= 7): ?>
                <small class="arsol-urgent-text"><?php printf(__('(%d days left)', 'arsol-pfw'), $days_remaining); ?></small>
            <?php endif; ?>
        </span>
    </div>
    <?php endif; ?>

    <!-- Associated Orders & Subscriptions -->
    <div class="form-field form-field-wide">
        <label><?php _e('Connected Commerce:', 'arsol-pfw'); ?></label>
        <div class="arsol-commerce-stats">
            <span class="arsol-stat-item">
                <strong><?php echo intval($order_count); ?></strong> <?php _e('Orders', 'arsol-pfw'); ?>
            </span>
            <?php if (class_exists('WC_Subscriptions')): ?>
            <span class="arsol-stat-item">
                <strong><?php echo intval($subscription_count); ?></strong> <?php _e('Subscriptions', 'arsol-pfw'); ?>
            </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="form-field form-field-wide">
        <label><?php _e('Quick Actions:', 'arsol-pfw'); ?></label>
        <div class="arsol-quick-actions">
            <button type="button" class="button button-secondary arsol-view-orders" data-project-id="<?php echo esc_attr($project_id); ?>">
                <?php _e('View Orders', 'arsol-pfw'); ?>
            </button>
            <?php if (class_exists('WC_Subscriptions') && $subscription_count > 0): ?>
            <button type="button" class="button button-secondary arsol-view-subscriptions" data-project-id="<?php echo esc_attr($project_id); ?>">
                <?php _e('View Subscriptions', 'arsol-pfw'); ?>
            </button>
            <?php endif; ?>
            <button type="button" class="button button-primary arsol-update-status" data-project-id="<?php echo esc_attr($project_id); ?>">
                <?php _e('Update Status', 'arsol-pfw'); ?>
            </button>
        </div>
    </div>

</div>