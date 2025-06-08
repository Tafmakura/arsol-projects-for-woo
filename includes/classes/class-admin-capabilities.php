<?php 

/**
 * Admin Capabilities Class
 *
 * Handles custom capabilities for Arsol Projects For Woo.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Admin_Capabilities {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', array($this, 'add_capabilities'));
    }

    /**
     * Add custom capabilities to administrator role
     */
    public function add_capabilities() {
        $admin_role = get_role('administrator');
        if (!$admin_role) {
            return;
        }

        // Project Management capabilities (for admins/managers)
        $admin_role->add_cap('arsol-manage-projects');
        $admin_role->add_cap('edit_arsol_project');
        $admin_role->add_cap('read_arsol_project');
        $admin_role->add_cap('delete_arsol_project');
        $admin_role->add_cap('edit_arsol_projects');
        $admin_role->add_cap('edit_others_arsol_projects');
        $admin_role->add_cap('publish_arsol_projects');
        $admin_role->add_cap('read_private_arsol_projects');
        $admin_role->add_cap('delete_arsol_projects');
        $admin_role->add_cap('delete_private_arsol_projects');
        $admin_role->add_cap('delete_published_arsol_projects');
        $admin_role->add_cap('delete_others_arsol_projects');
        $admin_role->add_cap('edit_private_arsol_projects');
        $admin_role->add_cap('edit_published_arsol_projects');

        // Project Creation capabilities (for regular users)
        $admin_role->add_cap('arsol-create-projects');
        $admin_role->add_cap('edit_own_arsol_projects');
        $admin_role->add_cap('read_own_arsol_projects');
        $admin_role->add_cap('delete_own_arsol_projects');
        $admin_role->add_cap('publish_own_arsol_projects');

        // Project Request capabilities
        $admin_role->add_cap('edit_arsol_project_request');
        $admin_role->add_cap('read_arsol_project_request');
        $admin_role->add_cap('delete_arsol_project_request');
        $admin_role->add_cap('edit_arsol_project_requests');
        $admin_role->add_cap('edit_others_arsol_project_requests');
        $admin_role->add_cap('publish_arsol_project_requests');
        $admin_role->add_cap('read_private_arsol_project_requests');
        $admin_role->add_cap('delete_arsol_project_requests');
        $admin_role->add_cap('delete_private_arsol_project_requests');
        $admin_role->add_cap('delete_published_arsol_project_requests');
        $admin_role->add_cap('delete_others_arsol_project_requests');
        $admin_role->add_cap('edit_private_arsol_project_requests');
        $admin_role->add_cap('edit_published_arsol_project_requests');

        // Project Proposal capabilities
        $admin_role->add_cap('edit_arsol_project_proposal');
        $admin_role->add_cap('read_arsol_project_proposal');
        $admin_role->add_cap('delete_arsol_project_proposal');
        $admin_role->add_cap('edit_arsol_project_proposals');
        $admin_role->add_cap('edit_others_arsol_project_proposals');
        $admin_role->add_cap('publish_arsol_project_proposals');
        $admin_role->add_cap('read_private_arsol_project_proposals');
        $admin_role->add_cap('delete_arsol_project_proposals');
        $admin_role->add_cap('delete_private_arsol_project_proposals');
        $admin_role->add_cap('delete_published_arsol_project_proposals');
        $admin_role->add_cap('delete_others_arsol_project_proposals');
        $admin_role->add_cap('edit_private_arsol_project_proposals');
        $admin_role->add_cap('edit_published_arsol_project_proposals');
    }

    /**
     * Check if user can manage projects (admin capability)
     *
     * @param int $user_id User ID
     * @return bool Whether user can manage projects
     */
    public static function can_manage_projects($user_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        return $user->has_cap('arsol-manage-projects');
    }

    /**
     * Check if user can create projects (user capability)
     *
     * @param int $user_id User ID
     * @return bool Whether user can create projects
     */
    public static function can_create_projects($user_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        return $user->has_cap('arsol-create-projects');
    }

    /**
     * Check if user can create project requests
     *
     * @param int $user_id User ID
     * @return bool Whether user can create project requests
     */
    public static function can_create_project_requests($user_id) {
        // Users with either management or creation capabilities can create requests
        return self::can_manage_projects($user_id) || self::can_create_projects($user_id);
    }

    /**
     * Check if user can create project proposals
     *
     * @param int $user_id User ID
     * @return bool Whether user can create project proposals
     */
    public static function can_create_project_proposals($user_id) {
        // Users with either management or creation capabilities can create proposals
        return self::can_manage_projects($user_id) || self::can_create_projects($user_id);
    }

    /**
     * Check if user can edit a specific project
     *
     * @param int $user_id User ID
     * @param int $project_id Project ID
     * @return bool Whether user can edit the project
     */
    public static function can_edit_project($user_id, $project_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }

        $project = get_post($project_id);
        if (!$project || $project->post_type !== 'arsol-project') {
            return false;
        }

        // Project managers can edit any project
        if (self::can_manage_projects($user_id)) {
            return true;
        }

        // Project creators can only edit their own projects
        if (self::can_create_projects($user_id) && $project->post_author == $user_id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can edit a specific project request
     *
     * @param int $user_id User ID
     * @param int $request_id Request ID
     * @return bool Whether user can edit the project request
     */
    public static function can_edit_project_request($user_id, $request_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }

        $request = get_post($request_id);
        if (!$request || $request->post_type !== 'arsol-pfw-request') {
            return false;
        }

        // Project managers can edit any request
        if (self::can_manage_projects($user_id)) {
            return true;
        }

        // Project creators can only edit their own requests
        if (self::can_create_projects($user_id) && $request->post_author == $user_id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can edit a specific project proposal
     *
     * @param int $user_id User ID
     * @param int $proposal_id Proposal ID
     * @return bool Whether user can edit the project proposal
     */
    public static function can_edit_project_proposal($user_id, $proposal_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }

        $proposal = get_post($proposal_id);
        if (!$proposal || $proposal->post_type !== 'arsol-pfw-proposal') {
            return false;
        }

        // Project managers can edit any proposal
        if (self::can_manage_projects($user_id)) {
            return true;
        }

        // Project creators can only edit their own proposals
        if (self::can_create_projects($user_id) && $proposal->post_author == $user_id) {
            return true;
        }

        return false;
    }
} 