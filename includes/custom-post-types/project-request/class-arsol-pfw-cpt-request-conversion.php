<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest;

use Exception;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Request Conversion Class
 * Handles converting requests to proposals
 */
class Request_Conversion {
    
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
        
        // Delete created proposal
        $this->rollback_request_to_proposal($source_id);
        
        // Log rollback completion
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('warning', 
            "Rollback completed for request_to_proposal. Reason: {$reason}");
        
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
     * Check if a workflow is in progress
     */
    private function is_workflow_in_progress($source_id) {
        $status = get_post_meta($source_id, '_arsol_workflow_status', true);
        return $status === 'in_progress';
    }

    /**
     * Force clear stuck workflow for a specific post
     */
    public function force_clear_stuck_workflow($post_id) {
        $status = get_post_meta($post_id, '_arsol_workflow_status', true);
        
        if ($status === 'in_progress') {
            // Force rollback the stuck transaction
            $this->rollback_workflow_transaction($post_id, 'Manual cleanup - stuck workflow cleared');
            
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                "Manually cleared stuck workflow for post #{$post_id}");
            
            return true;
        }
        
        return false;
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

    // ==========================================
    // UTILITY METHODS
    // ==========================================

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
}
