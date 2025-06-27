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
// $current_tab, $query, $total_pages, $wp_button_class, $paged, $user_id should be available

// Load the existing projects listing template
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-projects-listing.php'; 