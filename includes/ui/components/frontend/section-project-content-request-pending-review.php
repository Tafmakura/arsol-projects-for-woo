<?php
/**
 * Project Request Content Template: Pending Review Status
 * Shows the edit form for requests that are pending review
 */

if (!defined('ABSPATH')) {
    exit;
}

// Show edit form for pending-review requests
\Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
    'project_request_edit_form',
    ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/form-project-create-request.php',
    ['is_edit' => true, 'post' => $post]
); 