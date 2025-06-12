<?php
/**
 * Admin Template: Active Project - Project Details (Column 2)
 *
 * This template displays comprehensive project details and metadata for active projects.
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

// Get current project data for comparison
$project_budget = get_post_meta($project_id, '_project_budget', true);
$project_start_date = get_post_meta($project_id, '_project_start_date', true);
$project_due_date = get_post_meta($project_id, '_project_due_date', true);

// Check if we have any original request data
$has_original_data = $original_request_id || $original_request_budget || $original_request_start_date || $original_request_delivery_date;
?>

<div class="arsol-project-details-content">
    
    <?php if ($has_original_data): ?>
    <!-- Original Request Information -->
    <div class="arsol-section-header">
        <h4><?php _e('Original Request', 'arsol-pfw'); ?></h4>
        <small><?php _e('Data from the initial customer request', 'arsol-pfw'); ?></small>
    </div>

    <?php if (!empty($original_request_title)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Original Title:', 'arsol-pfw'); ?></label>
        <span class="arsol-detail-value arsol-highlight-text"><?php echo esc_html($original_request_title); ?></span>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_id)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Request ID:', 'arsol-pfw'); ?></label>
        <span class="arsol-detail-value">
            <code><?php echo esc_html($original_request_id); ?></code>
        </span>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_creation_date)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Request Created:', 'arsol-pfw'); ?></label>
        <span class="arsol-detail-value">
            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($original_request_creation_date))); ?>
        </span>
    </div>
    <?php endif; ?>

    <!-- Budget Comparison -->
    <?php if (!empty($original_request_budget) || !empty($project_budget)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Budget Comparison:', 'arsol-pfw'); ?></label>
        <div class="arsol-budget-comparison">
            <?php if (!empty($original_request_budget)): ?>
            <div class="arsol-budget-item">
                <span class="arsol-budget-label"><?php _e('Requested:', 'arsol-pfw'); ?></span>
                <span class="arsol-budget-value original">
                    <?php echo wc_price($original_request_budget['amount'], array('currency' => $original_request_budget['currency'])); ?>
                </span>
            </div>
            <?php endif; ?>
            <?php if (!empty($project_budget)): ?>
            <div class="arsol-budget-item">
                <span class="arsol-budget-label"><?php _e('Approved:', 'arsol-pfw'); ?></span>
                <span class="arsol-budget-value approved">
                    <?php echo wc_price($project_budget['amount'], array('currency' => $project_budget['currency'])); ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Date Comparison -->
    <div class="arsol-dates-comparison">
        <?php if (!empty($original_request_start_date) || !empty($project_start_date)): ?>
        <div class="form-field form-field-wide">
            <label><?php _e('Start Date Comparison:', 'arsol-pfw'); ?></label>
            <div class="arsol-date-comparison">
                <?php if (!empty($original_request_start_date)): ?>
                <div class="arsol-date-item">
                    <span class="arsol-date-label"><?php _e('Requested:', 'arsol-pfw'); ?></span>
                    <span class="arsol-date-value original">
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_request_start_date))); ?>
                    </span>
                </div>
                <?php endif; ?>
                <?php if (!empty($project_start_date)): ?>
                <div class="arsol-date-item">
                    <span class="arsol-date-label"><?php _e('Actual:', 'arsol-pfw'); ?></span>
                    <span class="arsol-date-value actual">
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($project_start_date))); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($original_request_delivery_date) || !empty($project_due_date)): ?>
        <div class="form-field form-field-wide">
            <label><?php _e('Due Date Comparison:', 'arsol-pfw'); ?></label>
            <div class="arsol-date-comparison">
                <?php if (!empty($original_request_delivery_date)): ?>
                <div class="arsol-date-item">
                    <span class="arsol-date-label"><?php _e('Requested:', 'arsol-pfw'); ?></span>
                    <span class="arsol-date-value original">
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_request_delivery_date))); ?>
                    </span>
                </div>
                <?php endif; ?>
                <?php if (!empty($project_due_date)): ?>
                <div class="arsol-date-item">
                    <span class="arsol-date-label"><?php _e('Scheduled:', 'arsol-pfw'); ?></span>
                    <span class="arsol-date-value actual">
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($project_due_date))); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($original_request_content)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Original Description:', 'arsol-pfw'); ?></label>
        <div class="arsol-description-content">
            <?php echo wp_kses_post(wp_trim_words(wpautop($original_request_content), 50, '...')); ?>
            <?php if (str_word_count($original_request_content) > 50): ?>
                <button type="button" class="arsol-expand-description" data-full-content="<?php echo esc_attr($original_request_content); ?>">
                    <?php _e('Read More', 'arsol-pfw'); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_attachments) && is_array($original_request_attachments)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Original Attachments:', 'arsol-pfw'); ?></label>
        <div class="arsol-attachments-grid">
            <?php foreach ($original_request_attachments as $attachment): ?>
                <div class="arsol-attachment-card">
                    <span class="arsol-attachment-icon">📎</span>
                    <div class="arsol-attachment-details">
                        <a href="<?php echo esc_url($attachment['url']); ?>" 
                           class="arsol-attachment-link" 
                           target="_blank">
                            <?php echo esc_html($attachment['title']); ?>
                        </a>
                        <small class="arsol-attachment-meta">
                            <?php echo esc_html($attachment['size']); ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- No Original Request Data -->
    <div class="arsol-no-data-message">
        <span class="arsol-icon">ℹ️</span>
        <div class="arsol-message-content">
            <strong><?php _e('Direct Project', 'arsol-pfw'); ?></strong>
            <p><?php _e('This project was created directly without an initial customer request.', 'arsol-pfw'); ?></p>
        </div>
    </div>
    <?php endif; ?>

</div>
