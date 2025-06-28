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
        // Delay asset hooks until after init to ensure text domain is loaded
        add_action('init', array($this, 'setup_asset_hooks'), 20);
    }

    /**
     * Setup asset hooks after init
     */
    public function setup_asset_hooks() {
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
        
        // Register main admin JS
        wp_register_script(
            'arsol-pfw-admin',
            $plugin_url . 'assets/js/arsol-pfw-admin.js',
            array('jquery'),
            $this->get_file_version('assets/js/arsol-pfw-admin.js'),
            true
        );
        
        // Register proposal admin JS
        wp_register_script(
            'arsol-pfw-admin-proposal',
            $plugin_url . 'assets/js/arsol-pfw-admin-proposal.js',
            array('jquery', 'wp-util', 'underscore', 'selectWoo', 'wc-enhanced-select'),
            $this->get_file_version('assets/js/arsol-pfw-admin-proposal.js'),
            true
        );
        
        // Register active project admin JS
        wp_register_script(
            'arsol-pfw-admin-active',
            $plugin_url . 'assets/js/arsol-pfw-admin-active.js',
            array('jquery'),
            $this->get_file_version('assets/js/arsol-pfw-admin-active.js'),
            true
        );
        
        // Register request admin JS
        wp_register_script(
            'arsol-pfw-admin-request',
            $plugin_url . 'assets/js/arsol-pfw-admin-request.js',
            array('jquery'),
            $this->get_file_version('assets/js/arsol-pfw-admin-request.js'),
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
            'arsol-pfw-project', 
            'arsol-pfw-request', 
            'arsol-pfw-proposal'
        );

        // Check if we're on a post type page that needs admin assets OR the settings page
        $is_post_type_page = in_array($screen->post_type, $allowed_post_types);
        $is_settings_page = ($hook === 'toplevel_page_arsol-projects');
        
        if ($is_post_type_page || $is_settings_page) {
            // Enqueue WooCommerce admin styles and scripts
            if ($is_post_type_page) {
                // WooCommerce assets are mainly needed for post type pages
            wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION);
            wp_enqueue_script('selectWoo');
            wp_enqueue_script('wc-enhanced-select');
            wp_enqueue_style('select2');
            }
            
            // Always enqueue our plugin assets
            wp_enqueue_style('arsol-pfw-admin');
            wp_enqueue_script('arsol-pfw-admin');
            
            // Enqueue post-type specific JavaScript (only for post type pages)
            if ($is_post_type_page) {
                if ($screen->post_type === 'arsol-pfw-proposal') {
                    wp_enqueue_script('arsol-pfw-admin-proposal');
                    
                    // Localize proposal script
                    wp_localize_script('arsol-pfw-admin-proposal', 'arsol_proposal_vars', array(
                        'validation_message' => __('Please complete all required fields before saving.', 'arsol-pfw'),
                    ));
                    
                    // Localize budget script
                    wp_localize_script('arsol-pfw-admin-proposal', 'arsol_budget_vars', array(
                        'currency_symbol' => get_woocommerce_currency_symbol(),
                    ));
                    
                    // Always localize quotation script for proposals (needed for all proposal types)
                    global $post;
                    $line_items = get_post_meta($post->ID, '_arsol_pfw_proposal_quotation_line_items', true) ?: array();
                        
                        // Fetch product names and map saved prices for existing product line items
                        if (!empty($line_items['products'])) {
                            foreach ($line_items['products'] as $key => $product_item) {
                                if (!empty($product_item['product_id'])) {
                                    $product = wc_get_product($product_item['product_id']);
                                    if ($product) {
                                        $line_items['products'][$key]['product_name'] = $product->get_formatted_name();
                                    }
                                }
                                // Map saved price to regular_price for the JavaScript template
                                if (isset($product_item['price'])) {
                                    $line_items['products'][$key]['regular_price'] = $product_item['price'];
                            }
                        }
                    }
                    
                    wp_localize_script('arsol-pfw-admin-proposal', 'arsol_proposal_quotation_vars', array(
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'nonce' => wp_create_nonce('arsol-proposal-quotation-nonce'),
                        'search_products_nonce' => wp_create_nonce('search-products'),
                        'currency_symbol' => get_woocommerce_currency_symbol(),
                        'line_items' => $line_items,
                        'calculation_constants' => array(
                            'days_in_month' => 30.44, // Average days in a month
                            'days_in_year' => 365.25  // Average days in a year
                        )
                    ));
                    
                } elseif ($screen->post_type === 'arsol-pfw-project') {
                    wp_enqueue_script('arsol-pfw-admin-active');
                    // Also enqueue proposal script for Create Proposal button functionality
                    wp_enqueue_script('arsol-pfw-admin-proposal');
                    
                } elseif ($screen->post_type === 'arsol-pfw-request') {
                    wp_enqueue_script('arsol-pfw-admin-request');
                }
                
                // WooCommerce should already provide wc_enhanced_select_params, but ensure our nonces are available
                // Only add minimal necessary data for our custom functionality
                wp_localize_script('wc-enhanced-select', 'arsol_enhanced_select_params', array(
                'search_products_nonce'   => wp_create_nonce('search-products'),
                'search_customers_nonce'  => wp_create_nonce('search-customers'),
            ));
            }
            
            // Localize our main plugin script (for both post type pages and settings page)
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
        if (in_array($screen->post_type, array('shop_order', 'arsol-pfw-project'))) {
            return true;
        }
        
        // Check for HPOS order list screen
        if ($screen->id === 'woocommerce_page_wc-orders') {
            return true;
        }
        
        return false;
    }
}
