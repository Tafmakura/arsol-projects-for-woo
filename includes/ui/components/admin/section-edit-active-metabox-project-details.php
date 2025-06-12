<?php
/**
 * Admin Template: Active Project - Project Details
 *
 * This template displays project details within active project headers.
 * Shows budget, proposed dates, and invoice information.
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

// Get project budget data
$budget_data = get_post_meta($project_id, '_project_budget', true);
$recurring_budget_data = get_post_meta($project_id, '_project_recurring_budget', true);
$billing_interval = get_post_meta($project_id, '_project_billing_interval', true);
$billing_period = get_post_meta($project_id, '_project_billing_period', true);

// Get proposed dates
$proposed_start_date = get_post_meta($project_id, '_proposal_start_date', true);
$proposed_delivery_date = get_post_meta($project_id, '_proposal_delivery_date', true);
$proposed_expiration_date = get_post_meta($project_id, '_proposal_expiration_date', true);

// Get invoice information
$standard_order_id = get_post_meta($project_id, '_standard_order_id', true);
$recurring_order_id = get_post_meta($project_id, '_recurring_order_id', true);
$recurring_start_date = get_post_meta($project_id, '_project_recurring_start_date', true);
?>

<div class="arsol-project-details-content">
    <div class="form-field form-field-wide">
        <label><?php _e('Project ID:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-detail-value"><?php echo esc_html($project_id); ?></span>
    </div>

    <?php if (!empty($budget_data) && is_array($budget_data)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Proposed Budget:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-detail-value">
            <?php echo wc_price($budget_data['amount'], array('currency' => $budget_data['currency'])); ?>
        </span>
    </div>
    <?php endif; ?>

    <?php if (!empty($recurring_budget_data) && is_array($recurring_budget_data)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Proposed Recurring Budget:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-detail-value">
            <?php
            $intervals = array('1' => __('every', 'arsol-pfw'), '2' => __('every 2nd', 'arsol-pfw'), '3' => __('every 3rd', 'arsol-pfw'), '4' => __('every 4th', 'arsol-pfw'), '5' => __('every 5th', 'arsol-pfw'), '6' => __('every 6th', 'arsol-pfw'));
            $periods = array('day' => __('day', 'arsol-pfw'), 'week' => __('week', 'arsol-pfw'), 'month' => __('month', 'arsol-pfw'), 'year' => __('year', 'arsol-pfw'));
            $interval_text = isset($intervals[$billing_interval]) ? $intervals[$billing_interval] : '';
            $period_text = isset($periods[$billing_period]) ? $periods[$billing_period] : '';
            $cycle_text = trim($interval_text . ' ' . $period_text);
            
            $output_string = wc_price($recurring_budget_data['amount'], array('currency' => $recurring_budget_data['currency']));

            if (!empty($cycle_text)) {
                $output_string .= ' ' . esc_html($cycle_text);
            }
            
            if (!empty($recurring_start_date)) {
                $output_string .= ' ' . __('starting on', 'arsol-pfw') . ' <strong>' . esc_html(date_i18n(get_option('date_format'), strtotime($recurring_start_date))) . '</strong>';
            }
            
            echo $output_string;
            ?>
        </span>
    </div>
    <?php endif; ?>

    <?php if (!empty($proposed_start_date)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Proposed Start Date:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-detail-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($proposed_start_date))); ?></span>
    </div>
    <?php endif; ?>

    <?php if (!empty($proposed_delivery_date)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Proposed Delivery Date:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-detail-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($proposed_delivery_date))); ?></span>
    </div>
    <?php endif; ?>

    <?php if (!empty($proposed_expiration_date)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Proposal Expiration Date:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-detail-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($proposed_expiration_date))); ?></span>
    </div>
    <?php endif; ?>

    <?php if ($standard_order_id || $recurring_order_id): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Proposal Invoices:', 'arsol-pfw'); ?></label>
        <div class="arsol-invoice-links">
            <?php 
            $links = array();
            if ($standard_order_id) {
                $links[] = '<a href="' . get_edit_post_link($standard_order_id) . '" class="arsol-attachment-link">#' . esc_html($standard_order_id) . '</a>';
            }
            if ($recurring_order_id) {
                $links[] = '<a href="' . get_edit_post_link($recurring_order_id) . '" class="arsol-attachment-link">#' . esc_html($recurring_order_id) . '</a>';
            }
            echo implode(', ', $links);
            ?>
        </div>
    </div>
    <?php endif; ?>
</div> 