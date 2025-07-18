<?php
/**
 * Centralized Access Control System
 *
 * Consolidates all access checking and permission validation into a single,
 * comprehensive class. This class provides pure access-checking logic and
 * returns boolean values.
 *
 * @package Arsol_Projects_For_Woo
 * @since 2.1.0
 */

namespace Arsol_Projects_For_Woo\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Centralized Access Handler Class
 *
 * Provides a unified, boolean-based interface for all access control operations.
 * It integrates global settings, user-specific settings, and WordPress-native
 * capabilities to determine access rights.
 */
class Access_Handler {

    // ========================================
    // Main Access Checking Method
    // ========================================

    /**
     * Check if a user can access a specific object or perform an action.
     * This is the primary entry point for all permission checks.
     *
     * @param string   $object_type   Type of object or action ('project', 'proposal', 'request', 'create_project', 'request_project').
     * @param int|null $object_id     The ID of the object (if applicable).
     * @param string   $action        Action being performed ('view', 'edit', 'delete', 'create').
     * @param int|null $user_id       User ID to check (defaults to current user).
     * @return bool                   True if user has access, false otherwise.
     */
    public static function can_access($object_type, $object_id = null, $action = 'view', $user_id = null) {
        $user_id = $user_id ? $user_id : get_current_user_id();

        if (empty($user_id)) {
            return false; // Must be logged in for any access.
        }

        // Admins have universal access.
        if (user_can($user_id, 'manage_options')) {
            return true;
        }

        switch ($object_type) {
            case 'project':
                return self::can_access_cpt($object_id, 'project', $action, $user_id);

            case 'proposal':
                return self::can_access_cpt($object_id, 'proposal', $action, $user_id);

            case 'request':
                return self::can_access_cpt($object_id, 'request', $action, $user_id);

            case 'create_project':
                return self::can_create_projects($user_id);

            case 'request_project':
                return self::can_request_projects($user_id);

            default:
                return false;
        }
    }

    // ========================================
    // CPT Specific Access Logic
    // ========================================

    /**
     * Generic CPT access checker.
     *
     * @param int      $post_id       The Post ID.
     * @param string   $post_type     The CPT slug ('project', 'proposal', 'request').
     * @param string   $action        The action ('view', 'edit', 'delete').
     * @param int      $user_id       The User ID.
     * @return bool
     */
    private static function can_access_cpt($post_id, $post_type, $action, $user_id) {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'arsol-pfw-' . $post_type) {
            return false;
        }

        switch ($action) {
            case 'view':
                return self::can_view_post($post, $user_id);
            case 'edit':
                return Capabilities_Handler::can_edit_project_proposal($user_id, $post_id);
            case 'delete':
                return Capabilities_Handler::can_delete_project_proposal($user_id, $post_id);
        }
        return false;
    }

    /**
     * Checks if a user can view a generic post.
     * Base check relies on ownership.
     *
     * @param \WP_Post $post      The post object.
     * @param int      $user_id   The User ID.
     * @return bool
     */
    private static function can_view_post(\WP_Post $post, $user_id) {
        // Owner can always view.
        if ((int) $post->post_author === (int) $user_id) {
            return true;
        }

        // For proposals, the assigned customer can also view.
        if ($post->post_type === 'arsol-pfw-proposal') {
            $customer_id = get_post_meta($post->ID, '_customer_id', true);
            if ((int) $customer_id === (int) $user_id) {
                return true;
            }
        }

        return false;
    }


    // ========================================
    // Action-Specific Access Logic (Integrates Global Settings)
    // ========================================

    /**
     * Checks if a user can create projects, considering global and user settings.
     *
     * @param int $user_id The User ID.
     * @return bool
     */
    public static function can_create_projects($user_id) {
        $global_permission = self::get_global_permission_setting();

        if ('none' === $global_permission) {
            return false;
        }

        if ('create' === $global_permission) {
            return Capabilities_Handler::can_create_projects($user_id);
        }

        if ('request' === $global_permission) {
            // Only managers can create if global setting is "request only".
            return Capabilities_Handler::can_manage_projects($user_id);
        }

        if ('user_specific' === $global_permission) {
            $user_permission = self::get_user_permission_setting($user_id);
            if ('create' === $user_permission) {
                return Capabilities_Handler::can_create_projects($user_id);
            }
            // If user-specific is 'request' or 'none', they can't create directly.
            return Capabilities_Handler::can_manage_projects($user_id);
        }

        // Fallback for misconfiguration.
        return false;
    }

    /**
     * Checks if a user can create project requests, considering global and user settings.
     *
     * @param int $user_id The User ID.
     * @return bool
     */
    public static function can_request_projects($user_id) {
        $global_permission = self::get_global_permission_setting();

        if ('none' === $global_permission) {
            return false;
        }

        if ('request' === $global_permission || 'create' === $global_permission) {
            return Capabilities_Handler::can_create_project_requests($user_id);
        }

        if ('user_specific' === $global_permission) {
            $user_permission = self::get_user_permission_setting($user_id);
            // If user can create, they can also request.
            if ('request' === $user_permission || 'create' === $user_permission) {
                return Capabilities_Handler::can_create_project_requests($user_id);
            }
            return Capabilities_Handler::can_manage_projects($user_id);
        }

        return false;
    }

    // ========================================
    // Settings Helper Methods
    // ========================================

    /**
     * Gets the global permission setting for project creation.
     *
     * @return string ('none', 'request', 'create', 'user_specific')
     */
    private static function get_global_permission_setting() {
        $settings = get_option('arsol_pfw_general_settings', []);
        return isset($settings['user_project_permissions']) ? $settings['user_project_permissions'] : 'request';
    }

    /**
     * Gets an individual user's specific permission override.
     *
     * @param int $user_id The User ID.
     * @return string ('none', 'request', 'create')
     */
    private static function get_user_permission_setting($user_id) {
        // Fallback to default if not set.
        $default = self::get_default_user_permission_setting();
        return get_user_meta($user_id, 'arsol_pfw_user_permission', true) ?: $default;
    }

    /**
     * Gets the default permission for newly registered users.
     *
     * @return string
     */
    private static function get_default_user_permission_setting() {
        $settings = get_option('arsol_pfw_general_settings', []);
        return isset($settings['default_user_permission']) ? $settings['default_user_permission'] : 'request';
    }
} 