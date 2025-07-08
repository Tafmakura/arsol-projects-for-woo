<?php
/**
 * Core Permissions Class
 *
 * Centralizes all permission checking logic for the plugin.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Permissions Class
 * 
 * Provides centralized permission checking for all plugin functionality.
 * This class consolidates permission logic from various parts of the codebase.
 */
class Permissions {

    /**
     * Check if a user can view a specific post
     * 
     * This method is used for security validation throughout the plugin.
     * Users can view their own posts, and admins can view everything.
     * 
     * @param int $user_id The user ID to check
     * @param int $post_id The post ID to check access for
     * @return bool Whether the user can view the post
     */
    public static function user_can_view_post($user_id, $post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }
        
        // The user can view their own posts
        if ($post->post_author == $user_id) {
            return true;
        }
        
        // Admins can view everything
        if (user_can($user_id, 'manage_options')) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if a user can view a specific project
     * 
     * @param int $user_id The user ID to check
     * @param int $project_id The project ID to check access for
     * @return bool Whether the user can view the project
     */
    public static function user_can_view_project($user_id, $project_id) {
        $post = get_post($project_id);
        if (!$post || $post->post_type !== 'arsol-pfw-project') {
            return false;
        }

        // Admins can view everything
        if (user_can($user_id, 'manage_options')) {
            return true;
        }

        // Check using Admin_Capabilities for project-specific permissions
        if (\Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_manage_projects($user_id)) {
            return true;
        }

        // Users can view projects they created if they have project creation permissions
        if (\Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_projects($user_id) && $post->post_author == $user_id) {
            return true;
        }

        return false;
    }

    /**
     * Check if a user can view a specific request
     * 
     * @param int $user_id The user ID to check
     * @param int $request_id The request ID to check access for
     * @return bool Whether the user can view the request
     */
    public static function user_can_view_request($user_id, $request_id) {
        $post = get_post($request_id);
        if (!$post || $post->post_type !== 'arsol-pfw-request') {
            return false;
        }

        return self::user_can_view_post($user_id, $request_id);
    }

    /**
     * Check if a user can view a specific proposal
     * 
     * @param int $user_id The user ID to check
     * @param int $proposal_id The proposal ID to check access for
     * @return bool Whether the user can view the proposal
     */
    public static function user_can_view_proposal($user_id, $proposal_id) {
        $post = get_post($proposal_id);
        if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
            return false;
        }

        // Check if user is the assigned customer for the proposal
        $customer_id = get_post_meta($proposal_id, '_arsol_pfw_proposal_customer_id', true);
        if (!empty($customer_id) && $customer_id == $user_id) {
            return true;
        }

        return self::user_can_view_post($user_id, $proposal_id);
    }

    /**
     * Check if current user can perform action on post
     * 
     * This method provides a unified way to check permissions and die with error if insufficient.
     * 
     * @param int $post_id The post ID to check
     * @param string $action The action being performed (default: 'view')
     * @param int|null $user_id The user ID to check (default: current user)
     * @return bool True if user has permission
     * @throws wp_die() if user doesn't have permission
     */
    public static function check_user_permissions($post_id, $action = 'view', $user_id = null) {
        if (is_null($user_id)) {
            $user_id = get_current_user_id();
        }
        
        if (!self::user_can_view_post($user_id, $post_id)) {
            wp_die(__('You do not have permission to perform this action.', 'arsol-pfw'));
        }
        
        return true;
    }

    /**
     * Check if user can edit a specific post based on WordPress capabilities
     * 
     * @param int $user_id The user ID to check
     * @param int $post_id The post ID to check
     * @return bool Whether the user can edit the post
     */
    public static function user_can_edit_post($user_id, $post_id) {
        return user_can($user_id, 'edit_post', $post_id);
    }

    /**
     * Check if user can publish posts
     * 
     * @param int $user_id The user ID to check
     * @return bool Whether the user can publish posts
     */
    public static function user_can_publish_posts($user_id) {
        return user_can($user_id, 'publish_posts');
    }

    /**
     * Check if user is administrator
     * 
     * @param int|null $user_id The user ID to check (default: current user)
     * @return bool Whether the user is an administrator
     */
    public static function user_is_admin($user_id = null) {
        if (is_null($user_id)) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, 'manage_options');
    }

    /**
     * Check conversion permissions for internal vs external calls
     * 
     * @param int $post_id The post ID being converted
     * @param bool $is_internal_call Whether this is an internal call
     * @param int|null $user_id The user ID to check (default: current user)
     * @return bool Whether the user has conversion permissions
     * @throws Exception if permissions are insufficient
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
            if (!self::user_can_view_post($user_id, $post_id)) {
                throw new \Exception(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
            }
        }

        return true;
    }

    /**
     * Validate nonce and permissions in one call
     * 
     * @param string $nonce_field The nonce field name
     * @param string $nonce_action The nonce action
     * @param int|null $post_id Optional post ID to check permissions for
     * @param string $capability Optional capability to check
     * @return bool True if validation passes
     * @throws wp_die() if validation fails
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
     * Get permission error message for display
     * 
     * @param string $action The action that was attempted
     * @param string $object_type The type of object (project, request, proposal)
     * @return string The formatted error message
     */
    public static function get_permission_error_message($action = 'access', $object_type = 'content') {
        return sprintf(
            __('You do not have permission to %s this %s.', 'arsol-pfw'),
            $action,
            $object_type
        );
    }
}
