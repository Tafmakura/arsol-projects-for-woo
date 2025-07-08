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
        $cleaned = \Arsol_Projects_For_Woo\Workflow\Workflow_Handler::cleanup_stuck_workflows(30);
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
        // CRUD Infrastructure - Load in dependency order
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/abstracts/interface-arsol-pfw-object-data-store.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/abstracts/interface-arsol-pfw-stage-interface.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/abstracts/interface-arsol-pfw-request-data-store.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/abstracts/abstract-arsol-pfw-data-store-wp.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-arsol-pfw-request.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/data-stores/class-arsol-pfw-request-data-store.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/arsol-pfw-core-functions.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/class-arsol-pfw-data-stores.php';
        
        // Development Testing (only loaded in debug mode)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            require_once ARSOL_PROJECTS_PLUGIN_DIR . 'test-crud-basic.php';
            require_once ARSOL_PROJECTS_PLUGIN_DIR . 'test-crud-compatibility.php';
        }
        
        // Core Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/class-arsol-pfw-permissions.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/class-arsol-pfw-cpt-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/class-arsol-pfw-shortcodes.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/class-arsol-pfw-wc-integration.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce-subscriptions/class-arsol-pfw-wc-subscriptions.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/class-arsol-pfw-wc-logs.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/email/class-arsol-pfw-wc-email-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/class-arsol-pfw-wc-biller-invoice.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/class-arsol-pfw-assets.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-arsol-pfw-frontend-woocommerce-endpoints.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-arsol-pfw-frontend-woocommerce-checkout.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/class-arsol-pfw-taxonomies-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-arsol-pfw-frontend-comments.php';
        
        // Admin Settings Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-arsol-pfw-admin-settings-general.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-arsol-pfw-admin-settings-display.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-arsol-pfw-admin-settings-files.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-arsol-pfw-admin-settings-advanced.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-arsol-pfw-admin-settings-tools.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-arsol-pfw-admin-settings-integrations.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-arsol-pfw-admin-setup-defaults.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-arsol-pfw-frontend-template-overrides.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-arsol-pfw-admin-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-arsol-pfw-admin-users.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-arsol-pfw-admin-capabilities.php';

        // Frontend handlers
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-arsol-pfw-frontend-handler.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-arsol-pfw-frontend-request.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-arsol-pfw-frontend-proposal.php';
        
        // Keep existing project frontend handler
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-arsol-pfw-cpt-project-frontend-handler.php';
        
        // Other includes...
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/workflow/class-arsol-pfw-workflow-handler.php';

        // Conversion classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-arsol-pfw-cpt-request-conversion.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-arsol-pfw-cpt-proposal-conversion.php';
    }

    /**
     * Instantiate plugin classes.
     */
    private function instantiate_classes() {
        // Initialize CRUD infrastructure - Register data stores with WooCommerce
        ARSOL_PFW_Data_Stores::init();
        
        // Initialize capabilities first
        new Admin\Admin_Capabilities();
        
        // Initialize other classes
        new Custom_Post_Types\Setup();
        new Shortcodes();
        new Woocommerce();
        new Woocommerce_Subscriptions();
        \Arsol_Email_Manager::init();
        new Woocommerce_Biller();
        new Assets();
        new Woocommerce\Frontend_Endpoints();
        new Frontend_Woocommerce_Checkout();
        new Taxonomies\Taxonomies_Setup();
        
        // Frontend Comments Classes
        new Frontend_Comments();

        // Initialize admin classes
        if (is_admin()) {
            new Admin\Settings_General();
            new Admin\Settings_Display();
            new Admin\Settings_Files();
            new Admin\Settings_Advanced();
            new Admin\Settings_Tools();
            new Admin\Setup_Defaults();
        }
        
        new Admin\Settings_Integrations();
        new Admin\Setup();
        new Admin\Users();

        // Frontend Handlers
        new Custom_Post_Types\Project\Frontend_Handler();
        new \Arsol_Projects_For_Woo\Frontend\Request_Frontend();
        new \Arsol_Projects_For_Woo\Frontend\Proposal_Frontend();
        
        // Workflow Handler
        new \Arsol_Projects_For_Woo\Workflow\Workflow_Handler();
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
