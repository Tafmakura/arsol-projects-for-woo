<?php
/**
 * Capabilities Handler Class - ARSOL PFW
 *
 * This class is the central authority for managing all user permissions in the plugin.
 * It registers the custom capabilities, handles their assignment to roles,
 * and contains the crucial meta capability mapping for custom ownership logic.
 *
 * @package Arsol_Projects_For_Woo
 * @since 2.0.0
 */

namespace Arsol_Projects_For_Woo\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manages the registration and logic for all custom capabilities.
 */
class Capabilities_Handler {

    /**
     * The single instance of the class.
     * @var Capabilities_Handler
     */
    protected static $_instance = null;

    /**
     * Ensures only one instance of the class is loaded.
     * @return Capabilities_Handler
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        add_filter('map_meta_cap', array($this, 'map_meta_caps'), 10, 4);
    }

    /**
     * Get all custom capabilities defined by the plugin.
     * @return array
     */
    public static function get_all_capabilities() {
        $capabilities = array(
            // Hierarchical Admin Capabilities
            'arsol_pfw_manage',
            'arsol_pfw_manage_all',
            'arsol_pfw_manage_assigned',
            'arsol_pfw_manage_own',

            // Granular Admin Capabilities
            'arsol_pfw_admin_manage_settings',
            'arsol_pfw_admin_manage_stages',
            'arsol_pfw_admin_manage_workflows',

            // Frontend Capabilities
            'arsol_pfw_frontend_create_own_projects',
            'arsol_pfw_frontend_view_own_projects',
            'arsol_pfw_frontend_edit_own_projects',
            'arsol_pfw_frontend_create_own_requests',
            'arsol_pfw_frontend_view_own_requests',
            'arsol_pfw_frontend_edit_own_requests',
        );

        // Add CPT-specific capabilities
        $cpt_slugs = array('arsol_pfw_project', 'arsol_pfw_request', 'arsol_pfw_proposal');
        foreach ($cpt_slugs as $cpt_slug) {
            $capabilities = array_merge($capabilities, self::get_cpt_capabilities($cpt_slug));
        }

        return $capabilities;
    }

    /**
     * Get the primitive capabilities for a custom post type.
     * @param string $cpt_slug The CPT slug.
     * @return array
     */
    private static function get_cpt_capabilities($cpt_slug) {
        return array(
            "edit_{$cpt_slug}",
            "read_{$cpt_slug}",
            "delete_{$cpt_slug}",
            "edit_{$cpt_slug}s",
            "edit_others_{$cpt_slug}s",
            "publish_{$cpt_slug}s",
            "read_private_{$cpt_slug}s",
            "delete_{$cpt_slug}s",
            "delete_private_{$cpt_slug}s",
            "delete_published_{$cpt_slug}s",
            "delete_others_{$cpt_slug}s",
        );
    }

    /**
     * Add capabilities to roles on plugin activation.
     */
    public static function add_capabilities() {
        // `arsol_pfw_manage` for Administrators
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('arsol_pfw_manage');
        }

        // `arsol_pfw_manage_all` for Shop Managers
        $shop_manager_role = get_role('shop_manager');
        if ($shop_manager_role) {
            $shop_manager_role->add_cap('arsol_pfw_manage_all');
        }

        // Note: Other capabilities are assigned via the plugin's settings page,
        // so we do not assign them here on activation.
    }

    /**
     * Remove all custom capabilities from all roles on deactivation.
     */
    public static function remove_capabilities() {
        $all_caps = self::get_all_capabilities();
        $roles = get_editable_roles();
        foreach (array_keys($roles) as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($all_caps as $cap) {
                    $role->remove_cap($cap);
                }
            }
        }
    }

    /**
     * The core of our custom permission logic. This function intercepts
     * WordPress's capability checks and applies our hierarchical and
     * custom ownership rules.
     *
     * @param array  $caps    Required capabilities.
     * @param string $cap     The capability being checked.
     * @param int    $user_id The user ID.
     * @param array  $args    Additional arguments, including post ID.
     * @return array
     */
    public function map_meta_caps($caps, $cap, $user_id, $args) {
        $cpt_slugs = array('arsol_pfw_project', 'arsol_pfw_request', 'arsol_pfw_proposal');
        $primitive_caps_map = array(
            "edit_{$cpt_slug}"   => "edit_posts",
            "delete_{$cpt_slug}" => "delete_posts",
            "read_{$cpt_slug}"   => "read",
        );

        // Check if the capability being checked is one of our CPT meta caps
        if (isset($primitive_caps_map[$cap])) {
            $post_id = !empty($args[0]) ? $args[0] : 0;
            if (!$post_id) {
                return $caps; // No post ID, so we can't check ownership.
            }

            // Super Admins and Managers can do anything. Grant access immediately.
            if (user_can($user_id, 'arsol_pfw_manage') || user_can($user_id, 'arsol_pfw_manage_all')) {
                return array($primitive_caps_map[$cap]); // Grant access.
            }

            // Team Leads: Check if they are the assigned project lead.
            if (user_can($user_id, 'arsol_pfw_manage_assigned')) {
                $project_lead_id = get_post_meta($post_id, '_arsol_pfw_project_lead', true);
                if ((int) $project_lead_id === (int) $user_id) {
                    return array($primitive_caps_map[$cap]); // Grant access.
                }
            }

            // Customers: Check if they are the customer for the item.
            if (user_can($user_id, 'arsol_pfw_manage_own')) {
                $customer_id = get_post_meta($post_id, '_arsol_pfw_customer_id', true);
                if ((int) $customer_id === (int) $user_id) {
                    return array($primitive_caps_map[$cap]); // Grant access.
                }
            }
            
            // If we've reached this point, no hierarchical role has granted access.
            // We return the original $caps, which will cause the check to fail, denying access.
            return $caps;
        }

        return $caps;
    }
}

/**
 * Global helper function to check capabilities.
 *
 * @param string $capability The capability to check.
 * @param int    $post_id    Optional. The post ID to check against.
 * @return bool
 */
function arsol_pfw_user_can($capability, $post_id = 0) {
    return current_user_can($capability, $post_id);
} 