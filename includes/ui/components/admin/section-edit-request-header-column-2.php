<?php
/**
 * Admin Template: Request - Request Details (Column 2)
 *
 * This template displays comprehensive request details and metadata within the request header.
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
$modified_date = get_the_modified_date('Y-m-d H:i:s', $request_id);
$budget_data = get_post_meta($request_id, '_request_budget', true);
$start_date = get_post_meta($request_id, '_request_start_date', true);
$delivery_date = get_post_meta($request_id, '_request_delivery_date', true);

// Get additional request metadata
$priority = get_post_meta($request_id, '_request_priority', true);
$urgency_level = get_post_meta($request_id, '_request_urgency', true);
$request_category = get_post_meta($request_id, '_request_category', true);
$customer_notes = get_post_meta($request_id, '_request_customer_notes', true);

// Get attachments
$attachments = get_attached_media('', $request_id);

// Calculate request timeline
$request_age_days = floor((time() - strtotime($creation_date)) / (60 * 60 * 24));
$is_modified = $creation_date !== $modified_date;

// Calculate timeline if dates are provided
$timeline_days = 0;
if (!empty($start_date) && !empty($delivery_date)) {
    $timeline_days = floor((strtotime($delivery_date) - strtotime($start_date)) / (60 * 60 * 24));
}
?>

<div class="arsol-request-details-content">
    
    <!-- Request Header Info -->
    <div class="arsol-section-header">
        <h4><?php _e('Request Information', 'arsol-pfw'); ?></h4>
        <small><?php _e('Customer requirements and specifications', 'arsol-pfw'); ?></small>
    </div>

    <div class="form-field form-field-wide">
        <label><?php _e('Request ID:', 'arsol-pfw'); ?></label>
        <span class="arsol-detail-value">
            <code>#<?php echo esc_html($request_id); ?></code>
        </span>
    </div>

    <div class="form-field form-field-wide">
        <label><?php _e('Submitted:', 'arsol-pfw'); ?></label>
        <span class="arsol-detail-value">
            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($creation_date))); ?>
            <small class="arsol-age-indicator">
                (<?php 
                if ($request_age_days == 0) {
                    _e('today', 'arsol-pfw');
                } elseif ($request_age_days == 1) {
                    _e('1 day ago', 'arsol-pfw');
                } else {
                    printf(__('%d days ago', 'arsol-pfw'), $request_age_days);
                }
                ?>)
            </small>
        </span>
    </div>

    <?php if ($is_modified): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Last Modified:', 'arsol-pfw'); ?></label>
        <span class="arsol-detail-value">
            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($modified_date))); ?>
        </span>
    </div>
    <?php endif; ?>

    <!-- Budget Information -->
    <?php if (!empty($budget_data['amount'])): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Customer Budget:', 'arsol-pfw'); ?></label>
        <span class="arsol-detail-value arsol-budget-value">
            <?php echo wc_price($budget_data['amount'], array('currency' => $budget_data['currency'])); ?>
        </span>
    </div>
    <?php endif; ?>

    <!-- Priority & Urgency -->
    <?php if (!empty($priority) || !empty($urgency_level)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Priority Level:', 'arsol-pfw'); ?></label>
        <div class="arsol-priority-indicators">
            <?php if (!empty($priority)): ?>
                <span class="arsol-priority-badge priority-<?php echo esc_attr(strtolower($priority)); ?>">
                    <?php echo esc_html($priority); ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($urgency_level)): ?>
                <span class="arsol-urgency-badge urgency-<?php echo esc_attr(strtolower($urgency_level)); ?>">
                    <?php echo esc_html($urgency_level); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Timeline Requirements -->
    <div class="arsol-timeline-section">
        <?php if (!empty($start_date)): ?>
        <div class="form-field form-field-wide">
            <label><?php _e('Required Start Date:', 'arsol-pfw'); ?></label>
            <span class="arsol-detail-value arsol-date-value">
                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($start_date))); ?>
                <?php
                $days_until_start = floor((strtotime($start_date) - time()) / (60 * 60 * 24));
                if ($days_until_start >= 0) {
                    echo '<small class="arsol-timeline-indicator">(' . sprintf(__('in %d days', 'arsol-pfw'), $days_until_start) . ')</small>';
                } else {
                    echo '<small class="arsol-timeline-indicator overdue">(' . sprintf(__('%d days ago', 'arsol-pfw'), abs($days_until_start)) . ')</small>';
                }
                ?>
            </span>
        </div>
        <?php endif; ?>

        <?php if (!empty($delivery_date)): ?>
        <div class="form-field form-field-wide">
            <label><?php _e('Required Delivery Date:', 'arsol-pfw'); ?></label>
            <span class="arsol-detail-value arsol-date-value">
                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($delivery_date))); ?>
                <?php
                $days_until_delivery = floor((strtotime($delivery_date) - time()) / (60 * 60 * 24));
                if ($days_until_delivery >= 0) {
                    echo '<small class="arsol-timeline-indicator">(' . sprintf(__('in %d days', 'arsol-pfw'), $days_until_delivery) . ')</small>';
                } else {
                    echo '<small class="arsol-timeline-indicator overdue">(' . sprintf(__('%d days overdue', 'arsol-pfw'), abs($days_until_delivery)) . ')</small>';
                }
                ?>
            </span>
        </div>
        <?php endif; ?>

        <?php if ($timeline_days > 0): ?>
        <div class="form-field form-field-wide">
            <label><?php _e('Project Duration:', 'arsol-pfw'); ?></label>
            <span class="arsol-detail-value">
                <strong><?php echo intval($timeline_days); ?></strong> <?php _e('days', 'arsol-pfw'); ?>
                <small>(<?php printf(__('≈ %.1f weeks', 'arsol-pfw'), $timeline_days / 7); ?>)</small>
            </span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Customer Notes -->
    <?php if (!empty($customer_notes)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Customer Notes:', 'arsol-pfw'); ?></label>
        <div class="arsol-customer-notes">
            <?php echo wp_kses_post(wpautop($customer_notes)); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Attachments -->
    <?php if (!empty($attachments)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Attachments:', 'arsol-pfw'); ?></label>
        <div class="arsol-attachments-grid">
            <?php foreach ($attachments as $attachment): ?>
                <div class="arsol-attachment-card">
                    <span class="arsol-attachment-icon">📎</span>
                    <div class="arsol-attachment-details">
                        <a href="<?php echo wp_get_attachment_url($attachment->ID); ?>" 
                           class="arsol-attachment-link" 
                           target="_blank">
                            <?php echo esc_html($attachment->post_title); ?>
                        </a>
                        <small class="arsol-attachment-meta">
                            <?php echo size_format(filesize(get_attached_file($attachment->ID))); ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Request Summary -->
    <div class="form-field form-field-wide">
        <label><?php _e('Request Summary:', 'arsol-pfw'); ?></label>
        <div class="arsol-request-summary">
            <div class="arsol-summary-stats">
                <div class="arsol-stat-item">
                    <span class="arsol-stat-value"><?php echo count($attachments); ?></span>
                    <span class="arsol-stat-label"><?php _e('Files', 'arsol-pfw'); ?></span>
                </div>
                <div class="arsol-stat-item">
                    <span class="arsol-stat-value"><?php echo str_word_count(get_the_content()); ?></span>
                    <span class="arsol-stat-label"><?php _e('Words', 'arsol-pfw'); ?></span>
                </div>
                <div class="arsol-stat-item">
                    <span class="arsol-stat-value"><?php echo intval($request_age_days); ?></span>
                    <span class="arsol-stat-label"><?php _e('Days Old', 'arsol-pfw'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Preview -->
    <?php if (has_excerpt() || get_the_content()): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Description Preview:', 'arsol-pfw'); ?></label>
        <div class="arsol-content-preview">
            <?php 
            $content = has_excerpt() ? get_the_excerpt() : get_the_content();
            echo wp_kses_post(wp_trim_words($content, 25, '...'));
            ?>
            <?php if (str_word_count($content) > 25): ?>
                <button type="button" class="arsol-view-full-content button-link">
                    <?php _e('View Full Description', 'arsol-pfw'); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>
