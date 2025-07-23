<?php
/**
 * Capabilities Handler Class
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

/**
 * Capabilities Handler Class
 * 
 * Provides WordPress-native capability management for the plugin.
 * This class handles capability assignment, removal, and checking.
 */
class Capabilities_Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Hook into plugin activation/deactivation for clean capability management
        register_activation_hook(ARSOL_PFW_PLUGIN_FILE, array($this, 'add_administrator_capabilities'));
        register_deactivation_hook(ARSOL_PFW_PLUGIN_FILE, array($this, 'remove_all_capabilities'));
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
    // CAPABILITY CHECKING METHODS
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
     * Get user permission level
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return string Permission level: 'manager', 'creator', or 'none'
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

    // ========================================
    // MANAGER OVERRIDE METHODS
    // ========================================

    /**
     * Get user's manager override settings
     *
     * @param int $user_id User ID
     * @return array Array of override capabilities with their states
     */
    public static function get_user_manager_overrides($user_id) {
        $capabilities = array('manage_stages', 'manage_workflows', 'manage_settings', 'manage_permissions');
        $overrides = array();
        
        foreach ($capabilities as $cap) {
            $override_value = get_user_meta($user_id, 'arsol_pfw_manager_override_' . $cap, true);
            if ($override_value === '1' || $override_value === '0') {
                $overrides[$cap] = $override_value === '1';
            }
        }
        
        return $overrides;
    }

    /**
     * Check if user has override for a specific capability
     *
     * @param int $user_id User ID
     * @param string $capability Capability to check
     * @return bool Whether user has override for this capability
     */
    public static function has_manager_override_capability($user_id, $capability) {
        $override_value = get_user_meta($user_id, 'arsol_pfw_manager_override_' . $capability, true);
        return $override_value === '1';
    }

    /**
     * Get effective manager capability considering overrides
     *
     * @param int $user_id User ID
     * @param string $capability Capability to check
     * @return bool Whether user has effective capability
     */
    public static function get_effective_manager_capability($user_id, $capability) {
        // ✅ STEP 1: Check if user has manager capabilities first
        if (!self::can_manage_projects($user_id)) {
            return false;
        }

        // ✅ STEP 2: Check if user has the specific WordPress capability
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }

        // Map capability names to WordPress capabilities
        $capability_mappings = array(
            'manage_stages' => 'manage_arsol_pfw_stages',
            'manage_workflows' => 'manage_arsol_pfw_workflows', 
            'manage_settings' => 'manage_arsol_pfw_settings',
            'manage_permissions' => 'manage_arsol_pfw_permissions'
        );

        $wp_capability = isset($capability_mappings[$capability]) ? $capability_mappings[$capability] : $capability;
        
        // ✅ CAPABILITIES FIRST: Check if user has the WordPress capability
        if (!$user->has_cap($wp_capability)) {
            return false; // User doesn't have the capability - no override checks happen
        }

        // ✅ STEP 3: User has capability - check if overrides are enabled globally
        $settings = get_option('arsol_pfw_permissions_settings', array());
        $allow_overrides = isset($settings['allow_manager_overrides']) ? $settings['allow_manager_overrides'] : false;
        
        if (!$allow_overrides) {
            // No overrides allowed - follow the capability
            return true;
        }

        // ✅ STEP 4: Overrides are enabled - check user override state
        $user_override = get_user_meta($user_id, 'arsol_pfw_manager_override_' . $capability, true);
        
        if ($user_override === '1') {
            return true; // Explicitly enabled
        } elseif ($user_override === '0') {
            return false; // Explicitly disabled
        }
        
        // ✅ NO OVERRIDE EXISTS - follow the capability
        return true;
    }

    /**
     * Check if user can manage stages (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can manage stages
     */
    public static function can_manage_stages($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        return self::get_effective_manager_capability($user_id, 'manage_stages');
    }

    /**
     * Check if user can manage workflows (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can manage workflows
     */
    public static function can_manage_workflows($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        return self::get_effective_manager_capability($user_id, 'manage_workflows');
    }

    /**
     * Check if user can manage settings (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can manage settings
     */
    public static function can_manage_settings($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        return self::get_effective_manager_capability($user_id, 'manage_settings');
    }

    /**
     * Check if user can manage permissions (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can manage permissions
     */
    public static function can_manage_permissions($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        return self::get_effective_manager_capability($user_id, 'manage_permissions');
    }

} 