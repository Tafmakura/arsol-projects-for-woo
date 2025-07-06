<?php

namespace Arsol_Projects_For_Woo\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Setup Class
 * 
 * Simple coordinator for admin functionality - all includes and instantiations
 * are handled by the master includes setup file
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
        // All includes and instantiations are handled by master includes setup
        // This class just provides a coordination point for admin functionality
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
            'menu_setup' => 'Setup',
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