<?php
/**
 * Project Request Content Template: On Hold Status
 * Shows custom content and edit form for requests that are on hold
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the default message for on-hold requests
$default_message = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_default_message('project_request_on_hold_message');
?>

<div class="arsol-pfw-request-content arsol-pfw-on-hold-content">
    <div class="arsol-pfw-default-message arsol-pfw-on-hold-message">
        <?php echo wp_kses_post(wpautop($default_message)); ?>
    </div>
</div>

<div class="arsol-pfw-form-section arsol-pfw-on-hold-form-section">
    <h2><?php esc_html_e('Update Your Request', 'arsol-pfw'); ?></h2>
    <p><?php esc_html_e('You can make changes to your request while it\'s on hold. Any updates will be reviewed when we resume processing.', 'arsol-pfw'); ?></p>
    
    <?php
    // Show edit form for on-hold requests
    \Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
        'project_request_edit_form',
        ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/form-project-create-request.php',
        ['is_edit' => true, 'post' => $post]
    );
    ?>
</div>
