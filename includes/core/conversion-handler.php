<?php

namespace Arsol_Projects_For_Woo\Core;

use Exception;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Conversion Handler Class
 * Handles conversions with minimal complexity while maintaining reliability
 */
class Conversion_Handler {

    /**
     * Convert request to proposal
     * 
     * @param int $request_id
     * @return int|WP_Error Proposal ID on success, WP_Error on failure
     */
    public function convert_request_to_proposal($request_id) {
        // Verify request exists and is valid
        $request = get_post($request_id);
        if (!$request || $request->post_type !== 'arsol-pfw-request') {
            return new \WP_Error('invalid_request', __('Invalid request ID.', 'arsol-pfw'));
        }
        
        // Get request entity for data access
        $request_entity = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($request_id);
        
        // Prepare proposal data
        $proposal_data = array(
            'post_type' => 'arsol-pfw-proposal',
            'post_title' => $request->post_title,
            'post_content' => $request->post_content,
            'post_status' => 'draft',
            'post_author' => $request->post_author, // Creator (admin)
            'meta_input' => array(
                '_arsol_pfw_customer_id' => $request_entity->get_customer_id(), // Customer
                '_arsol_pfw_proposed_project_start_date' => $request_entity->get_requested_start_date(),
                '_arsol_pfw_proposed_project_due_date' => $request_entity->get_requested_due_date(),
                '_arsol_pfw_proposed_project_lead' => $request_entity->get_requested_project_lead(),
                '_arsol_pfw_request_id' => $request_id,
                '_arsol_pfw_created_via' => 'request_conversion'
            )
        );
        
        // Create the proposal
        $proposal_id = wp_insert_post($proposal_data);
        
        if (is_wp_error($proposal_id)) {
            return $proposal_id;
        }
        
        // Copy request data based on type
        $cost_request_type = $request_entity->get_costing_type();
        
        switch ($cost_request_type) {
            case 'budget':
                // Copy budget data
                $budget_data = $request_entity->get_request_budget();
                if (!empty($budget_data)) {
                    $proposal_entity = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);
                    $proposal_entity->set_proposal_budget($budget_data);
                    $proposal_entity->save();
                }
                break;
                
            case 'quotation':
                // Copy quotation data
                $quotation_data = $request_entity->get_request_quotation();
                if (!empty($quotation_data)) {
                    $proposal_entity = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);
                    $proposal_entity->set_proposal_quotation($quotation_data);
                    $proposal_entity->save();
                }
                break;
        }
        
        // Copy taxonomies
        $this->copy_taxonomies($request_id, $proposal_id);
        
        // Set initial stage
        $this->set_initial_proposal_stage($proposal_id);
        
        // Trigger conversion hooks
        do_action('arsol_pfw_request_converted_to_proposal', $request_id, $proposal_id);
        
        return $proposal_id;
    }

    /**
     * Convert a Proposal to a Project
     * 
     * @param int $proposal_id Proposal ID to convert
     * @param bool $is_internal_call Whether this is an internal call
     * @return void
     */
    public function convert_proposal_to_project($proposal_id, $is_internal_call = false) {
        error_log("ARSOL PFW DEBUG: Starting proposal to project conversion for proposal #{$proposal_id} (internal: " . ($is_internal_call ? 'yes' : 'no') . ")");
        
        try {
            // 1. Security check (skip for internal calls)
            if (!$is_internal_call) {
                if (!wp_verify_nonce($_GET['_wpnonce'], 'arsol_convert_to_project_nonce')) {
                    error_log("ARSOL PFW DEBUG: Security check failed");
                    throw new Exception(__('Security check failed.', 'arsol-pfw'));
                }
            }
            
            // 2. Basic validation
            $proposal = get_post($proposal_id);
            if (!$proposal || $proposal->post_type !== 'arsol-pfw-proposal') {
                error_log("ARSOL PFW DEBUG: Invalid proposal - post type: " . ($proposal ? $proposal->post_type : 'null'));
                throw new Exception(__('Invalid proposal.', 'arsol-pfw'));
            }
            
            if ($proposal->post_status !== 'publish') {
                error_log("ARSOL PFW DEBUG: Proposal not published - status: " . $proposal->post_status);
                throw new Exception(__('Only published proposals can be converted.', 'arsol-pfw'));
            }
            
            error_log("ARSOL PFW DEBUG: Proposal validation passed");
            
            // 3. Create project using factory
            $proposal_obj = arsol_pfw_get_proposal($proposal_id);
            if (!$proposal_obj) {
                error_log("ARSOL PFW DEBUG: Failed to get proposal object");
                throw new Exception(__('Proposal not found.', 'arsol-pfw'));
            }
            
            error_log("ARSOL PFW DEBUG: Creating project...");
            $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project();
            $project->set_title($proposal_obj->get_title());
            $project->set_customer_id($proposal_obj->get_customer_id());
            $project->set_project_budget($proposal_obj->get_proposed_project_budget());
            $project->set_description($proposal_obj->get_prop('description'));
            $project->set_stage('not-started');
            
            if (is_wp_error($project)) {
                error_log("ARSOL PFW DEBUG: Failed to create project: " . $project->get_error_message());
                throw new Exception($project->get_error_message());
            }
            
            $project_id = $project->save();
            if (is_wp_error($project_id)) {
                error_log("ARSOL PFW DEBUG: Failed to save project: " . $project_id->get_error_message());
                throw new Exception($project_id->get_error_message());
            }
            
            error_log("ARSOL PFW DEBUG: Project created with ID: {$project_id}");
            
            // 4. Copy metadata
            error_log("ARSOL PFW DEBUG: Starting metadata copy...");
            $this->copy_proposal_metadata_to_project($proposal_id, $project_id);
            error_log("ARSOL PFW DEBUG: Metadata copy completed");
            
            // 5. Handle WooCommerce orders (if needed)
            $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);
            $cost_type = $proposal->get_proposal_costing_type();
            if ($cost_type === 'quotation') {
                error_log("ARSOL PFW DEBUG: Creating WooCommerce orders for quotation proposal");
                $this->create_woocommerce_orders($proposal_id, $project_id);
            } else {
                error_log("ARSOL PFW DEBUG: Skipping WooCommerce orders - cost type: {$cost_type}");
            }
            
            // 6. Delete original
            error_log("ARSOL PFW DEBUG: Deleting original proposal...");
            wp_delete_post($proposal_id, true);
            
            // 7. Success
            error_log("ARSOL PFW DEBUG: Conversion successful");
            $this->add_notice(__('Proposal converted to project successfully.', 'arsol-pfw'), 'success');
            
            // Use Settings API redirect pattern
            wp_redirect(add_query_arg('settings-updated', 'true', admin_url("post.php?post={$project_id}&action=edit")));
            exit;
            
        } catch (Exception $e) {
            error_log("ARSOL PFW DEBUG: Conversion failed: " . $e->getMessage());
            $this->add_notice(__('Conversion failed: ', 'arsol-pfw') . $e->getMessage(), 'error');
            wp_redirect(add_query_arg('settings-updated', 'true', admin_url("post.php?post={$proposal_id}&action=edit")));
            exit;
        }
    }

    /**
     * Copy request metadata to proposal
     * 
     * @param int $request_id Request ID
     * @param int $proposal_id Proposal ID
     */
    private function copy_request_metadata_to_proposal($request_id, $proposal_id) {
        // Debug: Log the start of metadata copy
        error_log("ARSOL PFW DEBUG: Starting metadata copy from request #{$request_id} to proposal #{$proposal_id}");
        
        // Get request and proposal objects using factory functions
        $request = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($request_id);
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);
        
        if (!$request || !$proposal) {
            error_log("ARSOL PFW DEBUG: Failed to load request or proposal objects");
            throw new Exception(__('Failed to load request or proposal for metadata copy.', 'arsol-pfw'));
        }
        
        error_log("ARSOL PFW DEBUG: Successfully loaded request and proposal objects");
        
        // 1. Preserve original request content in proposal meta
        $request_description = $request->get_prop('description');
        $proposal->set_request_details($request_description);
        error_log("ARSOL PFW DEBUG: Copied request description to proposal: " . ($request_description ? substr($request_description, 0, 50) . "..." : "null"));
        
        // 2. Rename request meta keys with proposal context
        $meta_mapping = array(
            '_arsol_pfw_request_title' => '_arsol_pfw_request_title',
            '_arsol_pfw_request_date' => '_arsol_pfw_request_date',
            '_arsol_pfw_requested_budget' => '_arsol_pfw_requested_project_budget',
            '_arsol_pfw_requested_start_date' => '_arsol_pfw_requested_project_start_date',
            '_arsol_pfw_requested_due_date' => '_arsol_pfw_requested_project_due_date',
            '_arsol_pfw_request_attachments' => '_arsol_pfw_request_attachments',
        );
        
        $copied_count = 0;
        foreach ($meta_mapping as $old_key => $new_key) {
            $value = $request->get_meta($old_key);
            if (!empty($value)) {
                $proposal->set_meta($new_key, $value);
                error_log("ARSOL PFW DEBUG: Copied meta key '{$old_key}' to '{$new_key}' with value: " . print_r($value, true));
                $copied_count++;
            } else {
                error_log("ARSOL PFW DEBUG: Meta key '{$old_key}' was empty or not found");
            }
        }
        error_log("ARSOL PFW DEBUG: Copied {$copied_count} mapped meta keys");
        
        // 3. Transfer request budget as proposed budget
        $request_budget = $request->get_requested_project_budget();
        if (!empty($request_budget)) {
            $proposal->set_proposal_costing_type('budget');
            
            // Use array-based budget structure
            $budget_data = array(
                'onetime' => $request_budget,
                'type' => 'budget'
            );
            $proposal->set_meta('_arsol_pfw_proposed_project_budget_line_items', $budget_data);
            error_log("ARSOL PFW DEBUG: Copied request budget using array structure: " . print_r($budget_data, true));
        } else {
            error_log("ARSOL PFW DEBUG: Request budget was empty");
        }
        
        // 4. Set proposal status to processing
        $proposal->set_stage('processing');
        error_log("ARSOL PFW DEBUG: Set proposal status to 'processing'");
        
        // 5. Copy custom fields and taxonomies
        $custom_fields = $request->get_meta('', false); // Get all meta
        $custom_copied_count = 0;
        foreach ($custom_fields as $key => $values) {
            if (strpos($key, '_arsol_pfw_') === 0 && !isset($meta_mapping[$key])) {
                foreach ($values as $value) {
                    $proposal->set_meta($key, maybe_unserialize($value));
                    error_log("ARSOL PFW DEBUG: Copied custom field '{$key}' with value: " . print_r($value, true));
                    $custom_copied_count++;
                }
            }
        }
        error_log("ARSOL PFW DEBUG: Copied {$custom_copied_count} custom fields");
        
        // Copy taxonomies
        $taxonomies = get_object_taxonomies('arsol-pfw-request');
        $taxonomy_copied_count = 0;
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_object_terms($request_id, $taxonomy, array('fields' => 'slugs'));
            if (!empty($terms) && !is_wp_error($terms)) {
                wp_set_object_terms($proposal_id, $terms, $taxonomy);
                error_log("ARSOL PFW DEBUG: Copied taxonomy '{$taxonomy}' with terms: " . print_r($terms, true));
                $taxonomy_copied_count++;
            }
        }
        error_log("ARSOL PFW DEBUG: Copied {$taxonomy_copied_count} taxonomies");
        
        // Save proposal entity to persist all changes
        $proposal->save();
        
        error_log("ARSOL PFW DEBUG: Completed metadata copy from request #{$request_id} to proposal #{$proposal_id}");
    }

    /**
     * Copy proposal metadata to project
     * 
     * @param int $proposal_id Proposal ID
     * @param int $project_id Project ID
     */
    private function copy_proposal_metadata_to_project($proposal_id, $project_id) {
        // Debug: Log the start of metadata copy
        error_log("ARSOL PFW DEBUG: Starting metadata copy from proposal #{$proposal_id} to project #{$project_id}");
        
        // Get proposal and project objects using entity classes
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);
        $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($project_id);
        
        if (!$proposal || !$project) {
            error_log("ARSOL PFW DEBUG: Failed to load proposal or project objects");
            throw new Exception(__('Failed to load proposal or project for metadata copy.', 'arsol-pfw'));
        }
        
        error_log("ARSOL PFW DEBUG: Successfully loaded proposal and project objects");
        
        // 1. Preserve proposal content in project meta
        $proposal_description = $proposal->get_prop('description');
        $project->set_meta('_arsol_pfw_project_proposal_details', $proposal_description);
        error_log("ARSOL PFW DEBUG: Copied proposal description to project: " . ($proposal_description ? substr($proposal_description, 0, 50) . "..." : "null"));
        
        // 2. Rename request data with project context
        $request_meta_mapping = array(
            '_arsol_pfw_proposal_request_details' => '_arsol_pfw_project_request_details',
            '_arsol_pfw_proposal_request_title' => '_arsol_pfw_project_request_title',
            '_arsol_pfw_proposal_request_date' => '_arsol_pfw_project_request_date',
            '_arsol_pfw_proposal_request_budget' => '_arsol_pfw_project_request_budget',
            '_arsol_pfw_proposal_request_start_date' => '_arsol_pfw_project_request_start_date',
            '_arsol_pfw_proposal_request_due_date' => '_arsol_pfw_project_request_due_date',
            '_arsol_pfw_proposal_request_attachments' => '_arsol_pfw_project_request_attachments',
        );
        
        // 3. Rename proposal data with project context
        $proposal_meta_mapping = array(
            '_arsol_pfw_proposal_notes' => '_arsol_pfw_project_proposal_notes',
            '_arsol_pfw_proposal_costing_type' => '_arsol_pfw_project_proposal_costing_type',
            '_arsol_pfw_proposed_project_lead' => '_arsol_pfw_project_lead',
            '_arsol_pfw_proposed_project_due_date' => '_arsol_pfw_project_due_date', // Map due date to project due date
        );
        
        // 4. Get proposal type for type-aware handling
        $cost_proposal_type = $proposal->get_costing_type();
        error_log("ARSOL PFW DEBUG: Proposal costing type: {$cost_proposal_type}");
        
        // 5. Copy type-specific data using entity methods
        if ($cost_proposal_type === 'budget') {
            // Copy budget data using entity methods
            $budget_data = $proposal->get_proposal_budget();
            if (!empty($budget_data)) {
                $project->set_meta('_arsol_pfw_proposed_project_budget_line_items', $budget_data);
                error_log("ARSOL PFW DEBUG: Copied budget data using entity methods");
            }
        } elseif ($cost_proposal_type === 'quotation') {
            // Copy quotation data using entity methods
            $quotation_data = $proposal->get_proposal_quotation();
            if (!empty($quotation_data)) {
                $project->set_project_quotation($quotation_data);
                error_log("ARSOL PFW DEBUG: Copied quotation data using entity methods");
            }
        }
        
        // 6. Combine all mappings for remaining meta
        $meta_to_copy = array_merge($request_meta_mapping, $proposal_meta_mapping);
        error_log("ARSOL PFW DEBUG: Total meta keys to copy: " . count($meta_to_copy));
        
        // 7. Copy all remaining meta data
        $copied_count = 0;
        foreach ($meta_to_copy as $proposal_key => $project_key) {
            $value = $proposal->get_meta($proposal_key);
            if ($value) {
                $project->set_meta($project_key, $value);
                error_log("ARSOL PFW DEBUG: Copied meta key '{$proposal_key}' to '{$project_key}' with value: " . print_r($value, true));
                $copied_count++;
            } else {
                error_log("ARSOL PFW DEBUG: Meta key '{$proposal_key}' was empty or not found");
            }
        }
        error_log("ARSOL PFW DEBUG: Copied {$copied_count} mapped meta keys");
        
        // 8. Historical preservation - keep original proposal field names for reference
        $historical_fields = array(
            '_arsol_pfw_proposed_project_start_date',
            '_arsol_pfw_proposed_project_due_date',
        );
        
        $historical_copied_count = 0;
        foreach ($historical_fields as $field) {
            $value = $proposal->get_meta($field);
            if ($value) {
                $project->set_meta($field, $value);
                error_log("ARSOL PFW DEBUG: Copied historical field '{$field}' with value: " . print_r($value, true));
                $historical_copied_count++;
            }
        }
        error_log("ARSOL PFW DEBUG: Copied {$historical_copied_count} historical fields");
        
        // Store original proposal ID for reference
        $project->set_meta('_arsol_pfw_project_proposal_id', $proposal_id);
        error_log("ARSOL PFW DEBUG: Stored original proposal ID: {$proposal_id}");
        
        // Set default project status to not-started
        $project->set_stage('not-started');
        error_log("ARSOL PFW DEBUG: Set project status to 'not-started'");
        
        // Save project entity to persist all changes
        $project->save();
        
        error_log("ARSOL PFW DEBUG: Completed metadata copy from proposal #{$proposal_id} to project #{$project_id}");
    }

    /**
     * Add a WordPress admin notice using session-based approach for post edit screens
     * 
     * @param string $message Notice message
     * @param string $type Notice type (success, error, warning, info)
     */
    private function add_notice($message, $type = 'success') {
        // Store notice in session for display after redirect
        $notices = get_transient('arsol_pfw_conversion_notices') ?: array();
        $notices[] = array(
            'message' => $message,
            'type' => $type,
            'timestamp' => current_time('timestamp')
        );
        set_transient('arsol_pfw_conversion_notices', $notices, 300); // 5 minutes
        
        // Debug: Log notice storage
        error_log("ARSOL PFW: Stored notice - Type: {$type}, Message: {$message}");
        error_log("ARSOL PFW: Total notices in transient: " . count($notices));
    }

    /**
     * Display stored admin notices
     */
    public static function display_admin_notices() {
        $notices = get_transient('arsol_pfw_conversion_notices');
        
        // Debug: Log notice retrieval
        error_log("ARSOL PFW: Retrieved notices from transient: " . print_r($notices, true));
        
        if (!$notices) {
            error_log("ARSOL PFW: No notices found in transient");
            return;
        }

        foreach ($notices as $notice) {
            $notice_class = 'notice notice-' . $notice['type'] . ' is-dismissible';
            echo '<div class="' . esc_attr($notice_class) . '">';
            echo '<p>' . esc_html($notice['message']) . '</p>';
            echo '</div>';
        }

        // Clear the notices after displaying
        delete_transient('arsol_pfw_conversion_notices');
        error_log("ARSOL PFW: Cleared notices from transient");
    }

    /**
     * Create WooCommerce orders from proposal
     * 
     * @param int $proposal_id Proposal ID
     * @param int $project_id Project ID
     */
    private function create_woocommerce_orders($proposal_id, $project_id) {
        try {
            // Check if WooCommerce_Biller class exists
            if (!class_exists('\Arsol_Projects_For_Woo\Woocommerce_Biller')) {
                throw new Exception('WooCommerce billing system not available');
            }
            
            $biller = new \Arsol_Projects_For_Woo\Woocommerce_Biller();
            $result = $biller->convert_proposal_to_order($proposal_id, $project_id);
            
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            // Store created order IDs
            if (!empty($result['order_id'])) {
                update_post_meta($project_id, '_arsol_pfw_project_woocommerce_order_id', $result['order_id']);
            }
            
            if (!empty($result['subscription_id'])) {
                update_post_meta($project_id, '_arsol_pfw_project_woocommerce_subscription_id', $result['subscription_id']);
            }
            
            update_post_meta($project_id, '_arsol_pfw_project_order_creation_note', $result['message']);
            
        } catch (Exception $e) {
            // Log error but don't fail conversion
            error_log('WooCommerce order creation failed: ' . $e->getMessage());
            update_post_meta($project_id, '_arsol_pfw_project_order_creation_error', $e->getMessage());
        }
    }
} 