<?php
/**
 * Frontend Project Endpoints Class
 *
 * Handles custom endpoints for project pages.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Woocommerce;

use Arsol_Projects_For_Woo\Frontend_Template_Overrides;
use Arsol_Projects_For_Woo\Woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend Project Endpoints class
 */
class Frontend_Endpoints {
    
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
        add_action('woocommerce_account_project-create_endpoint', array($this, 'project_create_endpoint_content'));
        add_action('woocommerce_account_project-request_endpoint', array($this, 'project_request_endpoint_content'));
        add_action('woocommerce_account_project-view-proposal_endpoint', array($this, 'project_view_proposal_endpoint_content'));
        add_action('woocommerce_account_project-view-request_endpoint', array($this, 'project_view_request_endpoint_content'));

        // Add comment redirect filter
        add_filter('comment_post_redirect', array($this, 'handle_comment_redirect'), 10, 2);
    }
    
    /**
     * Register custom rewrite endpoints
     *
     * @return void
     */
    public function register_endpoints() {
        $account_page_id = wc_get_page_id('myaccount');
        if ($account_page_id) {
            $account_page_slug = get_post_field('post_name', $account_page_id);
            add_rewrite_rule(
                '^' . $account_page_slug . '/projects/page/([0-9]+)/?$',
                'index.php?pagename=' . $account_page_slug . '&projects=1&paged=$matches[1]',
                'top'
            );
        }

        add_rewrite_endpoint('projects', EP_PAGES);
        add_rewrite_endpoint('project-overview', EP_PAGES);
        add_rewrite_endpoint('project-orders', EP_PAGES);
        add_rewrite_endpoint('project-subscriptions', EP_PAGES);
        add_rewrite_endpoint('project-create', EP_PAGES);
        add_rewrite_endpoint('project-request', EP_PAGES);
        add_rewrite_endpoint('project-view-proposal', EP_PAGES);
        add_rewrite_endpoint('project-view-request', EP_PAGES);

        // Debug logging
        if (function_exists('error_log')) {
            error_log('ARSOL DEBUG: Registering endpoints');
        }
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
        $query_vars['project-create'] = 'project-create';
        $query_vars['project-request'] = 'project-request';
        $query_vars['project-view-proposal'] = 'project-view-proposal';
        $query_vars['project-view-request'] = 'project-view-request';
        
        // Debug logging
        if (function_exists('error_log')) {
            error_log('ARSOL DEBUG: Registered query vars: ' . print_r($query_vars, true));
        }
        
        return $query_vars;
    }
    
    /**
     * Projects endpoint content
     */
    public function projects_endpoint_content() {
        // Get current tab from URL parameter, default to 'active'
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'active';

        // Get current user ID
        $user_id = get_current_user_id();
        
        // Determine current page from query vars
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;

        // Base query arguments
        $args = array(
            'posts_per_page' => 10,
            'paged'          => $paged,
            'author'         => $user_id,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        // Set post types and statuses based on current tab
        switch ($current_tab) {
            case 'proposals':
                $args['post_type']   = 'arsol-pfw-proposal';
                $args['post_status'] = 'publish';
                break;
            case 'requests':
                $args['post_type']   = 'arsol-pfw-request';
                $args['post_status'] = 'publish';
                break;
            case 'active':
            default:
                $args['post_type'] = 'arsol-project';
                $args['post_status'] = 'publish';
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'arsol-project-status',
                        'field'    => 'slug',
                        'terms'    => array('completed'),
                        'operator' => 'NOT IN',
                    ),
                );
                break;
        }

        // Perform the query
        $query = new \WP_Query($args);

        // Set up variables for the template
        $total_pages = $query->max_num_pages;
        $wp_button_class = function_exists('wc_wp_theme_get_element_class_name') 
            ? ' ' . wc_wp_theme_get_element_class_name('button') 
            : '';

        // Load the single master template and pass all necessary data
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
     * Display content for the project creation endpoint
     *
     * @return void
     */
    public function project_create_endpoint_content() {
        $user_id = get_current_user_id();
        $admin_users = new \Arsol_Projects_For_Woo\Admin\Users();
        
        if (!$admin_users->can_user_create_projects($user_id)) {
            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-access-denied.php';
            return;
        }
        
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project-create.php';
    }
    
    /**
     * Display content for the project request endpoint
     *
     * @return void
     */
    public function project_request_endpoint_content() {
        $user_id = get_current_user_id();
        $admin_users = new \Arsol_Projects_For_Woo\Admin\Users();

        if (!$admin_users->can_user_request_projects($user_id)) {
            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-access-denied.php';
            return;
        }

        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project-request.php';
    }
    
    /**
     * Display content for the project view proposal endpoint
     *
     * @return void
     */
    public function project_view_proposal_endpoint_content() {
        global $wp;
        $proposal_id = absint($wp->query_vars['project-view-proposal']);
        
        if (!$proposal_id) {
            wc_add_notice(__('Invalid proposal ID.', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }

        // Check if user has permission to view this proposal
        $proposal = get_post($proposal_id);
        $user_id = get_current_user_id();
        
        if (!$proposal || $proposal->post_type !== 'arsol-pfw-proposal') {
            wc_add_notice(__('Invalid proposal.', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }

        // Allow access if user is admin, has project management capabilities, or is the proposal author
        $can_view = \Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_manage_projects($user_id) ||
                   (\Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_projects($user_id) && $proposal->post_author === $user_id);

        if (!$can_view) {
            wc_add_notice(__('You do not have permission to view this proposal.', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }

        // Start output buffering
        ob_start();

        // Set the type for the template
        $_GET['type'] = 'proposal';
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project-approval.php';

        // Get the buffered content
        $content = ob_get_clean();

        // Output the content
        echo $content;
    }
    
    /**
     * Display content for the project view request endpoint
     *
     * @return void
     */
    public function project_view_request_endpoint_content() {
        global $wp;
        $request_id = absint($wp->query_vars['project-view-request']);
        
        if (!$request_id) {
            wc_add_notice(__('Invalid request ID.', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }

        // Check if user has permission to view this request
        $request = get_post($request_id);
        $user_id = get_current_user_id();
        
        if (!$request || $request->post_type !== 'arsol-pfw-request') {
            wc_add_notice(__('Invalid request.', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }

        // Allow access if user is admin, has project management capabilities, or is the request author
        $can_view = \Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_manage_projects($user_id) ||
                   (\Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_projects($user_id) && $request->post_author === $user_id);

        if (!$can_view) {
            wc_add_notice(__('You do not have permission to view this request.', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }

        // Start output buffering
        ob_start();

        // Set the type for the template
        $_GET['type'] = 'request';
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project-approval.php';

        // Get the buffered content
        $content = ob_get_clean();

        // Output the content
        echo $content;
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
            // Use the access denied template for a consistent look and feel
            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-access-denied.php';
            return;
        }

        // Project data, available to all included parts of the page
        $project = $this->get_project_data($project_id);

        // Load the main project template, which now handles the entire page structure
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project.php';
    }
    
    /**
     * Check if a user can view a project
     *
     * @param int $user_id User ID
     * @param int $project_id Project ID
     * @return bool Whether the user can view the project
     */
    public static function user_can_view_project($user_id, $project_id) {
        if (!is_user_logged_in()) {
            return false;
        }

        $post = get_post($project_id);
        if (!$post) {
            return false;
        }

        // Project managers can view all projects
        if (\Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_manage_projects($user_id)) {
            return true;
        }

        // Project creators can view their own projects
        if (\Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_projects($user_id) && $post->post_author == $user_id) {
            return true;
        }

        return false;
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