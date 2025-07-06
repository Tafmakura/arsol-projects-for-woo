<?php
/**
 * Admin Capabilities Class - Compatibility Layer
 *
 * This is a compatibility layer that bridges the old namespace to the new
 * Arsol_PFW\Core\Capabilities class to maintain backward compatibility.
 *
 * @package Arsol_Projects_For_Woo\Admin
 * @since   1.0.0
 * @deprecated Use Arsol_PFW\Core\Capabilities instead
 */

namespace Arsol_Projects_For_Woo\Admin;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Capabilities Class - Compatibility Layer
 *
 * Bridges old namespace references to the new capabilities system.
 */
class Admin_Capabilities {
    
    /**
     * Check if user has broad PFW management access
     * 
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can manage PFW
     */
    public static function can_manage_projects($user_id = null) {
        return \Arsol_PFW\Core\Capabilities::can_manage_projects($user_id);
    }

    /**
     * Check if user can create projects
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can create projects
     */
    public static function can_create_projects($user_id = null) {
        return \Arsol_PFW\Core\Capabilities::can_create_projects($user_id);
    }

    /**
     * Check if user can create project requests
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can create project requests
     */
    public static function can_create_project_requests($user_id = null) {
        return \Arsol_PFW\Core\Capabilities::can_create_project_requests($user_id);
    }

    /**
     * Check if user can create project proposals
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user can create project proposals
     */
    public static function can_create_project_proposals($user_id = null) {
        return \Arsol_PFW\Core\Capabilities::can_create_project_proposals($user_id);
    }

    /**
     * Check if user can edit a specific project
     *
     * @param int $user_id User ID
     * @param int $project_id Project ID
     * @return bool Whether user can edit the project
     */
    public static function can_edit_project($user_id, $project_id) {
        return \Arsol_PFW\Core\Capabilities::can_edit_project($user_id, $project_id);
    }

    /**
     * Check if user can edit a specific project request
     *
     * @param int $user_id User ID
     * @param int $request_id Request ID
     * @return bool Whether user can edit the request
     */
    public static function can_edit_project_request($user_id, $request_id) {
        return \Arsol_PFW\Core\Capabilities::can_edit_project_request($user_id, $request_id);
    }

    /**
     * Check if user can edit a specific project proposal
     *
     * @param int $user_id User ID
     * @param int $proposal_id Proposal ID
     * @return bool Whether user can edit the proposal
     */
    public static function can_edit_project_proposal($user_id, $proposal_id) {
        return \Arsol_PFW\Core\Capabilities::can_edit_project_proposal($user_id, $proposal_id);
    }

    /**
     * Check if user can delete a specific project
     *
     * @param int $user_id User ID
     * @param int $project_id Project ID
     * @return bool Whether user can delete the project
     */
    public static function can_delete_project($user_id, $project_id) {
        return \Arsol_PFW\Core\Capabilities::can_delete_project($user_id, $project_id);
    }

    /**
     * Check if user can delete a specific project request
     *
     * @param int $user_id User ID
     * @param int $request_id Request ID
     * @return bool Whether user can delete the request
     */
    public static function can_delete_project_request($user_id, $request_id) {
        return \Arsol_PFW\Core\Capabilities::can_delete_project_request($user_id, $request_id);
    }

    /**
     * Check if user can delete a specific project proposal
     *
     * @param int $user_id User ID
     * @param int $proposal_id Proposal ID
     * @return bool Whether user can delete the proposal
     */
    public static function can_delete_project_proposal($user_id, $proposal_id) {
        return \Arsol_PFW\Core\Capabilities::can_delete_project_proposal($user_id, $proposal_id);
    }

    /**
     * Get user permission level
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return string Permission level
     */
    public static function get_user_permission_level($user_id = null) {
        return \Arsol_PFW\Core\Capabilities::get_user_permission_level($user_id);
    }
} 