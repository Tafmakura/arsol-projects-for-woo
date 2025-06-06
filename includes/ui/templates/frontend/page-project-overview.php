<?php
/**
 * Project Overview Page
 *
 * This template acts as the main container for the project overview page.
 * It calls the overridable content section.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.1
 */

defined('ABSPATH') || exit;

// The $project variable is passed from the render_project_page function in class-frontend-woocommerce-endpoints.php
if (!isset($project)) {
    return;
}

// Render the project overview content, allowing for overrides
\Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
    'project_overview',
    ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-content-overview.php',
    compact('project')
);

