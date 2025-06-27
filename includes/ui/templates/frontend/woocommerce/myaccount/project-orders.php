<?php
/**
 * Project Orders endpoint template
 * 
 * Handles /my-account/project-orders/{project_id}/ endpoint
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables should be available from endpoint handler
// $project_id, $tab should be 'orders'

// For now, use the existing project template with orders tab
// This will load the appropriate project template and show the orders tab
$project_type = get_post_type($project_id);

switch ($project_type) {
    case 'arsol-project':
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/woocommerce/myaccount/project-overview.php';
        break;
    case 'arsol-pfw-proposal':
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/woocommerce/myaccount/project-view-proposal.php';
        break;
    case 'arsol-pfw-request':
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/woocommerce/myaccount/project-view-request.php';
        break;
    default:
        echo '<p>' . esc_html__('Unknown project type.', 'arsol-pfw') . '</p>';
        break;
} 