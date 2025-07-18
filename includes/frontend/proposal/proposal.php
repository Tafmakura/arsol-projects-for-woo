<?php
/**
 * Project Proposal Frontend Handler Class
 *
 * Handles frontend project proposal submissions and customer actions.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Frontend;

use Exception;

if (!defined('ABSPATH')) {
    exit;
}

class Proposal_Frontend extends Frontend_Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize the frontend handler
     */
    public function init() {
        add_action('template_redirect', array($this, 'handle_project_proposal_submission'));
        add_filter('the_content', array($this, 'display_proposal_content'));
    }

    /**
     * Display proposal content with status messages
     */
    public function display_proposal_content($content) {
        if (!is_singular('arsol-pfw-proposal') || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        global $post;
        $current_user_id = get_current_user_id();
        // Get customer ID using Proposal entity
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post->ID);
        $customer_id = $proposal->get_customer_id();

        // Check if current user is the assigned customer
        if ($current_user_id != $customer_id) {
            ob_start();
            \Arsol_Projects_For_Woo\Core\Access_Handler::display_no_access_template('proposal', array(
                'proposal_id' => $post->ID
            ));
            return ob_get_clean();
        }

        // Get proposal status
        $status_terms = get_the_terms($post->ID, 'arsol-pfw-proposal-stage');
        $status = $status_terms && !is_wp_error($status_terms) ? $status_terms[0]->slug : 'processing';

        // Check if user can proceed (only for approved status)
        $can_proceed = ($status === 'approved');

        // Build content with just customer notice (no stage-specific messages)
        $proposal_content = '';
        
        // Show Customer Notice unconditionally
        $customer_notice = \Arsol_Projects_For_Woo\Admin\Setup_Defaults\Setup_Defaults::get_effective_customer_notice($post->ID, 'proposal');
        if (!empty($customer_notice)) {
            $proposal_content .= '<div class="arsol-pfw-customer-notice">
                <div class="arsol-pfw-notice-header">
                    <h4>' . __('Important Notice', 'arsol-pfw') . '</h4>
                </div>
                <div class="arsol-pfw-notice-content">
                    ' . wp_kses_post(wpautop($customer_notice)) . '
                </div>
            </div>';
        }
        
        $proposal_content .= '<div class="arsol-pfw-proposal-details">';
        $proposal_content .= $content;
        $proposal_content .= '</div>';

        return $proposal_content;
    }

    /**
     * Handle project proposal form submission
     */
    public function handle_project_proposal_submission() {
        // Verify nonce
        if ('POST' !== $_SERVER['REQUEST_METHOD'] || !isset($_POST['arsol_project_proposal_nonce']) ||
            !wp_verify_nonce($_POST['arsol_project_proposal_nonce'], 'arsol_submit_project_proposal')) {
            return;
        }

        try {
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
             */
            do_action('arsol_before_proposal_creation_validation', $creation_data);

            // Check if user is logged in
            if (!is_user_logged_in()) {
                throw new Exception(__('You must be logged in to submit a project proposal', 'arsol-pfw'));
            }

            $user_id = get_current_user_id();
            $creation_data['user_id'] = $user_id;

            // Check if user can create project proposals
            if (!\Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_create_project_proposals($user_id)) {
                throw new Exception(__('You do not have permission to create project proposals', 'arsol-pfw'));
            }

            // Sanitize and validate input
            $title = isset($_POST['proposal_title']) ? sanitize_text_field($_POST['proposal_title']) : '';
            $description = isset($_POST['proposal_description']) ? wp_kses_post($_POST['proposal_description']) : '';
            $budget = isset($_POST['proposal_budget']) ? sanitize_text_field($_POST['proposal_budget']) : '';
            $request_id = isset($_POST['request_id']) ? absint($_POST['request_id']) : 0;

            // Validate required fields
            if (empty($title) || empty($description)) {
                throw new Exception(__('Please fill in all required fields', 'arsol-pfw'));
            }

            /**
             * Hook: arsol_after_proposal_creation_validated
             * Fired after validation passes, before proposal creation
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
             */
            $proposal_data = apply_filters('arsol_proposal_creation_args', $proposal_data, $creation_data);

            /**
             * Hook: arsol_before_proposal_creation_post_creation
             * Fired immediately before the proposal post is created
             */
            do_action('arsol_before_proposal_creation_post_creation', $proposal_data, $creation_data);

            $proposal_id = wp_insert_post($proposal_data);

            if (is_wp_error($proposal_id)) {
                /**
                 * Hook: arsol_proposal_creation_post_creation_failed
                 * Fired when proposal creation fails
                 */
                do_action('arsol_proposal_creation_post_creation_failed', $proposal_id, $proposal_data, $creation_data);
                
                throw new Exception($proposal_id->get_error_message());
            }

            $creation_data['proposal_id'] = $proposal_id;

            /**
             * Hook: arsol_after_proposal_creation_post_created
             * Fired after the proposal is successfully created, before status and metadata
             */
            do_action('arsol_after_proposal_creation_post_created', $proposal_id, $proposal_data, $creation_data);

            /**
             * Hook: arsol_before_proposal_creation_status_assignment
             * Fired before setting the proposal status
             */
            do_action('arsol_before_proposal_creation_status_assignment', $proposal_id, 'processing', $creation_data);

            // Set default proposal status to 'processing'
            wp_set_object_terms($proposal_id, 'processing', 'arsol-pfw-proposal-stage');

            /**
             * Hook: arsol_after_proposal_creation_status_assigned
             * Fired after the proposal status is assigned
             */
            do_action('arsol_after_proposal_creation_status_assigned', $proposal_id, 'processing', $creation_data);

            /**
             * Hook: arsol_before_proposal_creation_metadata_save
             * Fired before saving proposal metadata
             */
            do_action('arsol_before_proposal_creation_metadata_save', $proposal_id, $_POST, $creation_data);

            // Save additional metadata if needed
            $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);
            if (!empty($budget)) {
                $proposal->set_proposal_budget(array('amount' => $budget));
            }
            if (!empty($request_id)) {
                $proposal->set_request_id($request_id);
            }
            $proposal->save();

            /**
             * Hook: arsol_after_proposal_creation_metadata_saved
             * Fired after all metadata has been saved
             */
            do_action('arsol_after_proposal_creation_metadata_saved', $proposal_id, $_POST, $creation_data);

            /**
             * Hook: arsol_after_proposal_creation_complete
             * Fired after the proposal creation is complete, before redirect
             */
            do_action('arsol_after_proposal_creation_complete', $proposal_id, $creation_data);

            // Success - redirect to the proposal view
            $redirect_url = wc_get_account_endpoint_url('view-proposal/' . $proposal_id);
            $this->safe_redirect($redirect_url);

        } catch (Exception $e) {
            $this->handle_exception($e, 'handle_project_proposal_submission');
            $this->add_wc_notice($e->getMessage(), 'error');
        }
    }

    /**
     * Customer approve proposal action
     */
    public function customer_approve_proposal() {
        try {
            $this->validate_nonce('_wpnonce', 'arsol_approve_proposal_nonce');

            $proposal_id = intval($_GET['proposal_id']);
            $this->check_user_permissions($proposal_id);

            // Set the proposal status to 'approved' before conversion
            wp_set_object_terms($proposal_id, 'approved', 'arsol-pfw-proposal-stage');
            
            // Use the conversion class to convert proposal to project
            $converter = new \Arsol_Projects_For_Woo\Core\Conversion_Handler();
            $converter->convert_proposal_to_project($proposal_id, true);
            
        } catch (Exception $e) {
            $this->handle_exception($e, 'customer_approve_proposal');
            $this->add_wc_notice($e->getMessage(), 'error');
            
            // Redirect back to referrer or proposal view
            $fallback_url = wc_get_account_endpoint_url('view-proposal/' . (isset($proposal_id) ? $proposal_id : ''));
            $redirect_url = $this->get_safe_redirect_url(wp_get_referer(), $fallback_url);
            $this->safe_redirect($redirect_url);
        }
    }

    /**
     * Customer reject proposal action
     */
    public function customer_reject_proposal() {
        try {
            $this->validate_nonce('_wpnonce', 'arsol_reject_proposal_nonce');

            $proposal_id = intval($_GET['proposal_id']);
            $this->check_user_permissions($proposal_id);

            // Set the proposal status to 'rejected'
            wp_set_object_terms($proposal_id, 'rejected', 'arsol-pfw-proposal-stage');
            
            // Add success notice
            $this->add_wc_notice(__('Proposal has been rejected.', 'arsol-pfw'), 'success');
            
            // Redirect back to referrer
            $this->safe_redirect(wp_get_referer() ?: wc_get_account_endpoint_url('projects'));
            
        } catch (Exception $e) {
            $this->handle_exception($e, 'customer_reject_proposal');
            wp_die($e->getMessage());
        }
    }
} 