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
    }

    /**
     * Instantiate plugin classes.
     */
    private function instantiate_classes() {
        new \Arsol_Projects_For_Woo\Custom_Post_Types\Setup();
       // new \Arsol_Projects_For_Woo\Shortcodes();
        new \Arsol_Projects_For_Woo\Woo\AdminOrders();
        new \Arsol_Projects_For_Woo\Assets();
    }

    public function woocommerce_notice() {
        echo '<div class="error"><p>';
        echo esc_html__('Arsol Projects for WooCommerce requires WooCommerce to be installed and active.', 'arsol-pfw');
        echo '</p></div>';
    }
    
}

// Initialize the setup class
new Setup();
