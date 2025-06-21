<?php
/**
 * Project Request Sidebar Template: Approved Status
 * Shows congratulations and next steps for approved requests
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="arsol-pfw-project-status-info">
    <h3><?php esc_html_e('Request Status', 'arsol-pfw'); ?></h3>
    <p class="status-badge status-approved"><?php esc_html_e('✅ Approved', 'arsol-pfw'); ?></p>
    <p class="status-description"><?php esc_html_e('Congratulations! Moving to proposal stage', 'arsol-pfw'); ?></p>
</div>

<div class="arsol-pfw-approval-info">
    <h4><?php esc_html_e('Next Steps', 'arsol-pfw'); ?></h4>
    <p><strong><?php esc_html_e('Approved on:', 'arsol-pfw'); ?></strong> <?php echo esc_html(get_the_modified_date()); ?></p>
    <p><strong><?php esc_html_e('Proposal expected:', 'arsol-pfw'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime('+3 days'))); ?></p>
</div>

<div class="arsol-pfw-project-action">
    <a href="/projects/" class="brxe-button bricks-button button-primary"><?php esc_html_e('View All Projects', 'arsol-pfw'); ?></a>
</div>

<div class="arsol-pfw-project-action">
    <a href="/contact-us/" class="brxe-button bricks-button sm outline bricks-color-primary"><?php esc_html_e('Contact Project Team', 'arsol-pfw'); ?></a>
</div>

<div class="arsol-pfw-project-action">
    <button type="button" class="brxe-button bricks-button sm outline bricks-color-secondary" onclick="window.print()"><?php esc_html_e('Print Approval Details', 'arsol-pfw'); ?></button>
</div>

<div class="arsol-pfw-approval-timeline">
    <h4><?php esc_html_e('What\'s Next', 'arsol-pfw'); ?></h4>
    <ul>
        <li><?php esc_html_e('Proposal creation: 2-3 days', 'arsol-pfw'); ?></li>
        <li><?php esc_html_e('Email notification sent', 'arsol-pfw'); ?></li>
        <li><?php esc_html_e('Review & approve proposal', 'arsol-pfw'); ?></li>
        <li><?php esc_html_e('Project begins!', 'arsol-pfw'); ?></li>
    </ul>
</div>
