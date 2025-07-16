<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        $this->setup_autoloader();
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
     * Setup PSR-4 autoloader for the new structure
     */
    private function setup_autoloader() {
        spl_autoload_register(function ($class) {
            // Arsol Projects For Woo namespace
            $prefix = 'Arsol_Projects_For_Woo\\';
            $base_dir = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/';

            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relative_class = substr($class, $len);
            
            // Convert namespace to file path, handling case sensitivity
            $file_parts = explode('\\', $relative_class);
            $file_path = '';
            
            foreach ($file_parts as $part) {
                if ($file_path !== '') {
                    $file_path .= '/';
                }
                // Convert to lowercase to match actual directory structure
                $file_path .= strtolower($part);
            }
            
            $file = $base_dir . $file_path . '.php';

            if (file_exists($file)) {
                require $file;
            }
        });
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
        $cleaned = \Arsol_Projects_For_Woo\Core\Workflow_Handler::cleanup_stuck_workflows(30);
        if ($cleaned > 0) {
            \Arsol_Projects_For_Woo\Integrations\WooCommerce\Logs\Logs::log_workflow('info', 
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
     * Instantiate plugin classes.
     */
    private function instantiate_classes() {
        // Initialize core components
        new \Arsol_Projects_For_Woo\Core\Assets();
        new \Arsol_Projects_For_Woo\Core\Capabilities();
        new \Arsol_Projects_For_Woo\Core\Permissions();
        new \Arsol_Projects_For_Woo\Core\Stage_Handler();
        new \Arsol_Projects_For_Woo\Core\Workflow_Handler();
        new \Arsol_Projects_For_Woo\Core\Conversion_Handler();
        new \Arsol_Projects_For_Woo\Core\Shortcodes();

        // Initialize custom post types
        new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Core\Setup();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\Request\Core\Setup();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\Proposal\Core\Setup();

        // Initialize admin components
        new \Arsol_Projects_For_Woo\Admin\Settings\General();
        new \Arsol_Projects_For_Woo\Admin\Settings\Display();
        new \Arsol_Projects_For_Woo\Admin\Settings\Files();
        new \Arsol_Projects_For_Woo\Admin\Settings\Integrations();
        new \Arsol_Projects_For_Woo\Admin\Settings\Advanced();
        new \Arsol_Projects_For_Woo\Admin\Settings\Tools();
        new \Arsol_Projects_For_Woo\Admin\Users\Users();
        new \Arsol_Projects_For_Woo\Admin\Setup\Setup();
        new \Arsol_Projects_For_Woo\Admin\Setup_Defaults\Setup_Defaults();

        // Initialize frontend components
        new \Arsol_Projects_For_Woo\Frontend\Request_Frontend();
        new \Arsol_Projects_For_Woo\Frontend\Proposal_Frontend();
        new \Arsol_Projects_For_Woo\Frontend\WooCommerce\Checkout();
        new \Arsol_Projects_For_Woo\Frontend\WooCommerce\Endpoints();
        new \Arsol_Projects_For_Woo\Frontend\Template\Overrides();
        new \Arsol_Projects_For_Woo\Frontend\Template\Sidebar_Buttons();
        new \Arsol_Projects_For_Woo\Frontend\Template\Sidebar_Meta();
        new \Arsol_Projects_For_Woo\Frontend_Comments();

        // Initialize taxonomies
        new \Arsol_Projects_For_Woo\Taxonomies\Setup\Setup();
        new \Arsol_Projects_For_Woo\Taxonomies\Project_Stage\Setup();
        new \Arsol_Projects_For_Woo\Taxonomies\Proposal_Stage\Setup();
        new \Arsol_Projects_For_Woo\Taxonomies\Request_Stage\Setup();

        // Initialize integrations
        new \Arsol_Projects_For_Woo\Integrations\WooCommerce\Integration();
        new \Arsol_Projects_For_Woo\Integrations\WooCommerce\Biller_Invoice();
        new \Arsol_Projects_For_Woo\Integrations\WooCommerce\Logs\Logs();

        // Load functions
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/functions/project-functions.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/functions/proposal-functions.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/functions/request-functions.php';
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
        // Create default stages
        \Arsol_Projects_For_Woo\Core\Stage_Handler::create_default_stages();
        
        // Set up capabilities
        \Arsol_Projects_For_Woo\Core\Capabilities::setup_capabilities();
        
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
