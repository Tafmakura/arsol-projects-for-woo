<?php
/**
 * Project Subscriptions endpoint template
 * 
 * Handles /my-account/project-subscriptions/{project_id}/ endpoint
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce Subscriptions is active
if (!class_exists('WC_Subscriptions')) {
    echo '<p>' . esc_html__('WooCommerce Subscriptions plugin is required for this feature.', 'arsol-pfw') . '</p>';
    return;
}

// The following variables are passed from the endpoint function:
// $project
$project_id = $project->ID;
$project_title = $project->post_title;
$current_tab = 'subscriptions';

// Include unified project header
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/partials/frontend/project/project-header.php';

// --- Render Page Content ---
// Always render subscriptions content for this template
\Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
    'project_subscriptions',
    ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-listing-subscriptions.php',
    compact('project')
);
?>
 