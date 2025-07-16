<?php 

/**
 * Admin Capabilities Class - WordPress Native System
 *
 * Handles WordPress-native capabilities for Arsol Projects For Woo.
 * Uses proper CPT capability mapping with map_meta_cap = true.
 * Works with existing WordPress roles only - no custom roles.
 *
 * @package Arsol_Projects_For_Woo
 * @since 2.0.0
 */

namespace Arsol_Projects_For_Woo\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Capabilities {
    /**
     * Constructor
     */
    public function __construct() {
        // Hook into plugin activation/deactivation for clean capability management
        register_activation_hook(ARSOL_PROJECTS_PLUGIN_FILE, array($this, 'add_administrator_capabilities'));
        register_deactivation_hook(ARSOL_PROJECTS_PLUGIN_FILE, array($this, 'remove_all_capabilities'));
    }

    /**
     * Add WordPress-native capabilities to administrator role on plugin activation
     */
    public function add_administrator_capabilities() {
        $admin_role = get_role('administrator');
        if (!$admin_role) {
            return;
        }

        // Master capability for full plugin access
        $admin_role->add_cap('arsol_pfw_manage');

        // All project capabilities
        $project_caps = array(
            'edit_arsol_pfw_projects',
            'edit_others_arsol_pfw_projects',
            'publish_arsol_pfw_projects',
            'read_private_arsol_pfw_projects',
            'delete_arsol_pfw_projects',
            'delete_private_arsol_pfw_projects',
            'delete_published_arsol_pfw_projects',
            'delete_others_arsol_pfw_projects',
            'edit_private_arsol_pfw_projects',
            'edit_published_arsol_pfw_projects',
        );

        // All proposal capabilities
        $proposal_caps = array(
            'edit_arsol_pfw_proposals',
            'edit_others_arsol_pfw_proposals',
            'publish_arsol_pfw_proposals',
            'read_private_arsol_pfw_proposals',
            'delete_arsol_pfw_proposals',
            'delete_private_arsol_pfw_proposals',
            'delete_published_arsol_pfw_proposals',
            'delete_others_arsol_pfw_proposals',
            'edit_private_arsol_pfw_proposals',
            'edit_published_arsol_pfw_proposals',
        );

        // All request capabilities
        $request_caps = array(
            'edit_arsol_pfw_requests',
            'edit_others_arsol_pfw_requests',
            'publish_arsol_pfw_requests',
            'read_private_arsol_pfw_requests',
            'delete_arsol_pfw_requests',
            'delete_private_arsol_pfw_requests',
            'delete_published_arsol_pfw_requests',
            'delete_others_arsol_pfw_requests',
            'edit_private_arsol_pfw_requests',
            'edit_published_arsol_pfw_requests',
        );

        // Add all capabilities to administrator
        $all_caps = array_merge($project_caps, $proposal_caps, $request_caps);
        foreach ($all_caps as $cap) {
            $admin_role->add_cap($cap);
        }
    }

    /**
     * Remove all PFW capabilities from all roles on plugin deactivation
     */
    public function remove_all_capabilities() {
        // Get all roles
        $all_roles = wp_roles()->get_names();
        
        // Define all PFW capabilities to remove
        $capabilities_to_remove = array(
            'arsol_pfw_manage',
            // Project capabilities
            'edit_arsol_pfw_projects', 'edit_others_arsol_pfw_projects', 'publish_arsol_pfw_projects',
            'read_private_arsol_pfw_projects', 'delete_arsol_pfw_projects', 'delete_private_arsol_pfw_projects',
            'delete_published_arsol_pfw_projects', 'delete_others_arsol_pfw_projects',
            'edit_private_arsol_pfw_projects', 'edit_published_arsol_pfw_projects',
            // Proposal capabilities
            'edit_arsol_pfw_proposals', 'edit_others_arsol_pfw_proposals', 'publish_arsol_pfw_proposals',
            'read_private_arsol_pfw_proposals', 'delete_arsol_pfw_proposals', 'delete_private_arsol_pfw_proposals',
            'delete_published_arsol_pfw_proposals', 'delete_others_arsol_pfw_proposals',
            'edit_private_arsol_pfw_proposals', 'edit_published_arsol_pfw_proposals',
            // Request capabilities
            'edit_arsol_pfw_requests', 'edit_others_arsol_pfw_requests', 'publish_arsol_pfw_requests',
            'read_private_arsol_pfw_requests', 'delete_arsol_pfw_requests', 'delete_private_arsol_pfw_requests',
            'delete_published_arsol_pfw_requests', 'delete_others_arsol_pfw_requests',
            'edit_private_arsol_pfw_requests', 'edit_published_arsol_pfw_requests',
        );

        // Remove capabilities from all roles
        foreach ($all_roles as $role_slug => $role_name) {
            $role = get_role($role_slug);
            if ($role) {
                foreach ($capabilities_to_remove as $cap) {
                    $role->remove_cap($cap);
                }
            }
        }
    }

    // ========================================
    // HELPER METHODS - WordPress Native
    // ========================================

    /**
     * Check if user has broad PFW management access
     * 
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can manage PFW
     */
    public static function can_manage_projects($user_id = null) {
        if ($user_id) {
        $user = get_user_by('id', $user_id);
            return $user && ($user->has_cap('arsol_pfw_manage') || $user->has_cap('manage_options'));
        }
        return current_user_can('arsol_pfw_manage') || current_user_can('manage_options');
    }

    /**
     * Check if user can create projects
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can create projects
     */
    public static function can_create_projects($user_id = null) {
        if ($user_id) {
        $user = get_user_by('id', $user_id);
            return $user && ($user->has_cap('edit_arsol_pfw_projects') || $user->has_cap('arsol_pfw_manage'));
        }
        return current_user_can('edit_arsol_pfw_projects') || current_user_can('arsol_pfw_manage');
    }

