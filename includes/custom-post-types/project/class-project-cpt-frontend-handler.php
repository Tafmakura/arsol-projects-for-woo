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

        // Prepare creation data for hooks
        $creation_data = array(
            'user_id' => get_current_user_id(),
            'creation_method' => 'frontend_form',
            'timestamp' => current_time('timestamp'),
            'form_data' => $_POST
        );

        /**
         * Hook: arsol_before_project_creation_validation
         * Fired before any validation checks are performed
         * 
         * @param array $creation_data Creation context data
         */
        do_action('arsol_before_project_creation_validation', $creation_data);

        // Check if user is logged in
        if (!is_user_logged_in()) {
            // Not using wp_die() here to allow for a more graceful redirect or notice.
            wc_add_notice(__('You must be logged in to create a project', 'arsol-pfw'), 'error');
            return;
        }

        $user_id = get_current_user_id();
        $creation_data['user_id'] = $user_id;

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

        /**
         * Hook: arsol_after_project_creation_validated
         * Fired after validation passes, before project creation
         * 
         * @param array $creation_data Creation context data
         */
        do_action('arsol_after_project_creation_validated', $creation_data);

        // Create project post
        $project_data = array(
            'post_title'    => $title,
            'post_content'  => $description,
            'post_status'   => 'publish',
            'post_type'     => 'arsol-project',
            'post_author'   => $user_id
        );

        /**
         * Filter: arsol_project_creation_args
         * Allows modification of project creation arguments
         * 
         * @param array $project_data The project arguments
         * @param array $creation_data Creation context data
         */
        $project_data = apply_filters('arsol_project_creation_args', $project_data, $creation_data);

        /**
         * Hook: arsol_before_project_creation_post_creation
         * Fired immediately before the project post is created
         * 
         * @param array $project_data The project arguments that will be used
         * @param array $creation_data Creation context data
         */
        do_action('arsol_before_project_creation_post_creation', $project_data, $creation_data);

        $project_id = wp_insert_post($project_data);

        if (is_wp_error($project_id)) {
            /**
             * Hook: arsol_project_creation_post_creation_failed
             * Fired when project creation fails
             * 
             * @param WP_Error $error The error object
             * @param array $project_data The project arguments that failed
             * @param array $creation_data Creation context data
             */
            do_action('arsol_project_creation_post_creation_failed', $project_id, $project_data, $creation_data);
            
            wc_add_notice($project_id->get_error_message(), 'error');
            return;
        }

        $creation_data['project_id'] = $project_id;

        /**
         * Hook: arsol_after_project_creation_post_created
         * Fired after the project is successfully created, before status and metadata
         * 
         * @param int $project_id The new project ID
         * @param array $project_data Project post data
         * @param array $creation_data Creation context data
         */
        do_action('arsol_after_project_creation_post_created', $project_id, $project_data, $creation_data);

        /**
         * Hook: arsol_before_project_creation_status_assignment
         * Fired before setting the project status
         * 
         * @param int $project_id The project ID
         * @param string $default_status The default status to be assigned
         * @param array $creation_data Creation context data
         */
        do_action('arsol_before_project_creation_status_assignment', $project_id, 'not-started', $creation_data);

        // Set default project status
        wp_set_object_terms($project_id, 'not-started', 'arsol-project-status');

        /**
         * Hook: arsol_after_project_creation_status_assigned
         * Fired after the project status is assigned
         * 
         * @param int $project_id The project ID
         * @param string $assigned_status The status that was assigned
         * @param array $creation_data Creation context data
         */
        do_action('arsol_after_project_creation_status_assigned', $project_id, 'not-started', $creation_data);

        /**
         * Hook: arsol_before_project_creation_metadata_save
         * Fired before saving project metadata
         * 
         * @param int $project_id The project ID
         * @param array $form_data The form data to be processed
         * @param array $creation_data Creation context data
         */
        do_action('arsol_before_project_creation_metadata_save', $project_id, $_POST, $creation_data);
        
        // Save additional project meta
        if (!empty($budget)) {
            // Remove formatting from budget amount (commas, etc.)
            $amount = preg_replace('/[^\d.]/', '', $budget);
            $currency = get_woocommerce_currency();
            
            if (!empty($amount)) {
                $budget_data = array(
                    'amount'   => $amount,
                    'currency' => $currency
                );
                update_post_meta($project_id, '_project_budget', $budget_data);
            }
        }
        if (!empty($start_date)) {
            update_post_meta($project_id, '_project_start_date', $start_date);
        }
        if (!empty($delivery_date)) {
            update_post_meta($project_id, '_project_delivery_date', $delivery_date);
        }

        /**
         * Hook: arsol_after_project_creation_metadata_saved
         * Fired after all metadata has been saved
         * 
         * @param int $project_id The project ID
         * @param array $form_data The form data that was processed
         * @param array $creation_data Creation context data
         */
        do_action('arsol_after_project_creation_metadata_saved', $project_id, $_POST, $creation_data);

        /**
         * Hook: arsol_after_project_creation_complete
         * Fired after the project creation is complete, before redirect
         * 
         * @param int $project_id The new project ID
         * @param array $creation_data Creation context data
         */
        do_action('arsol_after_project_creation_complete', $project_id, $creation_data);

        /**
         * Hook: arsol_before_project_creation_redirect
         * Fired just before redirecting to the new project
         * Last chance to modify redirect or add notices
         * 
         * @param int $project_id The new project ID
         * @param string $redirect_url The URL about to redirect to
         * @param array $creation_data Creation context data
         */
        $redirect_url = wc_get_account_endpoint_url('project-overview/' . $project_id);
        do_action('arsol_before_project_creation_redirect', $project_id, $redirect_url, $creation_data);

        // Redirect to project overview page
        wp_safe_redirect($redirect_url);
        exit;
    }
} 