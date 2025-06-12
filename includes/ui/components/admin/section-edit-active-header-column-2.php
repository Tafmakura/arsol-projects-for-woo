<?php
/**
 * Admin Template: Active Project - Original Request Details
 *
 * This template displays original request details within active project headers.
 * Only shows if the project was converted from a request.
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

// Get original request data
$original_request_id = get_post_meta($project_id, '_original_request_id', true);
$original_request_title = get_post_meta($project_id, '_original_request_title', true);
$original_request_content = get_post_meta($project_id, '_original_request_content', true);
$original_request_budget = get_post_meta($project_id, '_original_request_budget', true);
$original_request_start_date = get_post_meta($project_id, '_original_request_start_date', true);
$original_request_delivery_date = get_post_meta($project_id, '_original_request_delivery_date', true);
$original_request_creation_date = get_post_meta($project_id, '_original_request_creation_date', true);
$original_request_attachments = get_post_meta($project_id, '_original_request_attachments', true);

// Check if we have any original request data
if (!$original_request_id && !$original_request_budget && !$original_request_start_date && !$original_request_delivery_date) {
    return;
}
?>

<div class="arsol-original-request-details">
    <?php if (!empty($original_request_title)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Original Request Title:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-detail-value"><?php echo esc_html($original_request_title); ?></span>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_id)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Original Request ID:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-detail-value"><?php echo esc_html($original_request_id); ?></span>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_creation_date)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Request Created:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-detail-value"><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($original_request_creation_date))); ?></span>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_budget)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Requested Budget:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-detail-value">
            <?php echo wc_price($original_request_budget['amount'], array('currency' => $original_request_budget['currency'])); ?>
        </span>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_start_date)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Requested Start Date:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-detail-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_request_start_date))); ?></span>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_delivery_date)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Requested Delivery Date:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-detail-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_request_delivery_date))); ?></span>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_content)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Original Request Description:', 'arsol-pfw'); ?></label>
        <div class="arsol-request-description-content">
            <?php echo wp_kses_post(wpautop($original_request_content)); ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_attachments) && is_array($original_request_attachments)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Original Attachments:', 'arsol-pfw'); ?></label>
        <div class="arsol-request-attachments-list">
            <?php foreach ($original_request_attachments as $attachment): ?>
                <div class="arsol-attachment-item">
                    <span class="arsol-attachment-icon">📎</span>
                    <div class="arsol-attachment-info">
                        <a href="<?php echo esc_url($attachment['url']); ?>" 
                           class="arsol-attachment-link" 
                           target="_blank">
                            <?php echo esc_html($attachment['title']); ?>
                        </a>
                        <span class="arsol-attachment-size">
                            <?php echo esc_html($attachment['size']); ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
