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
        
        // Add query vars
        add_filter('query_vars', array($this, 'add_query_vars'));
        
        // Handle template redirects
        add_action('template_redirect', array($this, 'handle_template_redirects'));
        
        // Register rewrite flush on plugin activation
        register_activation_hook(ARSOL_PROJECTS_PLUGIN_FILE, array($this, 'flush_rewrite_rules'));
    }
    
    /**
     * Register endpoints
     */
    public function register_endpoints() {
        add_rewrite_endpoint('overview', EP_PERMALINK);
        add_rewrite_endpoint('invoices', EP_PERMALINK);
        add_rewrite_endpoint('subscriptions', EP_PERMALINK);
    }
    
    /**
     * Add query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'overview';
        $vars[] = 'invoices';
        $vars[] = 'subscriptions';
        return $vars;
    }
    
    /**
     * Handle template redirects
     */
    public function handle_template_redirects() {
        global $wp_query, $post;
        
        // Only run on project post type
        if (!is_singular('project')) {
            return;
        }
        
        // Get the project ID
        $project_id = $post->ID;
        
        // Check user access
        $user_id = get_current_user_id();
        if ($user_id && method_exists('\Arsol_Projects_For_Woo\Woo\AdminOrders', 'user_can_view_project')) {
            if (!\Arsol_Projects_For_Woo\Woo\AdminOrders::user_can_view_project($user_id, $project_id)) {
                wp_redirect(home_url());
                exit;
            }
        }
        
        // Get project data
        $project = get_post($project_id);
        $project_title = get_the_title($project_id);
        $project_content = apply_filters('the_content', $project->post_content);
        $project_meta = get_post_meta($project_id);
        
        // Get order and subscription counts if available
        $project_orders_count = 0;
        $project_subscriptions_count = 0;
        
        if (class_exists('\Arsol_Projects_For_Woo\Woo\AdminOrders')) {
            $project_orders_count = AdminOrders::get_project_orders_count($project_id, $user_id);
            $project_subscriptions_count = AdminOrders::get_project_subscriptions_count($project_id, $user_id);
        }
        
        // Determine which template to load
        if (isset($wp_query->query_vars['invoices'])) {
            $template = 'project-orders.php';
        } elseif (isset($wp_query->query_vars['subscriptions'])) {
            $template = 'project-subscriptions.php';
        } else {
            // Default to overview
            $template = 'project-overview.php';
        }
        
        // Check if theme has custom template
        $theme_template = locate_template('arsol-projects-for-woo/' . $template);
        if ($theme_template) {
            include $theme_template;
            exit;
        }
        
        // Otherwise use plugin template
        $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/' . $template;
        if (file_exists($template_path)) {
            include $template_path;
            exit;
        }
        
        // If we reach here, no template was found
        if (current_user_can('manage_options')) {
            wp_die(sprintf(__('Template file not found: %s', 'arsol-projects-for-woo'), $template_path));
        }
    }
    
    /**
     * Flush rewrite rules on activation
     */
    public function flush_rewrite_rules() {
        $this->register_endpoints();
        flush_rewrite_rules();
    }
}

