<?php
/**
 * User Projects Page
 *
 * This template acts as the main container for the user projects listing.
 * It calls the overridable content section.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// The following variables are passed from the endpoint function:
// $user_projects, $has_items, $paged, $total_pages, $wp_button_class

\Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
    'projects_listing',
    ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-projects-listing.php',
    compact('user_projects', 'has_items', 'paged', 'total_pages', 'wp_button_class')
);