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
        
        // Register activation and deactivation hooks
        register_activation_hook(ARSOL_PROJECTS_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(ARSOL_PROJECTS_PLUGIN_FILE, array($this, 'deactivate'));
    }

    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_notice'));
            return;
        }
        
        // Load plugin text domain with updated domain name
        load_plugin_textdomain('arsol-pfw', false, dirname(ARSOL_PROJECTS_PLUGIN_BASENAME) . '/languages');
    }


    /**
     * Include necessary files.
     */
    private function require_files() {
        // Core Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/class-setup-custom-post-types.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-shortcodes.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-woocommerce.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-assets.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-woocommerce-endpoints.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-admin-settings-general.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-admin-settings-advanced.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-admin-settings-integrations.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-template-overrides.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-admin-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-admin-users.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-admin-capabilities.php';

        // Frontend Handlers
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-frontend-handler.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-frontend-handler.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-frontend-handler.php';
    }

    /**
     * Instantiate plugin classes.
     */
    private function instantiate_classes() {
        // Initialize capabilities first
        new Admin\Admin_Capabilities();
        
        // Initialize other classes
        new Custom_Post_Types\Setup();
        new Shortcodes();
        new Woocommerce();
        new Assets();
        new Woocommerce\Endpoints();
        new Admin\Settings_General();
        new Admin\Settings_Advanced();
        new Admin\Settings_Integrations();
        new Admin\Setup();
        new Admin\Users();

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
        // Flush rewrite rules on deactivation to clean up
        flush_rewrite_rules();
    }
}
