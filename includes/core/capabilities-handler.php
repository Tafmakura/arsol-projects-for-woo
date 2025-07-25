<?php
/**
 * Capabilities Handler Class
 *
 * Handles WordPress-native capabilities for Arsol Projects For Woo.
 * Uses native WordPress capability functions where possible.
 * Only adds custom logic for override functionality.
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
 * Uses native WordPress functions where possible, only adds custom logic for overrides.
 */
class Capabilities_Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Hook into plugin activation/deactivation for clean capability management
        register_activation_hook(ARSOL_PFW_PLUGIN_FILE, array($this, 'add_administrator_capabilities'));
        register_deactivation_hook(ARSOL_PFW_PLUGIN_FILE, array(__CLASS__, 'remove_all_capabilities'));
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
    public static function remove_all_capabilities() {
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
    // CUSTOM LOGIC METHODS (Override System)
    // ========================================

    /**
     * Get user's manager override settings
     *
     * @param int $user_id User ID
     * @return array Array of override capabilities with their states
     */
    public static function get_user_manager_overrides($user_id) {
        $capabilities = array(
            // Admin capabilities
            'manage_stages', 'manage_workflows', 'manage_settings', 'manage_permissions',
            
            // Project CRUD capabilities
            'view_all_projects', 'edit_all_projects', 'delete_all_projects', 'create_projects',
            
            // Request CRUD capabilities
            'view_all_requests', 'edit_all_requests', 'delete_all_requests', 'create_requests',
            
            // Proposal CRUD capabilities
            'view_all_proposals', 'edit_all_proposals', 'delete_all_proposals', 'create_proposals'
        );
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
     * Check if user has an override for a specific capability
     *
     * @param int $user_id User ID
     * @param string $capability Capability to check
     * @return bool Whether user has an override set for this capability
     */
    public static function has_manager_capability_override($user_id, $capability) {
        $override_value = get_user_meta($user_id, 'arsol_pfw_manager_override_' . $capability, true);
        return $override_value === '1' || $override_value === '0'; // Has override if value is set
    }

    /**
     * Get user's override value for a specific capability
     *
     * @param int $user_id User ID
     * @param string $capability Capability to check
     * @return string|null Override value ('1' for enabled, '0' for disabled, null if no override)
     */
    public static function get_manager_capability_override($user_id, $capability) {
        $override_value = get_user_meta($user_id, 'arsol_pfw_manager_override_' . $capability, true);
        return ($override_value === '1' || $override_value === '0') ? $override_value : null;
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
        if (!current_user_can('arsol_pfw_manage') && !current_user_can('manage_options')) {
            return false;
        }

        // ✅ STEP 2: Check if user has the specific WordPress capability
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }

        // Map capability names to WordPress capabilities
        $capability_mappings = array(
            // Admin capabilities
            'manage_stages' => 'arsol_pfw_manage_stages',
            'manage_workflows' => 'arsol_pfw_manage_workflows', 
            'manage_settings' => 'arsol_pfw_manage_settings',
            'manage_permissions' => 'arsol_pfw_manage_permissions',
            
            // Project CRUD capabilities
            'view_all_projects' => 'read_private_arsol_pfw_projects',
            'edit_all_projects' => 'edit_others_arsol_pfw_projects',
            'delete_all_projects' => 'delete_others_arsol_pfw_projects',
            'create_projects' => 'edit_arsol_pfw_projects',
            
            // Request CRUD capabilities
            'view_all_requests' => 'read_private_arsol_pfw_requests',
            'edit_all_requests' => 'edit_others_arsol_pfw_requests',
            'delete_all_requests' => 'delete_others_arsol_pfw_requests',
            'create_requests' => 'edit_arsol_pfw_requests',
            
            // Proposal CRUD capabilities
            'view_all_proposals' => 'read_private_arsol_pfw_proposals',
            'edit_all_proposals' => 'edit_others_arsol_pfw_proposals',
            'delete_all_proposals' => 'delete_others_arsol_pfw_proposals',
            'create_proposals' => 'edit_arsol_pfw_proposals'
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
        $user_override = self::get_manager_capability_override($user_id, $capability);
        
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

    /**
     * Check if user can manage assigned projects (checks if user is assigned project lead)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @param int $project_id Project ID to check (optional)
     * @return bool Whether user can manage assigned projects
     */
    public static function can_manage_assigned_projects($user_id = null, $project_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // If project_id provided, check if user is the assigned project lead for this specific project
        if ($project_id) {
            $project = get_post($project_id);
            if (!$project || $project->post_type !== 'arsol-pfw-project') {
                return false;
            }
            
            // Check if user is the assigned project lead
            $assigned_manager = get_post_meta($project_id, '_arsol_pfw_project_manager', true);
            return (int) $assigned_manager === (int) $user_id;
        }
        
        // If no project_id, check if user has capability to manage assigned projects
        return user_can($user_id, 'edit_arsol_pfw_projects');
    }

    /**
     * Check if user can manage all requests (uses native WordPress capabilities)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can manage all requests
     */
    public static function can_manage_all_requests($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, 'edit_others_arsol_pfw_requests');
    }

    /**
     * Check if user can manage all proposals (uses native WordPress capabilities)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can manage all proposals
     */
    public static function can_manage_all_proposals($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, 'edit_others_arsol_pfw_proposals');
    }

    /**
     * Check if user can manage all projects (uses native WordPress capabilities)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can manage all projects
     */
    public static function can_manage_all_projects($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, 'edit_others_arsol_pfw_projects');
    }

    /**
     * Get user permission level
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return string Permission level: 'manager', 'creator', or 'none'
     */
    public static function get_user_permission_level($user_id = null) {
        if (current_user_can('arsol_pfw_manage') || current_user_can('manage_options')) {
            return 'manager';
        }
        
        if (current_user_can('edit_arsol_pfw_projects') || 
            current_user_can('edit_arsol_pfw_proposals') || 
            current_user_can('edit_arsol_pfw_requests')) {
            return 'creator';
        }
        
        return 'none';
    }

    /**
     * Setup capabilities (called on plugin activation)
     */
    public static function setup_capabilities() {
        $instance = new self();
        $instance->add_administrator_capabilities();
    }

    // ========================================
    // COMPREHENSIVE CAPABILITY METHODS WITH OVERRIDE SUPPORT
    // ========================================

    /**
     * Check if user can create projects (default enabled, can be disabled by override)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can create projects
     */
    public static function can_create_projects($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Admins always can create
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Check WordPress capability first
        $user = get_user_by('id', $user_id);
        if (!$user || !$user->has_cap('edit_arsol_pfw_projects')) {
            return false;
        }

        // Project managers: check if creation is disabled by override
        if (self::is_manager($user_id)) {
            $override_value = self::get_manager_capability_override($user_id, 'create_projects');
            return $override_value !== '0'; // Return true unless explicitly disabled
        }
        
        // Customers: check frontend permissions (default enabled, can be disabled)
        $settings = get_option('arsol_pfw_permissions_settings', array());
        $global_setting = isset($settings['user_project_permissions']) ? $settings['user_project_permissions'] : 'create';
        
        if ($global_setting === 'none') {
            return false;
        }
        
        if ($global_setting === 'user_specific') {
            $user_permission = get_user_meta($user_id, 'arsol_pfw_user_permission', true);
            return $user_permission !== 'none';
        }
        
        // Default: can create (for 'create', 'request', etc.)
        return true;
    }

    /**
     * Check if user can create requests (default enabled, can be disabled by override)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can create requests
     */
    public static function can_create_requests($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Admins always can create
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Check WordPress capability first
        $user = get_user_by('id', $user_id);
        if (!$user || !$user->has_cap('edit_arsol_pfw_requests')) {
            return false;
        }

        // Project managers: check if creation is disabled by override
        if (self::is_manager($user_id)) {
            $override_value = self::get_manager_capability_override($user_id, 'create_requests');
            return $override_value !== '0'; // Return true unless explicitly disabled
        }
        
        // Customers: check frontend permissions (default enabled, can be disabled)
        $settings = get_option('arsol_pfw_permissions_settings', array());
        $global_setting = isset($settings['user_project_permissions']) ? $settings['user_project_permissions'] : 'create';
        
        if ($global_setting === 'none') {
            return false;
        }
        
        if ($global_setting === 'user_specific') {
            $user_permission = get_user_meta($user_id, 'arsol_pfw_user_permission', true);
            return $user_permission !== 'none';
        }
        
        // Default: can create (for 'create', 'request', etc.)
        return true;
    }

    /**
     * Check if user can create proposals (default enabled, can be disabled by override)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can create proposals
     */
    public static function can_create_proposals($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Admins always can create
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Check WordPress capability first
        $user = get_user_by('id', $user_id);
        if (!$user || !$user->has_cap('edit_arsol_pfw_proposals')) {
            return false;
        }

        // Project managers: check if creation is disabled by override
        if (self::is_manager($user_id)) {
            $override_value = self::get_manager_capability_override($user_id, 'create_proposals');
            return $override_value !== '0'; // Return true unless explicitly disabled
        }
        
        // Customers: check frontend permissions (default enabled, can be disabled)
        $settings = get_option('arsol_pfw_permissions_settings', array());
        $global_setting = isset($settings['user_project_permissions']) ? $settings['user_project_permissions'] : 'create';
        
        if ($global_setting === 'none') {
            return false;
        }
        
        if ($global_setting === 'user_specific') {
            $user_permission = get_user_meta($user_id, 'arsol_pfw_user_permission', true);
            return $user_permission !== 'none';
        }
        
        // Default: can create (for 'create', 'request', etc.)
        return true;
    }

    /**
     * Check if user can edit own projects (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @param int $project_id Project ID to check ownership
     * @return bool Whether user can edit own projects
     */
    public static function can_edit_own_projects($user_id = null, $project_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Check WordPress capability first
        $user = get_user_by('id', $user_id);
        if (!$user || !$user->has_cap('edit_arsol_pfw_projects')) {
            return false;
        }

        // If project_id provided, check ownership (post author or customer)
        if ($project_id) {
            $project = get_post($project_id);
            if (!$project || $project->post_type !== 'arsol_pfw_project') {
                return false;
            }
            
            // Check if user is post author (creator)
            if ($project->post_author == $user_id) {
                return true;
            }
            
            // Check if user is assigned customer
            $customer_id = get_post_meta($project_id, '_arsol_pfw_customer_id', true);
            if ((int) $customer_id === (int) $user_id) {
                return true;
            }
            
            // Check if user is assigned as project manager
            $assigned_manager = get_post_meta($project_id, '_arsol_pfw_project_manager', true);
            if ($assigned_manager && (int) $assigned_manager === (int) $user_id) {
                return true;
            }
            
            return false;
        }
        
        return true;
    }

    /**
     * Check if user can edit own requests (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @param int $request_id Request ID to check ownership
     * @return bool Whether user can edit own requests
     */
    public static function can_edit_own_requests($user_id = null, $request_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Check WordPress capability first
        $user = get_user_by('id', $user_id);
        if (!$user || !$user->has_cap('edit_arsol_pfw_requests')) {
            return false;
        }

        // If request_id provided, check ownership (post author or customer)
        if ($request_id) {
            $request = get_post($request_id);
            if (!$request || $request->post_type !== 'arsol_pfw_request') {
                return false;
            }
            
            // Check if user is post author (creator)
            if ($request->post_author == $user_id) {
                return true;
            }
            
            // Check if user is assigned customer
            $customer_id = get_post_meta($request_id, '_arsol_pfw_customer_id', true);
            if ((int) $customer_id === (int) $user_id) {
                return true;
            }
            
            return false;
        }
        
        return true;
    }

    /**
     * Check if user can edit own proposals (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @param int $proposal_id Proposal ID to check ownership
     * @return bool Whether user can edit own proposals
     */
    public static function can_edit_own_proposals($user_id = null, $proposal_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Check WordPress capability first
        $user = get_user_by('id', $user_id);
        if (!$user || !$user->has_cap('edit_arsol_pfw_proposals')) {
            return false;
        }

        // If proposal_id provided, check ownership (post author or customer)
        if ($proposal_id) {
            $proposal = get_post($proposal_id);
            if (!$proposal || $proposal->post_type !== 'arsol_pfw_proposal') {
                return false;
            }
            
            // Check if user is post author (creator)
            if ($proposal->post_author == $user_id) {
                return true;
            }
            
            // Check if user is assigned customer
            $customer_id = get_post_meta($proposal_id, '_arsol_pfw_customer_id', true);
            if ((int) $customer_id === (int) $user_id) {
                return true;
            }
            
            return false;
        }
        
        return true;
    }

    /**
     * Check if user can edit others' projects (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can edit others' projects
     */
    public static function can_edit_others_projects($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Check if user is admin
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Check if user has manager capabilities
        if (self::is_manager($user_id)) {
            return self::get_effective_manager_capability($user_id, 'edit_all_projects');
        }
        
        // Check WordPress capability
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        return $user->has_cap('edit_others_arsol_pfw_projects');
    }

    /**
     * Check if user can edit others' requests (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can edit others' requests
     */
    public static function can_edit_others_requests($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Check if user is admin
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Check if user has manager capabilities
        if (self::is_manager($user_id)) {
            return self::get_effective_manager_capability($user_id, 'edit_all_requests');
        }
        
        // Check WordPress capability
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        return $user->has_cap('edit_others_arsol_pfw_requests');
    }

    /**
     * Check if user can edit others' proposals (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can edit others' proposals
     */
    public static function can_edit_others_proposals($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Check if user is admin
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Check if user has manager capabilities
        if (self::is_manager($user_id)) {
            return self::get_effective_manager_capability($user_id, 'edit_all_proposals');
        }
        
        // Check WordPress capability
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        return $user->has_cap('edit_others_arsol_pfw_proposals');
    }

    /**
     * Check if user can delete projects (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @param int $project_id Project ID to check ownership
     * @return bool Whether user can delete projects
     */
    public static function can_delete_projects($user_id = null, $project_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Check if user is admin
        if (current_user_can('manage_options')) {
            return true;
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        // Check if user can delete own projects
        if (!$user->has_cap('delete_arsol_pfw_projects')) {
            return false;
        }
        
        // If project_id provided, check if user can delete this specific project
        if ($project_id) {
            $project = get_post($project_id);
            if (!$project || $project->post_type !== 'arsol_pfw_project') {
                return false;
            }
            
            // If it's their own project (post author), they can delete it
            if ($project->post_author == $user_id) {
                return true;
            }
            
            // If it's their assigned project (customer), they can delete it
            $customer_id = get_post_meta($project_id, '_arsol_pfw_customer_id', true);
            if ((int) $customer_id === (int) $user_id) {
                return true;
            }
            
            // If it's their assigned project (project manager), they can delete it
            $assigned_manager = get_post_meta($project_id, '_arsol_pfw_project_manager', true);
            if ($assigned_manager && (int) $assigned_manager === (int) $user_id) {
                return true;
            }
            
            // If it's someone else's project, check if they can delete others with override
            if (self::is_manager($user_id)) {
                return self::get_effective_manager_capability($user_id, 'delete_all_projects');
            }
            
            return $user->has_cap('delete_others_arsol_pfw_projects');
        }
        
        return true;
    }

    /**
     * Check if user can delete requests (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @param int $request_id Request ID to check ownership
     * @return bool Whether user can delete requests
     */
    public static function can_delete_requests($user_id = null, $request_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Check if user is admin
        if (current_user_can('manage_options')) {
            return true;
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        // Check if user can delete own requests
        if (!$user->has_cap('delete_arsol_pfw_requests')) {
            return false;
        }
        
        // If request_id provided, check if user can delete this specific request
        if ($request_id) {
            $request = get_post($request_id);
            if (!$request || $request->post_type !== 'arsol_pfw_request') {
                return false;
            }
            
            // If it's their own request (post author), they can delete it
            if ($request->post_author == $user_id) {
                return true;
            }
            
            // If it's their assigned request (customer), they can delete it
            $customer_id = get_post_meta($request_id, '_arsol_pfw_customer_id', true);
            if ((int) $customer_id === (int) $user_id) {
                return true;
            }
            
            // If it's someone else's request, check if they can delete others with override
            if (self::is_manager($user_id)) {
                return self::get_effective_manager_capability($user_id, 'delete_all_requests');
            }
            
            return $user->has_cap('delete_others_arsol_pfw_requests');
        }
        
        return true;
    }

    /**
     * Check if user can delete proposals (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @param int $proposal_id Proposal ID to check ownership
     * @return bool Whether user can delete proposals
     */
    public static function can_delete_proposals($user_id = null, $proposal_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Check if user is admin
        if (current_user_can('manage_options')) {
            return true;
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        // Check if user can delete own proposals
        if (!$user->has_cap('delete_arsol_pfw_proposals')) {
            return false;
        }
        
        // If proposal_id provided, check if user can delete this specific proposal
        if ($proposal_id) {
            $proposal = get_post($proposal_id);
            if (!$proposal || $proposal->post_type !== 'arsol_pfw_proposal') {
                return false;
            }
            
            // If it's their own proposal (post author), they can delete it
            if ($proposal->post_author == $user_id) {
                return true;
            }
            
            // If it's their assigned proposal (customer), they can delete it
            $customer_id = get_post_meta($proposal_id, '_arsol_pfw_customer_id', true);
            if ((int) $customer_id === (int) $user_id) {
                return true;
            }
            
            // If it's someone else's proposal, check if they can delete others with override
            if (self::is_manager($user_id)) {
                return self::get_effective_manager_capability($user_id, 'delete_all_proposals');
            }
            
            return $user->has_cap('delete_others_arsol_pfw_proposals');
        }
        
        return true;
    }

    /**
     * Check if user can publish projects (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can publish projects
     */
    public static function can_publish_projects($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Check if user is admin
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Check if user has manager capabilities
        if (self::is_manager($user_id)) {
            return self::get_effective_manager_capability($user_id, 'create_projects');
        }
        
        // Check WordPress capability
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        return $user->has_cap('publish_arsol_pfw_projects');
    }

    /**
     * Check if user can publish requests (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can publish requests
     */
    public static function can_publish_requests($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Check if user is admin
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Check if user has manager capabilities
        if (self::is_manager($user_id)) {
            return self::get_effective_manager_capability($user_id, 'create_requests');
        }
        
        // Check WordPress capability
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        return $user->has_cap('publish_arsol_pfw_requests');
    }

    /**
     * Check if user can publish proposals (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can publish proposals
     */
    public static function can_publish_proposals($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Check if user is admin
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Check if user has manager capabilities
        if (self::is_manager($user_id)) {
            return self::get_effective_manager_capability($user_id, 'create_proposals');
        }
        
        // Check WordPress capability
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        return $user->has_cap('publish_arsol_pfw_proposals');
    }

    /**
     * Check if user can read private projects (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can read private projects
     */
    public static function can_read_private_projects($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Check if user is admin
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Check if user has manager capabilities
        if (self::is_manager($user_id)) {
            return self::get_effective_manager_capability($user_id, 'view_all_projects');
        }
        
        // Check WordPress capability
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        return $user->has_cap('read_private_arsol_pfw_projects');
    }

    /**
     * Check if user can read private requests (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can read private requests
     */
    public static function can_read_private_requests($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Check if user is admin
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Check if user has manager capabilities
        if (self::is_manager($user_id)) {
            return self::get_effective_manager_capability($user_id, 'view_all_requests');
        }
        
        // Check WordPress capability
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        return $user->has_cap('read_private_arsol_pfw_requests');
    }

    /**
     * Check if user can read private proposals (with override support)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can read private proposals
     */
    public static function can_read_private_proposals($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Check if user is admin
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Check if user has manager capabilities
        if (self::is_manager($user_id)) {
            return self::get_effective_manager_capability($user_id, 'view_all_proposals');
        }
        
        // Check WordPress capability
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        return $user->has_cap('read_private_arsol_pfw_proposals');
    }

    /**
     * Check if user has full plugin access (master capability)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user has full plugin access
     */
    public static function has_full_access($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        return $user->has_cap('arsol_pfw_manage') || $user->has_cap('manage_options');
    }

    /**
     * Check if user is a manager
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user is a manager
     */
    public static function is_manager($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return self::has_full_access($user_id);
    }

    /**
     * Check if user is a project customer (has customer role or is assigned as customer)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @param int $project_id Project ID to check (optional)
     * @return bool Whether user is a project customer
     */
    public static function is_project_customer($user_id = null, $project_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        // Check if user has customer role
        if (!in_array('customer', $user->roles)) {
            return false;
        }
        
        // If project_id provided, check if user is the assigned customer for this specific project
        if ($project_id) {
            $project = get_post($project_id);
            if (!$project || $project->post_type !== 'arsol-pfw-project') {
                return false;
            }
            
            // Check if user is the assigned customer
            $assigned_customer = get_post_meta($project_id, '_arsol_pfw_customer_id', true);
            return (int) $assigned_customer === (int) $user_id;
        }
        
        // If no project_id, just check if user has customer role
        return true;
    }

    /**
     * Check if user is a project lead (assigned to any project)
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @param int $project_id Project ID to check (optional)
     * @return bool Whether user is a project lead
     */
    public static function is_project_manager($user_id = null, $project_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$project_id) {
            return false;
        }
        
        // Check if user is assigned as project manager
        $assigned_manager = get_post_meta($project_id, '_arsol_pfw_project_manager', true);
        if ($assigned_manager && (int) $assigned_manager === (int) $user_id) {
            return true;
        }
        
        return false;
    }



    // ========================================
    // WORDPRESS CAPABILITY ASSIGNMENT METHODS
    // ========================================

    /**
     * Update project manager roles with new "manage all" logic
     *
     * @param array $role_slugs Array of role slugs
     */
    public static function update_project_manager_roles($role_slugs) {
        $settings = get_option('arsol_pfw_permissions_settings', array());
        $admin_capabilities = isset($settings['project_manager_capabilities']) ? $settings['project_manager_capabilities'] : array();
        
        // First, remove all PFW capabilities from all roles
        self::remove_all_capabilities();

        // Then assign capabilities based on admin settings
        foreach ($role_slugs as $role_slug) {
            $role = get_role($role_slug);
            if (!$role) {
                continue;
            }

            // Always assign master capability
            $role->add_cap('arsol_pfw_manage');
            
            // Assign capabilities based on admin "manage all" settings
            if (in_array('manage_all_projects', $admin_capabilities)) {
                // Assign all project CRUD capabilities
                $project_caps = array(
                    'read_private_arsol_pfw_projects',      // view_all_projects
                    'edit_others_arsol_pfw_projects',       // edit_all_projects
                    'delete_others_arsol_pfw_projects',     // delete_all_projects
                    'edit_arsol_pfw_projects',              // create_projects
                    'publish_arsol_pfw_projects',           // create_projects
                );
                foreach ($project_caps as $cap) {
                    $role->add_cap($cap);
                }
            }
            
            if (in_array('manage_all_requests', $admin_capabilities)) {
                // Assign all request CRUD capabilities
                $request_caps = array(
                    'read_private_arsol_pfw_requests',      // view_all_requests
                    'edit_others_arsol_pfw_requests',       // edit_all_requests
                    'delete_others_arsol_pfw_requests',     // delete_all_requests
                    'edit_arsol_pfw_requests',              // create_requests
                    'publish_arsol_pfw_requests',           // create_requests
                );
                foreach ($request_caps as $cap) {
                    $role->add_cap($cap);
                }
            }
            
            if (in_array('manage_all_proposals', $admin_capabilities)) {
                // Assign all proposal CRUD capabilities
                $proposal_caps = array(
                    'read_private_arsol_pfw_proposals',     // view_all_proposals
                    'edit_others_arsol_pfw_proposals',      // edit_all_proposals
                    'delete_others_arsol_pfw_proposals',    // delete_all_proposals
                    'edit_arsol_pfw_proposals',             // create_proposals
                    'publish_arsol_pfw_proposals',          // create_proposals
                );
                foreach ($proposal_caps as $cap) {
                    $role->add_cap($cap);
                }
            }
            
            // Assign management capabilities
            if (in_array('manage_stages', $admin_capabilities)) {
                $role->add_cap('arsol_pfw_manage_stages');
            }
            
            if (in_array('manage_workflows', $admin_capabilities)) {
                $role->add_cap('arsol_pfw_manage_workflows');
            }
            
            if (in_array('manage_settings', $admin_capabilities)) {
                $role->add_cap('arsol_pfw_manage_settings');
            }
            
            if (in_array('manage_permissions', $admin_capabilities)) {
                $role->add_cap('arsol_pfw_manage_permissions');
            }
        }
    }

    /**
     * Update frontend permissions for customer role
     *
     * @param string $permission_level The permission level ('none', 'request', 'create', 'user_specific')
     */
    public static function update_frontend_permissions($permission_level) {
        $customer_role = get_role('customer');
        if (!$customer_role) {
            return;
        }

        // Remove all frontend capabilities first
        $frontend_caps = array(
            'edit_arsol_pfw_projects',
            'edit_arsol_pfw_requests',
            'publish_arsol_pfw_projects',
            'publish_arsol_pfw_requests',
        );
        
        foreach ($frontend_caps as $cap) {
            $customer_role->remove_cap($cap);
        }
        
        // Grant capabilities based on selected permission level
        switch ($permission_level) {
            case 'none':
                // No capabilities granted - creation disabled
                break;
                
            case 'request':
                $customer_role->add_cap('edit_arsol_pfw_requests');
                $customer_role->add_cap('publish_arsol_pfw_requests');
                break;
                
            case 'create':
            case 'user_specific':
            default:
                // Default: grant all creation capabilities (can be disabled by individual settings)
                $customer_role->add_cap('edit_arsol_pfw_projects');
                $customer_role->add_cap('publish_arsol_pfw_projects');
                $customer_role->add_cap('edit_arsol_pfw_requests');
                $customer_role->add_cap('publish_arsol_pfw_requests');
                break;
        }
    }

    /**
     * Update capabilities based on settings (legacy method for backward compatibility)
     * 
     * @deprecated Use specific update methods instead
     * @param array $settings The settings array
     */
    public static function update_capabilities_from_settings($settings) {
        // Update project manager roles
        if (isset($settings['project_manager_roles'])) {
            self::update_project_manager_roles($settings['project_manager_roles']);
        }

        // Update frontend permissions for customer role
        if (isset($settings['user_project_permissions'])) {
            self::update_frontend_permissions($settings['user_project_permissions']);
        }
    }

    /**
     * Assign capabilities to roles based on type
     *
     * @param array  $role_slugs Array of role slugs
     * @param string $type       'manager' or 'creator'
     */
    private static function assign_capabilities_to_roles($role_slugs, $type) {
        // First, remove all PFW capabilities from all roles
        self::remove_all_capabilities();

        // Then assign capabilities based on type
        foreach ($role_slugs as $role_slug) {
            $role = get_role($role_slug);
            if (!$role) {
                continue;
            }

            if ($type === 'manager') {
                // Assign manager capabilities
                $role->add_cap('arsol_pfw_manage');
                
                // All project capabilities
                $project_caps = array(
                    'edit_arsol_pfw_projects', 'edit_others_arsol_pfw_projects', 'publish_arsol_pfw_projects',
                    'read_private_arsol_pfw_projects', 'delete_arsol_pfw_projects', 'delete_private_arsol_pfw_projects',
                    'delete_published_arsol_pfw_projects', 'delete_others_arsol_pfw_projects',
                    'edit_private_arsol_pfw_projects', 'edit_published_arsol_pfw_projects',
                );

                // All proposal capabilities
                $proposal_caps = array(
                    'edit_arsol_pfw_proposals', 'edit_others_arsol_pfw_proposals', 'publish_arsol_pfw_proposals',
                    'read_private_arsol_pfw_proposals', 'delete_arsol_pfw_proposals', 'delete_private_arsol_pfw_proposals',
                    'delete_published_arsol_pfw_proposals', 'delete_others_arsol_pfw_proposals',
                    'edit_private_arsol_pfw_proposals', 'edit_published_arsol_pfw_proposals',
                );

                // All request capabilities
                $request_caps = array(
                    'edit_arsol_pfw_requests', 'edit_others_arsol_pfw_requests', 'publish_arsol_pfw_requests',
                    'read_private_arsol_pfw_requests', 'delete_arsol_pfw_requests', 'delete_private_arsol_pfw_requests',
                    'delete_published_arsol_pfw_requests', 'delete_others_arsol_pfw_requests',
                    'edit_private_arsol_pfw_requests', 'edit_published_arsol_pfw_requests',
                );

                $all_caps = array_merge($project_caps, $proposal_caps, $request_caps);
                foreach ($all_caps as $cap) {
                    $role->add_cap($cap);
                }

            } elseif ($type === 'creator') {
                // Assign creator capabilities (limited)
                $creator_caps = array(
                    'edit_arsol_pfw_projects', 'edit_arsol_pfw_proposals', 'edit_arsol_pfw_requests',
                    'publish_arsol_pfw_projects', 'publish_arsol_pfw_proposals', 'publish_arsol_pfw_requests',
                );

                foreach ($creator_caps as $cap) {
                    $role->add_cap($cap);
                }
            }
        }
    }


} 