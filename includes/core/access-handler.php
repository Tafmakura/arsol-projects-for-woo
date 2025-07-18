<?php
/**
 * Centralized Access Control System
 *
 * Consolidates all access checking, permission validation, and no-access handling
 * into a single, comprehensive class.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Centralized Access Handler Class
 * 
 * Provides a unified interface for all access control operations:
 * - Permission checking
 * - Access validation
 * - No-access handling
 * - Context-aware responses
 */
class Access_Handler {

    // ========================================
    // BUSINESS LOGIC ACCESS CHECKS
    // ========================================

    /**
     * Check if user can access a specific object
     * 
     * @param string $object_type Type of object ('project', 'proposal', 'request', 'create_project', 'request_project')
     * @param int|null $object_id Object ID
     * @param string $action Action being performed ('view', 'edit', 'delete', 'create')
     * @param int|null $user_id User ID (default: current user)
     * @return bool Whether user has access
     */
    public static function can_access($object_type, $object_id = null, $action = 'view', $user_id = null) {
        if (is_null($user_id)) {
            $user_id = get_current_user_id();
        }

        // Check if user is logged in
        if (!$user_id) {
            return false;
        }

        // Admin override
        if (user_can($user_id, 'manage_options')) {
            return true;
        }

        // Handle different object types
        switch ($object_type) {
            case 'project':
                return self::can_access_project($object_id, $action, $user_id);
            case 'proposal':
                return self::can_access_proposal($object_id, $action, $user_id);
            case 'request':
                return self::can_access_request($object_id, $action, $user_id);
            case 'create_project':
                return self::check_user_create_projects($user_id);
            case 'request_project':
                return self::check_user_request_projects($user_id);
            default:
                return false;
        }
    }

    /**
     * Check project access
     */
    private static function can_access_project($project_id, $action, $user_id) {
        $post = get_post($project_id);
        if (!$post || $post->post_type !== 'arsol-pfw-project') {
            return false;
        }

        // Check capabilities based on action
        switch ($action) {
            case 'view':
                return self::user_can_view_project($user_id, $project_id);
            case 'edit':
                return \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_edit_project($user_id, $project_id);
            case 'delete':
                return \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_delete_project($user_id, $project_id);
            default:
                return false;
        }
    }

    /**
     * Check proposal access
     */
    private static function can_access_proposal($proposal_id, $action, $user_id) {
        $post = get_post($proposal_id);
        if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
            return false;
        }

