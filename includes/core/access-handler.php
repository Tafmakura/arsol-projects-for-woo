<?php
/**
 * Access Handler Class
 *
 * Centralizes all access control and no-access display logic for the plugin.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Access Handler Class
 * 
 * Provides centralized access control and no-access template display.
 * This class consolidates access logic from various parts of the codebase.
 */
class Access_Handler {

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
     * @param string $object_type Type of object (project, proposal, request)
     * @return string Localized message
     */
    public static function get_no_access_message($context = 'general', $object_type = 'content') {
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
    public static function should_use_template($context) {
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
    public static function handle_no_access($context = 'general', $data = array(), $response_type = 'auto') {
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
    public static function check_access_and_handle($permission_check, $context = 'general', $data = array(), $response_type = 'auto') {
        if ($permission_check()) {
            return true;
        }
        
        self::handle_no_access($context, $data, $response_type);
        return false;
    }
    
    /**
     * Get context from current request
     * 
     * @return string Current context
     */
    public static function get_current_context() {
        if (is_admin()) {
            return 'admin';
        }
        
        if (wp_doing_ajax()) {
            return 'ajax';
        }
        
        if (is_account_page()) {
            return 'my_account';
        }
        
        if (is_singular()) {
            return 'frontend_content';
        }
        
        return 'general';
    }
}
