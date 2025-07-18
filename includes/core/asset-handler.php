<?php

namespace Arsol_Projects_For_Woo\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Assets class to manage CSS and JS files
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
        error_log('ARSOL DEBUG: Asset_Handler constructor called');
        
        // Delay asset hooks until after init to ensure text domain is loaded
        add_action('init', array($this, 'setup_asset_hooks'), 20);
        
        error_log('ARSOL DEBUG: Asset_Handler constructor completed');
    }

    /**
     * Setup asset hooks after init
     */
    public function setup_asset_hooks() {
        error_log('ARSOL DEBUG: Asset_Handler setup_asset_hooks called');
        
        // Register hooks for frontend assets
        add_action('wp_enqueue_scripts', array($this, 'register_frontend_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        
        // Register hooks for admin assets
        add_action('admin_enqueue_scripts', array($this, 'register_admin_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        error_log('ARSOL DEBUG: Asset_Handler hooks registered');


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

    /** Enqueue frontend assets on appropriate pages
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
        error_log('ARSOL DEBUG: Asset_Handler register_admin_assets called');
        
        $plugin_url = plugin_dir_url(ARSOL_PFW_PLUGIN_FILE);
        
        error_log('ARSOL DEBUG: Plugin URL: ' . $plugin_url);
        
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
     * Enqueue admin assets on appropriate pages
     * 
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        error_log('ARSOL DEBUG: Asset_Handler enqueue_admin_assets called with hook: ' . $hook);
        
        $screen = get_current_screen();
        if (!$screen) {
            error_log('ARSOL DEBUG: No screen found in enqueue_admin_assets');
            return;
        }

        error_log('ARSOL DEBUG: Screen post type: ' . $screen->post_type);
        error_log('ARSOL DEBUG: Screen ID: ' . $screen->id);

        // Simplified test - just enqueue the main admin script on any admin page
        error_log('ARSOL DEBUG: Enqueueing main admin script');
        wp_enqueue_style('arsol-pfw-admin');
        wp_enqueue_script('arsol-pfw-admin');
        
        error_log('ARSOL DEBUG: Main admin script enqueued');
        
        // Localize our main plugin script
        wp_localize_script('arsol-pfw-admin', 'arsolPfw', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('arsol-pfw-admin'),
            'confirmDelete' => __('Are you sure you want to remove this project?', 'arsol-pfw'),
        ));
        
        error_log('ARSOL DEBUG: Main admin script localized');
    }

    /**
     * Enqueue admin scripts for proposal quotation
     */
    public function enqueue_proposal_quotation_scripts() {
        global $post;
        
        if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
            return;
        }
        
        // Get proposal entity for data access
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post->ID);
        $line_items = $proposal->get_quotation_line_items() ?: array();
        
        wp_enqueue_script(
            'arsol-pfw-proposal-quotation',
            plugin_dir_url(ARSOL_PFW_PLUGIN_FILE) . 'assets/js/arsol-pfw-admin-cpt-proposal.js',
            array('jquery', 'jquery-ui-sortable'),
            ARSOL_PFW_VERSION,
            true
        );
        
        wp_localize_script('arsol-pfw-proposal-quotation', 'arsol_pfw_quotation_data', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('arsol_pfw_quotation_nonce'),
            'post_id' => $post->ID,
            'line_items' => $line_items,
            'currency' => $proposal->get_quotation_currency() ?: get_woocommerce_currency(),
            'currency_symbol' => $proposal->get_quotation_currency_symbol() ?: get_woocommerce_currency_symbol(),
            'strings' => array(
                'confirm_delete' => __('Are you sure you want to delete this item?', 'arsol-pfw'),
                'invalid_amount' => __('Please enter a valid amount.', 'arsol-pfw'),
                'invalid_quantity' => __('Please enter a valid quantity.', 'arsol-pfw'),
                'select_product' => __('Please select a product.', 'arsol-pfw'),
                'enter_description' => __('Please enter a description.', 'arsol-pfw')
            )
        ));
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
