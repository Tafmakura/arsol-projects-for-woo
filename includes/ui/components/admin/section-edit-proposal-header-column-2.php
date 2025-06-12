<?php
/**
 * Admin Template: Edit Proposal - Customer Request Details Metabox
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables should be passed from the calling function:
// $original_budget, $original_start_date, $original_delivery_date, 
// $original_request_date, $original_request_title, $original_request_content, $original_request_attachments
?>

<div class="arsol-customer-request-details">
    <?php if ($original_request_title) : ?>
        <h3 class="arsol-request-title">
            <?php echo esc_html($original_request_title); ?>
        </h3>
    <?php endif; ?>

    <div class="arsol-request-details-grid">
        <?php if ($original_request_date) : ?>
        <div class="arsol-request-detail-item">
            <span class="arsol-request-detail-label"><?php _e('Request Date:', 'arsol-pfw'); ?></span>
            <span class="arsol-request-detail-value">
                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_request_date))); ?>
            </span>
        </div>
        <?php endif; ?>

        <?php if (!empty($original_budget)) : ?>
        <div class="arsol-request-detail-item">
            <span class="arsol-request-detail-label"><?php _e('Budget:', 'arsol-pfw'); ?></span>
            <span class="arsol-request-detail-value">
                <?php if (is_array($original_budget)) : ?>
                    <?php echo wc_price($original_budget['amount'], array('currency' => $original_budget['currency'])); ?>
                <?php else : ?>
                    <?php echo wc_price($original_budget); ?>
                <?php endif; ?>
            </span>
        </div>
        <?php endif; ?>

        <?php if ($original_start_date) : ?>
        <div class="arsol-request-detail-item">
            <span class="arsol-request-detail-label"><?php _e('Start Date:', 'arsol-pfw'); ?></span>
            <span class="arsol-request-detail-value">
                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_start_date))); ?>
            </span>
        </div>
        <?php endif; ?>

        <?php if ($original_delivery_date) : ?>
        <div class="arsol-request-detail-item">
            <span class="arsol-request-detail-label"><?php _e('Delivery Date:', 'arsol-pfw'); ?></span>
            <span class="arsol-request-detail-value">
                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_delivery_date))); ?>
            </span>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($original_request_content) : ?>
    <div class="arsol-request-description">
        <h4><?php _e('Original Request Description:', 'arsol-pfw'); ?></h4>
        <div class="arsol-request-description-content">
            <?php echo wp_kses_post(wpautop($original_request_content)); ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($original_request_attachments && is_array($original_request_attachments)) : ?>
    <div class="arsol-request-attachments">
        <h4><?php _e('Original Request Attachments:', 'arsol-pfw'); ?></h4>
        <div class="arsol-request-attachments-list">
            <?php foreach ($original_request_attachments as $attachment_id) : ?>
                <?php 
                $file = get_post($attachment_id);
                if ($file) :
                    $file_url = wp_get_attachment_url($attachment_id);
                    $file_name = basename(get_attached_file($attachment_id));
                    $file_size = size_format(filesize(get_attached_file($attachment_id)));
                ?>
                <div class="arsol-attachment-item">
                    <span class="arsol-attachment-icon">
                        📎
                    </span>
                    <div class="arsol-attachment-info">
                        <a href="<?php echo esc_url($file_url); ?>" target="_blank" class="arsol-attachment-link">
                            <?php echo esc_html($file_name); ?>
                        </a>
                        <small class="arsol-attachment-size">
                            <?php echo esc_html($file_size); ?>
                        </small>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
