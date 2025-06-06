<?php
/**
 * Request Project Page
 *
 * This template acts as the main container for the request project form.
 * It calls the overridable content section.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

\Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
    'request_project_form',
    ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/form-request-project.php'
);