        // Check if user is the customer
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);
        $customer_id = $proposal->get_customer_id();
        if (!empty($customer_id) && $customer_id == $user_id) {
            return true;
        }

        // Check capabilities based on action
        switch ($action) {
            case 'view':
                return self::user_can_view_post($proposal_id, $user_id);
            case 'edit':
                return \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_edit_project_proposal($user_id, $proposal_id);
            case 'delete':
                return \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_delete_project_proposal($user_id, $proposal_id);
            default:
                return false;
        }
    }

    /**
     * Check request access
     */
    private static function can_access_request($request_id, $action, $user_id) {
        $post = get_post($request_id);
        if (!$post || $post->post_type !== 'arsol-pfw-request') {
            return false;
        }

        // Check capabilities based on action
        switch ($action) {
            case 'view':
                return self::user_can_view_post($request_id, $user_id);
            case 'edit':
                return \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_edit_project_request($user_id, $request_id);
            case 'delete':
                return \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_delete_project_request($user_id, $request_id);
            default:
                return false;
        }
    }

    /**
     * Check if user can view a post (basic ownership check)
     */
    private static function user_can_view_post($post_id, $user_id) {
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }
        
        return $post->post_author == $user_id;
    }

    /**
     * Check if user can view a project
     */
    public static function user_can_view_project($user_id, $project_id) {
        $post = get_post($project_id);
        if (!$post || $post->post_type !== 'arsol-pfw-project') {
            return false;
        }

        // Check management capabilities
        if (\Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_manage_projects($user_id)) {
            return true;
        }

        // Check creation capabilities + ownership
        if (\Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_create_projects($user_id) && $post->post_author == $user_id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can view a specific request
     */
    public static function user_can_view_request($user_id, $request_id) {
        $post = get_post($request_id);
        if (!$post || $post->post_type !== 'arsol-pfw-request') {
            return false;
        }

        return self::user_can_view_post($request_id, $user_id);
    }

    /**
     * Check if user can view a specific proposal
     */
    public static function user_can_view_proposal($user_id, $proposal_id) {
        $post = get_post($proposal_id);
        if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
            return false;
        }

        // Check if user is the customer of this proposal
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);
        $customer_id = $proposal->get_customer_id();
        if (!empty($customer_id) && $customer_id == $user_id) {
            return true;
        }

        return self::user_can_view_post($proposal_id, $user_id);
    }

    /**
     * Check if user can create projects (with global settings)
     */
    private static function check_user_create_projects($user_id) {
        $admin_users = new \Arsol_Projects_For_Woo\Admin\Users();
        return $admin_users->can_user_create_projects($user_id);
    }

    /**
     * Check if user can request projects (with global settings)
     */
    private static function check_user_request_projects($user_id) {
        $admin_users = new \Arsol_Projects_For_Woo\Admin\Users();
        return $admin_users->can_user_request_projects($user_id);
    }

    // ========================================
    // ACCESS VALIDATION WITH HANDLING
    // ========================================

    /**
     * Validate access and handle no-access automatically
     * 
     * @param string $object_type Type of object
     * @param int|null $object_id Object ID
     * @param string $action Action being performed
     * @param int|null $user_id User ID
     * @param string $response_type Response type ('template', 'html', 'redirect', 'auto')
     * @return bool True if access granted, false if denied
     */
    public static function validate_access($object_type, $object_id = null, $action = 'view', $user_id = null, $response_type = 'auto') {
        $context = self::get_context_from_object_type($object_type, $action);
        $data = self::prepare_context_data($object_type, $object_id);

        return self::check_access_and_handle(
            function() use ($object_type, $object_id, $action, $user_id) {
                return self::can_access($object_type, $object_id, $action, $user_id);
            },
            $context,
            $data,
            $response_type
        );
    }

    /**
     * Validate access for content filters (returns HTML)
     */
    public static function validate_content_access($object_type, $object_id, $action = 'view', $user_id = null) {
        return self::validate_access($object_type, $object_id, $action, $user_id, 'html');
    }

    /**
     * Validate access for endpoints (uses template)
     */
    public static function validate_endpoint_access($object_type, $object_id, $action = 'view', $user_id = null) {
        return self::validate_access($object_type, $object_id, $action, $user_id, 'template');
    }

    // ========================================
    // CONTEXT AND DATA HELPERS
    // ========================================

    /**
     * Get context from object type and action
     */
    private static function get_context_from_object_type($object_type, $action) {
        if ($object_type === 'create_project') {
            return 'create';
        }
        if ($object_type === 'request_project') {
            return 'request_projects';
        }
        return $object_type;
    }

    /**
     * Prepare context data for hooks
     */
    private static function prepare_context_data($object_type, $object_id) {
        $data = array();
        
        switch ($object_type) {
            case 'project':
                $data['project_id'] = $object_id;
                break;
            case 'proposal':
                $data['proposal_id'] = $object_id;
                break;
            case 'request':
                $data['request_id'] = $object_id;
                break;
        }
        
        return $data;
    }

    // ========================================
    // NO-ACCESS HANDLING
    // ========================================

    /**
     * Display no access template with full wrapper structure
     * 
     * @param string $context Context where no access occurred
     * @param array $data Additional data for hooks
     * @return void
     */
    public static function display_no_access_template($context = 'general', $data = array()) {
        // Set project type for hook compatibility
        $project_type = 'no-access';
        
        // Prepare wrapper data for hooks
        $wrapper_data = array_merge(array(
            'no_access' => true,
            'context' => $context
        ), $data);
        
        // Include the no-access template
        include ARSOL_PFW_PLUGIN_DIR . 'ui/templates/frontend/woocommerce/myaccount/no-access.php';
    }
    
    /**
     * Get no access message for specific context
     * 
     * @param string $context Context where no access occurred
     * @return string Localized message
     */
    public static function get_no_access_message($context = 'general') {
        switch ($context) {
            case 'proposal':
                return __('You do not have permission to view this proposal.', 'arsol-pfw');
            case 'request':
                return __('You do not have permission to view this request.', 'arsol-pfw');
            case 'project':
                return __('You do not have permission to view this project.', 'arsol-pfw');
            case 'create':
                return __('You do not have permission to create projects.', 'arsol-pfw');
            case 'request_projects':
                return __('You do not have permission to request projects.', 'arsol-pfw');
            case 'edit':
                return __('You do not have permission to edit this content.', 'arsol-pfw');
            case 'delete':
                return __('You do not have permission to delete this content.', 'arsol-pfw');
            case 'files':
                return __('You do not have permission to view these files.', 'arsol-pfw');
            case 'upload':
                return __('You do not have permission to upload files.', 'arsol-pfw');
            default:
                return __('You do not have permission to access this feature.', 'arsol-pfw');
        }
    }
    
    /**
     * Get no access title for specific context
     * 
     * @param string $context Context where no access occurred
     * @return string Localized title
     */
    public static function get_no_access_title($context = 'general') {
        switch ($context) {
            case 'proposal':
                return __('Proposal Access Denied', 'arsol-pfw');
            case 'request':
                return __('Request Access Denied', 'arsol-pfw');
            case 'project':
                return __('Project Access Denied', 'arsol-pfw');
            case 'create':
                return __('Create Project Access Denied', 'arsol-pfw');
            case 'request_projects':
                return __('Request Project Access Denied', 'arsol-pfw');
            case 'edit':
                return __('Edit Access Denied', 'arsol-pfw');
            case 'delete':
                return __('Delete Access Denied', 'arsol-pfw');
            case 'files':
                return __('File Access Denied', 'arsol-pfw');
            case 'upload':
                return __('Upload Access Denied', 'arsol-pfw');
            default:
                return __('Access Denied', 'arsol-pfw');
        }
    }
    
    /**
     * Check if we should use template vs redirect
     * 
     * @param string $context Current context
     * @return bool True if should use template, false for redirect
     */
    private static function should_use_template($context) {
        // Use template for My Account pages and frontend content
        $template_contexts = array('my_account', 'frontend_content', 'shortcode', 'general');
        
        // Use redirect for direct URL access and AJAX calls
        $redirect_contexts = array('direct_access', 'ajax', 'api');
        
        return in_array($context, $template_contexts);
    }
    
    /**
     * Handle no access scenario with appropriate response
     * 
     * @param string $context Context where no access occurred
     * @param array $data Additional data for hooks
     * @param string $response_type Type of response ('template', 'redirect', 'html', 'auto')
     * @return string|void HTML output or void for template/redirect
     */
    private static function handle_no_access($context = 'general', $data = array(), $response_type = 'auto') {
        // Determine response type
        if ($response_type === 'auto') {
            $response_type = self::should_use_template($context) ? 'template' : 'redirect';
        }
        
        switch ($response_type) {
            case 'template':
                // Display full template
                self::display_no_access_template($context, $data);
                break;
                
            case 'html':
                // Return HTML for shortcodes and content filters
                ob_start();
                self::display_no_access_template($context, $data);
                return ob_get_clean();
                
            case 'redirect':
                // Handle redirect with WooCommerce notice
                $message = self::get_no_access_message($context);
                if (function_exists('wc_add_notice')) {
                    wc_add_notice($message, 'error');
                }
                wp_safe_redirect(wc_get_account_endpoint_url('projects'));
                exit;
                
            default:
                // Default to template
                self::display_no_access_template($context, $data);
        }
    }
    
    /**
     * Check access and handle no access automatically
     * 
     * @param callable $permission_check Function that returns bool for access
     * @param string $context Context for no access handling
     * @param array $data Additional data for hooks
     * @param string $response_type Type of response
     * @return bool True if access granted, false if denied
     */
    private static function check_access_and_handle($permission_check, $context = 'general', $data = array(), $response_type = 'auto') {
        if ($permission_check()) {
            return true;
        }
        
        self::handle_no_access($context, $data, $response_type);
        return false;
    }

    // ========================================
    // LEGACY COMPATIBILITY METHODS
    // ========================================

    /**
     * Legacy method for backward compatibility
     */
    public static function check_user_permissions($post_id, $action = 'view', $user_id = null) {
        if (is_null($user_id)) {
            $user_id = get_current_user_id();
        }
        
        if (!self::user_can_view_post($post_id, $user_id)) {
            wp_die(__('You do not have permission to perform this action.', 'arsol-pfw'));
        }
        
        return true;
    }

    /**
     * Legacy method for backward compatibility
     */
    public static function user_can_edit_post($user_id, $post_id) {
        return user_can($user_id, 'edit_post', $post_id);
    }

    /**
     * Legacy method for backward compatibility
     */
    public static function user_can_publish_posts($user_id) {
        return user_can($user_id, 'publish_posts');
    }

    /**
     * Legacy method for backward compatibility
     */
    public static function user_is_admin($user_id = null) {
        if (is_null($user_id)) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, 'manage_options');
    }

    /**
     * Legacy method for backward compatibility
     */
    public static function check_conversion_permissions($post_id, $is_internal_call = false, $user_id = null) {
        if (is_null($user_id)) {
            $user_id = get_current_user_id();
        }

        if (!$is_internal_call) {
            // External calls require edit and publish permissions
            if (!self::user_can_edit_post($user_id, $post_id) || !self::user_can_publish_posts($user_id)) {
                throw new \Exception(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
            }
        } else {
            // Internal calls require view permissions
            if (!self::user_can_view_post($post_id, $user_id)) {
                throw new \Exception(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
            }
        }

        return true;
    }

    /**
     * Legacy method for backward compatibility
     */
    public static function validate_nonce_and_permissions($nonce_field, $nonce_action, $post_id = null, $capability = null) {
        // Validate nonce
        if (!isset($_GET[$nonce_field]) || !wp_verify_nonce($_GET[$nonce_field], $nonce_action)) {
            wp_die(__('Invalid request or nonce.', 'arsol-pfw'));
        }

        // Check post permissions if post_id provided
        if (!is_null($post_id)) {
            self::check_user_permissions($post_id);
        }

        // Check capability if provided
        if (!is_null($capability) && !current_user_can($capability)) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
        }

        return true;
    }

    /**
     * Legacy method for backward compatibility
     */
    public static function get_permission_error_message($action = 'access', $object_type = 'content') {
        return sprintf(
            __('You do not have permission to %s this %s.', 'arsol-pfw'),
            $action,
            $object_type
        );
    }
} 