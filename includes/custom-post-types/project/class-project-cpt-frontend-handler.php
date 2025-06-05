<?php
/**
 * Project Frontend Handler Class
 *
 * Handles frontend project creation submissions.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Custom_Post_Types\Project;

if (!defined('ABSPATH')) {
    exit;
}

class Frontend_Handler {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('template_redirect', array($this, 'handle_create_project_submission'));
    }

    /**
     * Handle project form submission
     */
    public function handle_create_project_submission() {
        if ('POST' !== $_SERVER['REQUEST_METHOD'] || !isset($_POST['create_project_nonce']) || !wp_verify_nonce($_POST['create_project_nonce'], 'create_project')) {
            return;
        }

        // Check if user is logged in
        if (!is_user_logged_in()) {
            // Not using wp_die() here to allow for a more graceful redirect or notice.
            wc_add_notice(__('You must be logged in to create a project', 'arsol-pfw'), 'error');
            return;
        }

        $user_id = get_current_user_id();

        // Check if user can create projects
        if (!\Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_projects($user_id)) {
            wc_add_notice(__('You do not have permission to create projects. Please contact the administrator if you believe this is an error.', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }

        // Sanitize and validate input
        $title = isset($_POST['project_title']) ? sanitize_text_field($_POST['project_title']) : '';
        $description = isset($_POST['project_description']) ? wp_kses_post($_POST['project_description']) : '';
        $budget = isset($_POST['project_budget']) ? sanitize_text_field($_POST['project_budget']) : '';
        $start_date = isset($_POST['project_start_date']) ? sanitize_text_field($_POST['project_start_date']) : '';
        $delivery_date = isset($_POST['project_delivery_date']) ? sanitize_text_field($_POST['project_delivery_date']) : '';
        
        // Validate required fields
        if (empty($title)) {
            wc_add_notice(__('Please fill in all required fields', 'arsol-pfw'), 'error');
            return;
        }

        // Create project post
        $project_data = array(
            'post_title'    => $title,
            'post_content'  => $description,
            'post_status'   => 'publish',
            'post_type'     => 'arsol-project',
            'post_author'   => $user_id
        );

        $project_id = wp_insert_post($project_data);

        if (is_wp_error($project_id)) {
            wc_add_notice($project_id->get_error_message(), 'error');
            return;
        }

        // Set default project status
        wp_set_object_terms($project_id, 'not-started', 'arsol-project-status');
        
        // Save additional project meta
        if (!empty($budget)) {
            update_post_meta($project_id, '_project_budget', $budget);
        }
        if (!empty($start_date)) {
            update_post_meta($project_id, '_project_start_date', $start_date);
        }
        if (!empty($delivery_date)) {
            update_post_meta($project_id, '_project_delivery_date', $delivery_date);
        }

        // Redirect to project overview page
        $redirect_url = wc_get_account_endpoint_url('project-overview/' . $project_id);
        wp_safe_redirect($redirect_url);
        exit;
    }
} 