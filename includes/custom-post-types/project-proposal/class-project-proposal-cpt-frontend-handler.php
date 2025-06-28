<?php
/**
 * Project Proposal Frontend Handler Class
 *
 * Handles frontend project proposal submissions.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal;

if (!defined('ABSPATH')) {
    exit;
}

class Frontend_Handler {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('template_redirect', array($this, 'handle_project_proposal_submission'));
        add_filter('the_content', array($this, 'display_proposal_content'));    }


    /**
     * Display proposal content with status messages
     */
    public function display_proposal_content($content) {
        if (!is_singular('arsol-pfw-proposal') || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        global $post;
        $current_user_id = get_current_user_id();
        $customer_id = get_post_meta($post->ID, '_arsol_pfw_proposal_customer_id', true);

        // Check if current user is the assigned customer
        if ($current_user_id != $customer_id) {
            return '<div class="arsol-pfw-error"><p>You do not have permission to view this proposal.</p></div>';
        }

        // Get proposal status
        $status_terms = get_the_terms($post->ID, 'arsol-proposal-status');
        $status = $status_terms && !is_wp_error($status_terms) ? $status_terms[0]->slug : 'processing';

        // Display status-specific messages in content section
        $status_message = '';
        switch ($status) {
            case 'processing':
                // Get custom feedback first, then fall back to settings defaults
                $custom_feedback = get_post_meta($post->ID, '_arsol_pfw_proposal_processing_feedback', true);
                $processing_message = !empty($custom_feedback) 
                    ? $custom_feedback 
                    : \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_default_message('arsol_pfw_proposal_default_empty_content');
                $status_message = '<div class="arsol-pfw-notice arsol-pfw-notice-info">
                    <div class="arsol-pfw-empty-state">
                        <div class="arsol-pfw-empty-state__content">
                            ' . wp_kses_post(wpautop($processing_message)) . '
                        </div>
                    </div>
                </div>';
                break;
                
            case 'pending-approval':
                // Get custom feedback first, then fall back to settings defaults
                $custom_feedback = get_post_meta($post->ID, '_arsol_pfw_proposal_pending_approval_feedback', true);
                $pending_approval_message = !empty($custom_feedback) 
                    ? $custom_feedback 
                    : \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_default_message('arsol_pfw_proposal_default_empty_content');
                $status_message = '<div class="arsol-pfw-notice arsol-pfw-notice-warning">
                    <div class="arsol-pfw-empty-state">
                        <div class="arsol-pfw-empty-state__content">
                            ' . wp_kses_post(wpautop($pending_approval_message)) . '
                        </div>
                    </div>
                </div>';
                break;
                
            case 'approved':
                $status_message = '<div class="arsol-pfw-notice arsol-pfw-notice-success">
                    <p><strong>Status:</strong> Excellent! Your proposal has been approved and a project will be created for you shortly.</p>
                </div>';
                break;
                
            case 'rejected':
                $status_message = '<div class="arsol-pfw-notice arsol-pfw-notice-error">
                    <p><strong>Status:</strong> This proposal has been rejected. If you have questions or would like to discuss alternatives, please contact us.</p>
                </div>';
                break;
        }

        // Check if user can proceed (only for approved status)
        $can_proceed = ($status === 'approved');
        
        if (!$can_proceed && in_array($status, ['processing', 'pending-approval', 'rejected'])) {
            $status_message .= '<div class="arsol-pfw-notice arsol-pfw-notice-info">
                <p><strong>Cannot Proceed:</strong> You cannot proceed to the next step until your proposal has been approved.</p>
            </div>';
        }

        // Build content with status messages
        $proposal_content = $status_message;
        $proposal_content .= '<div class="arsol-pfw-proposal-details">';
        $proposal_content .= $content;
        $proposal_content .= '</div>';

        return $proposal_content;
    }    /**
     * Handle project proposal form submission
     */
    public function handle_project_proposal_submission() {
        // Verify nonce
        if ('POST' !== $_SERVER['REQUEST_METHOD'] || !isset($_POST['arsol_project_proposal_nonce']) ||
            !wp_verify_nonce($_POST['arsol_project_proposal_nonce'], 'arsol_submit_project_proposal')) {
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
         * Hook: arsol_before_proposal_creation_validation
         * Fired before any validation checks are performed
         * 
         * @param array $creation_data Creation context data
         */
        do_action('arsol_before_proposal_creation_validation', $creation_data);

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wc_add_notice(__('You must be logged in to submit a project proposal', 'arsol-pfw'), 'error');
            return;
        }

        $user_id = get_current_user_id();
        $creation_data['user_id'] = $user_id;

        // Check if user can create project proposals
        if (!\Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_project_proposals($user_id)) {
            wc_add_notice(__('You do not have permission to create project proposals', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }

        // Sanitize and validate input
        $title = isset($_POST['proposal_title']) ? sanitize_text_field($_POST['proposal_title']) : '';
        $description = isset($_POST['proposal_description']) ? wp_kses_post($_POST['proposal_description']) : '';
        $budget = isset($_POST['proposal_budget']) ? sanitize_text_field($_POST['proposal_budget']) : '';
        $request_id = isset($_POST['request_id']) ? absint($_POST['request_id']) : 0;

        // Validate required fields
        if (empty($title) || empty($description)) {
            wc_add_notice(__('Please fill in all required fields', 'arsol-pfw'), 'error');
            return;
        }

        /**
         * Hook: arsol_after_proposal_creation_validated
         * Fired after validation passes, before proposal creation
         * 
         * @param array $creation_data Creation context data
         */
        do_action('arsol_after_proposal_creation_validated', $creation_data);

        // Create project proposal post
        $proposal_data = array(
            'post_title'    => $title,
            'post_content'  => $description,
            'post_status'   => 'publish',
            'post_type'     => 'arsol-pfw-proposal',
            'post_author'   => $user_id
        );

        // Allow filtering of proposal data (keeping existing filter)
        $proposal_data = apply_filters('arsol_before_project_proposal_insert', $proposal_data);

        /**
         * Filter: arsol_proposal_creation_args
         * Allows modification of proposal creation arguments
         * 
         * @param array $proposal_data The proposal arguments
         * @param array $creation_data Creation context data
         */
        $proposal_data = apply_filters('arsol_proposal_creation_args', $proposal_data, $creation_data);

        /**
         * Hook: arsol_before_proposal_creation_post_creation
         * Fired immediately before the proposal post is created
         * 
         * @param array $proposal_data The proposal arguments that will be used
         * @param array $creation_data Creation context data
         */
        do_action('arsol_before_proposal_creation_post_creation', $proposal_data, $creation_data);

        $proposal_id = wp_insert_post($proposal_data);

        if (is_wp_error($proposal_id)) {
            /**
             * Hook: arsol_proposal_creation_post_creation_failed
             * Fired when proposal creation fails
             * 
             * @param WP_Error $error The error object
             * @param array $proposal_data The proposal arguments that failed
             * @param array $creation_data Creation context data
             */
            do_action('arsol_proposal_creation_post_creation_failed', $proposal_id, $proposal_data, $creation_data);
            
            wc_add_notice($proposal_id->get_error_message(), 'error');
            return;
        }

        $creation_data['proposal_id'] = $proposal_id;

        /**
         * Hook: arsol_after_proposal_creation_post_created
         * Fired after the proposal is successfully created, before status and metadata
         * 
         * @param int $proposal_id The new proposal ID
         * @param array $proposal_data Proposal post data
         * @param array $creation_data Creation context data
         */
        do_action('arsol_after_proposal_creation_post_created', $proposal_id, $proposal_data, $creation_data);

        /**
         * Hook: arsol_before_proposal_creation_status_assignment
         * Fired before setting the proposal status
         * 
         * @param int $proposal_id The proposal ID
         * @param string $default_status The default status to be assigned
         * @param array $creation_data Creation context data
         */
        do_action('arsol_before_proposal_creation_status_assignment', $proposal_id, 'processing', $creation_data);

        // Set default proposal status
        wp_set_object_terms($proposal_id, 'processing', 'arsol-proposal-status');

        /**
         * Hook: arsol_after_proposal_creation_status_assigned
         * Fired after the proposal status is assigned
         * 
         * @param int $proposal_id The proposal ID
         * @param string $assigned_status The status that was assigned
         * @param array $creation_data Creation context data
         */
        do_action('arsol_after_proposal_creation_status_assigned', $proposal_id, 'processing', $creation_data);

        /**
         * Hook: arsol_before_proposal_creation_metadata_save
         * Fired before saving proposal metadata
         * 
         * @param int $proposal_id The proposal ID
         * @param array $form_data The form data to be processed
         * @param array $creation_data Creation context data
         */
        do_action('arsol_before_proposal_creation_metadata_save', $proposal_id, $_POST, $creation_data);

        // Save additional proposal meta
        if (!empty($budget)) {
            update_post_meta($proposal_id, '_arsol_pfw_proposal_budget_onetime_amount', $budget);
        }
        if ($request_id) {
            update_post_meta($proposal_id, '_arsol_pfw_proposal_request_id', $request_id);
        }

        /**
         * Hook: arsol_after_proposal_creation_metadata_saved
         * Fired after all metadata has been saved
         * 
         * @param int $proposal_id The proposal ID
         * @param array $form_data The form data that was processed
         * @param array $creation_data Creation context data
         */
        do_action('arsol_after_proposal_creation_metadata_saved', $proposal_id, $_POST, $creation_data);

        // Allow other plugins to hook into successful submission (keeping existing action)
        do_action('arsol_after_project_proposal_insert', $proposal_id);

        /**
         * Hook: arsol_after_proposal_creation_complete
         * Fired after the proposal creation is complete, before redirect
         * 
         * @param int $proposal_id The new proposal ID
         * @param array $creation_data Creation context data
         */
        do_action('arsol_after_proposal_creation_complete', $proposal_id, $creation_data);

        /**
         * Hook: arsol_before_proposal_creation_redirect
         * Fired just before redirecting to the new proposal
         * Last chance to modify redirect or add notices
         * 
         * @param int $proposal_id The new proposal ID
         * @param string $redirect_url The URL about to redirect to
         * @param array $creation_data Creation context data
         */
        $redirect_url = wc_get_account_endpoint_url('project-view-proposal/' . $proposal_id);
        do_action('arsol_before_proposal_creation_redirect', $proposal_id, $redirect_url, $creation_data);

        // Redirect to project proposal view page
        wp_safe_redirect($redirect_url);
        exit;
    }
} 