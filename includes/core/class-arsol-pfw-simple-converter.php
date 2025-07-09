<?php

namespace Arsol_Projects_For_Woo\Core;

use Exception;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Simple Converter Class
 * Handles conversions with minimal complexity while maintaining reliability
 */
class Simple_Converter {

    /**
     * Convert a Request to a Proposal
     * 
     * @param int $request_id Request ID to convert
     * @return void
     */
    public function convert_request_to_proposal($request_id) {
        try {
            // 1. Security check
            if (!wp_verify_nonce($_GET['_wpnonce'], 'arsol_convert_to_proposal_nonce')) {
                throw new Exception(__('Security check failed.', 'arsol-pfw'));
            }
            
            // 2. Basic validation
            $request = get_post($request_id);
            if (!$request || $request->post_type !== 'arsol-pfw-request') {
                throw new Exception(__('Invalid request.', 'arsol-pfw'));
            }
            
            if ($request->post_status !== 'publish') {
                throw new Exception(__('Only published requests can be converted.', 'arsol-pfw'));
            }
            
            // 3. Create proposal using factory
            $request_obj = arsol_pfw_get_request($request_id);
            if (!$request_obj) {
                throw new Exception(__('Request not found.', 'arsol-pfw'));
            }
            
            $proposal = arsol_pfw_create_proposal([
                'name' => $request_obj->get_name(),
                'customer_id' => $request_obj->get_customer_id(),
                'budget' => $request_obj->get_budget(),
                'description' => $request_obj->get_prop('description'),
                'stage' => 'processing'
            ]);
            
            if (is_wp_error($proposal)) {
                throw new Exception($proposal->get_error_message());
            }
            
            $proposal_id = $proposal->save();
            if (is_wp_error($proposal_id)) {
                throw new Exception($proposal_id->get_error_message());
            }
            
            // 4. Copy additional metadata
            $this->copy_request_metadata_to_proposal($request_id, $proposal_id);
            
            // 5. Delete original
            wp_delete_post($request_id, true);
            
            // 6. Success
            $this->add_notice(__('Request converted to proposal successfully.', 'arsol-pfw'), 'updated');
            
            // Use Settings API redirect pattern
            wp_redirect(add_query_arg('settings-updated', 'true', admin_url("post.php?post={$proposal_id}&action=edit")));
            exit;
            
        } catch (Exception $e) {
            // Simple error handling
            $this->add_notice(__('Conversion failed: ', 'arsol-pfw') . $e->getMessage(), 'error');
            wp_redirect(add_query_arg('settings-updated', 'true', admin_url("post.php?post={$request_id}&action=edit")));
            exit;
        }
    }

