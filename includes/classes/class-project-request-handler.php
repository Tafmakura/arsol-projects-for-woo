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
        add_action('template_redirect', array($this, 'handle_create_request_submission'));
    }

    /**
     * Handle create request form submission
     */
    public function handle_create_request_submission() {
        if ('POST' !== $_SERVER['REQUEST_METHOD'] || !isset($_POST['create_request_nonce']) || !wp_verify_nonce($_POST['create_request_nonce'], 'create_request')) {
            return;
        }

        // Check if user can create project requests
        $user_id = get_current_user_id();
        $can_create = \Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_project_requests($user_id);

        if (!$can_create) {
            wc_add_notice(__('You do not have permission to create project requests. Please contact the administrator if you believe this is an error.', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }
        
        $title = sanitize_text_field($_POST['request_title']);
        $description = wp_kses_post($_POST['request_description']);
        $budget = isset($_POST['request_budget']) ? sanitize_text_field($_POST['request_budget']) : '';
        $start_date = isset($_POST['request_start_date']) ? sanitize_text_field($_POST['request_start_date']) : '';
        $delivery_date = isset($_POST['request_delivery_date']) ? sanitize_text_field($_POST['request_delivery_date']) : '';
        
        // Create project request post
        $request_data = array(
            'post_title'    => $title,
            'post_content'  => $description,
            'post_status'   => 'publish',
            'post_type'     => 'arsol-pfw-request',
            'post_author'   => $user_id
        );
        
        $request_id = wp_insert_post($request_data);
        
        if (!is_wp_error($request_id)) {
            // Set default request status
            wp_set_object_terms($request_id, 'pending', 'arsol-request-status');
            
            // Save additional request meta
            if (!empty($budget)) {
                update_post_meta($request_id, '_request_budget', $budget);
            }
            if (!empty($start_date)) {
                update_post_meta($request_id, '_request_start_date', $start_date);
            }
            if (!empty($delivery_date)) {
                update_post_meta($request_id, '_request_delivery_date', $delivery_date);
            }
            
            // Redirect to the specific request view
            $redirect_url = wc_get_account_endpoint_url('project-view-request/' . $request_id);
            wp_safe_redirect($redirect_url);
            exit;
        } else {
            // Add error notice if request creation failed
            wc_add_notice(__('Failed to submit project request. Please try again.', 'arsol-pfw'), 'error');
        }
    }
} 