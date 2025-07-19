<?php

namespace Arsol_Projects_For_Woo\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Assets class to manage CSS and JS files
 * Follows WordPress best practices for asset enqueueing
 */
class Asset_Handler {
    /**
     * Get file version based on file modification time
     * 
     * @param string $file_path Path to the file relative to plugin directory
     * @return string|bool File modification time or false if file doesn't exist
     */
    private function get_file_version($file_path) {
        $full_path = ARSOL_PFW_PLUGIN_DIR . $file_path;
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
        
        // Register admin assets (always register, conditionally enqueue)
        add_action('admin_enqueue_scripts', array($this, 'register_admin_assets'));
        
        // Use WordPress best practices: hook into load-post.php and load-post-new.php for our CPTs
        add_action('load-post.php', array($this, 'setup_cpt_edit_assets'));
        add_action('load-post-new.php', array($this, 'setup_cpt_edit_assets'));
        
        // Handle WooCommerce order pages specifically
        add_action('load-edit.php', array($this, 'setup_woocommerce_order_list_assets'));
        
        // Handle settings pages and other admin areas
        add_action('admin_enqueue_scripts', array($this, 'enqueue_settings_assets'));
    }

    /**
     * Register frontend CSS and JS
     */
    public function register_frontend_assets() {
        $plugin_url = plugin_dir_url(ARSOL_PFW_PLUGIN_FILE);
        
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
        
        // Register frontend comments JS
        wp_register_script(
            'arsol-pfw-frontend-comments',
            $plugin_url . 'assets/js/arsol-pfw-frontend-comments.js',
            array('jquery', 'wp-util'),
            $this->get_file_version('assets/js/arsol-pfw-frontend-comments.js'),
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
            
            // Enqueue comments script on project-related pages (simple approach)
            if (is_account_page() && (is_wc_endpoint_url('view-project') || is_wc_endpoint_url('view-proposal') || is_wc_endpoint_url('view-request'))) {
                wp_enqueue_script('arsol-pfw-frontend-comments');
                
                // Simple localization for AJAX comments
                wp_localize_script('arsol-pfw-frontend-comments', 'arsolComments', array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('arsol_comments_nonce')
                ));
                
                // Add basic CSS for form validation errors
                wp_add_inline_style('arsol-pfw-frontend', '
                    .comment-form .error { border-color: red !important; }
                    .comment-form .loadingform { opacity: 0.6; }
                ');
            }
        }
    }

    /**
     * Register admin CSS and JS
     */
    public function register_admin_assets() {
        $plugin_url = plugin_dir_url(ARSOL_PFW_PLUGIN_FILE);
        
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
            'arsol-pfw-admin-cpt-proposal',
            $plugin_url . 'assets/js/arsol-pfw-admin-cpt-proposal.js',
            array('jquery', 'wp-util', 'underscore', 'selectWoo', 'wc-enhanced-select'),
            $this->get_file_version('assets/js/arsol-pfw-admin-cpt-proposal.js'),
            true
        );
        
        // Register active project admin JS
        wp_register_script(
            'arsol-pfw-admin-cpt-project',
            $plugin_url . 'assets/js/arsol-pfw-admin-cpt-project.js',
            array('jquery'),
            $this->get_file_version('assets/js/arsol-pfw-admin-cpt-project.js'),
            true
        );
        