    /**
     * Check if user can create project requests
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can create project requests
     */
    public static function can_create_project_requests($user_id = null) {
        if ($user_id) {
            $user = get_user_by('id', $user_id);
            return $user && ($user->has_cap('edit_arsol_pfw_requests') || $user->has_cap('arsol_pfw_manage'));
        }
        return current_user_can('edit_arsol_pfw_requests') || current_user_can('arsol_pfw_manage');
    }

    /**
     * Check if user can create project proposals
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can create project proposals
     */
    public static function can_create_project_proposals($user_id = null) {
        if ($user_id) {
            $user = get_user_by('id', $user_id);
            return $user && ($user->has_cap('edit_arsol_pfw_proposals') || $user->has_cap('arsol_pfw_manage'));
        }
        return current_user_can('edit_arsol_pfw_proposals') || current_user_can('arsol_pfw_manage');
    }

    /**
     * Check if user can edit a specific project (WordPress handles ownership)
     *
     * @param int $user_id User ID
     * @param int $project_id Project ID
     * @return bool Whether user can edit the project
     */
    public static function can_edit_project($user_id, $project_id) {
        // Use WordPress meta capability mapping - it handles ownership automatically
        if ($user_id) {
        $user = get_user_by('id', $user_id);
            return $user && ($user->has_cap('edit_arsol_pfw_project', $project_id) || $user->has_cap('arsol_pfw_manage'));
        }
        return current_user_can('edit_arsol_pfw_project', $project_id) || current_user_can('arsol_pfw_manage');
    }

    /**
     * Check if user can edit a specific project request (WordPress handles ownership)
     *
     * @param int $user_id User ID
     * @param int $request_id Request ID
     * @return bool Whether user can edit the project request
     */
    public static function can_edit_project_request($user_id, $request_id) {
        // Use WordPress meta capability mapping - it handles ownership automatically
        if ($user_id) {
        $user = get_user_by('id', $user_id);
            return $user && ($user->has_cap('edit_arsol_pfw_request', $request_id) || $user->has_cap('arsol_pfw_manage'));
        }
        return current_user_can('edit_arsol_pfw_request', $request_id) || current_user_can('arsol_pfw_manage');
    }

    /**
     * Check if user can edit a specific project proposal (WordPress handles ownership)
     *
     * @param int $user_id User ID
     * @param int $proposal_id Proposal ID
     * @return bool Whether user can edit the project proposal
     */
    public static function can_edit_project_proposal($user_id, $proposal_id) {
        // Use WordPress meta capability mapping - it handles ownership automatically
        if ($user_id) {
            $user = get_user_by('id', $user_id);
            return $user && ($user->has_cap('edit_arsol_pfw_proposal', $proposal_id) || $user->has_cap('arsol_pfw_manage'));
        }
        return current_user_can('edit_arsol_pfw_proposal', $proposal_id) || current_user_can('arsol_pfw_manage');
    }

    /**
     * Check if user can delete a specific project (WordPress handles ownership)
     * 
     * @param int $user_id User ID
     * @param int $project_id Project ID
     * @return bool Whether user can delete the project
     */
    public static function can_delete_project($user_id, $project_id) {
        if ($user_id) {
            $user = get_user_by('id', $user_id);
            return $user && ($user->has_cap('delete_arsol_pfw_project', $project_id) || $user->has_cap('arsol_pfw_manage'));
        }
        return current_user_can('delete_arsol_pfw_project', $project_id) || current_user_can('arsol_pfw_manage');
    }

    /**
     * Check if user can delete a specific request (WordPress handles ownership)
     * 
     * @param int $user_id User ID
     * @param int $request_id Request ID
     * @return bool Whether user can delete the request
     */
    public static function can_delete_project_request($user_id, $request_id) {
        if ($user_id) {
        $user = get_user_by('id', $user_id);
            return $user && ($user->has_cap('delete_arsol_pfw_request', $request_id) || $user->has_cap('arsol_pfw_manage'));
        }
        return current_user_can('delete_arsol_pfw_request', $request_id) || current_user_can('arsol_pfw_manage');
    }

    /**
     * Check if user can delete a specific proposal (WordPress handles ownership)
     * 
     * @param int $user_id User ID
     * @param int $proposal_id Proposal ID
     * @return bool Whether user can delete the proposal
     */
    public static function can_delete_project_proposal($user_id, $proposal_id) {
        if ($user_id) {
            $user = get_user_by('id', $user_id);
            return $user && ($user->has_cap('delete_arsol_pfw_proposal', $proposal_id) || $user->has_cap('arsol_pfw_manage'));
        }
        return current_user_can('delete_arsol_pfw_proposal', $proposal_id) || current_user_can('arsol_pfw_manage');
    }

    /**
     * Get user's highest PFW permission level
     * 
     * @param int $user_id User ID (optional, defaults to current user)
     * @return string Permission level: 'manager', 'creator', 'none'
     */
    public static function get_user_permission_level($user_id = null) {
        if (self::can_manage_projects($user_id)) {
            return 'manager';
        }
        
        if ($user_id) {
            $user = get_user_by('id', $user_id);
            if ($user && ($user->has_cap('edit_arsol_pfw_projects') || 
                         $user->has_cap('edit_arsol_pfw_proposals') || 
                         $user->has_cap('edit_arsol_pfw_requests'))) {
                return 'creator';
            }
        } else {
            if (current_user_can('edit_arsol_pfw_projects') || 
                current_user_can('edit_arsol_pfw_proposals') || 
                current_user_can('edit_arsol_pfw_requests')) {
                return 'creator';
            }
        }
        
        return 'none';
    }
} 