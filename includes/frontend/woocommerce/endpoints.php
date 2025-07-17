<?php
/**
 * Frontend Project Endpoints Class
 *
 * Handles custom endpoints for project pages.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Frontend\WooCommerce;

use Arsol_Projects_For_Woo\Frontend\Template\Overrides;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend Project Endpoints class
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
        add_action('woocommerce_account_view-project_endpoint', array($this, 'project_overview_endpoint_content'));
        add_action('woocommerce_account_view-project-orders_endpoint', array($this, 'project_orders_endpoint_content'));
        
        // Only register subscription endpoint if WooCommerce Subscriptions is active
        if (class_exists('WC_Subscriptions')) {
            add_action('woocommerce_account_view-project-subscriptions_endpoint', array($this, 'project_subscriptions_endpoint_content'));
        }
        
        add_action('woocommerce_account_create-project_endpoint', array($this, 'project_create_endpoint_content'));
        add_action('woocommerce_account_create-request_endpoint', array($this, 'project_request_endpoint_content'));
        add_action('woocommerce_account_view-proposal_endpoint', array($this, 'project_view_proposal_endpoint_content'));
        add_action('woocommerce_account_view-request_endpoint', array($this, 'project_view_request_endpoint_content'));

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
        add_rewrite_endpoint('view-project', EP_PAGES);
        add_rewrite_endpoint('view-project-orders', EP_PAGES);
        
        // Only register subscription endpoint if WooCommerce Subscriptions is active
        if (class_exists('WC_Subscriptions')) {
            add_rewrite_endpoint('view-project-subscriptions', EP_PAGES);
        }
        
        add_rewrite_endpoint('create-project', EP_PAGES);
        add_rewrite_endpoint('create-request', EP_PAGES);
        add_rewrite_endpoint('view-proposal', EP_PAGES);
        add_rewrite_endpoint('view-request', EP_PAGES);

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
        $query_vars['view-project'] = 'view-project';
        $query_vars['view-project-orders'] = 'view-project-orders';
        
        // Only add subscription query var if WooCommerce Subscriptions is active
        if (class_exists('WC_Subscriptions')) {
            $query_vars['view-project-subscriptions'] = 'view-project-subscriptions';
        }
        
        $query_vars['create-project'] = 'create-project';
        $query_vars['create-request'] = 'create-request';
        $query_vars['view-proposal'] = 'view-proposal';
        $query_vars['view-request'] = 'view-request';
        
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
                $args['post_type'] = 'arsol-pfw-project';
                $args['post_status'] = 'publish';
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'arsol-pfw-project-stage',
                        'field'    => 'slug',
                        'terms'    => 'active'
                    )
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
        include ARSOL_PFW_PLUGIN_DIR . 'ui/templates/frontend/woocommerce/myaccount/projects.php';
    }
    
    /**
     * Display project overview endpoint content
     *
     * @return void
     */
    public function project_overview_endpoint_content() {
        $project_id = absint(get_query_var('view-project'));
        
        if (!$this->validate_project_access($project_id)) {
            return;
        }
        
        // Create single project instance using our entity class
        $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($project_id);
        $current_tab = 'overview';
        
        // Stage handling with proper error checking
        $stage_terms = wp_get_object_terms($project_id, 'arsol-pfw-project-stage', array('fields' => 'slugs'));
        $current_stage = '';
        if (!is_wp_error($stage_terms) && !empty($stage_terms)) {
            $current_stage = $stage_terms[0];
        }
        
        // Prepare comprehensive data for efficient hook usage
        $wrapper_data = compact('project_id', 'current_stage');
        
        // Debug logging with object properties
        error_log("ARSOL DEBUG: Project Overview - ID: {$project->get_id()}, Title: '{$project->get_title()}', Type: {$project->get_post()->post_type}, Stage: '$current_stage'");
        
        include ARSOL_PFW_PLUGIN_DIR . 'ui/templates/frontend/woocommerce/myaccount/project-overview.php';
    }
    
    /**
     * Display project orders endpoint content
     *
     * @return void
     */
    public function project_orders_endpoint_content() {
        $project_id = absint(get_query_var('view-project-orders'));
        
        if (!$this->validate_project_access($project_id)) {
            return;
        }
        
        // Create single project instance using our entity class
        $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($project_id);
        $current_tab = 'orders';
        
        // Stage handling with proper error checking
        $stage_terms = wp_get_object_terms($project_id, 'arsol-pfw-project-stage', array('fields' => 'slugs'));
        $current_stage = '';
        if (!is_wp_error($stage_terms) && !empty($stage_terms)) {
            $current_stage = $stage_terms[0];
        }
        
        // Prepare comprehensive data for efficient hook usage
        $wrapper_data = compact('project_id', 'current_stage');
        
        // Debug logging with object properties
        error_log("ARSOL DEBUG: Project Orders - ID: {$project->get_id()}, Title: '{$project->get_title()}', Type: {$project->get_post()->post_type}, Stage: '$current_stage'");
        
        include ARSOL_PFW_PLUGIN_DIR . 'ui/templates/frontend/woocommerce/myaccount/project-orders.php';
    }
    
    /**
     * Display project subscriptions endpoint content
     *
     * @return void
     */
    public function project_subscriptions_endpoint_content() {
        // Check if WooCommerce Subscriptions is active
        if (!class_exists('WC_Subscriptions')) {
            wc_add_notice(__('WooCommerce Subscriptions plugin is required for this feature.', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }
        
        $project_id = absint(get_query_var('view-project-subscriptions'));
        
        if (!$this->validate_project_access($project_id)) {
            return;
        }
        
        // Create single project instance using our entity class
        $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($project_id);
        $current_tab = 'subscriptions';
        
        // Stage handling with proper error checking
        $stage_terms = wp_get_object_terms($project_id, 'arsol-pfw-project-stage', array('fields' => 'slugs'));
        $current_stage = '';
        if (!is_wp_error($stage_terms) && !empty($stage_terms)) {
            $current_stage = $stage_terms[0];
        }
        
        // Prepare comprehensive data for efficient hook usage
        $wrapper_data = compact('project_id', 'current_stage');
        
        // Debug logging with object properties
        error_log("ARSOL DEBUG: Project Subscriptions - ID: {$project->get_id()}, Title: '{$project->get_title()}', Type: {$project->get_post()->post_type}, Stage: '$current_stage'");
        
        include ARSOL_PFW_PLUGIN_DIR . 'ui/templates/frontend/woocommerce/myaccount/project-subscriptions.php';
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
            include ARSOL_PFW_PLUGIN_DIR . 'ui/templates/frontend/woocommerce/myaccount/no-access.php';
            return;
        }
        
        include ARSOL_PFW_PLUGIN_DIR . 'ui/templates/frontend/woocommerce/myaccount/project-create.php';
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
            include ARSOL_PFW_PLUGIN_DIR . 'ui/templates/frontend/woocommerce/myaccount/no-access.php';
            return;
        }

        // Use the project request template
        include ARSOL_PFW_PLUGIN_DIR . 'ui/templates/frontend/woocommerce/myaccount/project-request.php';
    }
    
    /**
     * Display content for the project view proposal endpoint
     *
     * @return void
     */
    public function project_view_proposal_endpoint_content() {
        $proposal_id = absint(get_query_var('view-proposal'));
        
        if (!$proposal_id) {
            wc_add_notice(__('Invalid proposal ID.', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }

        // Check if user has permission to view this proposal
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);
        $user_id = get_current_user_id();
        
        if (!$proposal || $proposal->get_post()->post_type !== 'arsol-pfw-proposal') {
            wc_add_notice(__('Invalid proposal.', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }

        // Allow access if user is the author or has project management capabilities
        $can_view = \Arsol_Projects_For_Woo\Core\Permissions::user_can_view_proposal($user_id, $proposal_id);

        if (!$can_view) {
            // Use centralized no-access template instead of redirect
                'proposal_id' => $proposal_id
            ));
            return;
        }

        // Generate contextual variables directly - no remapping
        $current_tab = 'proposal';

        // Stage handling with proper error checking
        $stage_terms = wp_get_object_terms($proposal_id, 'arsol-pfw-proposal-stage', array('fields' => 'slugs'));
        $current_stage = '';
        if (!is_wp_error($stage_terms) && !empty($stage_terms)) {
            $current_stage = $stage_terms[0];
        }

        // Prepare comprehensive data for efficient hook usage
        $wrapper_data = compact('proposal_id', 'current_stage');
        
        // Debug logging with object properties
        error_log("ARSOL DEBUG: Proposal View - ID: {$proposal->get_id()}, Title: '{$proposal->get_title()}', Type: {$proposal->get_post()->post_type}, Stage: '$current_stage'");

        // Include the new project view proposal template
        include ARSOL_PFW_PLUGIN_DIR . 'ui/templates/frontend/woocommerce/myaccount/project-view-proposal.php';
    }
    
    /**
     * Display content for the project view request endpoint
     *
     * @return void
     */
    public function project_view_request_endpoint_content() {
        $request_id = absint(get_query_var('view-request'));
        
        if (!$request_id) {
            wc_add_notice(__('Invalid request ID.', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }

        // Check if user has permission to view this request
        $request = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($request_id);
        $user_id = get_current_user_id();
        
        if (!$request || $request->get_post()->post_type !== 'arsol-pfw-request') {
            wc_add_notice(__('Invalid request.', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }

        // Allow access if user is the author or has project management capabilities
        $can_view = \Arsol_Projects_For_Woo\Core\Permissions::user_can_view_request($user_id, $request_id);

        if (!$can_view) {
            // Use centralized no-access template instead of redirect
            // Use centralized no-access template instead of redirect
            // Use centralized no-access template instead of redirect
            \Arsol_Projects_For_Woo\Core\Access_Handler::display_no_access_template('request', array(
                'request_id' => $request_id
            ));
            return;
        }

        // Generate contextual variables directly - no remapping
        $current_tab = 'request';

        // Get request stage (with proper error handling)
        $stage_terms = wp_get_object_terms($request_id, 'arsol-pfw-request-stage', array('fields' => 'slugs'));
        $current_stage = '';
        if (!is_wp_error($stage_terms) && !empty($stage_terms)) {
            $current_stage = $stage_terms[0];
        }

        // Prepare comprehensive data for efficient hook usage
        $wrapper_data = compact('request_id', 'current_stage');
        
        // Debug logging with object properties
        error_log("ARSOL DEBUG: Request View - ID: {$request->get_id()}, Title: '{$request->get_title()}', Type: {$request->get_post()->post_type}, Stage: '$current_stage'");

        // Include the new project view request template
        include ARSOL_PFW_PLUGIN_DIR . 'ui/templates/frontend/woocommerce/myaccount/project-view-request.php';
    }
    
    /**
     * Validate project access for the current user
     *
     * @param int $project_id Project ID
     * @return bool Whether the user can access the project
     */
    private function validate_project_access($project_id) {
        $user_id = get_current_user_id();
        
        // Check if project exists and user has access
        $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($project_id);
        if (!$project || !\Arsol_Projects_For_Woo\Core\Permissions::user_can_view_project($user_id, $project_id)) {
            // Use the no-access template for consistency
            include ARSOL_PFW_PLUGIN_DIR . 'ui/templates/frontend/woocommerce/myaccount/no-access.php';
            return false;
        }
        
        return true;
    }

    /**
     * Get project data for API response
     * 
     * @param int $project_id Project post ID
     * @return array Project data
     */
    private function get_project_data($project_id) {
        $project = get_post($project_id);
        if (!$project || $project->post_type !== 'arsol-pfw-project') {
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
    
    /**
     * Check if a user can view a project
     *
     * @param int $user_id User ID
     * @param int $project_id Project ID
     * @return bool Whether the user can view the project
     */
    public static function user_can_view_project($user_id, $project_id) {
        // This method is now deprecated - use \Arsol_Projects_For_Woo\Core\Permissions::user_can_view_project() instead
        return \Arsol_Projects_For_Woo\Core\Permissions::user_can_view_project($user_id, $project_id);
    }

    /**
     * Handle comment redirect for project-related posts
     *
     * @param string $location The redirect URL
     * @param WP_Comment $comment The comment object
     * @return string Modified redirect URL
     */
    public function handle_comment_redirect($location, $comment) {
        $post = get_post($comment->comment_post_ID);
        
        if (!$post) {
            return $location;
        }

        // Check if this is a project-related post type
        if (in_array($post->post_type, ['arsol-pfw-project', 'arsol-pfw-proposal', 'arsol-pfw-request'])) {
            // Determine the appropriate endpoint based on post type
            switch ($post->post_type) {
                case 'arsol-pfw-project':
                    $endpoint = 'view-project';
                    break;
                case 'arsol-pfw-proposal':
                    $endpoint = 'view-proposal';
                    break;
                case 'arsol-pfw-request':
                    $endpoint = 'view-request';
                    break;
                default:
                    return $location;
            }

            // Redirect to the appropriate WooCommerce account endpoint
            $redirect_url = wc_get_account_endpoint_url($endpoint, $post->ID);
            
            // Add comment anchor
            $redirect_url .= '#comment-' . $comment->comment_ID;
            
            return $redirect_url;
        }

        return $location;
    }
} 