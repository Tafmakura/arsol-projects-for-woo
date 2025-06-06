<?php
/**
 * Main Project Page Template
 *
 * This template provides the main structure for a single project page,
 * including the header, navigation, and the main content area. It acts
 * as a frame for the different project sections like overview, orders, etc.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// The following variables are passed from the render_project_page function:
// $project, $project_id, $tab

// Get project title for breadcrumb/heading
$project_title = get_the_title($project_id);
$current_tab = $tab; // Pass current tab to navigation component

// Render project header
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-header.php';

// Render project navigation
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-navigation.php';
?>

<div class="arsol-project-content-wrapper">
    <div class="arsol-project-main-content">
        <?php
        // Render content based on tab
        switch ($tab) {
            case 'orders':
                // This should be an overridable template in the future if needed
                include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-orders.php';
                break;
            case 'subscriptions':
                // This should be an overridable template in the future if needed
                include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-subscriptions.php';
                break;
            case 'overview':
            default:
                // This template already uses the override system
                include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project-overview.php';
                break;
        }
        ?>
    </div>
    <div class="arsol-project-sidebar-content">
        <?php
        // Include the project sidebar based on the current tab
        switch ($tab) {
            case 'orders':
                include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-sidebar-orders.php';
                break;
            case 'subscriptions':
                include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-sidebar-subscriptions.php';
                break;
            case 'overview':
            default:
                include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-sidebar-overview.php';
                break;
        }
        ?>
    </div>
</div>
