<?php

namespace Arsol_Projects_For_Woo\Workflow;

if (!defined('ABSPATH')) {
    exit;
}

class Workflow_Handler {
    public function __construct() {
        // Actions for converting request to proposal
        add_action('admin_post_arsol_convert_to_proposal', array($this, 'convert_request_to_proposal'));

        // Actions for converting proposal to project
        add_action('admin_post_arsol_convert_to_project', array($this, 'convert_proposal_to_project'));

        // Action to set review status when a proposal is published
        add_action('transition_post_status', array($this, 'set_proposal_review_status'), 10, 3);

        // Customer actions
        add_action('admin_post_arsol_cancel_request', array($this, 'customer_cancel_request'));
        add_action('admin_post_arsol_approve_proposal', array($this, 'customer_approve_proposal'));
        add_action('admin_post_arsol_reject_proposal', array($this, 'customer_reject_proposal'));

        // Form submissions
        add_action('admin_post_arsol_create_request', array($this, 'handle_create_request'));
        add_action('admin_post_arsol_edit_request', array($this, 'handle_edit_request'));
    }

    /**
     * Check if a user can view a specific post (project, proposal, or request).
     *
     * @param int $user_id The ID of the user.
     * @param int $post_id The ID of the post.
     * @return bool True if the user can view the post, false otherwise.
     */
    public static function user_can_view_post($user_id, $post_id) {
        if (empty($user_id) || empty($post_id)) {
            return false;
        }
    
        $post = get_post($post_id);
    
        if (!$post) {
            return false;
        }
    
        // Check if the user is the author of the post
        if ((int) $post->post_author === (int) $user_id) {
            return true;
        }
    
        // Fallback to the general project management capability check
        return \Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_manage_projects($user_id);
    }

    public function set_proposal_review_status($new_status, $old_status, $post) {
        if ($post->post_type === 'arsol-pfw-proposal' && $new_status === 'publish' && $old_status !== 'publish') {
            // Set the review status to 'under-review'
            wp_set_object_terms($post->ID, 'under-review', 'arsol-review-status');
        }
    }

    public function convert_request_to_proposal() {
        ob_start();

        // Prepare conversion data for hooks
        $request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;
        $conversion_data = array(
            'request_id' => $request_id,
            'user_id' => get_current_user_id(),
            'conversion_method' => 'admin_conversion',
            'timestamp' => current_time('timestamp')
        );

        /**
         * Hook: arsol_before_proposal_conversion_validation
         * Fired before any validation checks are performed
         * 
         * @param int $request_id The request ID
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_before_proposal_conversion_validation', $request_id, $conversion_data);

        if (!isset($_GET['request_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_convert_to_proposal_nonce')) {
            wp_die(__('Invalid request or nonce.', 'arsol-pfw'));
        }

        $request_id = intval($_GET['request_id']);
        $conversion_data['request_id'] = $request_id;

        if (!current_user_can('edit_post', $request_id) || !current_user_can('publish_posts')) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
        }

        $request_post = get_post($request_id);

        if (!$request_post || $request_post->post_type !== 'arsol-pfw-request') {
            wp_die(__('Invalid request.', 'arsol-pfw'));
        }

        $conversion_data['request_post'] = $request_post;

        // Server-side validation of the request status
        $current_status = wp_get_object_terms($request_id, 'arsol-request-status', array('fields' => 'slugs'));
        if (empty($current_status) || $current_status[0] !== 'under-review') {
            wp_die(__('This request cannot be converted. The status must be "Under Review".', 'arsol-pfw'));
        }

        $conversion_data['request_status'] = $current_status[0];

        /**
         * Hook: arsol_after_proposal_conversion_validated
         * Fired after all validation checks pass, before proposal creation
         * 
         * @param int $request_id The request ID
         * @param WP_Post $request_post The request post object
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_after_proposal_conversion_validated', $request_id, $request_post, $conversion_data);

        // Create proposal args with filter for customization
        $proposal_args = array(
            'post_title'   => $request_post->post_title,
            'post_content' => $request_post->post_content,
            'post_status'  => 'publish',
            'post_type'    => 'arsol-pfw-proposal',
            'post_author'  => $request_post->post_author,
        );

        /**
         * Filter: arsol_proposal_conversion_args
         * Allows modification of proposal creation arguments
         * 
         * @param array $proposal_args The proposal arguments
         * @param int $request_id The request ID
         * @param WP_Post $request_post The request post
         * @param array $conversion_data Conversion context data
         */
        $proposal_args = apply_filters('arsol_proposal_conversion_args', $proposal_args, $request_id, $request_post, $conversion_data);

        /**
         * Hook: arsol_before_proposal_conversion_proposal_creation
         * Fired immediately before the proposal post is created
         * 
         * @param array $proposal_args The proposal arguments that will be used
         * @param int $request_id The request ID
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_before_proposal_conversion_proposal_creation', $proposal_args, $request_id, $conversion_data);

        $new_proposal_id = wp_insert_post($proposal_args);

        if (is_wp_error($new_proposal_id)) {
            /**
             * Hook: arsol_proposal_conversion_proposal_creation_failed
             * Fired when proposal creation fails
             * 
             * @param WP_Error $error The error object
             * @param int $request_id The request ID
             * @param array $proposal_args The proposal arguments that failed
             * @param array $conversion_data Conversion context data
             */
            do_action('arsol_proposal_conversion_proposal_creation_failed', $new_proposal_id, $request_id, $proposal_args, $conversion_data);
            
            wp_die($new_proposal_id->get_error_message());
        }

