<?php
/**
 * Admin Template: Request Details Metabox Content
 *
 * This template displays request details within the request header.
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
$creation_date = get_the_date('Y-m-d H:i:s', $request_id);
$budget_data = get_post_meta($request_id, '_request_budget', true);
$start_date = get_post_meta($request_id, '_request_start_date', true);
$delivery_date = get_post_meta($request_id, '_request_delivery_date', true);

// Get attachments
$attachments = get_attached_media('', $request_id);
?>

<div class="arsol-request-details-content">
    <div class="form-field form-field-wide">
        <label><?php _e('Request ID:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-detail-value"><?php echo esc_html($request_id); ?></span>
    </div>

    <div class="form-field form-field-wide">
        <label><?php _e('Created:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-detail-value"><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($creation_date))); ?></span>
    </div>

    <?php if (!empty($budget_data['amount'])): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Budget:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-detail-value">
            <?php echo wc_price($budget_data['amount'], array('currency' => $budget_data['currency'])); ?>
        </span>
    </div>
    <?php endif; ?>

    <?php if (!empty($start_date)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Required Start Date:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-detail-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($start_date))); ?></span>
    </div>
    <?php endif; ?>

    <?php if (!empty($delivery_date)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Required Delivery Date:', 'arsol-pfw'); ?></label>
        <span class="arsol-request-detail-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($delivery_date))); ?></span>
    </div>
    <?php endif; ?>

    <?php if (!empty($attachments)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Attachments:', 'arsol-pfw'); ?></label>
        <div class="arsol-request-attachments-list">
            <?php foreach ($attachments as $attachment): ?>
                <div class="arsol-attachment-item">
                    <span class="arsol-attachment-icon">📎</span>
                    <div class="arsol-attachment-info">
                        <a href="<?php echo wp_get_attachment_url($attachment->ID); ?>" 
                           class="arsol-attachment-link" 
                           target="_blank">
                            <?php echo esc_html($attachment->post_title); ?>
                        </a>
                        <span class="arsol-attachment-size">
                            <?php echo size_format(filesize(get_attached_file($attachment->ID))); ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
