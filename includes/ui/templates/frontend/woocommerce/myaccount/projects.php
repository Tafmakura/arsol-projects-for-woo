<?php
/**
 * Projects endpoint template
 * 
 * Handles the main /my-account/projects/ endpoint with tabs
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from the endpoint handler
// $current_tab, $query, $paged, $total_pages, $wp_button_class, $user_id should be available
$current_tab = $current_tab ?? 'active';

// --- Header Section ---
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/partials/frontend/projects/projects-header.php';

// --- Content Section ---
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/partials/frontend/projects/projects-content.php'; 