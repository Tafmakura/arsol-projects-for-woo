<?php
/**
 * Project Request Content Template: Under Review Status
 * Shows review progress and information for requests being reviewed
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="arsol-pfw-under-review-content">
    <div class="arsol-pfw-under-review-header">
        <h1><?php esc_html_e('Your Project Request is Under Review', 'arsol-pfw'); ?></h1>
        <p class="arsol-pfw-under-review-description"><?php printf(esc_html__('Your project request "%s" is currently being reviewed by our team. We\'re carefully evaluating all the details to provide you with the best possible proposal.', 'arsol-pfw'), '<strong>' . esc_html($post->post_title) . '</strong>'); ?></p>
    </div>

    <div class="arsol-pfw-review-progress">
        <h2><?php esc_html_e('Review Progress', 'arsol-pfw'); ?></h2>
        <div class="progress-bar">
            <div class="progress-fill" style="width: 60%;"></div>
        </div>
        <p class="progress-text"><?php esc_html_e('Review in progress... 60% complete', 'arsol-pfw'); ?></p>
    </div>

    <div class="arsol-pfw-review-info">
        <h2><?php esc_html_e('What we\'re reviewing', 'arsol-pfw'); ?></h2>
        <div class="review-checklist">
            <div class="checklist-item completed">
                <span class="checkmark">✓</span>
                <span class="item-text"><?php esc_html_e('Project requirements and scope', 'arsol-pfw'); ?></span>
            </div>
            <div class="checklist-item completed">
                <span class="checkmark">✓</span>
                <span class="item-text"><?php esc_html_e('Budget and timeline feasibility', 'arsol-pfw'); ?></span>
            </div>
            <div class="checklist-item active">
                <span class="checkmark">⏳</span>
                <span class="item-text"><?php esc_html_e('Resource allocation and team assignment', 'arsol-pfw'); ?></span>
            </div>
            <div class="checklist-item">
                <span class="checkmark">○</span>
                <span class="item-text"><?php esc_html_e('Technical specifications and approach', 'arsol-pfw'); ?></span>
            </div>
            <div class="checklist-item">
                <span class="checkmark">○</span>
                <span class="item-text"><?php esc_html_e('Final approval and proposal preparation', 'arsol-pfw'); ?></span>
            </div>
        </div>
    </div>

    <div class="arsol-pfw-review-details">
        <h2><?php esc_html_e('Your Request Details', 'arsol-pfw'); ?></h2>
        <div class="arsol-pfw-request-summary">
            <p><?php echo wp_kses_post($post->post_content); ?></p>
            
            <?php
            $request_budget = get_post_meta($post->ID, '_arsol_pfw_request_budget', true);
            $start_date = get_post_meta($post->ID, '_arsol_pfw_request_start_date', true);
            $delivery_date = get_post_meta($post->ID, '_arsol_pfw_request_delivery_date', true);
            ?>
            
            <?php if (!empty($request_budget) && is_array($request_budget)) : ?>
                <p><strong><?php esc_html_e('Budget:', 'arsol-pfw'); ?></strong> <?php echo wc_price($request_budget['amount'], array('currency' => $request_budget['currency'])); ?></p>
            <?php endif; ?>
            
            <?php if ($start_date) : ?>
                <p><strong><?php esc_html_e('Requested Start Date:', 'arsol-pfw'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($start_date))); ?></p>
            <?php endif; ?>
            
            <?php if ($delivery_date) : ?>
                <p><strong><?php esc_html_e('Requested Delivery Date:', 'arsol-pfw'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($delivery_date))); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="arsol-pfw-review-timeline">
        <h2><?php esc_html_e('Expected Timeline', 'arsol-pfw'); ?></h2>
        <ul>
            <li><?php esc_html_e('Review completion: 3-5 business days', 'arsol-pfw'); ?></li>
            <li><?php esc_html_e('Decision notification: Within 24 hours of review completion', 'arsol-pfw'); ?></li>
            <li><?php esc_html_e('If approved, proposal creation begins immediately', 'arsol-pfw'); ?></li>
        </ul>
    </div>

    <div class="arsol-pfw-contact-info">
        <h3><?php esc_html_e('Questions about the review process?', 'arsol-pfw'); ?></h3>
        <p><?php esc_html_e('Contact our review team at reviews@example.com or call (555) 123-4567', 'arsol-pfw'); ?></p>
        <p><strong><?php esc_html_e('Review reference:', 'arsol-pfw'); ?></strong> #<?php echo esc_html($post->ID); ?>-REV</p>
    </div>
</div>
