<?php
/**
 * Project Request Content Template: On Hold Status
 * Shows custom content and edit form for requests that are on hold
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="arsol-pfw-on-hold-content">
    <div class="arsol-pfw-on-hold-header">
        <h1><?php esc_html_e('Your Project Request is On Hold', 'arsol-pfw'); ?></h1>
        <p class="arsol-pfw-on-hold-description"><?php printf(esc_html__('Your project request "%s" has been temporarily placed on hold. You can still make changes to your request while we work on resolving any issues.', 'arsol-pfw'), '<strong>' . esc_html($post->post_title) . '</strong>'); ?></p>
    </div>

    <div class="arsol-pfw-on-hold-info">
        <h2><?php esc_html_e('Why is my request on hold?', 'arsol-pfw'); ?></h2>
        <ul>
            <li><?php esc_html_e('We may need additional information or clarification', 'arsol-pfw'); ?></li>
            <li><?php esc_html_e('Resource availability or scheduling conflicts', 'arsol-pfw'); ?></li>
            <li><?php esc_html_e('Budget or scope adjustments may be needed', 'arsol-pfw'); ?></li>
            <li><?php esc_html_e('Technical requirements need further evaluation', 'arsol-pfw'); ?></li>
        </ul>
        
        <h2><?php esc_html_e('What can you do?', 'arsol-pfw'); ?></h2>
        <ul>
            <li><?php esc_html_e('Update your request details below if needed', 'arsol-pfw'); ?></li>
            <li><?php esc_html_e('Contact our support team for more information', 'arsol-pfw'); ?></li>
            <li><?php esc_html_e('Wait for our team to reach out with next steps', 'arsol-pfw'); ?></li>
        </ul>
    </div>

    <div class="arsol-pfw-contact-info">
        <h3><?php esc_html_e('Need immediate assistance?', 'arsol-pfw'); ?></h3>
        <p><?php esc_html_e('Contact our team at support@example.com or call (555) 123-4567', 'arsol-pfw'); ?></p>
        <p><strong><?php esc_html_e('Expected response time:', 'arsol-pfw'); ?></strong> <?php esc_html_e('Within 2-3 business days', 'arsol-pfw'); ?></p>
    </div>
</div>

<div class="arsol-pfw-on-hold-form-section">
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
