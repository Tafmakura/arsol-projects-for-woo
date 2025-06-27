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
$current_tab = $current_tab ?? 'active';

// --- Header Section ---
wc_get_template(
    'partials/projects/projects-header.php',
    compact('current_tab'),
    'arsol-pfw/',
    ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/woocommerce/'
);

// --- Content Section ---
wc_get_template(
    'partials/projects/projects-content.php',
    compact('current_tab'),
    'arsol-pfw/',
    ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/woocommerce/'
); 