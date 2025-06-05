<?php
/**
 * Project Endpoints Class
 *
 * Handles custom endpoints for project pages.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Woocommerce;

use Arsol_Projects_For_Woo\Woocommerce;

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
        
        // Add custom query vars
        add_filter('query_vars', array($this, 'add_woocommerce_query_vars'));
        
        // Handle endpoint content
        add_action('woocommerce_account_project-overview_endpoint', array($this, 'project_overview_endpoint_content'));
    }
    
    /**
     * Register custom rewrite endpoints
     */
    public function register_endpoints() {
        add_rewrite_endpoint('project-overview', EP_ROOT | EP_PAGES);
        flush_rewrite_rules();
    }
    
    /**
     * Add Projects to WooCommerce account menu
     */
    public function add_projects_menu_item($items) {
        $logout = $items['customer-logout'];
        unset($items['customer-logout']);
        $items['project-overview'] = __('Projects', 'arsol-pfw');
        $items['customer-logout'] = $logout;
        return $items;
    }
    
    /**
     * Add custom endpoints to WooCommerce query vars
     */
    public function add_woocommerce_query_vars($query_vars) {
        $query_vars['project-overview'] = 'project-overview';
        return $query_vars;
    }
    
    /**
     * Display project overview endpoint content
     */
    public function project_overview_endpoint_content() {
        $project_id = absint(get_query_var('project-overview'));
        
        if (!$project_id) {
            echo '<p>' . __('Invalid project ID.', 'arsol-pfw') . '</p>';
            return;
        }
        
        // Get project data
        $project = get_post($project_id);
        
        if (!$project || $project->post_type !== 'arsol-project') {
            echo '<p>' . __('Project not found.', 'arsol-pfw') . '</p>';
            return;
        }
        
        // Set up project data for template
        $project_title = get_the_title($project_id);
        $project_content = get_post_field('post_content', $project_id);
        $project_excerpt = get_post_field('post_excerpt', $project_id);
        $project_status = get_post_status($project_id);
        $project_date = get_the_date('', $project_id);
        
        // Include the project overview template
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project-overview.php';
    }
}

