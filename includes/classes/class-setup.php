<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        $this->include_files();
        add_action('plugins_loaded', array($this, 'init'));
    }

    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_notice'));
            return;
        }
        
        // Load plugin text domain
        load_plugin_textdomain('arsol-projects-for-woo', false, dirname(ARSOL_PROJECTS_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Include necessary files.
     */
    private function include_files() {
        // Core Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-setup-custom-post-types.php';
        
        // Initialize CPT
        new \Arsol_Projects_For_Woo\Custom_Post_Types\Setup();
    }

    public function woocommerce_notice() {
        echo '<div class="error"><p>';
        echo esc_html__('Arsol Projects for WooCommerce requires WooCommerce to be installed and active.', 'arsol-projects-for-woo');
        echo '</p></div>';
    }
}

// Initialize the setup class
new Setup();
