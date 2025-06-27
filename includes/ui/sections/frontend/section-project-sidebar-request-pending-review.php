<?php
/**
 * Project Request Sidebar Template: Pending Review Status
 * Shows update and cancel buttons for pending review requests
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

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
