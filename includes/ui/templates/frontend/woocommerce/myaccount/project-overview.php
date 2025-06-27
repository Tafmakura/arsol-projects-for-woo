<?php
/**
 * Project Overview endpoint template
 *
 * Handles /my-account/project-overview/{project_id}/ endpoint
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// The following variables are passed from the endpoint function:
// $project
$project_id = $project['id'];
$project_title = $project['title'];
$current_tab = 'overview';

// Include unified project header
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/partials/frontend/project/project-header.php';

// --- Render Page Content ---
// Always render overview content for this template
\Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
    'project_content',
    ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project.php',
    compact('project')
);
?>
