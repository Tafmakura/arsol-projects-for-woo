<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        $this->require_files();
        $this->instantiate_classes();
        add_action('plugins_loaded', array($this, 'init'));
        add_action('init', array($this, 'load_textdomain'));
        
        // Register activation and deactivation hooks
        register_activation_hook(ARSOL_PROJECTS_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(ARSOL_PROJECTS_PLUGIN_FILE, array($this, 'deactivate'));
        
        // Schedule automatic conversion cleanup
        add_action('wp', array($this, 'schedule_conversion_cleanup'));
        add_action('arsol_cleanup_stuck_conversions', array($this, 'cleanup_stuck_conversions'));
    }

    /**
     * Schedule conversion cleanup cron job
     */
    public function schedule_conversion_cleanup() {
        if (!wp_next_scheduled('arsol_cleanup_stuck_conversions')) {
            wp_schedule_event(time(), 'hourly', 'arsol_cleanup_stuck_conversions');
        }
    }

    /**
     * Cleanup stuck conversions via cron
     */
    public function cleanup_stuck_conversions() {
        $cleaned = \Arsol_Projects_For_Woo\Workflows\Standard::cleanup_stuck_workflows(30);
        if ($cleaned > 0) {
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                "Automatic cleanup: removed {$cleaned} stuck conversions");
        }
    }

    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_notice'));
            return;
        }
    }

    public function load_textdomain() {
        // Load plugin text domain with updated domain name
        load_plugin_textdomain('arsol-pfw', false, dirname(ARSOL_PROJECTS_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Include necessary files.
     */
    private function require_files() {
        // Core Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/class-setup-custom-post-types.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/class-shortcodes.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/class-assets.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/class-taxonomies-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/class-email-manager.php';
        
        // Admin Management
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-arsol-pfw-admin-setup.php';
        
        // Frontend Management
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-arsol-pfw-frontend-setup.php';
        
        // Integrations Management
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/class-arsol-pfw-integrations-setup.php';
        
        // Phases Management System
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/phases/class-arsol-pfw-phases-setup.php';

        // Frontend Handlers
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-frontend-handler.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-frontend-handler.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-frontend-handler.php';
    }

    /**
     * Instantiate plugin classes.
     */
    private function instantiate_classes() {
        // Initialize core classes
        new Custom_Post_Types\Setup();
        new Shortcodes();
        new Assets();
        \Arsol_Email_Manager::init();
        new Taxonomies\Taxonomies_Setup();
        
        // Initialize Admin Management
        Admin\Setup::get_instance();
        
        // Initialize Frontend Management
        Frontend\Setup::get_instance();
        
        // Initialize Integrations Management
        Integrations\Setup::get_instance();
        
        // Initialize Phases Management System
        Phases\Setup::get_instance();

        // Frontend Handlers
        new Custom_Post_Types\Project\Frontend_Handler();
        new Custom_Post_Types\ProjectRequest\Frontend_Handler();
        new Custom_Post_Types\ProjectProposal\Frontend_Handler();
    }

    public function woocommerce_notice() {
        echo '<div class="error"><p>';
        echo esc_html__('Arsol Projects for WooCommerce requires WooCommerce to be installed and active.', 'arsol-pfw');
        echo '</p></div>';
    }

    /**
     * Plugin activation callback
     * 
     * @return void
     */
    public function activate() {
        // Flush rewrite rules to ensure our custom post types and endpoints are registered
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation callback
     * 
     * @return void
     */
    public function deactivate() {
        // Clear scheduled cron jobs
        wp_clear_scheduled_hook('arsol_cleanup_stuck_conversions');
        
        // Flush rewrite rules on deactivation to clean up
        flush_rewrite_rules();
    }
}
