<?php
/**
 * Create Project Page
 *
 * This template acts as the main container for the create project form.
 * It calls the overridable content section.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if user can create projects
$user_id = get_current_user_id();
$can_create = \Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_projects($user_id);

if (!$can_create) {
    wc_add_notice(__('You do not have permission to create projects. Please contact the administrator if you believe this is an error.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
}

\Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
    'create_project_form',
    ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/form-create-project.php'
);
