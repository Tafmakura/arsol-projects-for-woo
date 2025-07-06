<?php
/**
 * Arsol Projects for Woo - Plugin Setup
 * 
 * Contains all base setup methods, hooks, and initialization logic
 * 
 * @package Arsol_Projects_For_Woo
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Use statements at top level
use Arsol_Projects_For_Woo\Setup;
use Arsol_Projects_For_Woo\Workflows\Setup as WorkflowsSetup;
use Arsol_Projects_For_Woo\Admin\Setup_Defaults;
use Arsol_Projects_For_Woo\Frontend_Template_Sidebar_Meta;
use Arsol_Projects_For_Woo\Frontend_Template_Sidebar_Buttons;
use Arsol_Projects_For_Woo\Setup\Workflows_Setup;
use Arsol_Projects_For_Woo\Workflows\StandardWorkflow;

/**
 * Arsol Projects for Woo Setup Class
 * 
 * Handles plugin activation, deactivation, and initialization
 */
class Arsol_PFW_Setup {
    
    /**
     * Initialize the plugin setup
     */
    public static function init() {
        // Include the Master Includes Setup class
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/class-arsol-pfw-includes-setup.php';
        
        // Register activation and deactivation hooks
        register_activation_hook(ARSOL_PROJECTS_PLUGIN_FILE, array(__CLASS__, 'activate'));
        register_deactivation_hook(ARSOL_PROJECTS_PLUGIN_FILE, array(__CLASS__, 'deactivate'));
        
        // Initialize the plugin
        self::initialize_plugin();
        
        // Setup HPOS compatibility
        self::setup_hpos_compatibility();
    }
    
    /**
     * Plugin activation callback
     */
    public static function activate() {
        // Set flag to flush rewrite rules on next init
        update_option('arsol_projects_flush_rewrite_rules', false);
        
        // Trigger defaults initialization
        do_action('arsol_pfw_plugin_activated');
    }
    
    /**
     * Plugin deactivation callback
     */
    public static function deactivate() {
        // Delete the flush rewrite rules option
        delete_option('arsol_projects_flush_rewrite_rules');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Initialize the plugin
     * 
     * This function ensures that all dependent plugins are loaded 
     * before our plugin's main logic runs.
     */
    private static function initialize_plugin() {
        // Initialize the master includes setup
        \Arsol_Projects_For_Woo\Includes_Setup::get_instance();
    }
    
    /**
     * Setup HPOS (High-Performance Order Storage) compatibility
     */
    private static function setup_hpos_compatibility() {
        add_action('before_woocommerce_init', function() {
            if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                    'custom_order_tables', 
                    ARSOL_PROJECTS_PLUGIN_FILE, 
                    true
                );
            }
        });
    }
    
    /**
     * Get plugin information
     */
    public static function get_plugin_info() {
        return [
            'name' => 'Arsol Projects for Woo',
            'version' => '0.0.9.6',
            'text_domain' => 'arsol-pfw',
            'requires_php' => '7.4.1',
            'requires_wp' => '5.8',
            'requires_plugins' => ['woocommerce'],
        ];
    }
    
    /**
     * Get plugin constants
     */
    public static function get_plugin_constants() {
        return [
            'ARSOL_PROJECTS_PLUGIN_FILE' => ARSOL_PROJECTS_PLUGIN_FILE,
            'ARSOL_PROJECTS_PLUGIN_DIR' => ARSOL_PROJECTS_PLUGIN_DIR,
            'ARSOL_PROJECTS_PLUGIN_URL' => ARSOL_PROJECTS_PLUGIN_URL,
            'ARSOL_PROJECTS_PLUGIN_BASENAME' => ARSOL_PROJECTS_PLUGIN_BASENAME,
            'ARSOL_PFW_PLUGIN_DIR' => ARSOL_PFW_PLUGIN_DIR,
            'ARSOL_PROJECT_META_KEY' => ARSOL_PROJECT_META_KEY,
        ];
    }
    
    /**
     * Check if plugin is properly initialized
     */
    public static function is_initialized() {
        return class_exists('Arsol_Projects_For_Woo\Includes_Setup');
    }
    
    /**
     * Get initialization status
     */
    public static function get_initialization_status() {
        return [
            'constants_defined' => defined('ARSOL_PROJECTS_PLUGIN_FILE'),
            'includes_setup_loaded' => class_exists('Arsol_Projects_For_Woo\Includes_Setup'),
            'woocommerce_active' => class_exists('WooCommerce'),
            'hpos_compatible' => true,
        ];
    }
}
