<?php
/**
 * Project Overview endpoint template
 * 
 * Handles individual project view at /my-account/projects/{project-id}/
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables should be available: $project_id, $project, $user_id, etc.

// --- Header Section ---
wc_get_template(
    'partials/project-overview/project-overview-header.php',
    compact('project_id', 'project', 'user_id'),
    'arsol-pfw/',
    ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/woocommerce/'
);

// --- Content Section ---
wc_get_template(
    'partials/project-overview/project-overview-content.php',
    compact('project_id', 'project', 'user_id'),
    'arsol-pfw/',
    ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/woocommerce/'
);

// --- Sidebar Section ---
wc_get_template(
    'partials/project-overview/project-overview-sidebar.php',
    compact('project_id', 'project', 'user_id'),
    'arsol-pfw/',
    ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/woocommerce/'
); 