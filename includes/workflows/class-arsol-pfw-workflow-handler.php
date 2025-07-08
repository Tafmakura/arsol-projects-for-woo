<?php

namespace Arsol_Projects_For_Woo\Workflow;

use Exception;

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

        // Admin notice display
        add_action('admin_notices', array($this, 'display_conversion_notices'));
    }

    /**
     * Check if a user can view a specific post (project, proposal, or request).
     *
     * @param int $user_id The ID of the user.
     * @param int $post_id The ID of the post.
     * @return bool True if the user can view the post, false otherwise.
     */
    public static function user_can_view_post($user_id, $post_id) {
        // This method is now deprecated - use \Arsol_Projects_For_Woo\Core\Permissions::user_can_view_post() instead
        return \Arsol_Projects_For_Woo\Core\Permissions::user_can_view_post($user_id, $post_id);
    }

    public function set_proposal_review_status($new_status, $old_status, $post) {
        // Removed automatic review status setting - using proposal status only
        // if ($post->post_type === 'arsol-pfw-proposal' && $new_status === 'publish' && $old_status !== 'publish') {
        // Set proposal to pending-approval status when published');
        // }
    }

    public function convert_request_to_proposal() {
        $request_id = intval($_GET['request_id']);
        $request_post = null;
        
        try {
            // Get source details
            $request_post = get_post($request_id);
            if (!$request_post || $request_post->post_type !== 'arsol-pfw-request') {
                throw new Exception(__('Invalid request.', 'arsol-pfw'));
            }

            // Security validation
        if (!isset($_GET['request_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_convert_to_proposal_nonce')) {
                throw new Exception(__('Invalid request or nonce.', 'arsol-pfw'));
        }

        if (!current_user_can('edit_post', $request_id) || !current_user_can('publish_posts')) {
                throw new Exception(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
            }
            
            // Prevent concurrent conversions and handle stuck workflows
            if ($this->is_workflow_in_progress($request_id)) {
                // Check if this is a stuck workflow (older than 5 minutes)
                $workflow_started = get_post_meta($request_id, '_arsol_workflow_started', true);
                $is_stuck = false;
                
                if ($workflow_started) {
                    $started_time = strtotime($workflow_started);
                    $current_time = current_time('timestamp');
                    $age_minutes = ($current_time - $started_time) / 60;
                    
                    if ($age_minutes > 0.5) { // 30 seconds
                        $is_stuck = true;
                        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('warning', 
                            "Detected stuck workflow for request #{$request_id}, age: {$age_minutes} minutes. Auto-clearing...");
                    }
                }
                
                if ($is_stuck) {
                    // Force clear the stuck workflow
                    $this->force_clear_stuck_workflow($request_id);
                    
                    \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                        "Successfully cleared stuck workflow for request #{$request_id}. Proceeding with conversion...");
                } else {
                throw new Exception(__('Conversion already in progress.', 'arsol-pfw'));
                }
            }
            
            // Start transaction with logging
            $this->start_workflow_transaction($request_id, 'conversion', 'request_to_proposal');
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_request_to_proposal_conversion('info', 
                "Starting conversion: Request #{$request_id} to Proposal");
            
            // Validation step
            update_post_meta($request_id, '_arsol_conversion_step', 'validation');

            // Server-side validation of the request stage
            $current_stage = wp_get_object_terms($request_id, 'arsol-pfw-request-stage', array('fields' => 'slugs'));
            if (empty($current_stage) || $current_stage[0] !== 'approved') {
                throw new Exception(sprintf(
                    __('This request cannot be converted. The stage is "%s", must be "approved".', 'arsol-pfw'),
                    empty($current_stage) ? 'none' : $current_stage[0]
                ));
            }

            // Prepare conversion data for hooks
            $conversion_data = array(
                'request_id' => $request_id,
                'user_id' => get_current_user_id(),
                'conversion_method' => 'admin_conversion',
                'timestamp' => current_time('timestamp'),
                'request_post' => $request_post,
                'request_stage' => $current_stage[0]
            );

            /**
             * Hook: arsol_before_proposal_conversion_validation
             * Fired before any validation checks are performed
             */
            do_action('arsol_before_proposal_conversion_validation', $request_id, $conversion_data);

        /**
         * Hook: arsol_after_proposal_conversion_validated
         * Fired after all validation checks pass, before proposal creation
         */
        do_action('arsol_after_proposal_conversion_validated', $request_id, $request_post, $conversion_data);
            
            // Creation step
            update_post_meta($request_id, '_arsol_conversion_step', 'creation');

        // Create proposal args with filter for customization
        $proposal_args = array(
            'post_title'   => $request_post->post_title,
            'post_content' => '', // ✅ CLEAN CONTENT FLOW: Empty slate for proposal writing
            'post_status'  => 'publish',
            'post_type'    => 'arsol-pfw-proposal',
            'post_author'  => $request_post->post_author,
        );

        /**
         * Filter: arsol_proposal_conversion_args
         * Allows modification of proposal creation arguments
         */
        $proposal_args = apply_filters('arsol_proposal_conversion_args', $proposal_args, $request_id, $request_post, $conversion_data);

        /**
         * Hook: arsol_before_proposal_conversion_proposal_creation
         * Fired immediately before the proposal post is created
         */
        do_action('arsol_before_proposal_conversion_proposal_creation', $proposal_args, $request_id, $conversion_data);

        $new_proposal_id = wp_insert_post($proposal_args);
        if (is_wp_error($new_proposal_id)) {
                throw new Exception($new_proposal_id->get_error_message());
            }
            
            // Record created entity
            $this->record_transaction_entity($request_id, $new_proposal_id);
        $conversion_data['new_proposal_id'] = $new_proposal_id;

        /**
         * Hook: arsol_after_proposal_conversion_proposal_created
         * Fired after the proposal is successfully created, before metadata copy
         */
        do_action('arsol_after_proposal_conversion_proposal_created', $new_proposal_id, $request_id, $request_post, $conversion_data);
            
            // Metadata copy step
            update_post_meta($request_id, '_arsol_conversion_step', 'metadata_copy');
            
            // Copy metadata from request to proposal
            $this->copy_request_metadata_to_proposal($request_id, $new_proposal_id);

            /**
             * Hook: arsol_after_proposal_conversion_complete
             * Fired after the conversion is complete, before cleanup
             */
            do_action('arsol_after_proposal_conversion_complete', $new_proposal_id, $request_id, $conversion_data);
            
            // Complete transaction
            $this->complete_workflow_transaction($request_id);
            
            // Log success
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_request_to_proposal_conversion('success', 
                "Conversion completed: Request #{$request_id} → Proposal #{$new_proposal_id}");
            
            // Set success notice
            $this->set_conversion_success_notice('request', 'proposal', $request_id, $new_proposal_id, $request_post->post_title);
            
            /**
             * Hook: arsol_before_proposal_conversion_redirect
             * Fired just before redirecting to the new proposal
             */
            $redirect_url = admin_url('post.php?post=' . $new_proposal_id . '&action=edit');
            do_action('arsol_before_proposal_conversion_redirect', $new_proposal_id, $redirect_url, $conversion_data);

            // Redirect
            $this->safe_redirect($redirect_url);
            
        } catch (Exception $e) {
            // Log error
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_request_to_proposal_conversion('error', 
                "Conversion failed: Request #{$request_id} - " . $e->getMessage());
            
            // Rollback everything
            $this->rollback_workflow_transaction($request_id, $e->getMessage());
            
            // Set failure notice
            if ($request_post) {
                $this->set_conversion_failure_notice('request', 'proposal', $request_id, $request_post->post_title, $e->getMessage());
            }
            
            // Redirect back
            $this->safe_redirect(admin_url('post.php?post=' . $request_id . '&action=edit'));
        }
    }

    // Helper method to copy request metadata to proposal
    private function copy_request_metadata_to_proposal($request_id, $proposal_id) {
        // ✅ PHASE 1: COMPREHENSIVE META KEY RESTRUCTURING
        
        // 1. Preserve original request content in proposal meta
        $request_post = get_post($request_id);
        update_post_meta($proposal_id, '_arsol_pfw_proposal_request_details', $request_post->post_content);
        
        // 2. Rename request meta keys with proposal context
        $meta_mapping = array(
            '_arsol_pfw_request_title' => '_arsol_pfw_proposal_request_title',
            '_arsol_pfw_request_date' => '_arsol_pfw_proposal_request_date',
            '_arsol_pfw_request_budget' => '_arsol_pfw_proposal_request_budget',
            '_arsol_pfw_request_start_date' => '_arsol_pfw_proposal_request_start_date',
            '_arsol_pfw_request_delivery_date' => '_arsol_pfw_proposal_request_delivery_date',
            '_arsol_pfw_request_attachments' => '_arsol_pfw_proposal_request_attachments',
        );
        
        foreach ($meta_mapping as $old_key => $new_key) {
            $value = get_post_meta($request_id, $old_key, true);
            if (!empty($value)) {
                update_post_meta($proposal_id, $new_key, $value);
            }
        }
        
        // 3. Transfer request budget as proposed budget
        $request_budget = get_post_meta($request_id, '_arsol_pfw_request_budget', true);
        if (!empty($request_budget)) {
            // Set proposal costing type to budget
            update_post_meta($proposal_id, '_arsol_pfw_proposal_costing_type', 'budget');
            
            // Transfer request budget as proposed budget
            update_post_meta($proposal_id, '_arsol_pfw_proposal_budget_onetime_amount', $request_budget);
            
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_request_to_proposal_conversion('info', 
                "Budget transferred: Request budget → Proposal budget");
        }
        
        // 4. Set proposal status to processing
        wp_set_object_terms($proposal_id, 'processing', 'arsol-pfw-proposal-stage');
        
        // 5. Copy custom fields and taxonomies
        $custom_fields = get_post_meta($request_id);
        foreach ($custom_fields as $key => $values) {
            if (strpos($key, '_arsol_pfw_') === 0 && !isset($meta_mapping[$key])) {
                foreach ($values as $value) {
                    add_post_meta($proposal_id, $key, maybe_unserialize($value));
                }
            }
        }
        
        // Copy taxonomies
        $taxonomies = get_object_taxonomies('arsol-pfw-request');
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_object_terms($request_id, $taxonomy, array('fields' => 'slugs'));
            if (!empty($terms) && !is_wp_error($terms)) {
                wp_set_object_terms($proposal_id, $terms, $taxonomy);
            }
        }
    }

    public function convert_proposal_to_project($proposal_id = 0, $is_internal_call = false) {
        // Get proposal ID first if not provided
        if (empty($proposal_id)) {
            if (!isset($_GET['proposal_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_convert_to_project_nonce')) {
                wp_die(__('Invalid proposal or nonce.', 'arsol-pfw'));
            }
            $proposal_id = intval($_GET['proposal_id']);
        }

        $proposal_post = null;
        
        try {
            // Get source details
            $proposal_post = get_post($proposal_id);
            if (!$proposal_post || $proposal_post->post_type !== 'arsol-pfw-proposal') {
                throw new Exception(__('Invalid proposal.', 'arsol-pfw'));
            }

            // Security validation
            if (!$is_internal_call && (!current_user_can('edit_post', $proposal_id) || !current_user_can('publish_posts'))) {
                throw new Exception(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
            }

            if ($is_internal_call && !self::user_can_view_post(get_current_user_id(), $proposal_id)) {
                throw new Exception(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
            }

            if ($proposal_post->post_status !== 'publish') {
                throw new Exception(__('Only published proposals can be converted to projects.', 'arsol-pfw'));
            }

            // Prevent concurrent conversions and handle stuck workflows
            if ($this->is_workflow_in_progress($proposal_id)) {
                // Check if this is a stuck workflow (older than 5 minutes)
                $workflow_started = get_post_meta($proposal_id, '_arsol_workflow_started', true);
                $is_stuck = false;
                
                if ($workflow_started) {
                    $started_time = strtotime($workflow_started);
                    $current_time = current_time('timestamp');
                    $age_minutes = ($current_time - $started_time) / 60;
                    
                    if ($age_minutes > 0.5) { // 30 seconds
                        $is_stuck = true;
                        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('warning', 
                            "Detected stuck workflow for proposal #{$proposal_id}, age: {$age_minutes} minutes. Auto-clearing...");
                    }
                }
                
                if ($is_stuck) {
                    // Force clear the stuck workflow
                    $this->force_clear_stuck_workflow($proposal_id);
                    
                    \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                        "Successfully cleared stuck workflow for proposal #{$proposal_id}. Proceeding with conversion...");
                } else {
                    throw new Exception(__('Conversion already in progress.', 'arsol-pfw'));
                }
            }
            
            // Start transaction with logging
            $this->start_workflow_transaction($proposal_id, 'conversion', 'proposal_to_project');
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_proposal_to_project_conversion('info', 
                "Starting conversion: Proposal #{$proposal_id} to Project");
            
            // Validation step
            update_post_meta($proposal_id, '_arsol_conversion_step', 'validation');

            // Server-side validation: Check proposal stage
            $proposal_stage_terms = wp_get_object_terms($proposal_id, 'arsol-pfw-proposal-stage', array('fields' => 'slugs'));
            $current_proposal_stage = !empty($proposal_stage_terms) ? $proposal_stage_terms[0] : '';

            if ($current_proposal_stage !== 'approved') {
                throw new Exception(sprintf(
                    __('This proposal cannot be converted. The stage is "%s", must be "approved".', 'arsol-pfw'),
                    $current_proposal_stage ?: 'none'
                ));
        }

        // Prepare conversion data for hooks
        $conversion_data = array(
            'proposal_id' => $proposal_id,
            'proposal_post' => $proposal_post,
            'is_internal_call' => $is_internal_call,
            'user_id' => get_current_user_id(),
                'conversion_method' => $is_internal_call ? 'customer_approval' : 'admin_conversion',
                'timestamp' => current_time('timestamp'),
                'proposal_stage' => $current_proposal_stage
        );

        /**
         * Hook: arsol_before_project_conversion_validation
         * Fired before any validation checks are performed
         */
        do_action('arsol_before_project_conversion_validation', $proposal_id, $conversion_data);

        /**
         * Hook: arsol_after_project_conversion_validated
         * Fired after all validation checks pass, before project creation
         */
        do_action('arsol_after_project_conversion_validated', $proposal_id, $proposal_post, $conversion_data);
            
            // Creation step
            update_post_meta($proposal_id, '_arsol_conversion_step', 'creation');

        // Create project args with filter for customization
        $project_args = array(
            'post_title'   => $proposal_post->post_title,
            'post_content' => $proposal_post->post_content,
                'post_status'  => 'publish',
            'post_type'    => 'arsol-pfw-project',
            'post_author'  => $proposal_post->post_author,
        );

        /**
         * Filter: arsol_project_conversion_args
         * Allows modification of project creation arguments
         */
        $project_args = apply_filters('arsol_project_conversion_args', $project_args, $proposal_id, $proposal_post, $conversion_data);

        /**
         * Hook: arsol_before_project_conversion_project_creation
         * Fired immediately before the project post is created
         */
        do_action('arsol_before_project_conversion_project_creation', $project_args, $proposal_id, $conversion_data);

        $new_project_id = wp_insert_post($project_args);
        if (is_wp_error($new_project_id)) {
                throw new Exception($new_project_id->get_error_message());
            }
            
            // Record created entity
            $this->record_transaction_entity($proposal_id, $new_project_id);
            $conversion_data['new_project_id'] = $new_project_id;

            /**
             * Hook: arsol_after_project_conversion_project_created
             * Fired after the project is successfully created, before metadata copy
             */
            do_action('arsol_after_project_conversion_project_created', $new_project_id, $proposal_id, $proposal_post, $conversion_data);
            
            // Metadata copy step
            update_post_meta($proposal_id, '_arsol_conversion_step', 'metadata_copy');
            
            // Copy metadata from proposal to project
            $this->copy_proposal_metadata_to_project($proposal_id, $new_project_id);

            // Order creation step  
            update_post_meta($proposal_id, '_arsol_conversion_step', 'order_creation');

            // Handle WooCommerce order creation
            $cost_proposal_type = get_post_meta($proposal_id, '_arsol_pfw_proposal_costing_type', true) ?: 'none';
            
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_woocommerce_billing('info', 
                sprintf('Starting billing operations for proposal #%d (type: %s) → project #%d', 
                    $proposal_id, $cost_proposal_type, $new_project_id));

            try {
                // Only create orders for quotation proposals
                if ($cost_proposal_type === 'quotation') {
                    \Arsol_Projects_For_Woo\Woocommerce_Logs::log_woocommerce_billing('info', 
                        sprintf('Creating orders for quotation proposal %d', $proposal_id));
                    
                    // Create a biller instance and convert proposal to order
                    $biller = new \Arsol_Projects_For_Woo\Woocommerce_Biller();
                    $result = $biller->convert_proposal_to_order($proposal_id, $new_project_id);
                    
                    if (!$result['success']) {
                        throw new Exception($result['message']);
                    }
                    
                    // Store created order IDs and record them for rollback
                    if (!empty($result['order_id'])) {
                        update_post_meta($new_project_id, '_arsol_pfw_project_woocommerce_order_id', $result['order_id']);
                        // Record order for potential rollback
                        $this->record_transaction_entity($proposal_id, $result['order_id']);
                    }
                    
                    if (!empty($result['subscription_id'])) {
                        update_post_meta($new_project_id, '_arsol_pfw_project_woocommerce_subscription_id', $result['subscription_id']);
                        // Record subscription for potential rollback
                        $this->record_transaction_entity($proposal_id, $result['subscription_id']);
                    }
                    
                    update_post_meta($new_project_id, '_arsol_pfw_project_order_creation_note', $result['message']);
                    
                    \Arsol_Projects_For_Woo\Woocommerce_Logs::log_woocommerce_billing('info',
                        sprintf('Successfully created orders for project #%d: %s', $new_project_id, $result['message']));
                    
            } else {
                    \Arsol_Projects_For_Woo\Woocommerce_Logs::log_woocommerce_billing('info', 
                        sprintf('Skipping order creation for proposal %d with type: %s', $proposal_id, $cost_proposal_type));
                }
                
            } catch (Exception $e) {
                // Log order creation error but don't fail the conversion
                $error_message = $e->getMessage();
                \Arsol_Projects_For_Woo\Woocommerce_Logs::log_woocommerce_billing('error',
                    sprintf('Order creation failed for project #%d: %s', $new_project_id, $error_message));
                
                // Store error for debugging but continue with conversion
                update_post_meta($new_project_id, '_arsol_pfw_project_order_creation_error', $error_message);
            }

            /**
             * Hook: arsol_after_project_conversion_complete
             * Fired after the conversion is complete, before cleanup
             */
            do_action('arsol_after_project_conversion_complete', $new_project_id, $proposal_id, $conversion_data);
            
            // Complete transaction
            $this->complete_workflow_transaction($proposal_id);
            
            // Log success
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_proposal_to_project_conversion('success', 
                "Conversion completed: Proposal #{$proposal_id} → Project #{$new_project_id}");
            
            // Set success notice
            $this->set_conversion_success_notice('proposal', 'project', $proposal_id, $new_project_id, $proposal_post->post_title);
            
            /**
             * Hook: arsol_before_project_conversion_redirect
             * Fired just before redirecting to the new project
             */
            $redirect_url = $is_internal_call 
                ? wc_get_account_endpoint_url('view-project/' . $new_project_id)
                : admin_url('post.php?post=' . $new_project_id . '&action=edit');
            
            do_action('arsol_before_project_conversion_redirect', $new_project_id, $redirect_url, $conversion_data);

            // Redirect
            $this->safe_redirect($redirect_url);
            
        } catch (Exception $e) {
            // Log error
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_proposal_to_project_conversion('error', 
                "Conversion failed: Proposal #{$proposal_id} - " . $e->getMessage());
            
            // Rollback everything
            $this->rollback_workflow_transaction($proposal_id, $e->getMessage());
            
            // Set failure notice
            if ($proposal_post) {
                $this->set_conversion_failure_notice('proposal', 'project', $proposal_id, $proposal_post->post_title, $e->getMessage());
            }
            
            // Redirect based on call type
            if ($is_internal_call) {
                if (function_exists('wc_add_notice')) {
                    wc_add_notice($e->getMessage(), 'error');
                }
                $this->safe_redirect(wp_get_referer() ?: wc_get_account_endpoint_url('view-proposal/' . $proposal_id));
            } else {
                // Redirect back to the proposal edit page instead of listing page
                $this->safe_redirect(admin_url('post.php?post=' . $proposal_id . '&action=edit'));
            }
        }
    }

    // Helper method to copy proposal metadata to project
    private function copy_proposal_metadata_to_project($proposal_id, $project_id) {
        // ✅ PHASE 2: COMPREHENSIVE META KEY RESTRUCTURING
        
        // 1. Preserve proposal content in project meta
        $proposal_post = get_post($proposal_id);
        update_post_meta($project_id, '_arsol_pfw_project_proposal_details', $proposal_post->post_content);
        
        // 2. Rename request data with project context
        $request_meta_mapping = array(
            '_arsol_pfw_proposal_request_details' => '_arsol_pfw_project_request_details',
            '_arsol_pfw_proposal_request_title' => '_arsol_pfw_project_request_title',
            '_arsol_pfw_proposal_request_date' => '_arsol_pfw_project_request_date',
            '_arsol_pfw_proposal_request_budget' => '_arsol_pfw_project_request_budget',
            '_arsol_pfw_proposal_request_start_date' => '_arsol_pfw_project_request_start_date',
            '_arsol_pfw_proposal_request_delivery_date' => '_arsol_pfw_project_request_delivery_date',
            '_arsol_pfw_proposal_request_attachments' => '_arsol_pfw_project_request_attachments',
        );
        
        // 3. Rename proposal data with project context
        $proposal_meta_mapping = array(
            '_arsol_pfw_proposal_notes' => '_arsol_pfw_project_proposal_notes',
            '_arsol_pfw_proposal_costing_type' => '_arsol_pfw_project_proposal_costing_type',
            '_arsol_pfw_proposal_project_lead' => '_arsol_pfw_project_lead',
            '_arsol_pfw_proposal_delivery_date' => '_arsol_pfw_project_due_date', // Map delivery date to project due date
        );
        
        // 4. Get proposal type for type-aware handling
        $cost_proposal_type = get_post_meta($proposal_id, '_arsol_pfw_proposal_costing_type', true) ?: 'none';
        
        // 5. Type-aware meta mapping
        $type_specific_mapping = array();
        
        if ($cost_proposal_type === 'budget') {
            // Budget proposals: Preserve ALL budget details
            $type_specific_mapping = array(
                '_arsol_pfw_proposal_budget_onetime_amount' => '_arsol_pfw_project_proposal_budget_onetime_amount',
                '_arsol_pfw_proposal_budget_onetime_amount_details' => '_arsol_pfw_project_proposal_budget_onetime_amount_details',
                '_arsol_pfw_proposal_budget_recurring_amount' => '_arsol_pfw_project_proposal_budget_recurring_amount',
                '_arsol_pfw_proposal_budget_recurring_amount_details' => '_arsol_pfw_project_proposal_budget_recurring_amount_details',
                '_arsol_pfw_proposal_budget_recurring_amount_billing_interval' => '_arsol_pfw_project_proposal_budget_recurring_amount_billing_interval',
                '_arsol_pfw_proposal_budget_recurring_amount_billing_period' => '_arsol_pfw_project_proposal_budget_recurring_amount_billing_period',
                '_arsol_pfw_proposal_budget_recurring_billing_start_date' => '_arsol_pfw_project_proposal_budget_recurring_billing_start_date',
            );
        } elseif ($cost_proposal_type === 'quotation') {
            // Quotation proposals: Preserve quotation details with key totals
            $type_specific_mapping = array(
                '_arsol_pfw_proposal_quotation_line_items' => '_arsol_pfw_project_proposal_quotation_line_items',
                '_arsol_pfw_proposal_quotation_onetime_total' => '_arsol_pfw_project_proposal_quotation_onetime_total',
                '_arsol_pfw_proposal_quotation_recurring_totals_grouped' => '_arsol_pfw_project_proposal_quotation_recurring_average_total',
                '_arsol_pfw_proposal_quotation_currency' => '_arsol_pfw_project_proposal_quotation_currency',
                '_arsol_pfw_proposal_quotation_currency_symbol' => '_arsol_pfw_project_proposal_quotation_currency_symbol',
            );
        }
        
        // 6. Combine all mappings
        $meta_to_copy = array_merge($request_meta_mapping, $proposal_meta_mapping, $type_specific_mapping);

        /**
         * Filter: arsol_project_conversion_meta_mapping
         * Allows modification of metadata mapping from proposal to project
         */
        $meta_to_copy = apply_filters('arsol_project_conversion_meta_mapping', $meta_to_copy, $project_id, $proposal_id, $cost_proposal_type, array());

        // 7. Copy all meta data
        foreach ($meta_to_copy as $proposal_key => $project_key) {
            $value = get_post_meta($proposal_id, $proposal_key, true);
            if ($value) {
                update_post_meta($project_id, $project_key, $value);
            }
        }

        // 8. Historical preservation - keep original proposal field names for reference
        $historical_fields = array(
            '_arsol_pfw_proposal_start_date',
            '_arsol_pfw_proposal_delivery_date',
        );
        
        foreach ($historical_fields as $field) {
            $value = get_post_meta($proposal_id, $field, true);
            if ($value) {
                update_post_meta($project_id, $field, $value); // Keep original field name
            }
        }

        // Store original proposal ID for reference
        update_post_meta($project_id, '_arsol_pfw_project_proposal_id', $proposal_id);

        // Set default project status to not-started
        wp_set_object_terms($project_id, 'not-started', 'arsol-pfw-project-stage');

        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_proposal_to_project_conversion('info', 
            sprintf('Metadata copied from proposal #%d to project #%d (type: %s): %s', 
                $proposal_id, $project_id, $cost_proposal_type, implode(', ', array_keys($meta_to_copy))));

        /**
         * Hook: arsol_after_project_conversion_metadata_copied
         * Fired after all metadata is copied from proposal to project
         */
        do_action('arsol_after_project_conversion_metadata_copied', $project_id, $proposal_id, $meta_to_copy, $cost_proposal_type, array());
    }

    public function customer_cancel_request() {
        if (!isset($_GET['request_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_cancel_request_nonce')) {
            wp_die(__('Invalid request or nonce.', 'arsol-pfw'));
        }

        $request_id = intval($_GET['request_id']);
        if (self::user_can_view_post(get_current_user_id(), $request_id)) {
            wp_delete_post($request_id, true); // Delete the request
            
            // Redirect to the requests list tab instead of general projects page
            $requests_url = add_query_arg('tab', 'requests', wc_get_account_endpoint_url('projects'));
            $this->safe_redirect($requests_url);
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
            wp_set_object_terms($proposal_id, 'approved', 'arsol-pfw-proposal-stage');
            
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
            wp_set_object_terms($proposal_id, 'rejected', 'arsol-pfw-proposal-stage');
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
            
            $this->safe_redirect(wc_get_account_endpoint_url('create-request'));
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
        do_action('arsol_before_request_creation_status_assignment', $post_id, 'pending-review', $creation_data);

        wp_set_object_terms($post_id, 'pending-review', 'arsol-pfw-request-stage');

        /**
         * Hook: arsol_after_request_creation_status_assigned
         * Fired after the request status is assigned
         * 
         * @param int $post_id The request ID
         * @param string $assigned_status The status that was assigned
         * @param array $creation_data Creation context data
         */
        do_action('arsol_after_request_creation_status_assigned', $post_id, 'pending-review', $creation_data);

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
        $redirect_url = wc_get_account_endpoint_url('view-request/' . $post_id);
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
        
        $this->safe_redirect(wc_get_account_endpoint_url('view-request/' . $post_id));
    }

    private function update_request_meta($post_id, $data) {
        // Save parent project if provided
        if (isset($data['parent_project_id']) && !empty($data['parent_project_id'])) {
            $parent_project_id = absint($data['parent_project_id']);
            if ($parent_project_id) {
                // Validate parent project exists
                $parent_project = get_post($parent_project_id);
                if ($parent_project && $parent_project->post_type === 'arsol-pfw-project') {
                    // Save parent project ID
                    update_post_meta($post_id, '_arsol_pfw_parent_project_id', $parent_project_id);
                    
                    // Mark as project-tied request
                    update_post_meta($post_id, '_arsol_pfw_is_project_tied_request', 1);
                }
            }
        }
        
        if (isset($data['request_budget'])) {
            $amount = wc_clean(wp_unslash($data['request_budget']));
            // Remove commas and other non-numeric characters except decimal point
            $amount = \Arsol_Projects_For_Woo\Woocommerce::clean_amount_input($amount);
            // Convert to proper decimal format
            $amount = wc_format_decimal($amount);
            $currency = get_woocommerce_currency();
            update_post_meta($post_id, '_arsol_pfw_request_budget', ['amount' => $amount, 'currency' => $currency]);
        }
        if (isset($data['request_start_date'])) {
            update_post_meta($post_id, '_arsol_pfw_request_start_date', sanitize_text_field($data['request_start_date']));
        }
        if (isset($data['request_delivery_date'])) {
            update_post_meta($post_id, '_arsol_pfw_request_delivery_date', sanitize_text_field($data['request_delivery_date']));
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
        $debug_info['proposal_stage'] = $proposal ? $proposal->post_status : 'N/A';
        $debug_info['proposal_author'] = $proposal ? $proposal->post_author : 'N/A';
        
        // Check cost proposal type
        $cost_proposal_type = get_post_meta($proposal_id, '_arsol_pfw_proposal_costing_type', true) ?: 'none';
        
        $debug_info['cost_proposal_type'] = $cost_proposal_type;
        $debug_info['should_create_orders'] = ($cost_proposal_type === 'quotation');
        
        // Check line items
        $line_items = get_post_meta($proposal_id, '_arsol_pfw_proposal_quotation_line_items', true);
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

    // ==========================================
    // TRANSACTION SYSTEM METHODS
    // ==========================================

    /**
     * Start a workflow transaction
     */
    private function start_workflow_transaction($source_id, $workflow_type, $conversion_type) {
        // Set workflow metadata
        update_post_meta($source_id, '_arsol_workflow_status', 'in_progress');
        update_post_meta($source_id, '_arsol_workflow_type', $workflow_type);
        update_post_meta($source_id, '_arsol_workflow_started', current_time('mysql'));
        update_post_meta($source_id, '_arsol_conversion_type', $conversion_type);
        update_post_meta($source_id, '_arsol_conversion_created_ids', array());
        
        // Clear any previous rollback reason
        delete_post_meta($source_id, '_arsol_conversion_rollback_reason');
    }

    /**
     * Record a created entity in the transaction
     */
    private function record_transaction_entity($source_id, $entity_id) {
        $created_ids = get_post_meta($source_id, '_arsol_conversion_created_ids', true) ?: array();
        $created_ids[] = $entity_id;
        update_post_meta($source_id, '_arsol_conversion_created_ids', $created_ids);
    }

    /**
     * Complete a workflow transaction successfully
     */
    private function complete_workflow_transaction($source_id) {
        update_post_meta($source_id, '_arsol_workflow_status', 'completed');
        
        // Clean up transaction metadata but keep audit trail
        delete_post_meta($source_id, '_arsol_conversion_created_ids');
        delete_post_meta($source_id, '_arsol_conversion_step');
        
        // Delete the source post (conversion completed successfully)
        wp_delete_post($source_id, true);
    }

    /**
     * Rollback a workflow transaction
     */
    private function rollback_workflow_transaction($source_id, $reason) {
        // Store rollback reason
        update_post_meta($source_id, '_arsol_conversion_rollback_reason', $reason);
        update_post_meta($source_id, '_arsol_workflow_status', 'failed');
        
        // Get conversion type for specific rollback
        $conversion_type = get_post_meta($source_id, '_arsol_conversion_type', true);
        
        $deleted_count = 0;
        switch ($conversion_type) {
            case 'request_to_proposal':
                $deleted_count = $this->rollback_request_to_proposal($source_id);
                break;
            case 'proposal_to_project':
                $deleted_count = $this->rollback_proposal_to_project($source_id);
                break;
        }
        
        // Log rollback completion
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('warning', 
            "Rollback completed for {$conversion_type}: {$deleted_count} entities deleted. Reason: {$reason}");
        
        // Clean up transaction metadata
        delete_post_meta($source_id, '_arsol_conversion_created_ids');
        delete_post_meta($source_id, '_arsol_conversion_step');
    }

    /**
     * Rollback request to proposal conversion
     */
    private function rollback_request_to_proposal($source_id) {
        $deleted_count = 0;
        
        // Delete created proposal
        $created_ids = get_post_meta($source_id, '_arsol_conversion_created_ids', true) ?: array();
        foreach ($created_ids as $entity_id) {
            if (wp_delete_post($entity_id, true)) {
                $deleted_count++;
                \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                    "Rollback: Deleted proposal #{$entity_id}");
            }
        }
        
        return $deleted_count;
    }

    /**
     * Rollback proposal to project conversion (complex)
     */
    private function rollback_proposal_to_project($source_id) {
        $deleted_count = 0;
        $created_ids = get_post_meta($source_id, '_arsol_conversion_created_ids', true) ?: array();
        
        foreach ($created_ids as $entity_id) {
            $post = get_post($entity_id);
            if (!$post) continue;
            
            // Handle different entity types
            switch ($post->post_type) {
                case 'shop_order':
                    // WooCommerce order - use proper WC deletion method
                    $order = wc_get_order($entity_id);
                    if ($order) {
                        $order->delete(true); // Force delete
                        $deleted_count++;
                        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                            "Rollback: Deleted shop_order #{$entity_id} using WooCommerce API");
                    } else {
                        // Fallback to direct deletion if WC object not found
                        if (wp_delete_post($entity_id, true)) {
                            $deleted_count++;
                            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                                "Rollback: Deleted shop_order #{$entity_id} using WordPress API (fallback)");
                        }
                    }
                    break;
                case 'shop_subscription':
                    // WooCommerce subscription - use proper WC deletion method
                    if (function_exists('wcs_get_subscription')) {
                        $subscription = wcs_get_subscription($entity_id);
                        if ($subscription) {
                            $subscription->delete(true); // Force delete
                            $deleted_count++;
                            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                                "Rollback: Deleted shop_subscription #{$entity_id} using WooCommerce Subscriptions API");
                        } else {
                            // Fallback to direct deletion if WCS object not found
                    if (wp_delete_post($entity_id, true)) {
                        $deleted_count++;
                        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                                    "Rollback: Deleted shop_subscription #{$entity_id} using WordPress API (fallback)");
                            }
                        }
                    } else {
                        // WooCommerce Subscriptions not active, use direct deletion
                        if (wp_delete_post($entity_id, true)) {
                            $deleted_count++;
                            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                                "Rollback: Deleted shop_subscription #{$entity_id} using WordPress API (no WCS)");
                        }
                    }
                    break;
                case 'arsol-pfw-project':
                    // Project entity
                    if (wp_delete_post($entity_id, true)) {
                        $deleted_count++;
                        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                            "Rollback: Deleted project #{$entity_id}");
                    }
                    break;
                default:
                    // Unknown entity type - try generic deletion
                    if (wp_delete_post($entity_id, true)) {
                        $deleted_count++;
                        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                            "Rollback: Deleted {$post->post_type} #{$entity_id}");
                    }
                    break;
            }
        }
        
        return $deleted_count;
    }

    /**
     * Check if a workflow is in progress
     */
    private function is_workflow_in_progress($source_id) {
        $status = get_post_meta($source_id, '_arsol_workflow_status', true);
        return $status === 'in_progress';
    }

    /**
     * Cleanup stuck workflows (static method for cron)
     */
    public static function cleanup_stuck_workflows($max_age_minutes = 30) {
        global $wpdb;
        
        $cutoff_time = date('Y-m-d H:i:s', strtotime("-{$max_age_minutes} minutes"));
        
        // Find stuck conversions
        $stuck_posts = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, pm1.meta_value as conversion_type, pm2.meta_value as started_time
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_arsol_workflow_status'
            INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_arsol_workflow_started'
            WHERE pm1.meta_value = 'in_progress'
            AND pm2.meta_value < %s
        ", $cutoff_time));
        
        $cleaned = 0;
        foreach ($stuck_posts as $post) {
            // Force rollback stuck conversion
            $workflow_handler = new self();
            $workflow_handler->rollback_workflow_transaction($post->ID, 'Automatic cleanup - stuck for over ' . $max_age_minutes . ' minutes');
            $cleaned++;
            
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('warning', 
                "Cleaned up stuck conversion: Post #{$post->ID}, Type: {$post->conversion_type}");
        }
        
        return $cleaned;
    }

    /**
     * Force clear stuck workflow for a specific post (public method for manual cleanup)
     */
    public function force_clear_stuck_workflow($post_id) {
        $status = get_post_meta($post_id, '_arsol_workflow_status', true);
        
        if ($status === 'in_progress') {
            // Force rollback the stuck transaction
            $this->rollback_workflow_transaction($post_id, 'Manual cleanup - stuck workflow cleared by admin');
            
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                "Manually cleared stuck workflow for post #{$post_id}");
            
            return true;
        }
        
        return false;
    }

    /**
     * Clear all stuck workflows regardless of age (emergency cleanup)
     */
    public static function emergency_cleanup_all_stuck_workflows() {
        global $wpdb;
        
        // Find all stuck conversions regardless of age
        $stuck_posts = $wpdb->get_results("
            SELECT p.ID, pm1.meta_value as conversion_type
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_arsol_workflow_status'
            WHERE pm1.meta_value = 'in_progress'
        ");
        
        $cleaned = 0;
        foreach ($stuck_posts as $post) {
            // Force rollback stuck conversion
            $workflow_handler = new self();
            $workflow_handler->rollback_workflow_transaction($post->ID, 'Emergency cleanup - all stuck workflows cleared');
            $cleaned++;
            
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('warning', 
                "Emergency cleanup: Post #{$post->ID}, Type: {$post->conversion_type}");
        }
        
        return $cleaned;
    }

    // ==========================================
    // NOTICE SYSTEM METHODS
    // ==========================================

    /**
     * Set admin notice for display after redirect
     */
    private function set_admin_notice($type, $message, $details = array()) {
        $notice_data = array(
            'type' => $type, // 'success', 'error', 'warning', 'info'
            'message' => $message,
            'details' => $details,
            'timestamp' => current_time('timestamp')
        );
        
        $user_id = get_current_user_id();
        set_transient('arsol_notice_' . $user_id, $notice_data, 300); // 5 minutes
    }

    /**
     * Set conversion success notice
     */
    private function set_conversion_success_notice($from_type, $to_type, $from_id, $to_id, $title) {
        // Map types to proper display names
        $type_names = array(
            'request' => __('Project Request', 'arsol-pfw'),
            'proposal' => __('Project Proposal', 'arsol-pfw'),
            'project' => __('Project', 'arsol-pfw')
        );
        
        $from_name = isset($type_names[$from_type]) ? $type_names[$from_type] : ucfirst(str_replace('_', ' ', $from_type));
        $to_name = isset($type_names[$to_type]) ? $type_names[$to_type] : ucfirst(str_replace('_', ' ', $to_type));
        
        $message = sprintf(
            __('%s "%s" successfully converted to %s.', 'arsol-pfw'),
            $from_name,
            $title,
            $to_name
        );
        
        $this->set_admin_notice('success', $message, array(
            'conversion_type' => $from_type . '_to_' . $to_type,
            'from_id' => $from_id,
            'to_id' => $to_id
        ));
    }

    /**
     * Set conversion failure notice
     */
    private function set_conversion_failure_notice($from_type, $to_type, $from_id, $title, $error) {
        // Map types to proper display names
        $type_names = array(
            'request' => __('Project Request', 'arsol-pfw'),
            'proposal' => __('Project Proposal', 'arsol-pfw'),
            'project' => __('Project', 'arsol-pfw')
        );
        
        $from_name = isset($type_names[$from_type]) ? $type_names[$from_type] : ucfirst(str_replace('_', ' ', $from_type));
        $to_name = isset($type_names[$to_type]) ? $type_names[$to_type] : ucfirst(str_replace('_', ' ', $to_type));
        
        $message = sprintf(
            __('Failed to convert %s "%s" to %s. Error: %s', 'arsol-pfw'),
            $from_name,
            $title,
            $to_name,
            $error
        );
        
        $this->set_admin_notice('error', $message, array(
            'conversion_type' => $from_type . '_to_' . $to_type,
            'from_id' => $from_id,
            'error' => $error
        ));
    }

    /**
     * Display conversion notices
     */
    public function display_conversion_notices() {
        $user_id = get_current_user_id();
        $notice_data = get_transient('arsol_notice_' . $user_id);
        
        if ($notice_data && is_array($notice_data)) {
            $class = 'notice notice-' . $notice_data['type'] . ' is-dismissible';
            echo '<div class="' . esc_attr($class) . '">';
            echo '<p>' . wp_kses_post($notice_data['message']) . '</p>';
            echo '</div>';
            
            // Clear the transient after displaying
            delete_transient('arsol_notice_' . $user_id);
        }
    }
}