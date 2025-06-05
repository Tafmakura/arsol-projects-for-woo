<?php
/**
 * Project Proposal Handler Class
 *
 * Handles frontend project proposal submissions.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

class Project_Proposal_Handler {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_post_arsol_submit_project_proposal', array($this, 'handle_project_proposal_submission'));
        add_action('admin_post_nopriv_arsol_submit_project_proposal', array($this, 'handle_project_proposal_submission'));
    }

    /**
     * Handle project proposal form submission
     */
    public function handle_project_proposal_submission() {
        // Verify nonce
        if (!isset($_POST['arsol_project_proposal_nonce']) || 
            !wp_verify_nonce($_POST['arsol_project_proposal_nonce'], 'arsol_submit_project_proposal')) {
            wp_die(__('Security check failed', 'arsol-pfw'));
        }

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_die(__('You must be logged in to submit a project proposal', 'arsol-pfw'));
        }

        $user_id = get_current_user_id();

        // Check if user can create project proposals
        if (!\Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_project_proposals($user_id)) {
            wp_die(__('You do not have permission to create project proposals', 'arsol-pfw'));
        }

        // Sanitize and validate input
        $title = isset($_POST['proposal_title']) ? sanitize_text_field($_POST['proposal_title']) : '';
        $description = isset($_POST['proposal_description']) ? wp_kses_post($_POST['proposal_description']) : '';
        $budget = isset($_POST['proposal_budget']) ? sanitize_text_field($_POST['proposal_budget']) : '';
        $timeline = isset($_POST['proposal_timeline']) ? sanitize_text_field($_POST['proposal_timeline']) : '';
        $request_id = isset($_POST['request_id']) ? absint($_POST['request_id']) : 0;

        // Validate required fields
        if (empty($title) || empty($description)) {
            wp_die(__('Please fill in all required fields', 'arsol-pfw'));
        }

        // Create project proposal post
        $proposal_data = array(
            'post_title'    => $title,
            'post_content'  => $description,
            'post_status'   => 'publish',
            'post_type'     => 'arsol-pfw-proposal',
            'post_author'   => $user_id
        );

        // Allow filtering of proposal data
        $proposal_data = apply_filters('arsol_before_project_proposal_insert', $proposal_data);

        $proposal_id = wp_insert_post($proposal_data);

        if (is_wp_error($proposal_id)) {
            wp_die($proposal_id->get_error_message());
        }

        // Set default proposal status
        wp_set_object_terms($proposal_id, 'pending', 'arsol-proposal-status');

        // Save additional proposal meta
        if (!empty($budget)) {
            update_post_meta($proposal_id, '_proposal_budget', $budget);
        }
        if (!empty($timeline)) {
            update_post_meta($proposal_id, '_proposal_timeline', $timeline);
        }
        if ($request_id) {
            update_post_meta($proposal_id, '_related_request_id', $request_id);
        }

        // Allow other plugins to hook into successful submission
        do_action('arsol_after_project_proposal_insert', $proposal_id);

        // Redirect to project proposal view page
        $redirect_url = wc_get_account_endpoint_url('project-view-proposal/' . $proposal_id);
        wp_safe_redirect($redirect_url);
        exit;
    }
} 