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

// Variables should be available from endpoint handler
// $project_id, $tab, $project_data, etc.

// Get project data
$project = get_post($project_id);
$project_type = get_post_type($project_id);

// Determine which template to load based on project type
switch ($project_type) {
    case 'arsol-project':
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project-active.php';
        break;
    case 'arsol-pfw-proposal':
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project-proposal.php';
        break;
    case 'arsol-pfw-request':
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project-request.php';
        break;
    default:
        echo '<p>' . esc_html__('Unknown project type.', 'arsol-pfw') . '</p>';
        break;
} 