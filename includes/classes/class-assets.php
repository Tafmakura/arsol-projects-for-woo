<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Assets class to manage CSS and JS files
 */
class Assets {
    /**
     * Get file version based on file modification time
     * 
     * @param string $file_path Path to the file relative to plugin directory
     * @return string|bool File modification time or false if file doesn't exist
     */
    private function get_file_version($file_path) {
        $full_path = ARSOL_PROJECTS_PLUGIN_DIR . $file_path;
        return file_exists($full_path) ? filemtime($full_path) : false;
    }

    /**
     * Constructor
     */
    public function __construct() {
        // Register hooks for frontend assets
        add_action('wp_enqueue_scripts', array($this, 'register_frontend_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        
        // Register hooks for admin assets
        add_action('admin_enqueue_scripts', array($this, 'register_admin_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Register frontend CSS and JS
     */
    public function register_frontend_assets() {
        $plugin_url = plugin_dir_url(ARSOL_PROJECTS_PLUGIN_FILE);
        
        // Register CSS with prefixed filename
        wp_register_style(
            'arsol-pfw-frontend',
            $plugin_url . 'assets/css/arsol-pfw-frontend.css',
            array(),
            $this->get_file_version('assets/css/arsol-pfw-frontend.css')
        );
        
        // Register JS with prefixed filename
        wp_register_script(
            'arsol-pfw-frontend',
            $plugin_url . 'assets/js/arsol-pfw-frontend.js',
            array('jquery'),
            $this->get_file_version('assets/js/arsol-pfw-frontend.js'),
            true
        );
    }

    /**
     * Enqueue frontend assets on appropriate pages
     */
    public function enqueue_frontend_assets() {
        // Only load on relevant pages like checkout, account page, etc.
        if (is_checkout() || is_account_page() || is_wc_endpoint_url('view-order') || is_wc_endpoint_url('orders')) {
            wp_enqueue_style('arsol-pfw-frontend');
            wp_enqueue_script('arsol-pfw-frontend');
            
            // Add localized data if needed
            wp_localize_script('arsol-pfw-frontend', 'arsolPfw', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('arsol-pfw-frontend'),
                'selectProject' => __('Please select a project', 'arsol-pfw'),
            ));
        }
    }

    /**
     * Register admin CSS and JS
     */
    public function register_admin_assets() {
        $plugin_url = plugin_dir_url(ARSOL_PROJECTS_PLUGIN_FILE);
        
        // Register CSS with prefixed filename
        wp_register_style(
            'arsol-pfw-admin',
            $plugin_url . 'assets/css/arsol-pfw-admin.css',
            array(),
            $this->get_file_version('assets/css/arsol-pfw-admin.css')
        );
        
        // Register JS with prefixed filename
        wp_register_script(
            'arsol-pfw-admin',
            $plugin_url . 'assets/js/arsol-pfw-admin.js',
            array('jquery'),
            $this->get_file_version('assets/js/arsol-pfw-admin.js'),
            true
        );
    }

    /**
     * Enqueue admin assets on appropriate pages
     * 
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Define post types that should load admin assets
        $allowed_post_types = array(
            'shop_order', 
            'arsol-project', 
            'arsol-pfw-request', 
            'arsol-pfw-proposal'
        );

        // Only load on specified post type pages
        if (in_array($screen->post_type, $allowed_post_types)) {
            wp_enqueue_style('arsol-pfw-admin');
            wp_enqueue_script('arsol-pfw-admin');
            
            // Add localized data if needed
            wp_localize_script('arsol-pfw-admin', 'arsolPfw', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('arsol-pfw-admin'),
                'confirmDelete' => __('Are you sure you want to remove this project?', 'arsol-pfw'),
            ));
        }
    }

    /**
     * Check if we're on an order or project screen
     *
     * @param \WP_Screen $screen Current screen object
     * @return bool
     */
    private function is_order_or_project_screen($screen) {
        // Check for post type screens
        if (in_array($screen->post_type, array('shop_order', 'arsol-project'))) {
            return true;
        }
        
        // Check for HPOS order list screen
        if ($screen->id === 'woocommerce_page_wc-orders') {
            return true;
        }
        
        return false;
    }
}
