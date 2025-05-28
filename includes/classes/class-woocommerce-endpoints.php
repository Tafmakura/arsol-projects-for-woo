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
        add_filter('woocommerce_get_query_vars', array($this, 'add_woocommerce_query_vars'));
        
        // Handle endpoint content
        add_action('woocommerce_account_projects_endpoint', array($this, 'projects_endpoint_content'));
        add_action('woocommerce_account_project-overview_endpoint', array($this, 'project_overview_endpoint_content'));
        add_action('woocommerce_account_project-orders_endpoint', array($this, 'project_orders_endpoint_content'));
        add_action('woocommerce_account_project-subscriptions_endpoint', array($this, 'project_subscriptions_endpoint_content'));

        // Add comment redirect filter
        add_filter('comment_post_redirect', array($this, 'handle_comment_redirect'), 10, 2);
    }
    
    /**
     * Register custom rewrite endpoints
     *
     * @return void
     */
    public function register_endpoints() {
        add_rewrite_endpoint('projects', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('project-overview', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('project-orders', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('project-subscriptions', EP_ROOT | EP_PAGES);
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
     * Add custom endpoints to WooCommerce query vars
     *
     * @param array $query_vars Query vars to add to
     * @return array
     */
    public function add_woocommerce_query_vars($query_vars) {
        $query_vars['projects'] = 'projects';
        $query_vars['project-overview'] = 'project-overview';
        $query_vars['project-orders'] = 'project-orders';
        $query_vars['project-subscriptions'] = 'project-subscriptions';
        return $query_vars;
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
            'post_type'      => 'arsol-project',
            'posts_per_page' => $posts_per_page,
            'paged'          => $paged,
            'author'         => $user_id
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
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-projects.php';
    }
    
    /**
     * Display project overview endpoint content
     *
     * @return void
     */
    public function project_overview_endpoint_content() {
        $project_id = absint(get_query_var('project-overview'));
        $this->render_project_page($project_id, 'overview');
    }
    
    /**
     * Display project orders endpoint content
     *
     * @return void
     */
    public function project_orders_endpoint_content() {
        $project_id = absint(get_query_var('project-orders'));
        $this->render_project_page($project_id, 'orders');
    }
    
    /**
     * Display project subscriptions endpoint content
     *
     * @return void
     */
    public function project_subscriptions_endpoint_content() {
        $project_id = absint(get_query_var('project-subscriptions'));
        $this->render_project_page($project_id, 'subscriptions');
    }
    
    /**
     * Helper method to render a project page
     *
     * @param int $project_id Project ID
     * @param string $tab Current tab (overview, orders, subscriptions)
     * @return void
     */
    private function render_project_page($project_id, $tab) {
        // Get current user ID
        $user_id = get_current_user_id();
        
        // Check if project exists and user has access
        if (!$project_id || !$this->user_can_view_project($user_id, $project_id)) {
            echo '<p>' . __('You do not have permission to view this project.', 'arsol-pfw') . '</p>';
            return;
        }
        
        // Get project data
        $project = get_post($project_id);
        
        // Set common variables needed for all templates
        $project_title = get_the_title($project_id);
        $project_content = get_post_field('post_content', $project_id);
        $project_excerpt = get_post_field('post_excerpt', $project_id);
        $project_status = get_post_status($project_id);
        $project_date = get_the_date('', $project_id);
        
        // Display the project navigation
        echo $this->get_project_navigation($project_id, $tab);
  
        // Include appropriate template based on tab
        switch ($tab) {
            case 'orders':
                include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project-orders.php';
                break;
                
            case 'subscriptions':
                include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project-subscriptions.php';
                break;
                
            case 'overview':
            default:
                // If content is empty, use the project overview template
                if (empty($project_content)) {
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project-overview.php';
                } else {
                    // Use the default content display
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project-overview.php';
                }
                break;
        }
    }
    
    /**
     * Check if a user can view a project
     *
     * @param int $user_id User ID
     * @param int $project_id Project ID
     * @return bool Whether the user can view the project
     */
    public static function user_can_view_project($user_id, $project_id) {
        // If Woocommerce class has this method, use it
        if (method_exists('Arsol_Projects_For_Woo\Woocommerce', 'user_can_view_project')) {
            return Woocommerce::user_can_view_project($user_id, $project_id);
        }
        
        // Otherwise implement simple check
        $project = get_post($project_id);
        if (!$project || $project->post_type !== 'arsol-project') {
            return false;
        }
        
        $project_author_id = get_post_field('post_author', $project_id);
        return ($project_author_id == $user_id);
    }

    /**
     * Generate project navigation tabs
     *
     * @param int $project_id Current project ID
     * @param string $current_tab Current active tab
     * @return string HTML for navigation tabs
     */
    private function get_project_navigation($project_id, $current_tab) {
        // Get project title for breadcrumb/heading
        $project_title = get_the_title($project_id);
        
        // Include the navigation template
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-navigation.php';
        
        // Get the output buffer content
        return ob_get_clean();
    }

    /**
     * Handle comment redirect for project pages
     *
     * @param string $location The redirect location
     * @param WP_Comment $comment The comment object
     * @return string Modified redirect location
     */
    public function handle_comment_redirect($location, $comment) {
        // Check if the comment is on a project post type
        $post = get_post($comment->comment_post_ID);
        
        if ($post && $post->post_type === 'project') {
            // Get the project overview URL
            $project_url = wc_get_account_endpoint_url('project-overview/' . $post->ID);
            
            // Parse the original location to get query parameters
            $parsed_url = parse_url($location);
            $query_params = array();
            if (isset($parsed_url['query'])) {
                parse_str($parsed_url['query'], $query_params);
            }
            
            // Add necessary query parameters back
            if (!empty($query_params)) {
                $project_url = add_query_arg($query_params, $project_url);
            }
            
            // Add the comment anchor
            $location = $project_url . '#comment-' . $comment->comment_ID;
        }
        
        return $location;
    }

    /**
     * Get project data for API response
     * 
     * @param int $project_id Project post ID
     * @return array Project data
     */
    private function get_project_data($project_id) {
        $project = get_post($project_id);
        if (!$project || $project->post_type !== 'arsol-project') {
            return null;
        }

        return [
            'id' => $project->ID,
            'title' => $project->post_title,
            'content' => $project->post_content,
            'date' => $project->post_date,
            'modified' => $project->post_modified,
            'status' => $project->post_status,
            'author' => $project->post_author
        ];
    }
}

