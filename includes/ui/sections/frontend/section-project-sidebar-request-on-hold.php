<?php
/**
 * Project Request Sidebar Template: On Hold Status
 * Shows status info, update/cancel buttons, and support actions for on-hold requests
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="arsol-pfw-project-status-info">
    <h3><?php esc_html_e('Request Status', 'arsol-pfw'); ?></h3>
    <p class="status-badge status-on-hold"><?php esc_html_e('On Hold', 'arsol-pfw'); ?></p>
    <p class="status-description"><?php esc_html_e('Temporarily paused - still editable', 'arsol-pfw'); ?></p>
</div>

<div class="arsol-pfw-project-action">
    <button type="submit" form="arsol-request-edit-form" class="brxe-button bricks-button button-primary request-action-btn">
        <?php esc_html_e('Update Request', 'arsol-pfw'); ?>
    </button>
</div>

<div class="arsol-pfw-project-action">
    <button type="button" class="brxe-button bricks-button sm outline bricks-color-primary cancel-request-btn" data-confirm-text="<?php esc_attr_e('Are you sure you want to cancel this request?', 'arsol-pfw'); ?>">
        <?php esc_html_e('Cancel Request', 'arsol-pfw'); ?>
    </button>
</div>

<div class="arsol-pfw-project-action">
    <a href="/contact-us/" class="brxe-button bricks-button sm outline bricks-color-primary"><?php esc_html_e('Contact Support', 'arsol-pfw'); ?></a>
</div>

<div class="arsol-pfw-project-action">
    <a href="/services/" class="brxe-button bricks-button sm outline bricks-color-primary"><?php esc_html_e('View Our Services', 'arsol-pfw'); ?></a>
</div>

<div class="arsol-pfw-on-hold-timeline">
    <h4><?php esc_html_e('Expected Timeline', 'arsol-pfw'); ?></h4>
    <ul>
        <li><?php esc_html_e('Review resumption: TBD', 'arsol-pfw'); ?></li>
        <li><?php esc_html_e('Next update: Within 7 days', 'arsol-pfw'); ?></li>
    </ul>
</div>
