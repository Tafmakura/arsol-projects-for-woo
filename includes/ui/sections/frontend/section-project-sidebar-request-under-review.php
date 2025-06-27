<?php
/**
 * Project Request Sidebar Template: Under Review Status
 * Shows review status and information for requests under review
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="arsol-pfw-project-status-info">
    <h3><?php esc_html_e('Request Status', 'arsol-pfw'); ?></h3>
    <p class="status-badge status-under-review"><?php esc_html_e('Under Review', 'arsol-pfw'); ?></p>
    <p class="status-description"><?php esc_html_e('Being evaluated by our team', 'arsol-pfw'); ?></p>
</div>

<div class="arsol-pfw-review-info">
    <h4><?php esc_html_e('Review Details', 'arsol-pfw'); ?></h4>
    <p><strong><?php esc_html_e('Started:', 'arsol-pfw'); ?></strong> <?php echo esc_html(get_the_date()); ?></p>
    <p><strong><?php esc_html_e('Expected completion:', 'arsol-pfw'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime('+5 days'))); ?></p>
</div>

<div class="arsol-pfw-project-action">
    <a href="/contact-us/" class="brxe-button bricks-button sm outline bricks-color-primary"><?php esc_html_e('Contact Review Team', 'arsol-pfw'); ?></a>
</div>

<div class="arsol-pfw-project-action">
    <a href="/faq/" class="brxe-button bricks-button sm outline bricks-color-secondary"><?php esc_html_e('Review Process FAQ', 'arsol-pfw'); ?></a>
</div>

<div class="arsol-pfw-review-tips">
    <h4><?php esc_html_e('What to expect', 'arsol-pfw'); ?></h4>
    <ul>
        <li><?php esc_html_e('Email updates on progress', 'arsol-pfw'); ?></li>
        <li><?php esc_html_e('Possible follow-up questions', 'arsol-pfw'); ?></li>
        <li><?php esc_html_e('Decision within 5 business days', 'arsol-pfw'); ?></li>
    </ul>
</div>
