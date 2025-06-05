<?php
/**
 * Project Request Handler Class
 *
 * Handles frontend project request submissions.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

class Project_Request_Handler {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_post_arsol_submit_project_request', array($this, 'handle_project_request_submission'));
        add_action('admin_post_nopriv_arsol_submit_project_request', array($this, 'handle_project_request_submission'));
    }

    /**
     * Handle project request form submission
     */
    public function handle_project_request_submission() {
        // Verify nonce
        if (!isset($_POST['arsol_project_request_nonce']) || 
            !wp_verify_nonce($_POST['arsol_project_request_nonce'], 'arsol_submit_project_request')) {
            wp_die(__('Security check failed', 'arsol-pfw'));
        }

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_die(__('You must be logged in to submit a project request', 'arsol-pfw'));
        }

        $user_id = get_current_user_id();

        // Check if user can create project requests
        if (!\Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_project_requests($user_id)) {
            wp_die(__('You do not have permission to create project requests', 'arsol-pfw'));
        }

        // Sanitize and validate input
        $title = isset($_POST['request_title']) ? sanitize_text_field($_POST['request_title']) : '';
        $description = isset($_POST['request_description']) ? wp_kses_post($_POST['request_description']) : '';
        $budget = isset($_POST['request_budget']) ? sanitize_text_field($_POST['request_budget']) : '';
        $timeline = isset($_POST['request_timeline']) ? sanitize_text_field($_POST['request_timeline']) : '';

        // Validate required fields
        if (empty($title) || empty($description)) {
            wp_die(__('Please fill in all required fields', 'arsol-pfw'));
        }

        // Create project request post
        $request_data = array(
            'post_title'    => $title,
            'post_content'  => $description,
            'post_status'   => 'publish',
            'post_type'     => 'arsol-project-request',
            'post_author'   => $user_id
        );

        // Allow filtering of request data
        $request_data = apply_filters('arsol_before_project_request_insert', $request_data);

        $request_id = wp_insert_post($request_data);

        if (is_wp_error($request_id)) {
            wp_die($request_id->get_error_message());
        }

        // Set default request status
        wp_set_object_terms($request_id, 'pending', 'arsol-request-status');

        // Save additional request meta
        if (!empty($budget)) {
            update_post_meta($request_id, '_request_budget', $budget);
        }
        if (!empty($timeline)) {
            update_post_meta($request_id, '_request_timeline', $timeline);
        }

        // Allow other plugins to hook into successful submission
        do_action('arsol_after_project_request_insert', $request_id);

        // Redirect to project request view page
        $redirect_url = wc_get_account_endpoint_url('project-view-request/' . $request_id);
        wp_safe_redirect($redirect_url);
        exit;
    }
} 