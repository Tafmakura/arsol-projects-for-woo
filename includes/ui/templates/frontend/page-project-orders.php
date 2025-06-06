<?php
/**
 * Project Orders Page
 *
 * This template acts as the main container for the project orders content.
 * It calls the overridable content section.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// The $project variable is passed from the render_project_page function
if (!isset($project)) {
    return;
}

// Render the project orders content, allowing for overrides
\Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
    'project_orders',
    ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-content-orders.php',
    compact('project')
);