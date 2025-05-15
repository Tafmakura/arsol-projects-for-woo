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
     * Plugin version - used for cache busting
     */
    const VERSION = '1.0.0';

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
        
        // Register CSS
        wp_register_style(
            'arsol-projects-for-woo-frontend',
            $plugin_url . 'assets/css/frontend.css',
            array(),
            self::VERSION
        );
        
        // Register JS
        wp_register_script(
            'arsol-projects-for-woo-frontend',
            $plugin_url . 'assets/js/frontend.js',
            array('jquery'),
            self::VERSION,
            true
        );
    }

    /**
     * Enqueue frontend assets on appropriate pages
     */
    public function enqueue_frontend_assets() {
        // Only load on relevant pages like checkout, account page, etc.
        if (is_checkout() || is_account_page() || is_wc_endpoint_url('view-order') || is_wc_endpoint_url('orders')) {
            wp_enqueue_style('arsol-projects-for-woo-frontend');
            wp_enqueue_script('arsol-projects-for-woo-frontend');
            
            // Add localized data if needed
            wp_localize_script('arsol-projects-for-woo-frontend', 'arsolProjects', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('arsol-projects-frontend'),
                'i18n' => array(
                    'selectProject' => __('Please select a project', 'arsol-projects-for-woo'),
                )
            ));
        }
    }

    /**
     * Register admin CSS and JS
     */
    public function register_admin_assets() {
                $plugin_url = plugin_dir_url(ARSOL_PROJECTS_PLUGIN_FILE);
        
        // Register CSS
        wp_register_style(
            'arsol-projects-for-woo-admin',
            $plugin_url . 'assets/css/admin.css',
            array(),
            self::VERSION
        );
        
        // Register JS
        wp_register_script(
            'arsol-projects-for-woo-admin',
            $plugin_url . 'assets/js/admin.js',
            array('jquery'),
            self::VERSION,
            true
        );
    }

    /**
     * Enqueue admin assets on appropriate pages
     * 
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        // Get current screen to check post type
        $screen = get_current_screen();
        
        // Only load on specific admin pages (orders, projects, settings)
        $load_assets = false;
        
        if (
            (in_array($hook, array('post.php', 'post-new.php')) && 
                in_array($screen->post_type, array('shop_order', 'project'))) ||
            $hook === 'woocommerce_page_wc-orders' ||
            $hook === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'shop_order'
        ) {
            $load_assets = true;
        }
        
        if ($load_assets) {
            wp_enqueue_style('arsol-projects-for-woo-admin');
            wp_enqueue_script('arsol-projects-for-woo-admin');
            
            // Add localized data if needed
            wp_localize_script('arsol-projects-for-woo-admin', 'arsolProjects', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('arsol-projects-admin'),
                'i18n' => array(
                    'confirmDelete' => __('Are you sure you want to remove this project?', 'arsol-projects-for-woo'),
                )
            ));
        }
    }
}
