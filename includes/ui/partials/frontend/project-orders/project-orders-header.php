<?php
/**
 * Project Orders Header
 * 
 * Header section for project orders page.
 * Variables: $project_id, $project_title (optional)
 */

if (!defined('ABSPATH')) exit;

// Set header configuration for project-related page
$header_type = 'project-page';
$page_title = __('Project Orders', 'arsol-pfw');
$page_subtitle = !empty($project_title) ? sprintf(__('Orders related to: %s', 'arsol-pfw'), $project_title) : '';

// Ensure project_title is set if not provided
if (empty($project_title) && !empty($project_id)) {
    $project_title = get_the_title($project_id);
}

// Include the reusable project header
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/partials/frontend/project/project-header.php';
