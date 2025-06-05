<?php
/**
 * Project Handler Class
 *
 * Handles frontend project creation submissions.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

class Project_Handler {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_post_arsol_submit_project', array($this, 'handle_project_submission'));
        add_action('admin_post_nopriv_arsol_submit_project', array($this, 'handle_project_submission'));
    }

    /**
     * Handle project form submission
     */
    public function handle_project_submission() {
        // Verify nonce
        if (!isset($_POST['arsol_project_nonce']) || 
            !wp_verify_nonce($_POST['arsol_project_nonce'], 'arsol_submit_project')) {
            wp_die(__('Security check failed', 'arsol-pfw'));
        }

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_die(__('You must be logged in to create a project', 'arsol-pfw'));
        }

        $user_id = get_current_user_id();

        // Check if user can create projects
        if (!\Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_projects($user_id)) {
            wp_die(__('You do not have permission to create projects', 'arsol-pfw'));
        }

        // Sanitize and validate input
        $title = isset($_POST['project_title']) ? sanitize_text_field($_POST['project_title']) : '';
        $description = isset($_POST['project_description']) ? wp_kses_post($_POST['project_description']) : '';
        $due_date = isset($_POST['project_due_date']) ? sanitize_text_field($_POST['project_due_date']) : '';
        $proposal_id = isset($_POST['proposal_id']) ? absint($_POST['proposal_id']) : 0;

        // Validate required fields
        if (empty($title)) {
            wp_die(__('Please fill in all required fields', 'arsol-pfw'));
        }

        // Create project post
        $project_data = array(
            'post_title'    => $title,
            'post_content'  => $description,
            'post_status'   => 'publish',
            'post_type'     => 'arsol-project',
            'post_author'   => $user_id
        );

        // Allow filtering of project data
        $project_data = apply_filters('arsol_before_project_insert', $project_data);

        $project_id = wp_insert_post($project_data);

        if (is_wp_error($project_id)) {
            wp_die($project_id->get_error_message());
        }

        // Set default project status
        wp_set_object_terms($project_id, 'not-started', 'arsol-project-status');

        // Save additional project meta
        if (!empty($due_date)) {
            update_post_meta($project_id, '_project_due_date', $due_date);
        }
        if ($proposal_id) {
            update_post_meta($project_id, '_related_proposal_id', $proposal_id);
        }

        // Allow other plugins to hook into successful submission
        do_action('arsol_after_project_insert', $project_id);

        // Redirect to project overview page
        $redirect_url = wc_get_account_endpoint_url('project-overview');
        $redirect_url = add_query_arg('project', $project_id, $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }
} 