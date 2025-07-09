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
        add_action('admin_post_arsol_edit_project', array($this, 'handle_edit_project_submission'));
        add_action('admin_post_nopriv_arsol_edit_project', array($this, 'handle_edit_project_submission'));
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
        if (!\Arsol_Projects_For_Woo\Core\Capabilities::can_create_projects($user_id)) {
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
            'post_type'     => 'arsol-pfw-project',
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
        wp_set_object_terms($project_id, 'not-started', 'arsol-pfw-project-stage');

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
            $amount = \Arsol_Projects_For_Woo\Woocommerce::clean_amount_input($budget);
            $currency = get_woocommerce_currency();
            
            if (!empty($amount)) {
                $budget_data = array(
                    'amount'   => $amount,
                    'currency' => $currency
                );
                update_post_meta($project_id, '_arsol_pfw_project_budget', $budget_data);
            }
        }
        if (!empty($start_date)) {
            update_post_meta($project_id, '_arsol_pfw_project_start_date', $start_date);
        }
        if (!empty($delivery_date)) {
            update_post_meta($project_id, '_arsol_pfw_project_delivery_date', $delivery_date);
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
        $redirect_url = wc_get_account_endpoint_url('view-project/' . $project_id);
        do_action('arsol_before_project_creation_redirect', $project_id, $redirect_url, $creation_data);

        // Redirect to project overview page
        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Handle edit project form submission
     */
    public function handle_edit_project_submission() {
        if ('POST' !== $_SERVER['REQUEST_METHOD'] || !isset($_POST['edit_project_nonce']) || !wp_verify_nonce($_POST['edit_project_nonce'], 'edit_project')) {
            wp_die(__('Security check failed', 'arsol-pfw'));
        }

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wc_add_notice(__('You must be logged in to edit a project', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }

        $user_id = get_current_user_id();
        $project_id = isset($_POST['project_id']) ? absint($_POST['project_id']) : 0;

        if (!$project_id) {
            wc_add_notice(__('Invalid project ID', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }

        // Get the project
        $project = get_post($project_id);
        if (!$project || $project->post_type !== 'arsol-pfw-project') {
            wc_add_notice(__('Invalid project', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }

        // Check permissions - user must own the project or be admin
        if ($project->post_author != $user_id && !current_user_can('manage_options')) {
            wc_add_notice(__('You do not have permission to edit this project', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }

        // Check if user can edit projects
        if (!\Arsol_Projects_For_Woo\Core\Capabilities::can_create_projects($user_id)) {
            wc_add_notice(__('You do not have permission to edit projects. Please contact the administrator if you believe this is an error.', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }

        // Prepare edit data for hooks
        $edit_data = array(
            'user_id' => $user_id,
            'project_id' => $project_id,
            'edit_method' => 'frontend_form',
            'timestamp' => current_time('timestamp'),
            'form_data' => $_POST,
            'original_project' => $project
        );

        /**
         * Hook: arsol_before_project_edit_validation
         * Fired before any validation checks are performed
         * 
         * @param array $edit_data Edit context data
         */
        do_action('arsol_before_project_edit_validation', $edit_data);

        // Sanitize and validate input
        $title = isset($_POST['project_title']) ? sanitize_text_field($_POST['project_title']) : '';
        $description = isset($_POST['project_description']) ? wp_kses_post($_POST['project_description']) : '';
        $budget = isset($_POST['project_budget']) ? sanitize_text_field($_POST['project_budget']) : '';
        $start_date = isset($_POST['project_start_date']) ? sanitize_text_field($_POST['project_start_date']) : '';
        $delivery_date = isset($_POST['project_delivery_date']) ? sanitize_text_field($_POST['project_delivery_date']) : '';
        
        // Validate required fields
        if (empty($title)) {
            wc_add_notice(__('Please fill in all required fields', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('view-project/' . $project_id));
            exit;
        }

        /**
         * Hook: arsol_after_project_edit_validated
         * Fired after validation passes, before project update
         * 
         * @param array $edit_data Edit context data
         */
        do_action('arsol_after_project_edit_validated', $edit_data);

        // Update project post
        $project_data = array(
            'ID'            => $project_id,
            'post_title'    => $title,
            'post_content'  => $description,
        );

        /**
         * Filter: arsol_project_edit_args
         * Allows modification of project edit arguments
         * 
         * @param array $project_data The project arguments
         * @param array $edit_data Edit context data
         */
        $project_data = apply_filters('arsol_project_edit_args', $project_data, $edit_data);

        /**
         * Hook: arsol_before_project_edit_post_update
         * Fired immediately before the project post is updated
         * 
         * @param array $project_data The project arguments that will be used
         * @param array $edit_data Edit context data
         */
        do_action('arsol_before_project_edit_post_update', $project_data, $edit_data);

        $result = wp_update_post($project_data);

        if (is_wp_error($result)) {
            /**
             * Hook: arsol_project_edit_post_update_failed
             * Fired when project edit fails
             * 
             * @param WP_Error $error The error object
             * @param array $project_data The project arguments that failed
             * @param array $edit_data Edit context data
             */
            do_action('arsol_project_edit_post_update_failed', $result, $project_data, $edit_data);
            
            wc_add_notice($result->get_error_message(), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('view-project/' . $project_id));
            exit;
        }

        /**
         * Hook: arsol_after_project_edit_post_updated
         * Fired after the project is successfully updated, before metadata
         * 
         * @param int $project_id The project ID
         * @param array $project_data Project post data
         * @param array $edit_data Edit context data
         */
        do_action('arsol_after_project_edit_post_updated', $project_id, $project_data, $edit_data);

        /**
         * Hook: arsol_before_project_edit_metadata_save
         * Fired before saving project metadata
         * 
         * @param int $project_id The project ID
         * @param array $form_data The form data to be processed
         * @param array $edit_data Edit context data
         */
        do_action('arsol_before_project_edit_metadata_save', $project_id, $_POST, $edit_data);
        
        // Save additional project meta
        if (!empty($budget)) {
            $budget_data = array(
                'amount' => $budget,
                'currency' => get_woocommerce_currency()
            );
            update_post_meta($project_id, '_arsol_pfw_project_budget', $budget_data);
        }

        if (!empty($start_date)) {
            update_post_meta($project_id, '_arsol_pfw_project_start_date', $start_date);
        }

        if (!empty($delivery_date)) {
            update_post_meta($project_id, '_arsol_pfw_project_delivery_date', $delivery_date);
        }

        /**
         * Hook: arsol_after_project_edit_metadata_saved
         * Fired after all project metadata is saved
         * 
         * @param int $project_id The project ID
         * @param array $form_data The processed form data
         * @param array $edit_data Edit context data
         */
        do_action('arsol_after_project_edit_metadata_saved', $project_id, $_POST, $edit_data);

        /**
         * Hook: arsol_project_edit_completed
         * Fired when the project edit process is fully completed
         * 
         * @param int $project_id The project ID that was edited
         * @param array $edit_data Edit context data including original project
         */
        do_action('arsol_project_edit_completed', $project_id, $edit_data);

        // Success message
        wc_add_notice(__('Project updated successfully!', 'arsol-pfw'), 'success');

        /**
         * Filter: arsol_project_edit_redirect_url
         * Allows modification of the redirect URL after project edit
         * 
         * @param string $redirect_url The default redirect URL
         * @param int $project_id The project ID that was edited
         * @param array $edit_data Edit context data
         */
        $redirect_url = wc_get_account_endpoint_url('view-project/' . $project_id);
        $redirect_url = apply_filters('arsol_project_edit_redirect_url', $redirect_url, $project_id, $edit_data);

        /**
         * Hook: arsol_before_project_edit_redirect
         * Fired immediately before redirect after project edit
         * 
         * @param int $project_id The project ID that was edited
         * @param string $redirect_url The URL being redirected to
         * @param array $edit_data Edit context data
         */
        do_action('arsol_before_project_edit_redirect', $project_id, $redirect_url, $edit_data);

        // Redirect to project overview page
        wp_safe_redirect($redirect_url);
        exit;
    }
}
