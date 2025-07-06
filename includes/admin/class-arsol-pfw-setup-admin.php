<?php
/**
 * Admin Setup Class for Arsol Projects for WooCommerce
 *
 * Initializes all admin functionality including settings, user management,
 * and backend administrative features.
 *
 * @package Arsol_PFW\Setup
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Arsol_PFW\Setup;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Setup Class
 *
 * Manages initialization of all admin components.
 */
class Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->init_classes();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('arsol_pfw_init', array($this, 'init'), 10);
        add_action('admin_init', array($this, 'admin_init'));
    }
    
    /**
     * Initialize admin classes
     */
    private function init_classes() {
        // Only initialize admin classes in admin context
        if (!is_admin()) {
            return;
        }
        
        // Include admin classes
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/admin/class-arsol-pfw-settings-general.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/admin/class-arsol-pfw-settings-display.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/admin/class-arsol-pfw-settings-files.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/admin/class-arsol-pfw-settings-advanced.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/admin/class-arsol-pfw-settings-integrations.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/admin/class-arsol-pfw-settings-tools.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/admin/class-arsol-pfw-users.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/admin/class-arsol-pfw-setup-defaults.php';
        
        // Initialize admin classes that need to be loaded immediately
        if (class_exists('\Arsol_PFW\Admin\Settings_General')) {
            new \Arsol_PFW\Admin\Settings_General();
        }
        
        if (class_exists('\Arsol_PFW\Admin\Settings_Display')) {
            new \Arsol_PFW\Admin\Settings_Display();
        }
        
        if (class_exists('\Arsol_PFW\Admin\Settings_Files')) {
            new \Arsol_PFW\Admin\Settings_Files();
        }
        
        if (class_exists('\Arsol_PFW\Admin\Settings_Advanced')) {
            new \Arsol_PFW\Admin\Settings_Advanced();
        }
        
        if (class_exists('\Arsol_PFW\Admin\Settings_Integrations')) {
            new \Arsol_PFW\Admin\Settings_Integrations();
        }
        
        if (class_exists('\Arsol_PFW\Admin\Settings_Tools')) {
            new \Arsol_PFW\Admin\Settings_Tools();
        }
        
        if (class_exists('\Arsol_PFW\Admin\Users')) {
            new \Arsol_PFW\Admin\Users();
        }
        
        if (class_exists('\Arsol_PFW\Admin\Setup_Defaults')) {
            new \Arsol_PFW\Admin\Setup_Defaults();
        }
    }
    
    /**
     * Initialize admin functionality
     */
    public function init() {
        // Admin initialization actions
        do_action('arsol_pfw_admin_init');
    }
    
    /**
     * Admin init hook
     */
    public function admin_init() {
        // Admin-specific initialization
        do_action('arsol_pfw_admin_init_hook');
    }
} 