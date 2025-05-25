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
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-admin-woo-orders.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-assets.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-endpoints.php';
    }

    /**
     * Instantiate plugin classes.
     */
    private function instantiate_classes() {
        new \Arsol_Projects_For_Woo\Custom_Post_Types\Setup();
        new \Arsol_Projects_For_Woo\Woo\AdminOrders();
        new \Arsol_Projects_For_Woo\Assets();
        new \Arsol_Projects_For_Woo\Shortcodes();
        new \Arsol_Projects_For_Woo\Endpoints();
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