        // Register request admin JS
        wp_register_script(
            'arsol-pfw-admin-cpt-request',
            $plugin_url . 'assets/js/arsol-pfw-admin-cpt-request.js',
            array('jquery'),
            $this->get_file_version('assets/js/arsol-pfw-admin-cpt-request.js'),
            true
        );
    }

    /**
     * Setup CPT edit screen assets using WordPress best practices
     * Hooked into load-post.php and load-post-new.php
     */
    public function setup_cpt_edit_assets() {
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Only target our custom post types
        $allowed_post_types = ['arsol-pfw-project', 'arsol-pfw-request', 'arsol-pfw-proposal'];
        if (!in_array($screen->post_type, $allowed_post_types, true)) {
            return;
        }

        // Add admin_enqueue_scripts hook specifically for this CPT
        add_action('admin_enqueue_scripts', function($hook_suffix) use ($screen) {
            // Only enqueue on post.php or post-new.php
            if ($hook_suffix !== 'post.php' && $hook_suffix !== 'post-new.php') {
                return;
            }

            // Enqueue core plugin assets
            wp_enqueue_style('arsol-pfw-admin');
            wp_enqueue_script('arsol-pfw-admin');

            // Enqueue CPT-specific assets
            $this->enqueue_cpt_specific_assets($screen->post_type);

            // Conditionally enqueue WooCommerce assets only if WooCommerce is active
            if (class_exists('WooCommerce')) {
                $this->enqueue_woocommerce_assets();
            }

            // Localize main admin script
            wp_localize_script('arsol-pfw-admin', 'arsolPfw', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('arsol-pfw-admin'),
                'confirmDelete' => __('Are you sure you want to remove this project?', 'arsol-pfw'),
            ));
        });
    }

    /**
     * Setup WooCommerce order list page assets
     * Hooked into load-edit.php for order listing
     */
    public function setup_woocommerce_order_list_assets() {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'shop_order') {
            return;
        }

        // Add admin_enqueue_scripts hook specifically for WooCommerce order list
        add_action('admin_enqueue_scripts', function($hook_suffix) {
            // Only enqueue on edit.php for shop_order
            if ($hook_suffix !== 'edit.php') {
                return;
            }

            // Only enqueue if WooCommerce is active
            if (class_exists('WooCommerce')) {
                wp_enqueue_style('arsol-pfw-admin');
                wp_enqueue_script('arsol-pfw-admin');
                
                wp_localize_script('arsol-pfw-admin', 'arsolPfw', array(
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('arsol-pfw-admin'),
                ));
            }
        });
    }

    /**
     * Enqueue assets for settings pages and other admin areas
     * Uses $hook_suffix for precise targeting
     */
    public function enqueue_settings_assets($hook_suffix) {
        // Target settings pages
        if (strpos($hook_suffix, 'arsol-projects') !== false) {
            wp_enqueue_style('arsol-pfw-admin');
            wp_enqueue_script('arsol-pfw-admin');

            wp_localize_script('arsol-pfw-admin', 'arsolPfw', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('arsol-pfw-admin'),
            ));
        }

        // Target WooCommerce order edit pages (existing orders)
        if ($hook_suffix === 'post.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'shop_order') {
            // Only enqueue if WooCommerce is active
            if (class_exists('WooCommerce')) {
                wp_enqueue_style('arsol-pfw-admin');
                wp_enqueue_script('arsol-pfw-admin');
                
                wp_localize_script('arsol-pfw-admin', 'arsolPfw', array(
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('arsol-pfw-admin'),
                ));
            }
        }
    }

    /**
     * Enqueue WooCommerce assets conditionally
     */
    private function enqueue_woocommerce_assets() {
        // Only enqueue if WooCommerce assets are registered
        if (wp_style_is('woocommerce_admin_styles', 'registered')) {
            wp_enqueue_style('woocommerce_admin_styles');
        }
        if (wp_script_is('selectWoo', 'registered')) {
            wp_enqueue_script('selectWoo');
        }
        if (wp_script_is('wc-enhanced-select', 'registered')) {
            wp_enqueue_script('wc-enhanced-select');
        }
        if (wp_style_is('select2', 'registered')) {
            wp_enqueue_style('select2');
        }

        // Localize WooCommerce enhanced select
        if (wp_script_is('wc-enhanced-select', 'enqueued')) {
            wp_localize_script('wc-enhanced-select', 'arsol_enhanced_select_params', array(
                'search_products_nonce'   => wp_create_nonce('search-products'),
                'search_customers_nonce'  => wp_create_nonce('search-customers'),
            ));
        }
    }
    
    /**
     * Enqueue CPT-specific assets
     */
    private function enqueue_cpt_specific_assets($post_type) {
        switch ($post_type) {
            case 'arsol-pfw-proposal':
                wp_enqueue_script('arsol-pfw-admin-cpt-proposal');

                // Get current proposal data using proper getter methods
                $proposal_id = get_the_ID();
                $quotation_data = array();
                $budget_data = array();
                $budget_notes = '';
                $quotation_notes = '';
                $costing_type = 'none';
                
                if ($proposal_id) {
                    $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);
                    if ($proposal && $proposal->exists()) {
                        // Get costing type to determine which data to load
                        $costing_type = $proposal->get_proposal_costing_type();
                        
                        // Get quotation data using proper getter
                        $quotation_data = $proposal->get_proposed_project_quotation();
                        
                        // Get budget data using proper getter
                        $budget_data = $proposal->get_proposed_project_budget();
                        
                        // Get notes using proper getters
                        $budget_notes = $proposal->get_proposal_budget_notes();
                        $quotation_notes = $proposal->get_proposal_quotation_notes();
                    }
                }

                // Localize proposal script with all required variables
                $data = array(
                    'validation_message' => __('Please complete all required fields before saving.', 'arsol-pfw'),
                    'currency_symbol' => class_exists('WooCommerce') ? get_woocommerce_currency_symbol() : '$',
                );
                wp_localize_script('arsol-pfw-admin-cpt-proposal', 'arsol_proposal_vars', $data);
                
                // Localize budget script with proper budget data
                wp_localize_script('arsol-pfw-admin-cpt-proposal', 'arsol_budget_vars', array(
                    'currency_symbol' => class_exists('WooCommerce') ? get_woocommerce_currency_symbol() : '$',
                    'budget_data' => $budget_data,
                    'budget_notes' => $budget_notes,
                    'costing_type' => $costing_type,
                ));
                
                // Localize quotation script with proper quotation data
                wp_localize_script('arsol-pfw-admin-cpt-proposal', 'arsol_proposal_quotation_vars', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('arsol_proposal_quotation_nonce'),
                    'search_products_nonce' => wp_create_nonce('search-products'),
                    'currency_symbol' => class_exists('WooCommerce') ? get_woocommerce_currency_symbol() : '$',
                    'line_items' => $quotation_data,
                    'quotation_notes' => $quotation_notes,
                    'costing_type' => $costing_type,
                    'calculation_constants' => array(
                        'days_in_month' => 30.44,
                        'days_in_year' => 365.25,
                        'months_in_year' => 12
                    ),
                    'average_monthly_total_formatted' => '', // Will be calculated client-side
                ));
                break;

            case 'arsol-pfw-project':
                wp_enqueue_script('arsol-pfw-admin-cpt-project');
                wp_enqueue_script('arsol-pfw-admin-cpt-proposal'); // for Create Proposal
                break;

            case 'arsol-pfw-request':
                wp_enqueue_script('arsol-pfw-admin-cpt-request');
                break;
        }
    }
}
