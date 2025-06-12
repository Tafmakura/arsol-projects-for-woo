<?php
/**
 * Admin Template: Proposal - Original Request Details (Column 2)
 *
 * This template displays the original customer request details within proposal headers.
 * Shows comparison between original request and current proposal data.
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

// Get original request data
$original_request_id = get_post_meta($proposal_id, '_original_request_id', true);
$original_request_title = get_post_meta($proposal_id, '_original_request_title', true);
$original_request_content = get_post_meta($proposal_id, '_original_request_content', true);
$original_request_date = get_post_meta($proposal_id, '_original_request_date', true);
$original_budget = get_post_meta($proposal_id, '_original_request_budget', true);
$original_start_date = get_post_meta($proposal_id, '_original_request_start_date', true);
$original_delivery_date = get_post_meta($proposal_id, '_original_request_delivery_date', true);
$original_request_attachments = get_post_meta($proposal_id, '_original_request_attachments', true);

// Get current proposal data for comparison
$proposal_budget = get_post_meta($proposal_id, '_proposal_budget', true);
$proposal_start_date = get_post_meta($proposal_id, '_proposal_start_date', true);
$proposal_delivery_date = get_post_meta($proposal_id, '_proposal_delivery_date', true);

// Check if we have original request data
$has_original_data = $original_request_id || $original_budget || $original_start_date || $original_delivery_date || $original_request_title;
?>

<div class="arsol-proposal-request-details">
    
    <?php if ($has_original_data): ?>
    <!-- Original Request Header -->
    <div class="arsol-section-header">
        <h4><?php _e('Customer\'s Original Request', 'arsol-pfw'); ?></h4>
        <small><?php _e('Details from the initial customer request', 'arsol-pfw'); ?></small>
    </div>

    <?php if (!empty($original_request_title)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Original Request Title:', 'arsol-pfw'); ?></label>
        <span class="arsol-detail-value arsol-highlight-text">
            "<?php echo esc_html($original_request_title); ?>"
        </span>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_id)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Request ID:', 'arsol-pfw'); ?></label>
        <span class="arsol-detail-value">
            <code>#<?php echo esc_html($original_request_id); ?></code>
        </span>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_date)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Request Submitted:', 'arsol-pfw'); ?></label>
        <span class="arsol-detail-value">
            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($original_request_date))); ?>
        </span>
    </div>
    <?php endif; ?>

    <!-- Budget Comparison -->
    <?php if (!empty($original_budget) || !empty($proposal_budget)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Budget Analysis:', 'arsol-pfw'); ?></label>
        <div class="arsol-budget-comparison">
            <?php if (!empty($original_budget)): ?>
            <div class="arsol-budget-item">
                <span class="arsol-budget-label"><?php _e('Customer Budget:', 'arsol-pfw'); ?></span>
                <span class="arsol-budget-value requested">
                    <?php 
                    if (is_array($original_budget)) {
                        echo wc_price($original_budget['amount'], array('currency' => $original_budget['currency']));
                    } else {
                        echo wc_price($original_budget);
                    }
                    ?>
                </span>
            </div>
            <?php endif; ?>
            <?php if (!empty($proposal_budget)): ?>
            <div class="arsol-budget-item">
                <span class="arsol-budget-label"><?php _e('Proposed Budget:', 'arsol-pfw'); ?></span>
                <span class="arsol-budget-value proposed">
                    <?php 
                    if (is_array($proposal_budget)) {
                        echo wc_price($proposal_budget['amount'], array('currency' => $proposal_budget['currency']));
                    } else {
                        echo wc_price($proposal_budget);
                    }
                    ?>
                </span>
            </div>
            <?php endif; ?>
            
            <!-- Budget Variance -->
            <?php if (!empty($original_budget) && !empty($proposal_budget)): ?>
            <?php 
            $original_amount = is_array($original_budget) ? $original_budget['amount'] : $original_budget;
            $proposal_amount = is_array($proposal_budget) ? $proposal_budget['amount'] : $proposal_budget;
            $variance = $proposal_amount - $original_amount;
            $variance_percentage = $original_amount > 0 ? round(($variance / $original_amount) * 100, 1) : 0;
            ?>
            <div class="arsol-budget-item variance">
                <span class="arsol-budget-label"><?php _e('Variance:', 'arsol-pfw'); ?></span>
                <span class="arsol-budget-value <?php echo $variance >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo wc_price(abs($variance)); ?>
                    <small>(<?php echo $variance >= 0 ? '+' : '-'; ?><?php echo abs($variance_percentage); ?>%)</small>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Timeline Comparison -->
    <div class="arsol-timeline-comparison">
        <?php if (!empty($original_start_date) || !empty($proposal_start_date)): ?>
        <div class="form-field form-field-wide">
            <label><?php _e('Start Date Analysis:', 'arsol-pfw'); ?></label>
            <div class="arsol-date-comparison">
                <?php if (!empty($original_start_date)): ?>
                <div class="arsol-date-item">
                    <span class="arsol-date-label"><?php _e('Requested:', 'arsol-pfw'); ?></span>
                    <span class="arsol-date-value requested">
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_start_date))); ?>
                    </span>
                </div>
                <?php endif; ?>
                <?php if (!empty($proposal_start_date)): ?>
                <div class="arsol-date-item">
                    <span class="arsol-date-label"><?php _e('Proposed:', 'arsol-pfw'); ?></span>
                    <span class="arsol-date-value proposed">
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($proposal_start_date))); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($original_delivery_date) || !empty($proposal_delivery_date)): ?>
        <div class="form-field form-field-wide">
            <label><?php _e('Delivery Date Analysis:', 'arsol-pfw'); ?></label>
            <div class="arsol-date-comparison">
                <?php if (!empty($original_delivery_date)): ?>
                <div class="arsol-date-item">
                    <span class="arsol-date-label"><?php _e('Requested:', 'arsol-pfw'); ?></span>
                    <span class="arsol-date-value requested">
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_delivery_date))); ?>
                    </span>
                </div>
                <?php endif; ?>
                <?php if (!empty($proposal_delivery_date)): ?>
                <div class="arsol-date-item">
                    <span class="arsol-date-label"><?php _e('Proposed:', 'arsol-pfw'); ?></span>
                    <span class="arsol-date-value proposed">
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($proposal_delivery_date))); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($original_request_content)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Customer\'s Original Description:', 'arsol-pfw'); ?></label>
        <div class="arsol-description-content">
            <div class="arsol-description-preview">
                <?php echo wp_kses_post(wp_trim_words(wpautop($original_request_content), 30, '...')); ?>
            </div>
            <?php if (str_word_count($original_request_content) > 30): ?>
                <button type="button" class="arsol-expand-description button-link" 
                        data-full-content="<?php echo esc_attr($original_request_content); ?>"
                        data-short-content="<?php echo esc_attr(wp_trim_words($original_request_content, 30, '...')); ?>">
                    <span class="expand-text"><?php _e('Read Full Request', 'arsol-pfw'); ?></span>
                    <span class="collapse-text" style="display: none;"><?php _e('Show Less', 'arsol-pfw'); ?></span>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_attachments) && is_array($original_request_attachments)): ?>
    <div class="form-field form-field-wide">
        <label><?php _e('Customer\'s Attachments:', 'arsol-pfw'); ?></label>
        <div class="arsol-attachments-grid">
            <?php foreach ($original_request_attachments as $attachment_id): ?>
                <?php 
                $file = get_post($attachment_id);
                if ($file) :
                    $file_url = wp_get_attachment_url($attachment_id);
                    $file_name = basename(get_attached_file($attachment_id));
                    $file_size = size_format(filesize(get_attached_file($attachment_id)));
                ?>
                <div class="arsol-attachment-card">
                    <span class="arsol-attachment-icon">📎</span>
                    <div class="arsol-attachment-details">
                        <a href="<?php echo esc_url($file_url); ?>" target="_blank" class="arsol-attachment-link">
                            <?php echo esc_html($file_name); ?>
                        </a>
                        <small class="arsol-attachment-meta">
                            <?php echo esc_html($file_size); ?>
                        </small>
                    </div>
                </div>
                <?php endif; ?>
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
                    <span class="arsol-stat-value"><?php echo !empty($original_request_attachments) ? count($original_request_attachments) : 0; ?></span>
                    <span class="arsol-stat-label"><?php _e('Attachments', 'arsol-pfw'); ?></span>
                </div>
                <div class="arsol-stat-item">
                    <span class="arsol-stat-value"><?php echo !empty($original_request_content) ? str_word_count($original_request_content) : 0; ?></span>
                    <span class="arsol-stat-label"><?php _e('Words', 'arsol-pfw'); ?></span>
                </div>
                <?php if (!empty($original_request_date)): ?>
                <div class="arsol-stat-item">
                    <span class="arsol-stat-value"><?php echo floor((time() - strtotime($original_request_date)) / (60 * 60 * 24)); ?></span>
                    <span class="arsol-stat-label"><?php _e('Days Ago', 'arsol-pfw'); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- No Original Request Data -->
    <div class="arsol-no-data-message">
        <span class="arsol-icon">📝</span>
        <div class="arsol-message-content">
            <strong><?php _e('Direct Proposal', 'arsol-pfw'); ?></strong>
            <p><?php _e('This proposal was created directly without an initial customer request.', 'arsol-pfw'); ?></p>
        </div>
    </div>
    <?php endif; ?>

</div>