    /**
     * Convert a Proposal to a Project
     * 
     * @param int $proposal_id Proposal ID to convert
     * @param bool $is_internal_call Whether this is an internal call
     * @return void
     */
    public function convert_proposal_to_project($proposal_id, $is_internal_call = false) {
        try {
            // 1. Security check (skip for internal calls)
            if (!$is_internal_call) {
                if (!wp_verify_nonce($_GET['_wpnonce'], 'arsol_convert_to_project_nonce')) {
                    throw new Exception(__('Security check failed.', 'arsol-pfw'));
                }
            }
            
            // 2. Basic validation
            $proposal = get_post($proposal_id);
            if (!$proposal || $proposal->post_type !== 'arsol-pfw-proposal') {
                throw new Exception(__('Invalid proposal.', 'arsol-pfw'));
            }
            
            if ($proposal->post_status !== 'publish') {
                throw new Exception(__('Only published proposals can be converted.', 'arsol-pfw'));
            }
            
            // 3. Create project using factory
            $proposal_obj = arsol_pfw_get_proposal($proposal_id);
            if (!$proposal_obj) {
                throw new Exception(__('Proposal not found.', 'arsol-pfw'));
            }
            
            $project = arsol_pfw_create_project([
                'name' => $proposal_obj->get_name(),
                'customer_id' => $proposal_obj->get_customer_id(),
                'budget' => $proposal_obj->get_budget(),
                'description' => $proposal_obj->get_prop('description'),
                'stage' => 'not-started'
            ]);
            
            if (is_wp_error($project)) {
                throw new Exception($project->get_error_message());
            }
            
            $project_id = $project->save();
            if (is_wp_error($project_id)) {
                throw new Exception($project_id->get_error_message());
            }
            
            // 4. Copy metadata
            $this->copy_proposal_metadata_to_project($proposal_id, $project_id);
            
            // 5. Handle WooCommerce orders (if needed)
            $cost_type = get_post_meta($proposal_id, '_arsol_pfw_proposal_costing_type', true);
            if ($cost_type === 'quotation') {
                $this->create_woocommerce_orders($proposal_id, $project_id);
            }
            
            // 6. Delete original
            wp_delete_post($proposal_id, true);
            
            // 7. Success
            $this->add_notice(__('Proposal converted to project successfully.', 'arsol-pfw'), 'updated');
            
            // Use Settings API redirect pattern
            wp_redirect(add_query_arg('settings-updated', 'true', admin_url("post.php?post={$project_id}&action=edit")));
            exit;
            
        } catch (Exception $e) {
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
        // Get request and proposal objects using factory functions
        $request = arsol_pfw_get_request($request_id);
        $proposal = arsol_pfw_get_proposal($proposal_id);
        
        if (!$request || !$proposal) {
            throw new Exception(__('Failed to load request or proposal for metadata copy.', 'arsol-pfw'));
        }
        
        // 1. Preserve original request content in proposal meta
        update_post_meta($proposal_id, '_arsol_pfw_proposal_request_details', $request->get_prop('description'));
        
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
        $request_budget = $request->get_budget();
        if (!empty($request_budget)) {
            update_post_meta($proposal_id, '_arsol_pfw_proposal_costing_type', 'budget');
            update_post_meta($proposal_id, '_arsol_pfw_proposal_budget_onetime_amount', $request_budget);
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

    /**
     * Copy proposal metadata to project
     * 
     * @param int $proposal_id Proposal ID
     * @param int $project_id Project ID
     */
    private function copy_proposal_metadata_to_project($proposal_id, $project_id) {
        // Get proposal and project objects using factory functions
        $proposal = arsol_pfw_get_proposal($proposal_id);
        $project = arsol_pfw_get_project($project_id);
        
        if (!$proposal || !$project) {
            throw new Exception(__('Failed to load proposal or project for metadata copy.', 'arsol-pfw'));
        }
        
        // 1. Preserve proposal content in project meta
        update_post_meta($project_id, '_arsol_pfw_project_proposal_details', $proposal->get_prop('description'));
        
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
            '_arsol_pfw_proposal_delivery_date' => '_arsol_pfw_project_due_date',
        );
        
        // 4. Get proposal type for type-aware handling
        $cost_proposal_type = get_post_meta($proposal_id, '_arsol_pfw_proposal_costing_type', true) ?: 'none';
        
        // 5. Type-specific mapping
        $type_specific_mapping = array();
        if ($cost_proposal_type === 'budget') {
            $type_specific_mapping = array(
                '_arsol_pfw_proposal_budget_onetime_amount' => '_arsol_pfw_project_proposal_budget_onetime_amount',
                '_arsol_pfw_proposal_budget_recurring_amount' => '_arsol_pfw_project_proposal_budget_recurring_amount',
                '_arsol_pfw_proposal_budget_recurring_amount_billing_interval' => '_arsol_pfw_project_billing_interval',
                '_arsol_pfw_proposal_budget_recurring_amount_billing_period' => '_arsol_pfw_project_billing_period',
                '_arsol_pfw_proposal_budget_recurring_billing_start_date' => '_arsol_pfw_project_recurring_start_date',
            );
        } elseif ($cost_proposal_type === 'quotation') {
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
                update_post_meta($project_id, $field, $value);
            }
        }
        
        // Store original proposal ID for reference
        update_post_meta($project_id, '_arsol_pfw_project_proposal_id', $proposal_id);
        
        // Set default project status to not-started
        wp_set_object_terms($project_id, 'not-started', 'arsol-pfw-project-stage');
    }

    /**
     * Add a WordPress admin notice using Settings API best practice
     * 
     * @param string $message Notice message
     * @param string $type Notice type (updated, error, notice-warning, notice-info)
     */
    private function add_notice($message, $type = 'updated') {
        // Use WordPress Settings API for admin notices
        add_settings_error(
            'arsol_pfw_messages', // slug
            'arsol_pfw_conversion_notice', // unique ID
            $message, // message
            $type // type: 'updated', 'error', 'notice-warning', 'notice-info'
        );
    }

    /**
     * Display stored admin notices using Settings API
     */
    public static function display_admin_notices() {
        // Display any settings errors/notices
        settings_errors('arsol_pfw_messages');
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