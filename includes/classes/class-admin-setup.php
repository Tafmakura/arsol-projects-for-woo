<?php
/**
 * Admin Setup Class
 *
 * Handles the admin menu setup for Arsol Projects For Woo.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    /**
     * Constructor
     */
    public function __construct() {
        // Add menu item
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    /**
     * Add menu item to WordPress admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Arsol Projects For Woo', 'arsol-pfw'),
            __('Arsol Projects', 'arsol-pfw'),
            'manage_options',
            'arsol-projects',
            array($this, 'render_settings_page'),
            'dashicons-portfolio',
            56
        );
    }

    /**
     * Render the settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Include the template
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/admin/page-admin-settings-general.php';
    }
}
