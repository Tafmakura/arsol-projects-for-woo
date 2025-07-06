<?php

namespace Arsol_Projects_For_Woo\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Setup Class
 * 
 * Manages all admin-related classes for the plugin
 */
class Setup {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Get the singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->require_admin_files();
        $this->initialize_admin_classes();
    }

    /**
     * Require admin files
     */
    private function require_admin_files() {
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-capabilities.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-advanced.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-display.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-files.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-general.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-integrations.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-tools.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-setup-defaults.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-users.php';
    }

    /**
     * Initialize admin classes
     */
    private function initialize_admin_classes() {
        // Initialize capabilities first
        new Admin_Capabilities();
        
        // Initialize admin-only classes
        if (is_admin()) {
            new Settings_General();
            new Settings_Display();
            new Settings_Files();
            new Settings_Advanced();
            new Settings_Tools();
            new Setup_Defaults();
        }
        
        // Initialize classes that work in both admin and frontend
        new Settings_Integrations();
        new \Arsol_Projects_For_Woo\Admin\Setup();
        new Users();
    }

    /**
     * Check if current user is admin
     */
    public function is_admin_user() {
        return current_user_can('manage_options');
    }

    /**
     * Check if we're in admin area
     */
    public function is_admin_area() {
        return is_admin();
    }

    /**
     * Get admin class instances
     */
    public function get_admin_classes() {
        return [
            'capabilities' => 'Admin_Capabilities',
            'settings_general' => 'Settings_General',
            'settings_display' => 'Settings_Display',
            'settings_files' => 'Settings_Files',
            'settings_advanced' => 'Settings_Advanced',
            'settings_tools' => 'Settings_Tools',
            'settings_integrations' => 'Settings_Integrations',
            'setup_defaults' => 'Setup_Defaults',
            'setup' => 'Setup',
            'users' => 'Users',
        ];
    }

    /**
     * Get admin settings status
     */
    public function get_admin_status() {
        return [
            'admin_area' => $this->is_admin_area(),
            'admin_user' => $this->is_admin_user(),
            'classes_loaded' => count($this->get_admin_classes()),
        ];
    }
} 