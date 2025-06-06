<?php
/**
 * Project Requests Page
 *
 * This template acts as the main container for the project requests listing.
 * It calls the overridable content section.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// The following variables are passed from the endpoint function:
// $user_projects, $has_items, $paged, $total_pages, $wp_button_class, $query

\Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
    'project_requests_listings',
    ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-requests-listings.php',
    compact('user_projects', 'has_items', 'paged', 'total_pages', 'wp_button_class', 'query')
); 