        $conversion_data['new_proposal_id'] = $new_proposal_id;

        /**
         * Hook: arsol_after_proposal_conversion_proposal_created
         * Fired after the proposal is successfully created, before metadata copy
         * 
         * @param int $new_proposal_id The new proposal ID
         * @param int $request_id The original request ID
         * @param WP_Post $request_post The request post object
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_after_proposal_conversion_proposal_created', $new_proposal_id, $request_id, $request_post, $conversion_data);

        /**
         * Hook: arsol_before_proposal_conversion_metadata_copy
         * Fired before copying metadata from request to proposal
         * 
         * @param int $new_proposal_id The new proposal ID
         * @param int $request_id The request ID
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_before_proposal_conversion_metadata_copy', $new_proposal_id, $request_id, $conversion_data);

        // Store the original request creation date and basic info
        $request_post = get_post($request_id);
        if ($request_post) {
            update_post_meta($new_proposal_id, '_original_request_date', $request_post->post_date);
            update_post_meta($new_proposal_id, '_original_request_title', $request_post->post_title);
            update_post_meta($new_proposal_id, '_original_request_content', $request_post->post_content);
        }

        // Copy relevant meta data from request to proposal, renaming keys as needed
        $meta_to_copy = array(
            '_request_budget'         => '_proposal_budget',
            '_request_start_date'     => '_proposal_start_date',
            '_request_delivery_date'  => '_proposal_delivery_date',
            '_request_attachments'    => '_proposal_attachments',
        );

        /**
         * Filter: arsol_proposal_conversion_meta_mapping
         * Allows modification of the meta key mapping from request to proposal
         * 
         * @param array $meta_to_copy Array of request_key => proposal_key mappings
         * @param int $request_id The request ID
         * @param int $new_proposal_id The new proposal ID
         * @param array $conversion_data Conversion context data
         */
        $meta_to_copy = apply_filters('arsol_proposal_conversion_meta_mapping', $meta_to_copy, $request_id, $new_proposal_id, $conversion_data);

        foreach ($meta_to_copy as $request_key => $proposal_key) {
            $value = get_post_meta($request_id, $request_key, true);
            if ($value) {
                // Copy to the main editable proposal field
                update_post_meta($new_proposal_id, $proposal_key, $value);
                // Also copy to a new field to preserve the original request data for display
                update_post_meta($new_proposal_id, '_original' . $request_key, $value);
            }
        }

        /**
         * Hook: arsol_after_proposal_conversion_metadata_copied
         * Fired after all metadata has been copied from request to proposal
         * 
         * @param int $new_proposal_id The new proposal ID
         * @param int $request_id The request ID
         * @param array $meta_to_copy The meta mapping that was used
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_after_proposal_conversion_metadata_copied', $new_proposal_id, $request_id, $meta_to_copy, $conversion_data);

        /**
         * Hook: arsol_before_proposal_conversion_request_deletion
         * Fired before the original request is deleted
         * 
         * @param int $request_id The request ID about to be deleted
         * @param int $new_proposal_id The new proposal ID
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_before_proposal_conversion_request_deletion', $request_id, $new_proposal_id, $conversion_data);

        // Delete the original request
        wp_delete_post($request_id, true);

        /**
         * Hook: arsol_after_proposal_conversion_complete
         * Fired after the conversion is complete, before redirect
         * 
         * @param int $new_proposal_id The new proposal ID
         * @param int $request_id The original request ID (now deleted)
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_after_proposal_conversion_complete', $new_proposal_id, $request_id, $conversion_data);

        // Clean the output buffer and redirect
        ob_end_clean();

        /**
         * Hook: arsol_before_proposal_conversion_redirect
         * Fired just before redirecting to the new proposal
         * Last chance to modify redirect or add notices
         * 
         * @param int $new_proposal_id The new proposal ID
         * @param string $redirect_url The URL about to redirect to
         * @param array $conversion_data Conversion context data
         */
        $redirect_url = admin_url('post.php?post=' . $new_proposal_id . '&action=edit');
        do_action('arsol_before_proposal_conversion_redirect', $new_proposal_id, $redirect_url, $conversion_data);

