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
        // COMMENTED OUT AUTOLOADER - USING MANUAL LOADER INSTEAD
        /*
        spl_autoload_register(function ($class) {
            // Arsol Projects For Woo namespace
            $prefix = 'Arsol_Projects_For_Woo\\';
            $base_dir = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/';

            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relative_class = substr($class, $len);
            $file_parts = explode('\\', $relative_class);
            $file_path = '';
            $count = count($file_parts);
            foreach ($file_parts as $i => $part) {
                if ($file_path !== '') {
                    $file_path .= '/';
                }
                // Only convert underscores to hyphens in the last part (the filename)
                if ($i === $count - 1) {
                    $part = str_replace('_', '-', $part);
                }
                $file_path .= strtolower($part);
            }
            $file = $base_dir . $file_path . '.php';
            if (file_exists($file)) {
                require $file;
            }
        });
        */
        
        // MANUAL LOADER - Load all required files directly
        $this->load_core_files();
        $this->load_custom_post_types();
        $this->load_admin_files();
        $this->load_frontend_files();
        $this->load_taxonomy_files();
        $this->load_integration_files();
        $this->load_function_files();
    }

    /**
     * Load core files manually
     */
    private function load_core_files() {
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/assets.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/capabilities.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/permissions.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/stage-handler.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/conversion-handler.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/shortcodes.php';
        
        // Load workflow files
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/workflow-handler.php';
        
        // Load data store files
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/data-stores/project-data-store.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/data-stores/proposal-data-store.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/data-stores/request-data-store.php';
    }

    /**
     * Load custom post type files manually
     */
    private function load_custom_post_types() {
        // Project
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/core/setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/core/project.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/admin/single-controller.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/admin/list-controller.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/frontend/handler.php';
        
        // Request
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/request/core/setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/request/core/conversion-handler.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/request/admin/single-controller.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/request/admin/list-controller.php';
        
        // Proposal
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/proposal/core/setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/proposal/core/conversion-handler.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/proposal/admin/single-controller.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/proposal/admin/list-controller.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/proposal/admin/budget-controller.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/proposal/admin/quotation-controller.php';
    }

    /**
     * Load admin files manually
     */
    private function load_admin_files() {
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/settings/general.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/settings/display.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/settings/files.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/settings/integrations.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/settings/advanced.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/settings/tools.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/users/users.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/setup-defaults.php';
    }

    /**
     * Load frontend files manually
     */
    private function load_frontend_files() {
        // Load the base handler first (other classes extend this)
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/handler/handler.php';
        
        // Load other frontend files
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/request/request.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/proposal/proposal.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/woocommerce/checkout.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/woocommerce/endpoints.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/template/overrides.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/template/sidebar-buttons.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/template/sidebar-meta.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/comments/comments.php';
    }

    /**
     * Load taxonomy files manually
     */
    private function load_taxonomy_files() {
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/setup/setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/project-stage/setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/project-stage/admin.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/proposal-stage/setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/proposal-stage/admin.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/request-stage/setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/request-stage/admin.php';
    }

    /**
     * Load integration files manually
     */
    private function load_integration_files() {
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/integration.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/biller-invoice.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/logs/logs.php';
        
        // Email classes are loaded after WooCommerce is available
        // See load_woocommerce_email_classes() method
    }

    /**
     * Load WooCommerce email classes after WooCommerce is loaded
     */
    private function load_woocommerce_email_classes() {
        // Only load if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            return;
        }

        // Load email classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/email/class-arsol-pfw-wc-email-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/email/class-arsol-pfw-wc-email-admin-new-project.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/email/class-arsol-pfw-wc-email-admin-new-request.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/email/class-arsol-pfw-wc-email-new-request.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/email/class-arsol-pfw-wc-email-project-completion.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/email/class-arsol-pfw-wc-email-project-creation.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/email/class-arsol-pfw-wc-email-project-stage.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/email/class-arsol-pfw-wc-email-proposal-decision.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/email/class-arsol-pfw-wc-email-proposal-processing.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/email/class-arsol-pfw-wc-email-proposal-ready.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/email/class-arsol-pfw-wc-email-request-stage.php';
    }

    /**
     * Load function files manually
     */
    private function load_function_files() {
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/functions/project-functions.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/functions/proposal-functions.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/functions/request-functions.php';
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

        // Load WooCommerce email classes after WooCommerce is available
        $this->load_woocommerce_email_classes();
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
        new \Arsol_Projects_For_Woo\Workflow\Workflow_Handler();
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

        // Functions are now loaded in the manual loader
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
