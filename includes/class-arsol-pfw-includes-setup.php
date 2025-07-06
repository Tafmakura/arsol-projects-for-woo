<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Master Includes Setup Class
 * 
 * Coordinates all plugin includes and their setup classes
 */
class Includes_Setup {

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
        $this->require_includes_files();
        $this->initialize_includes_classes();
        $this->setup_hooks();
    }

    /**
     * Require all includes files
     */
    private function require_includes_files() {
        // Core Setup
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/class-pfw-core-setup.php';
        
        // Custom Post Types Setup
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/class-arsol-pfw-custom-post-types-setup.php';
        
        // Taxonomies Setup
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/class-arsol-pfw-taxonomies-setup.php';
        
        // Email Manager
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/class-email-manager.php';
        
        // Frontend Handlers
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-frontend-handler.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-frontend-handler.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-frontend-handler.php';
        
        // Admin Files
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-capabilities.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-advanced.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-display.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-files.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-general.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-integrations.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-tools.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-setup-defaults.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-arsol-pfw-admin-menu-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-users.php';
        
        // Admin Setup
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-arsol-pfw-admin-setup.php';
        
        // Frontend Setup
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-arsol-pfw-frontend-setup.php';
        
        // Integrations Setup
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/class-arsol-pfw-integrations-setup.php';
        
        // Phases Setup
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/phases/class-arsol-pfw-phases-setup.php';
        
        // Workflows Setup
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/workflows/class-arsol-pfw-workflows-setup.php';
    }

    /**
     * Initialize all includes classes
     */
    private function initialize_includes_classes() {
        // Initialize Core Setup (assets, shortcodes, basic hooks)
        new Core\Setup();
        
        // Initialize Custom Post Types Setup
        new Custom_Post_Types\Setup();
        
        // Initialize Taxonomies Setup
        new Taxonomies\Setup();
        
        // Initialize Email Manager
        \Arsol_Email_Manager::init();
        
        // Initialize Frontend Handlers
        new Custom_Post_Types\Project\Frontend_Handler();
        new Custom_Post_Types\ProjectRequest\Frontend_Handler();
        new Custom_Post_Types\ProjectProposal\Frontend_Handler();
        
        // Initialize Admin Classes
        // Initialize capabilities first
        new Admin\Admin_Capabilities();
        
        // Initialize admin-only classes
        if (is_admin()) {
            new Admin\Settings_General();
            new Admin\Settings_Display();
            new Admin\Settings_Files();
            new Admin\Settings_Advanced();
            new Admin\Settings_Tools();
            new Admin\Setup_Defaults();
            
            // Initialize Admin Menu Setup
            new Admin\Menu\Setup(); // Admin menu management
        }
        
        // Initialize classes that work in both admin and frontend
        new Admin\Settings_Integrations();
        new Admin\Users();
        
        // Initialize Admin Management Coordinator (singleton)
        Admin\Setup::get_instance(); // This refers to the coordinator in class-arsol-pfw-admin-setup.php
        
        // Initialize Frontend Management
        Frontend\Setup::get_instance();
        
        // Initialize Integrations Management
        Integrations\Setup::get_instance();
        
        // Initialize Phases Management
        Phases\Setup::get_instance();
        
        // Initialize Workflows Management
        Workflows\Setup::get_instance();
    }

    /**
     * Setup plugin hooks
     */
    private function setup_hooks() {
        add_action('plugins_loaded', array($this, 'init'));
        add_action('init', array($this, 'load_textdomain'));
        
        // Register activation and deactivation hooks
        register_activation_hook(ARSOL_PROJECTS_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(ARSOL_PROJECTS_PLUGIN_FILE, array($this, 'deactivate'));
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_notice'));
            return;
        }
        
        // Plugin is fully loaded and WooCommerce is active
        do_action('arsol_pfw_includes_loaded');
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('arsol-pfw', false, dirname(ARSOL_PROJECTS_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * WooCommerce dependency notice
     */
    public function woocommerce_notice() {
        echo '<div class="error"><p>';
        echo esc_html__('Arsol Projects for WooCommerce requires WooCommerce to be installed and active.', 'arsol-pfw');
        echo '</p></div>';
    }

    /**
     * Plugin activation callback
     */
    public function activate() {
        // Flush rewrite rules to ensure our custom post types and endpoints are registered
        flush_rewrite_rules();
        
        // Trigger activation hook for all setup classes
        do_action('arsol_pfw_includes_activate');
    }

    /**
     * Plugin deactivation callback
     */
    public function deactivation() {
        // Trigger deactivation hook for all setup classes
        do_action('arsol_pfw_includes_deactivate');
        
        // Flush rewrite rules on deactivation to clean up
        flush_rewrite_rules();
    }

    /**
     * Get setup status for all includes
     */
    public function get_includes_status() {
        return [
            'core' => class_exists('Arsol_Projects_For_Woo\Core\Setup'),
            'custom_post_types' => class_exists('Arsol_Projects_For_Woo\Custom_Post_Types\Setup'),
            'taxonomies' => class_exists('Arsol_Projects_For_Woo\Taxonomies\Setup'),
            'email_manager' => class_exists('Arsol_Email_Manager'),
            'admin' => class_exists('Arsol_Projects_For_Woo\Admin\Setup'),
            'frontend' => class_exists('Arsol_Projects_For_Woo\Frontend\Setup'),
            'integrations' => class_exists('Arsol_Projects_For_Woo\Integrations\Setup'),
            'phases' => class_exists('Arsol_Projects_For_Woo\Phases\Setup'),
            'workflows' => class_exists('Arsol_Projects_For_Woo\Workflows\Setup'),
            'woocommerce_active' => class_exists('WooCommerce'),
        ];
    }

    /**
     * Get all setup class instances
     */
    public function get_setup_instances() {
        return [
            'core' => new Core\Setup(),
            'custom_post_types' => new Custom_Post_Types\Setup(),
            'taxonomies' => new Taxonomies\Setup(),
            'admin' => Admin\Setup::get_instance(),
            'frontend' => Frontend\Setup::get_instance(),
            'integrations' => Integrations\Setup::get_instance(),
            'phases' => Phases\Setup::get_instance(),
            'workflows' => Workflows\Setup::get_instance(),
        ];
    }
} 