        // Redirect to the new proposal's edit screen
        $this->safe_redirect($redirect_url);
    }

    public function convert_proposal_to_project($proposal_id = 0, $is_internal_call = false) {
        ob_start();

        // Get proposal ID first if not provided
        if (empty($proposal_id)) {
            if (!isset($_GET['proposal_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_convert_to_project_nonce')) {
                wp_die(__('Invalid proposal or nonce.', 'arsol-pfw'));
            }
            $proposal_id = intval($_GET['proposal_id']);
        }

        // Foundational check: Prevent double-triggering by checking if conversion is already in progress
        $conversion_lock_key = 'arsol_converting_proposal_' . $proposal_id;
        if (get_transient($conversion_lock_key)) {
            $error_message = __('Conversion already in progress. Please wait.', 'arsol-pfw');
            if ($is_internal_call) {
                if (function_exists('wc_add_notice')) {
                    wc_add_notice($error_message, 'error');
                }
                $this->safe_redirect(wp_get_referer() ?: wc_get_account_endpoint_url('project-view-proposal/' . $proposal_id));
                return;
            } else {
                wp_die($error_message);
            }
        }

        // Set conversion lock (expires in 30 seconds)
        set_transient($conversion_lock_key, true, 30);

        $can_convert = self::user_can_view_post(get_current_user_id(), $proposal_id);
        if (!$is_internal_call) {
            $can_convert = current_user_can('publish_posts');
        }

        if (!$can_convert) {
            delete_transient($conversion_lock_key);
            wp_die(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
        }

        $proposal_post = get_post($proposal_id);

        if (!$proposal_post || $proposal_post->post_type !== 'arsol-pfw-proposal') {
            delete_transient($conversion_lock_key);
            wp_die(__('Invalid proposal.', 'arsol-pfw'));
        }

        if ($proposal_post->post_status !== 'publish') {
            delete_transient($conversion_lock_key);
            wp_die(__('Only published proposals can be converted to projects.', 'arsol-pfw'));
        }

        // Prepare conversion data for hooks
        $conversion_data = array(
            'proposal_id' => $proposal_id,
            'proposal_post' => $proposal_post,
            'is_internal_call' => $is_internal_call,
            'user_id' => get_current_user_id(),
            'conversion_method' => $is_internal_call ? 'customer_approval' : 'admin_conversion'
        );

        /**
         * Hook: arsol_before_project_conversion_validation
         * Fired before any validation checks are performed
         * 
         * @param int $proposal_id The proposal ID
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_before_project_conversion_validation', $proposal_id, $conversion_data);

        /**
         * Hook: arsol_after_project_conversion_validated
         * Fired after all validation checks pass, before project creation
         * 
         * @param int $proposal_id The proposal ID
         * @param WP_Post $proposal_post The proposal post object
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_after_project_conversion_validated', $proposal_id, $proposal_post, $conversion_data);

        // Create project args with filter for customization
        $project_args = array(
            'post_title'   => $proposal_post->post_title,
            'post_content' => $proposal_post->post_content,
            'post_status'  => 'publish', // Projects are published immediately
            'post_type'    => 'arsol-project',
            'post_author'  => $proposal_post->post_author,
        );

        /**
         * Filter: arsol_project_conversion_args
         * Allows modification of project creation arguments
         * 
         * @param array $project_args The project arguments
         * @param int $proposal_id The proposal ID
         * @param WP_Post $proposal_post The proposal post
         * @param array $conversion_data Conversion context data
         */
        $project_args = apply_filters('arsol_project_conversion_args', $project_args, $proposal_id, $proposal_post, $conversion_data);

        /**
         * Hook: arsol_before_project_conversion_project_creation
         * Fired immediately before the project post is created
         * 
         * @param array $project_args The project arguments that will be used
         * @param int $proposal_id The proposal ID
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_before_project_conversion_project_creation', $project_args, $proposal_id, $conversion_data);

        $new_project_id = wp_insert_post($project_args);

        if (is_wp_error($new_project_id)) {
            delete_transient($conversion_lock_key);
            
            /**
             * Hook: arsol_project_conversion_project_creation_failed
             * Fired when project creation fails
             * 
             * @param WP_Error $error The error object
             * @param int $proposal_id The proposal ID
             * @param array $project_args The project arguments that failed
             * @param array $conversion_data Conversion context data
             */
            do_action('arsol_project_conversion_project_creation_failed', $new_project_id, $proposal_id, $project_args, $conversion_data);
            
            if ($is_internal_call) {
                $this->safe_redirect(wp_get_referer() ?: wc_get_account_endpoint_url('project-view-proposal/' . $proposal_id));
            } else {
                wp_die($new_project_id->get_error_message());
            }
        }

        /**
         * Hook: arsol_after_project_conversion_project_created
         * Fired immediately after project is created, before any metadata
         * 
         * @param int $project_id The new project ID
         * @param int $proposal_id The original proposal ID
         * @param WP_Post $proposal_post The proposal post object
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_after_project_conversion_project_created', $new_project_id, $proposal_id, $proposal_post, $conversion_data);

        // Set the project status to 'not-started'
        wp_set_object_terms($new_project_id, 'not-started', 'arsol-project-status');

        /**
         * Hook: arsol_before_project_conversion_metadata_copy
         * Fired before copying metadata from proposal to project
         * 
         * @param int $project_id The new project ID
         * @param int $proposal_id The original proposal ID
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_before_project_conversion_metadata_copy', $new_project_id, $proposal_id, $conversion_data);

        // Copy and rename relevant meta data from proposal to project
        $meta_to_copy = array(
            '_proposal_budget'           => '_project_budget',
            '_proposal_recurring_budget' => '_project_recurring_budget',
            '_proposal_billing_interval' => '_project_billing_interval',
            '_proposal_billing_period'   => '_project_billing_period',
            '_proposal_recurring_start_date' => '_project_recurring_start_date',
            '_proposal_start_date'       => '_proposal_start_date', // Keep for display
            '_proposal_delivery_date'    => '_project_due_date', // Correctly map to due date
        );

        /**
         * Filter: arsol_project_conversion_meta_mapping
         * Allows modification of metadata mapping from proposal to project
         * 
         * @param array $meta_to_copy Array of proposal_key => project_key mappings
         * @param int $project_id The new project ID
         * @param int $proposal_id The original proposal ID
         * @param array $conversion_data Conversion context data
         */
        $meta_to_copy = apply_filters('arsol_project_conversion_meta_mapping', $meta_to_copy, $new_project_id, $proposal_id, $conversion_data);

        foreach ($meta_to_copy as $proposal_key => $project_key) {
            $value = get_post_meta($proposal_id, $proposal_key, true);
            if ($value) {
                update_post_meta($new_project_id, $project_key, $value);
            }
        }

        // Store original proposal ID for reference
        update_post_meta($new_project_id, '_original_proposal_id', $proposal_id);

        /**
         * Hook: arsol_after_project_conversion_metadata_copied
         * Fired after all metadata is copied from proposal to project
         * 
         * @param int $project_id The new project ID
         * @param int $proposal_id The original proposal ID
         * @param array $meta_to_copy The metadata that was copied
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_after_project_conversion_metadata_copied', $new_project_id, $proposal_id, $meta_to_copy, $conversion_data);

        // Capture any order creation errors and created order IDs for rollback
        $order_creation_errors = array();
        $created_order_ids = array();
        
        // Hook into order creation to capture errors and order IDs
        add_action('arsol_proposal_converted_to_project', function($project_id, $proposal_id) use (&$order_creation_errors, &$created_order_ids) {
            // Check for order creation errors after the billers run
            $order_error = get_post_meta($project_id, '_project_order_creation_error', true);
            $subscription_error = get_post_meta($project_id, '_project_subscription_creation_error', true);
            
            if (!empty($order_error)) {
                $order_creation_errors[] = $order_error;
            }
            if (!empty($subscription_error)) {
                $order_creation_errors[] = $subscription_error;
            }
            
            // Collect created order IDs for potential rollback
            $order_note = get_post_meta($project_id, '_project_order_creation_note', true);
            $subscription_note = get_post_meta($project_id, '_project_subscription_creation_note', true);
            
            // Extract order IDs from success notes
            if (!empty($order_note) && preg_match('/#(\d+)/', $order_note, $matches)) {
                $created_order_ids[] = intval($matches[1]);
            }
            if (!empty($subscription_note) && preg_match('/#(\d+)/', $subscription_note, $matches)) {
                $created_order_ids[] = intval($matches[1]);
            }
        }, 20, 2); // Run after the billers (priority 10)

        /**
         * Hook: arsol_before_project_conversion_order_creation
         * Fired before order/subscription creation from proposal
         * 
         * @param int $project_id The new project ID
         * @param int $proposal_id The original proposal ID
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_before_project_conversion_order_creation', $new_project_id, $proposal_id, $conversion_data);

        // Trigger action for order creation (MAIN CONVERSION HOOK - Legacy)
        do_action('arsol_proposal_converted_to_project', $new_project_id, $proposal_id);

        /**
         * Hook: arsol_after_project_conversion_order_creation_attempt
         * Fired after order creation attempt, before error checking
         * 
         * @param int $project_id The new project ID
         * @param int $proposal_id The original proposal ID
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_after_project_conversion_order_creation_attempt', $new_project_id, $proposal_id, $conversion_data);

        // Add debugging to check what happened during order creation
        if (function_exists('wc_get_logger')) {
            $logger = wc_get_logger();
            
            // Check what meta was set on the project
            $order_note = get_post_meta($new_project_id, '_project_order_creation_note', true);
            $order_error = get_post_meta($new_project_id, '_project_order_creation_error', true);
            $subscription_note = get_post_meta($new_project_id, '_project_subscription_creation_note', true);
            $subscription_error = get_post_meta($new_project_id, '_project_subscription_creation_error', true);
            
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_conversion('info',
                sprintf('Conversion results for project #%d from proposal #%d:', $new_project_id, $proposal_id));
            
            if (!empty($order_note)) {
                \Arsol_Projects_For_Woo\Woocommerce_Logs::log_conversion('info', 'Order creation note: ' . $order_note);
            }
            if (!empty($order_error)) {
                \Arsol_Projects_For_Woo\Woocommerce_Logs::log_conversion('error', 'Order creation error: ' . $order_error);
            }
            if (!empty($subscription_note)) {
                \Arsol_Projects_For_Woo\Woocommerce_Logs::log_conversion('info', 'Subscription creation note: ' . $subscription_note);
            }
            if (!empty($subscription_error)) {
                \Arsol_Projects_For_Woo\Woocommerce_Logs::log_conversion('error', 'Subscription creation error: ' . $subscription_error);
            }
            
            // Check proposal data
            $cost_proposal_type = get_post_meta($proposal_id, '_cost_proposal_type', true);
            $line_items = get_post_meta($proposal_id, '_arsol_proposal_line_items', true);
            
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_conversion('info',
                sprintf('Proposal #%d details - Type: %s, Line items: %s', 
                    $proposal_id, 
                    $cost_proposal_type, 
                    !empty($line_items) ? 'present' : 'missing'));
            
            if (!empty($line_items)) {
                \Arsol_Projects_For_Woo\Woocommerce_Logs::log_conversion('info',
                    sprintf('Line items structure: %s', wp_json_encode(array_keys($line_items))));
            }
        }

        // Check if there were any order creation errors
        if (!empty($order_creation_errors)) {
            /**
             * Hook: arsol_before_project_conversion_rollback
             * Fired before rollback due to order creation failure
             * 
             * @param int $project_id The project ID that will be deleted
             * @param int $proposal_id The original proposal ID
             * @param array $order_creation_errors Array of error messages
             * @param array $created_order_ids Array of order IDs to be deleted
             * @param array $conversion_data Conversion context data
             */
            do_action('arsol_before_project_conversion_rollback', $new_project_id, $proposal_id, $order_creation_errors, $created_order_ids, $conversion_data);
            
            // Order creation failed - rollback everything
            
            // Delete any created orders first
            foreach ($created_order_ids as $order_id) {
                $order = wc_get_order($order_id);
                if ($order) {
                    // Force delete the order (bypass trash)
                    wp_delete_post($order_id, true);
                    
                    // Log the rollback for debugging
                    \Arsol_Projects_For_Woo\Woocommerce_Logs::log_conversion('info',
                        sprintf('Rolled back order #%d due to conversion failure', $order_id));
                }
            }
            
            // Delete the project
            wp_delete_post($new_project_id, true);
            
            // Clear the conversion lock
            delete_transient($conversion_lock_key);
            
            /**
             * Hook: arsol_after_project_conversion_rollback
             * Fired after rollback is completed
             * 
             * @param int $proposal_id The original proposal ID (still exists)
             * @param array $order_creation_errors Array of error messages
             * @param array $conversion_data Conversion context data
             */
            do_action('arsol_after_project_conversion_rollback', $proposal_id, $order_creation_errors, $conversion_data);
            
            $error_message = sprintf(
                __('Failed to create orders from proposal. Errors: %s', 'arsol-pfw'),
                implode('; ', $order_creation_errors)
            );
            
            if ($is_internal_call) {
                // For internal calls, add error notice and redirect back
                if (function_exists('wc_add_notice')) {
                    wc_add_notice($error_message, 'error');
                }
                $this->safe_redirect(wp_get_referer() ?: wc_get_account_endpoint_url('project-view-proposal/' . $proposal_id));
                return;
            } else {
                // For admin calls, show error and stop
                wp_die($error_message);
            }
        }

        /**
         * Hook: arsol_before_project_conversion_proposal_deletion
         * Fired before the original proposal is deleted (conversion successful)
         * 
         * @param int $proposal_id The proposal ID that will be deleted
         * @param int $project_id The new project ID
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_before_project_conversion_proposal_deletion', $proposal_id, $new_project_id, $conversion_data);

        // If we get here, both project and orders were created successfully
        // Now it's safe to delete the original proposal
        wp_delete_post($proposal_id, true);

        /**
         * Hook: arsol_after_project_conversion_complete
         * Fired after successful conversion completion (proposal deleted, project active)
         * 
         * @param int $project_id The new project ID
         * @param int $proposal_id The original proposal ID (now deleted)
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_after_project_conversion_complete', $new_project_id, $proposal_id, $conversion_data);

        // Clear the conversion lock
        delete_transient($conversion_lock_key);

        // Clean the output buffer and redirect
        ob_end_clean();

        /**
         * Hook: arsol_before_project_conversion_redirect
         * Fired before redirect after successful conversion
         * 
         * @param int $project_id The new project ID
         * @param int $proposal_id The original proposal ID (deleted)
         * @param array $conversion_data Conversion context data
         */
        do_action('arsol_before_project_conversion_redirect', $new_project_id, $proposal_id, $conversion_data);

        // Redirect based on how the function was called
        if ($is_internal_call) {
            $this->safe_redirect(wc_get_account_endpoint_url('project-overview/' . $new_project_id));
        } else {
            $this->safe_redirect(admin_url('post.php?post=' . $new_project_id . '&action=edit'));
        }
    }

    public function customer_cancel_request() {
        if (!isset($_GET['request_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_cancel_request_nonce')) {
            wp_die(__('Invalid request or nonce.', 'arsol-pfw'));
        }

        $request_id = intval($_GET['request_id']);
        if (self::user_can_view_post(get_current_user_id(), $request_id)) {
            wp_delete_post($request_id, true); // Delete the request
            $this->safe_redirect(wc_get_account_endpoint_url('projects')); // Redirect to projects page
        } else {
            wp_die(__('You do not have permission to cancel this request.', 'arsol-pfw'));
        }
    }

    public function customer_approve_proposal() {
        if (!isset($_GET['proposal_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_approve_proposal_nonce')) {
            wp_die(__('Invalid proposal or nonce.', 'arsol-pfw'));
        }

        $proposal_id = intval($_GET['proposal_id']);
        if (self::user_can_view_post(get_current_user_id(), $proposal_id)) {
            // Set the proposal status to 'approved' before conversion
            wp_set_object_terms($proposal_id, 'approved', 'arsol-review-status');
            
            // Re-use the conversion logic
            $this->convert_proposal_to_project($proposal_id, true);
        } else {
            wp_die(__('You do not have permission to approve this proposal.', 'arsol-pfw'));
        }
    }

    public function customer_reject_proposal() {
        if (!isset($_GET['proposal_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_reject_proposal_nonce')) {
            wp_die(__('Invalid proposal or nonce.', 'arsol-pfw'));
        }

        $proposal_id = intval($_GET['proposal_id']);
        if (self::user_can_view_post(get_current_user_id(), $proposal_id)) {
            wp_set_object_terms($proposal_id, 'rejected', 'arsol-review-status');
            $this->safe_redirect(wp_get_referer());
        } else {
            wp_die(__('You do not have permission to reject this proposal.', 'arsol-pfw'));
        }
    }

    public function handle_create_request() {
        // Prepare creation data for hooks
        $creation_data = array(
            'user_id' => get_current_user_id(),
            'creation_method' => 'frontend_form',
            'timestamp' => current_time('timestamp'),
            'form_data' => $_POST
        );

        /**
         * Hook: arsol_before_request_creation_validation
         * Fired before any validation checks are performed
         * 
         * @param array $creation_data Creation context data
         */
        do_action('arsol_before_request_creation_validation', $creation_data);

        if (!wp_verify_nonce($_POST['arsol_request_nonce'], 'arsol_create_request')) {
            wp_die(__('Invalid nonce.', 'arsol-pfw'));
        }

        /**
         * Hook: arsol_after_request_creation_validated
         * Fired after validation passes, before request creation
         * 
         * @param array $creation_data Creation context data
         */
        do_action('arsol_after_request_creation_validated', $creation_data);

        // Prepare request args with filter for customization
        $post_data = array(
            'post_title'   => sanitize_text_field($_POST['request_title']),
            'post_content' => wp_kses_post($_POST['request_description']),
            'post_status'  => 'publish',
            'post_type'    => 'arsol-pfw-request',
            'post_author'  => get_current_user_id(),
        );

        /**
         * Filter: arsol_request_creation_args
         * Allows modification of request creation arguments
         * 
         * @param array $post_data The request arguments
         * @param array $creation_data Creation context data
         */
        $post_data = apply_filters('arsol_request_creation_args', $post_data, $creation_data);

        /**
         * Hook: arsol_before_request_creation_post_creation
         * Fired immediately before the request post is created
         * 
         * @param array $post_data The request arguments that will be used
         * @param array $creation_data Creation context data
         */
        do_action('arsol_before_request_creation_post_creation', $post_data, $creation_data);

        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            /**
             * Hook: arsol_request_creation_post_creation_failed
             * Fired when request creation fails
             * 
             * @param WP_Error $error The error object
             * @param array $post_data The request arguments that failed
             * @param array $creation_data Creation context data
             */
            do_action('arsol_request_creation_post_creation_failed', $post_id, $post_data, $creation_data);
            
            $this->safe_redirect(wc_get_account_endpoint_url('project-create-request'));
        }

        $creation_data['request_id'] = $post_id;

        /**
         * Hook: arsol_after_request_creation_post_created
         * Fired after the request is successfully created, before metadata and status
         * 
         * @param int $post_id The new request ID
         * @param array $post_data Request post data
         * @param array $creation_data Creation context data
         */
        do_action('arsol_after_request_creation_post_created', $post_id, $post_data, $creation_data);

        /**
         * Hook: arsol_before_request_creation_status_assignment
         * Fired before setting the request status
         * 
         * @param int $post_id The request ID
         * @param string $default_status The default status to be assigned
         * @param array $creation_data Creation context data
         */
        do_action('arsol_before_request_creation_status_assignment', $post_id, 'pending', $creation_data);

        wp_set_object_terms($post_id, 'pending', 'arsol-request-status');

        /**
         * Hook: arsol_after_request_creation_status_assigned
         * Fired after the request status is assigned
         * 
         * @param int $post_id The request ID
         * @param string $assigned_status The status that was assigned
         * @param array $creation_data Creation context data
         */
        do_action('arsol_after_request_creation_status_assigned', $post_id, 'pending', $creation_data);

        /**
         * Hook: arsol_before_request_creation_metadata_save
         * Fired before saving request metadata
         * 
         * @param int $post_id The request ID
         * @param array $form_data The form data to be processed
         * @param array $creation_data Creation context data
         */
        do_action('arsol_before_request_creation_metadata_save', $post_id, $_POST, $creation_data);

        $this->update_request_meta($post_id, $_POST);

        /**
         * Hook: arsol_after_request_creation_metadata_saved
         * Fired after all metadata has been saved
         * 
         * @param int $post_id The request ID
         * @param array $form_data The form data that was processed
         * @param array $creation_data Creation context data
         */
        do_action('arsol_after_request_creation_metadata_saved', $post_id, $_POST, $creation_data);

        /**
         * Hook: arsol_after_request_creation_complete
         * Fired after the request creation is complete, before redirect
         * 
         * @param int $post_id The new request ID
         * @param array $creation_data Creation context data
         */
        do_action('arsol_after_request_creation_complete', $post_id, $creation_data);

        /**
         * Hook: arsol_before_request_creation_redirect
         * Fired just before redirecting to the new request
         * Last chance to modify redirect or add notices
         * 
         * @param int $post_id The new request ID
         * @param string $redirect_url The URL about to redirect to
         * @param array $creation_data Creation context data
         */
        $redirect_url = wc_get_account_endpoint_url('project-view-request/' . $post_id);
        do_action('arsol_before_request_creation_redirect', $post_id, $redirect_url, $creation_data);

        $this->safe_redirect($redirect_url);
    }

    public function handle_edit_request() {
        // Fixed namespace issues for WooCommerce functions
        if (!wp_verify_nonce($_POST['arsol_request_nonce'], 'arsol_edit_request')) {
            wp_die(__('Invalid nonce.', 'arsol-pfw'));
        }

        $post_id = intval($_POST['request_id']);

        // Verify user has permission to edit
        if (!self::user_can_view_post(get_current_user_id(), $post_id)) {
            wp_die(__('You do not have permission to edit this request.', 'arsol-pfw'));
        }

        $post_data = array(
            'ID'           => $post_id,
            'post_title'   => sanitize_text_field($_POST['request_title']),
            'post_content' => wp_kses_post($_POST['request_description']),
        );
        $result = wp_update_post($post_data);



        $this->update_request_meta($post_id, $_POST);
        
        $this->safe_redirect(wc_get_account_endpoint_url('project-view-request/' . $post_id));
    }

    private function update_request_meta($post_id, $data) {
        if (isset($data['request_budget'])) {
            $amount = wc_clean(wp_unslash($data['request_budget']));
            $currency = get_woocommerce_currency();
            update_post_meta($post_id, '_request_budget', ['amount' => $amount, 'currency' => $currency]);
        }
        if (isset($data['request_start_date'])) {
            update_post_meta($post_id, '_request_start_date', sanitize_text_field($data['request_start_date']));
        }
        if (isset($data['request_delivery_date'])) {
            update_post_meta($post_id, '_request_delivery_date', sanitize_text_field($data['request_delivery_date']));
        }
    }

    /**
     * Safe redirect that handles "headers already sent" issues
     */
    private function safe_redirect($url) {
        if (headers_sent()) {
            // If headers are already sent, use JavaScript redirect
            echo '<script type="text/javascript">window.location.href="' . esc_url($url) . '";</script>';
            echo '<noscript><meta http-equiv="refresh" content="0;url=' . esc_url($url) . '" /></noscript>';
            exit;
        } else {
            // Use normal redirect
            wp_safe_redirect($url);
            exit;
        }
    }

    /**
     * Debug function to test proposal conversion
     * Call this function manually to test conversion logic
     * 
     * @param int $proposal_id The proposal ID to test
     * @return array Debug information
     */
    public static function debug_proposal_conversion($proposal_id) {
        $debug_info = array();
        
        // Check proposal exists
        $proposal = get_post($proposal_id);
        $debug_info['proposal_exists'] = !empty($proposal);
        $debug_info['proposal_type'] = $proposal ? $proposal->post_type : 'N/A';
        $debug_info['proposal_status'] = $proposal ? $proposal->post_status : 'N/A';
        $debug_info['proposal_author'] = $proposal ? $proposal->post_author : 'N/A';
        
        // Check cost proposal type
        $cost_proposal_type = get_post_meta($proposal_id, '_cost_proposal_type', true);
        $debug_info['cost_proposal_type'] = $cost_proposal_type;
        $debug_info['should_create_orders'] = ($cost_proposal_type === 'invoice_line_items');
        
        // Check line items
        $line_items = get_post_meta($proposal_id, '_arsol_proposal_line_items', true);
        $debug_info['has_line_items'] = !empty($line_items);
        $debug_info['line_items_structure'] = !empty($line_items) ? array_keys($line_items) : array();
        
        if (!empty($line_items)) {
            $debug_info['products_count'] = !empty($line_items['products']) ? count($line_items['products']) : 0;
            $debug_info['one_time_fees_count'] = !empty($line_items['one_time_fees']) ? count($line_items['one_time_fees']) : 0;
            $debug_info['recurring_fees_count'] = !empty($line_items['recurring_fees']) ? count($line_items['recurring_fees']) : 0;
            $debug_info['shipping_fees_count'] = !empty($line_items['shipping_fees']) ? count($line_items['shipping_fees']) : 0;
        }
        
        // Check if customer exists
        if ($proposal) {
            $customer = new \WC_Customer($proposal->post_author);
            $debug_info['customer_exists'] = $customer && $customer->get_id();
            $debug_info['customer_email'] = $customer ? $customer->get_billing_email() : 'N/A';
        }
        
        // Check WooCommerce Subscriptions
        $debug_info['wc_subscriptions_active'] = class_exists('WC_Subscriptions') && function_exists('wcs_create_subscription');
        
        return $debug_info;
    }
}