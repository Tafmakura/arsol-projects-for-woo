<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal;

use Exception;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Proposal Conversion Class
 * Handles converting proposals to projects
 */
class Proposal_Conversion {
    
    public function convert_proposal_to_project($proposal_id = 0, $is_internal_call = false) {
        // Get proposal ID first if not provided
        if (empty($proposal_id)) {
            // Wrap parameter parsing in try/catch flow
            try {
            if (!isset($_GET['proposal_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_convert_to_project_nonce')) {
                    throw new Exception(__('Invalid proposal or nonce.', 'arsol-pfw'));
            }
            $proposal_id = intval($_GET['proposal_id']);
            } catch (Exception $e) {
                // Handle error via notice mechanism (same as other failures)
                \Arsol_Projects_For_Woo\Woocommerce_Logs::log_proposal_to_project_conversion('error', $e->getMessage());
                // Set admin notice and redirect back if possible
                if (!empty($_GET['proposal_id'])) {
                    $redirect_back = admin_url('post.php?post=' . intval($_GET['proposal_id']) . '&action=edit');
                    \Arsol_Projects_For_Woo\Core\Workflow\Workflow_Handler::set_static_conversion_failure_notice('proposal', 'project', intval($_GET['proposal_id']), __('Invalid Proposal', 'arsol-pfw'), $e->getMessage());
                    wp_safe_redirect($redirect_back);
                    exit;
                }
                // As last resort, rethrow to outer catch
                throw $e;
            }
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

            if ($is_internal_call && !\Arsol_Projects_For_Woo\Core\Permissions::user_can_view_post(get_current_user_id(), $proposal_id)) {
                throw new Exception(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
            }

            if ($proposal_post->post_status !== 'publish') {
                throw new Exception(__('Only published proposals can be converted to projects.', 'arsol-pfw'));
            }

            // Prevent concurrent conversions and handle stuck workflows
            if ($this->is_workflow_in_progress($proposal_id)) {
                // Check if this is a stuck workflow (older than 5 minutes)
                $workflow_started = get_post_meta($proposal_id, '_arsol_pfw_workflow_started', true);
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
            update_post_meta($proposal_id, '_arsol_pfw_conversion_step', 'validation');

            // No stage restriction – conversion allowed for any published proposal

            // Prepare conversion data for hooks
            $conversion_data = array(
                'proposal_id' => $proposal_id,
                'proposal_post' => $proposal_post,
                'is_internal_call' => $is_internal_call,
                'user_id' => get_current_user_id(),
                'conversion_method' => $is_internal_call ? 'customer_approval' : 'admin_conversion',
                'timestamp' => current_time('timestamp'),
                'proposal_stage' => '' // No longer needed
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
            update_post_meta($proposal_id, '_arsol_pfw_conversion_step', 'creation');

            // NEW WAY - Factory functions and CRUD methods
            $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Arsol_PFW_Proposal($proposal_id);
            if (!$proposal) {
                throw new Exception(__('Proposal not found.', 'arsol-pfw'));
            }
            
            $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Arsol_PFW_Project();
        $project->set_title($proposal->get_title());
        $project->set_customer_id($proposal->get_customer_id());
        $project->set_budget($proposal->get_budget());
        $project->set_description($proposal->get_prop('description'));
        $project->set_stage('not-started');
            
            if (is_wp_error($project)) {
                throw new Exception($project->get_error_message());
            }
            
            $new_project_id = $project->save();
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
            do_action('arsol_after_project_conversion_project_created', $new_project_id, $proposal_id, $proposal, $conversion_data);            
            // Metadata copy step
            update_post_meta($proposal_id, '_arsol_pfw_conversion_step', 'metadata_copy');
            
            // Copy metadata from proposal to project
            $this->copy_proposal_metadata_to_project($proposal_id, $new_project_id);

            // Order creation step  
            update_post_meta($proposal_id, '_arsol_pfw_conversion_step', 'order_creation');

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

    // Helper method to copy proposal metadata to project using new CRUD methods
    private function copy_proposal_metadata_to_project($proposal_id, $project_id) {
        // Get proposal and project objects using entity classes
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Arsol_PFW_Proposal($proposal_id);
        $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Arsol_PFW_Project($project_id);
        
        if (!$proposal || !$project) {
            throw new Exception(__('Failed to load proposal or project for metadata copy.', 'arsol-pfw'));
        }
        
        // Copy basic proposal data
        update_post_meta($project_id, '_arsol_pfw_project_proposal_details', $proposal->get_prop('description'));        
        
        // Copy request data with project context
        $request_meta_mapping = array(
            '_arsol_pfw_proposal_request_details' => '_arsol_pfw_project_request_details',
            '_arsol_pfw_proposal_request_title' => '_arsol_pfw_project_request_title',
            '_arsol_pfw_proposal_request_date' => '_arsol_pfw_project_request_date',
            '_arsol_pfw_proposal_request_budget' => '_arsol_pfw_project_request_budget',
            '_arsol_pfw_proposal_request_start_date' => '_arsol_pfw_project_request_start_date',
            '_arsol_pfw_proposal_request_delivery_date' => '_arsol_pfw_project_request_delivery_date',
            '_arsol_pfw_proposal_request_attachments' => '_arsol_pfw_project_request_attachments',
        );
        
        // Copy proposal data with project context
        $proposal_meta_mapping = array(
            '_arsol_pfw_proposal_notes' => '_arsol_pfw_project_proposal_notes',
            '_arsol_pfw_proposal_costing_type' => '_arsol_pfw_project_proposal_costing_type',
            '_arsol_pfw_proposal_project_lead' => '_arsol_pfw_project_lead',
            '_arsol_pfw_proposal_due_date' => '_arsol_pfw_project_due_date', // Map due date to project due date
        );
        
        // Get proposal type for type-aware handling
        $cost_proposal_type = $proposal->get_costing_type();
        
        // Copy type-specific data using entity methods
        if ($cost_proposal_type === 'budget') {
            // Copy budget data using entity methods
            $budget_data = $proposal->get_proposal_budget();
            if (!empty($budget_data)) {
                $project->set_project_budget($budget_data);
            }
        } elseif ($cost_proposal_type === 'quotation') {
            // Copy quotation data using entity methods
            $quotation_data = $proposal->get_proposal_quotation();
            if (!empty($quotation_data)) {
                $project->set_project_quotation($quotation_data);
            }
        }
        
        // Save project entity to persist the copied data
        $project->save();
        
        // Combine all mappings for remaining meta
        $meta_to_copy = array_merge($request_meta_mapping, $proposal_meta_mapping);

        /**
         * Filter: arsol_project_conversion_meta_mapping
         * Allows modification of metadata mapping from proposal to project
         */
        $meta_to_copy = apply_filters('arsol_project_conversion_meta_mapping', $meta_to_copy, $project_id, $proposal_id, $cost_proposal_type, array());

        // Copy all remaining meta data
        foreach ($meta_to_copy as $proposal_key => $project_key) {
            $value = get_post_meta($proposal_id, $proposal_key, true);
            if ($value) {
                update_post_meta($project_id, $project_key, $value);
            }
        }

        // Historical preservation - keep original proposal field names for reference
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

    // ==========================================
    // TRANSACTION SYSTEM METHODS
    // ==========================================

    /**
     * Start a workflow transaction
     */
    private function start_workflow_transaction($source_id, $workflow_type, $conversion_type) {
        // Set workflow metadata
        update_post_meta($source_id, '_arsol_pfw_workflow_status', 'in_progress');
        update_post_meta($source_id, '_arsol_pfw_workflow_type', $workflow_type);
        update_post_meta($source_id, '_arsol_pfw_workflow_started', current_time('mysql'));
        update_post_meta($source_id, '_arsol_pfw_conversion_type', $conversion_type);
        update_post_meta($source_id, '_arsol_pfw_conversion_created_ids', array());
        
        // Clear any previous rollback reason
        delete_post_meta($source_id, '_arsol_pfw_conversion_rollback_reason');
    }

    /**
     * Record a created entity in the transaction
     */
    private function record_transaction_entity($source_id, $entity_id) {
        $created_ids = get_post_meta($source_id, '_arsol_pfw_conversion_created_ids', true) ?: array();
        $created_ids[] = $entity_id;
        update_post_meta($source_id, '_arsol_pfw_conversion_created_ids', $created_ids);
    }

    /**
     * Complete a workflow transaction successfully
     */
    private function complete_workflow_transaction($source_id) {
        update_post_meta($source_id, '_arsol_pfw_workflow_status', 'completed');
        
        // Clean up transaction metadata but keep audit trail
        delete_post_meta($source_id, '_arsol_pfw_conversion_created_ids');
        delete_post_meta($source_id, '_arsol_pfw_conversion_step');
        
        // Delete the source post (conversion completed successfully)
        wp_delete_post($source_id, true);
    }

    /**
     * Rollback a workflow transaction
     */
    private function rollback_workflow_transaction($source_id, $reason) {
        // Store rollback reason
        update_post_meta($source_id, '_arsol_pfw_conversion_rollback_reason', $reason);
        update_post_meta($source_id, '_arsol_pfw_workflow_status', 'failed');
        
        // Delete created entities
        $this->rollback_proposal_to_project($source_id);
        
        // Log rollback completion
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('warning', 
            "Rollback completed for proposal_to_project. Reason: {$reason}");
        
        // Clean up transaction metadata
        delete_post_meta($source_id, '_arsol_pfw_conversion_created_ids');
        delete_post_meta($source_id, '_arsol_pfw_conversion_step');
    }

    /**
     * Rollback proposal to project conversion (complex)
     */
    private function rollback_proposal_to_project($source_id) {
        $deleted_count = 0;
        $created_ids = get_post_meta($source_id, '_arsol_pfw_conversion_created_ids', true) ?: array();
        
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
        $status = get_post_meta($source_id, '_arsol_pfw_workflow_status', true);
        return $status === 'in_progress';
    }

    /**
     * Force clear stuck workflow for a specific post
     */
    public function force_clear_stuck_workflow($post_id) {
        $status = get_post_meta($post_id, '_arsol_pfw_workflow_status', true);
        
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
