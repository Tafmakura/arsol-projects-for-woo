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
            'post_title' => $request_entity->get_title(),
            'post_content' => $request_entity->get_content(),
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
            'meta_input' => array(
                '_arsol_pfw_proposal_customer_id' => $request_entity->get_customer_id(), // Customer
                '_arsol_pfw_proposed_project_start_date' => $request_entity->get_requested_project_start_date(),
                '_arsol_pfw_proposed_project_due_date' => $request_entity->get_requested_project_due_date(),
                '_arsol_pfw_request_id' => $request_id,
                '_arsol_pfw_proposal_created_via' => 'request_conversion'
            )
        );
        
        // Create the proposal
        $proposal_id = wp_insert_post($proposal_data);
        
        if (is_wp_error($proposal_id)) {
            return $proposal_id;
        }
        
        // Copy request data based on type
        $request_budget = $request_entity->get_requested_project_budget();
        $cost_request_type = !empty($request_budget) ? 'budget' : 'none';
        
        switch ($cost_request_type) {
            case 'budget':
                // Copy budget data
                if (!empty($request_budget)) {
                    $proposal_entity = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);
                    $proposal_entity->set_budget($request_budget);
                    $proposal_entity->save();
                }
                break;
        }
        
        // Copy metadata and taxonomies
        $this->copy_request_metadata_to_proposal($request_id, $proposal_id);
        
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);
        $proposal->set_stage('processing');
        
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
            $project->set_budget($proposal_obj->get_budget());
            $project->set_description($proposal_obj->get_description());      
            
            if (!$project) {
                error_log("ARSOL PFW DEBUG: Failed to create project object");
                throw new Exception(__("Failed to create project object.", "arsol-pfw"));
            }
            
            $project_id = $project->save();
            if (!$project_id || is_wp_error($project_id)) {
                error_log("ARSOL PFW DEBUG: Failed to save project");
                throw new Exception(__("Failed to save project.", "arsol-pfw"));
            }

            // Reload the project object to ensure it has the ID before setting the stage
            $project = arsol_pfw_get_project($project_id);
            if (!$project) {
                error_log("ARSOL PFW DEBUG: Failed to reload project after saving. ID: {$project_id}");
                throw new Exception(__("Failed to reload project after saving.", "arsol-pfw"));
            }
            
            error_log("ARSOL PFW DEBUG: Project created with ID: {$project_id}");
            
            // Set initial stage after project is saved
            $project->set_stage("not-started");
            
            // 4. Copy metadata
            error_log("ARSOL PFW DEBUG: Starting metadata copy...");
            $this->copy_proposal_metadata_to_project($proposal_id, $project_id);
            
            // 5. Handle WooCommerce orders (if needed)
            $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);
            $cost_type = $proposal->get_costing_type();
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
        $request = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($request_id);
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);
        
        // Organize all request data into a single array
        $request_data = array(
            'id' => $request_id,
            'title' => $request->get_title(),
            'date' => $request->get_date_created(),
            'expiration_date' => $request->get_expiration_date(),
            'customer_notice' => $request->get_customer_notice(),
            'details' => $request->get_details(),
            'requested_project_start_date' => $request->get_requested_project_start_date(),
            'requested_project_due_date' => $request->get_requested_project_due_date(),
            'requested_project_budget' => $request->get_requested_project_budget(),
            'attachments' => $request->get_attachments(),
        );
        
        // Save organized request data
        $proposal->set_request_data($request_data);
        
        // Mark request as converted
        $request->set_converted_to_proposal(true);
        $request->save();
        
        error_log("ARSOL PFW DEBUG: Copied organized request data to proposal #{$proposal_id}");
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
        
        // 1. Organize all proposal data into a single array
        $proposal_data = array(
            'id' => $proposal_id,
            'title' => $proposal->get_title(),
            'date' => $proposal->get_date_created(),
            'expiration_date' => $proposal->get_expiration_date(),
            'customer_notice' => $proposal->get_customer_notice(),
            'details' => $proposal->get_details(),
            'costing_type' => $proposal->get_costing_type(),
            'proposed_project_start_date' => $proposal->get_start_date(),
            'proposed_project_due_date' => $proposal->get_due_date(),
            'proposed_project_manager' => $proposal->get_project_manager(),
            'proposed_project_budget' => $proposal->get_budget(),
            'proposed_project_quotation' => $proposal->get_quotation(),
            'attachments' => $proposal->get_attachments(),
        );
        
        // 2. Organize all request data (from proposal's historical data)
        $request_data = $proposal->get_request_data();
        
        // 3. Save organized data to project
        $project->set_proposal_data($proposal_data);
        if (!empty($request_data)) {
            $project->set_request_data($request_data);
        }
        
        // 4. Copy current proposal data to project's own fields
        $project->set_customer_id($proposal->get_customer_id());
        $project->set_description($proposal->get_description());
        
        // Copy budget/quotation based on costing type
        $costing_type = $proposal->get_costing_type();
        if ($costing_type === 'budget') {
            $budget_data = $proposal->get_budget();
            if (!empty($budget_data)) {
                $project->set_budget($budget_data);
            }
        } elseif ($costing_type === 'quotation') {
            $quotation_data = $proposal->get_quotation();
            if (!empty($quotation_data)) {
                $project->set_quotation($quotation_data);
            }
        }
        
        // 5. Set project due date (with fallback logic)
        $project_due_date = $project->get_due_date();
        if (empty($project_due_date)) {
            $proposed_due_date = $proposal->get_due_date();
            if (!empty($proposed_due_date)) {
                $project->set_due_date($proposed_due_date);
            }
        }
        
        // 6. Store original proposal ID for reference
        $project->set_meta('_arsol_pfw_project_proposal_id', $proposal_id);
        
        // 7. Set default project status
        $project->set_stage('not-started');
        
        // 8. Save project entity to persist all changes
        $project->save();
        
        error_log("ARSOL PFW DEBUG: Completed organized metadata copy from proposal #{$proposal_id} to project #{$project_id}");
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
            // Check if Biller_Invoice class exists
            if (!class_exists('\Arsol_Projects_For_Woo\Integrations\WooCommerce\Biller_Invoice')) {
                throw new Exception(__('WooCommerce billing integration not available.', 'arsol-pfw'));
            }
            
            $biller = new \Arsol_Projects_For_Woo\Integrations\WooCommerce\Biller_Invoice();
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