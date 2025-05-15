<?php
/**
 * Project Endpoints Class
 *
 * Handles custom endpoints for project pages.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo;

use Arsol_Projects_For_Woo\Woo\AdminOrders;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Project Endpoints class
 */
class Endpoints {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Register endpoints
        add_action('init', array($this, 'register_endpoints'));
        
        // Add Projects to account menu
        add_filter('woocommerce_account_menu_items', array($this, 'add_projects_menu_item'));
        
        // Handle endpoint content
        add_action('woocommerce_account_projects_endpoint', array($this, 'projects_endpoint_content'));
    }
    
    /**
     * Register custom rewrite endpoints
     *
     * @return void
     */
    public function register_endpoints() {
        add_rewrite_endpoint('projects', EP_ROOT | EP_PAGES);
    }
    
    /**
     * Add Projects to WooCommerce account menu
     *
     * @param array $items Menu items
     * @return array Modified menu items
     */
    public function add_projects_menu_item($items) {
        $logout = $items['customer-logout'];
        unset($items['customer-logout']);
        $items['projects'] = __('Projects', 'arsol-pfw');
        $items['customer-logout'] = $logout;
        return $items;
    }
    
    /**
     * Display content for the projects endpoint
     *
     * @return void
     */
    public function projects_endpoint_content() {
        // Get current user ID
        $user_id = get_current_user_id();
        
        // Set up pagination
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $posts_per_page = 10; // Number of projects per page
        
        // Query user projects
        $args = array(
            'post_type'      => 'project', // Adjust if your CPT has a different name
            'posts_per_page' => $posts_per_page,
            'paged'          => $paged,
            'meta_query'     => array(
                array(
                    'key'     => '_project_user_id', // Adjust based on how you store user association
                    'value'   => $user_id,
                    'compare' => '='
                )
            )
        );
        
        $projects_query = new \WP_Query($args);
        $has_projects = $projects_query->have_posts();
        
        // Pagination data
        $total_pages = $projects_query->max_num_pages;
        $current_page = max(1, $paged);
        
        // Button class for WooCommerce 3.5+
        $wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? 
            ' ' . wc_wp_theme_get_element_class_name('button') : '';
        
        // Include the template
        include ARSOL_PFW_PATH . 'includes/ui/templates/frontend/projects.php';
    }
}

