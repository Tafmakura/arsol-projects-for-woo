<?php
/**
 * Project Request Frontend Handler Class
 *
 * Handles frontend project request submissions and customer actions.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Frontend;

use Exception;

if (!defined('ABSPATH')) {
    exit;
}

class Request_Frontend extends Frontend_Handler {
    
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
        add_action('template_redirect', array($this, 'customer_create_request'));
        add_filter('the_content', array($this, 'display_request_content'));
    }

    /**
     * Display request content with status messages
     */
    public function display_request_content($content) {
        if (!is_singular('arsol-pfw-request') || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        global $post;
        $current_user_id = get_current_user_id();
        $post_author_id = $post->post_author;

        // Check if current user is the author
        if ($current_user_id !== $post_author_id) {
            ob_start();
            \Arsol_Projects_For_Woo\Core\Access_Handler::display_no_access_template('request', array(
                'request_id' => $post->ID
            ));
            return ob_get_clean();
        }

        // Get request status
        $status_terms = get_the_terms($post->ID, 'arsol-pfw-request-stage');
        $status = $status_terms && !is_wp_error($status_terms) ? $status_terms[0]->slug : 'pending-review';

        // Display status-specific messages in content section
        $status_message = '';
        switch ($status) {
            case 'pending-review':
                $status_message = '<div class="arsol-pfw-notice arsol-pfw-notice-info">
                    <p><strong>Status:</strong> Your request is pending review. We will review it shortly and get back to you.</p>
                </div>';
                break;
                
            case 'under-review':
                $status_message = '<div class="arsol-pfw-notice arsol-pfw-notice-warning">
                    <p><strong>Status:</strong> Your request is currently under review. We are evaluating the details and will update you soon.</p>
                </div>';
                break;
                
            case 'on-hold':
                $status_message = '<div class="arsol-pfw-notice arsol-pfw-notice-warning">
                    <p><strong>Status:</strong> Your request is currently on hold. We may need additional information or are waiting for resources to become available.</p>
                </div>';
                break;
                
            case 'approved':
                $status_message = '<div class="arsol-pfw-notice arsol-pfw-notice-success">
                    <p><strong>Status:</strong> Great news! Your request has been approved and a proposal will be created for you shortly.</p>
                </div>';
                break;
        }

        // Check if user can proceed (only for approved status)
        $can_proceed = ($status === 'approved');
        
        if (!$can_proceed && $status !== 'approved') {
            $status_message .= '<div class="arsol-pfw-notice arsol-pfw-notice-info">
                <p><strong>Cannot Proceed:</strong> You cannot proceed to the next step until your request has been approved.</p>
            </div>';
        }

        // Build content with status messages
        $request_content = $status_message;
        $request_content .= '<div class="arsol-pfw-request-details">';
        $request_content .= $content;
        $request_content .= '</div>';

        return $request_content;
    }

    /**
     * Handle customer request creation (renamed from handle_create_request_submission)
     */
    public function customer_create_request() {
        if ('POST' !== $_SERVER['REQUEST_METHOD'] || !isset($_POST['create_request_nonce']) || !wp_verify_nonce($_POST['create_request_nonce'], 'create_request')) {
            return;
        }

        try {
            // Check if user can create requests
            $user_id = get_current_user_id();
            $user = get_user_by('id', $user_id);
            $can_create = $user && ($user->has_cap('edit_arsol_pfw_requests') || $user->has_cap('arsol_pfw_manage'));

            if (!$can_create) {
                throw new Exception(__('You do not have permission to create project requests. Please contact the administrator if you believe this is an error.', 'arsol-pfw'));
            }
            
            $title = sanitize_text_field($_POST['request_title']);
            $description = wp_kses_post($_POST['request_description']);
            $requested_budget = isset($_POST['requested_budget']) ? sanitize_text_field($_POST['requested_budget']) : '';
            $requested_start_date = isset($_POST['requested_start_date']) ? sanitize_text_field($_POST['requested_start_date']) : '';
            $requested_due_date = isset($_POST['requested_due_date']) ? sanitize_text_field($_POST['requested_due_date']) : '';
            
            // Prepare creation data for hooks
            $creation_data = array(
                'user_id' => $user_id,
                'creation_method' => 'frontend_form',
                'timestamp' => current_time('timestamp'),
                'form_data' => $_POST
            );

            /**
             * Hook: arsol_before_request_creation_validation
             * Fired before any validation checks are performed
             */
            do_action('arsol_before_request_creation_validation', $creation_data);

            /**
             * Hook: arsol_after_request_creation_validated
             * Fired after validation passes, before request creation
             */
            do_action('arsol_after_request_creation_validated', $creation_data);

            // Create project request post
            $request_data = array(
                'post_title'    => $title,
                'post_content'  => $description,
                'post_status'   => 'publish',
                'post_type'     => 'arsol-pfw-request',
                'post_author'   => $user_id
            );

            /**
             * Filter: arsol_request_creation_args
             * Allows modification of request creation arguments
             */
            $request_data = apply_filters('arsol_request_creation_args', $request_data, $creation_data);

            /**
             * Hook: arsol_before_request_creation_post_creation
             * Fired immediately before the request post is created
             */
            do_action('arsol_before_request_creation_post_creation', $request_data, $creation_data);
            
            $request_id = wp_insert_post($request_data);
            
            if (is_wp_error($request_id)) {
                /**
                 * Hook: arsol_request_creation_post_creation_failed
                 * Fired when request creation fails
                 */
                do_action('arsol_request_creation_post_creation_failed', $request_id, $request_data, $creation_data);
                
                throw new Exception($request_id->get_error_message());
            }

            $creation_data['request_id'] = $request_id;

            /**
             * Hook: arsol_after_request_creation_post_created
             * Fired after the request is successfully created, before metadata and status
             */
            do_action('arsol_after_request_creation_post_created', $request_id, $request_data, $creation_data);

            /**
             * Hook: arsol_before_request_creation_status_assignment
             * Fired before setting the request status
             */
            do_action('arsol_before_request_creation_status_assignment', $request_id, 'pending-review', $creation_data);

            // Set default request stage using the stage entity
            $request_entity = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($request_id);
            $request_entity->set_stage('pending-review');

            /**
             * Hook: arsol_after_request_creation_status_assigned
             * Fired after the request status is assigned
             */
            do_action('arsol_after_request_creation_status_assigned', $request_id, 'pending-review', $creation_data);

            /**
             * Hook: arsol_before_request_creation_metadata_save
             * Fired before saving request metadata
             */
            do_action('arsol_before_request_creation_metadata_save', $request_id, $_POST, $creation_data);
            
            // Save additional request meta
            $this->update_request_meta($request_id, $_POST);

            /**
             * Hook: arsol_after_request_creation_metadata_saved
             * Fired after all metadata has been saved
             */
            do_action('arsol_after_request_creation_metadata_saved', $request_id, $_POST, $creation_data);

            /**
             * Hook: arsol_after_request_creation_complete
             * Fired after the request creation is complete, before redirect
             */
            do_action('arsol_after_request_creation_complete', $request_id, $creation_data);
            
            // Redirect to the specific request view
            $redirect_url = wc_get_account_endpoint_url('view-request/' . $request_id);
            $this->safe_redirect($redirect_url);
            
        } catch (Exception $e) {
            $this->handle_exception($e, 'customer_create_request');
            $this->add_wc_notice($e->getMessage(), 'error');
            $this->safe_redirect(wc_get_account_endpoint_url('projects'));
        }
    }

    /**
     * Customer edit request action
     */
    public function customer_edit_request() {
        try {
            // Prepare edit data for hooks
            $edit_data = array(
                'user_id' => get_current_user_id(),
                'edit_method' => 'frontend_form',
                'timestamp' => current_time('timestamp'),
                'form_data' => $_POST
            );

            /**
             * Hook: arsol_before_request_edit_validation
             * Fired before any validation checks are performed
             */
            do_action('arsol_before_request_edit_validation', $edit_data);

            if (!wp_verify_nonce($_POST['arsol_request_edit_nonce'], 'arsol_edit_request')) {
                throw new Exception(__('Invalid nonce.', 'arsol-pfw'));
            }

            $request_id = intval($_POST['request_id']);
            $this->check_user_permissions($request_id);

            /**
             * Hook: arsol_after_request_edit_validated
             * Fired after validation passes, before request editing
             */
            do_action('arsol_after_request_edit_validated', $request_id, $edit_data);

            // Update request post
            $post_data = array(
                'ID' => $request_id,
                'post_title' => sanitize_text_field($_POST['request_title']),
                'post_content' => wp_kses_post($_POST['request_description']),
            );

            /**
             * Filter: arsol_request_edit_args
             * Allows modification of request edit arguments
             */
            $post_data = apply_filters('arsol_request_edit_args', $post_data, $edit_data);

            /**
             * Hook: arsol_before_request_edit_post_update
             * Fired immediately before the request post is updated
             */
            do_action('arsol_before_request_edit_post_update', $post_data, $edit_data);

            $update_result = wp_update_post($post_data);

            if (is_wp_error($update_result)) {
                /**
                 * Hook: arsol_request_edit_post_update_failed
                 * Fired when request editing fails
                 */
                do_action('arsol_request_edit_post_update_failed', $update_result, $post_data, $edit_data);
                
                throw new Exception($update_result->get_error_message());
            }

            $edit_data['request_id'] = $request_id;

            /**
             * Hook: arsol_after_request_edit_post_updated
             * Fired after the request is successfully updated, before metadata
             */
            do_action('arsol_after_request_edit_post_updated', $request_id, $post_data, $edit_data);

            /**
             * Hook: arsol_before_request_edit_metadata_save
             * Fired before saving request metadata
             */
            do_action('arsol_before_request_edit_metadata_save', $request_id, $_POST, $edit_data);

            // Update request metadata
            $this->update_request_meta($request_id, $_POST);

            /**
             * Hook: arsol_after_request_edit_metadata_saved
             * Fired after all metadata has been saved
             */
            do_action('arsol_after_request_edit_metadata_saved', $request_id, $_POST, $edit_data);

            /**
             * Hook: arsol_after_request_edit_complete
             * Fired after the request editing is complete, before redirect
             */
            do_action('arsol_after_request_edit_complete', $request_id, $edit_data);

            // Redirect to request view
            $redirect_url = wc_get_account_endpoint_url('view-request/' . $request_id);
            $this->safe_redirect($redirect_url);

        } catch (Exception $e) {
            $this->handle_exception($e, 'customer_edit_request');
            $this->add_wc_notice($e->getMessage(), 'error');
            $this->safe_redirect(wc_get_account_endpoint_url('projects'));
        }
    }

    /**
     * Customer cancel request action
     */
    public function customer_cancel_request() {
        try {
            $this->validate_nonce('_wpnonce', 'arsol_cancel_request_nonce');

            $request_id = intval($_GET['request_id']);
            $this->check_user_permissions($request_id);

            // Delete the request
            wp_delete_post($request_id, true);
            
            // Redirect to the requests list tab instead of general projects page
            $requests_url = add_query_arg('tab', 'requests', wc_get_account_endpoint_url('projects'));
            $this->safe_redirect($requests_url);
            
        } catch (Exception $e) {
            $this->handle_exception($e, 'customer_cancel_request');
            wp_die($e->getMessage());
        }
    }

    /**
     * Update request metadata
     */
    private function update_request_meta($post_id, $data) {
        // Get request object to use setter methods
        $request = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($post_id);
        
        if (!empty($data['requested_budget'])) {
            // Clean and format budget amount
            $amount = \Arsol_Projects_For_Woo\Integrations\WooCommerce\Integration::clean_amount_input($data['requested_budget']);
            $amount = floatval(wc_format_decimal($amount));
            $currency = get_woocommerce_currency();
            
            if ($amount > 0) {
                $budget_data = array(
                    'amount' => $amount,
                    'currency' => $currency,
                    'currency_symbol' => get_woocommerce_currency_symbol($currency)
                );
                $request->set_requested_project_budget($budget_data);
            }
        }
        
        if (!empty($data['requested_start_date'])) {
            $request->set_requested_project_start_date(sanitize_text_field($data['requested_start_date']));
        }
        
        if (!empty($data['requested_due_date'])) {
            $request->set_requested_project_due_date(sanitize_text_field($data['requested_due_date']));
        }
        
        if (!empty($data['request_project_lead'])) {
            $request->set_project_lead(sanitize_text_field($data['request_project_lead']));
        }

        // Save any additional custom fields
        $custom_fields = array(
            '_arsol_pfw_requested_project_budget' => 'requested_budget',
            '_arsol_pfw_requested_project_start_date' => 'requested_start_date',
            '_arsol_pfw_requested_project_due_date' => 'requested_due_date',
        );

        foreach ($custom_fields as $meta_key => $form_field) {
            if (!empty($data[$form_field])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($data[$form_field]));
            }
        }
    }
} 