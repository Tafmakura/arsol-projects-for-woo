<?php
/**
 * Phases Conversion Class
 *
 * Handles all conversion logic between project phases (Request → Proposal → Project)
 *
 * @package Arsol_Projects_For_Woo\Phases
 * @version 1.0.0
 */

namespace Arsol_Projects_For_Woo\Phases;

if (!defined('ABSPATH')) {
    exit;
}

class Conversion {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance(): Conversion {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Initialize the conversion system
     */
    private function init(): void {
        $this->setup_hooks();
    }

    /**
     * Setup WordPress hooks for conversion actions
     */
    private function setup_hooks(): void {
        // Admin conversion hooks
        add_action('admin_post_arsol_convert_to_proposal', array($this, 'convert_request_to_proposal'));
        add_action('admin_post_arsol_convert_to_project', array($this, 'convert_proposal_to_project'));
        
        // Customer workflow actions
        add_action('admin_post_arsol_cancel_request', array($this, 'customer_cancel_request'));
        add_action('admin_post_arsol_approve_proposal', array($this, 'customer_approve_proposal'));
        add_action('admin_post_arsol_reject_proposal', array($this, 'customer_reject_proposal'));
        
        // Form handling
        add_action('admin_post_arsol_create_request', array($this, 'handle_create_request'));
        add_action('admin_post_arsol_edit_request', array($this, 'handle_edit_request'));
        
        // Admin notices
        add_action('admin_notices', array($this, 'display_conversion_notices'));
    }

    /**
     * Convert request to proposal
     */
    public function convert_request_to_proposal(): void {
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
            
            // Check for concurrent conversions
            if ($this->is_workflow_in_progress($request_id)) {
                $this->handle_stuck_workflow($request_id);
            }
            
            // Start conversion transaction
            $this->start_workflow_transaction($request_id, 'conversion', 'request_to_proposal');
            
            // Validate request stage
            $current_stage = wp_get_object_terms($request_id, 'arsol-pfw-request-stage', array('fields' => 'slugs'));
            if (empty($current_stage) || $current_stage[0] !== 'approved') {
                throw new Exception(sprintf(
                    __('This request cannot be converted. The stage is "%s", must be "approved".', 'arsol-pfw'),
                    empty($current_stage) ? 'none' : $current_stage[0]
                ));
            }

            // Prepare conversion data
            $conversion_data = array(
                'request_id' => $request_id,
                'user_id' => get_current_user_id(),
                'conversion_method' => 'admin_conversion',
                'timestamp' => current_time('timestamp'),
                'request_post' => $request_post,
                'request_stage' => $current_stage[0]
            );

            // Fire validation hooks
            do_action('arsol_before_proposal_conversion_validation', $request_id, $conversion_data);
            do_action('arsol_after_proposal_conversion_validated', $request_id, $request_post, $conversion_data);
            
            // Create proposal
            $proposal_args = array(
                'post_title'   => $request_post->post_title,
                'post_content' => '',
                'post_type'    => 'arsol-pfw-proposal',
                'post_status'  => 'publish',
                'post_author'  => $request_post->post_author,
                'post_parent'  => $request_id,
                'meta_input'   => array(
                    '_arsol_parent_request_id' => $request_id,
                    '_arsol_proposal_status' => 'pending-approval',
                    '_arsol_created_method' => 'admin_conversion',
                    '_arsol_created_by' => get_current_user_id()
                )
            );

            $proposal_args = apply_filters('arsol_proposal_creation_args', $proposal_args, $request_post);
            $proposal_id = wp_insert_post($proposal_args);

            if (is_wp_error($proposal_id)) {
                throw new Exception(sprintf(
                    __('Failed to create proposal: %s', 'arsol-pfw'),
                    $proposal_id->get_error_message()
                ));
            }

            // Copy metadata and complete conversion
            $this->record_transaction_entity($request_id, $proposal_id);
            $this->copy_request_metadata_to_proposal($request_id, $proposal_id);
            wp_set_object_terms($proposal_id, 'pending-approval', 'arsol-pfw-proposal-stage');
            $this->complete_workflow_transaction($request_id);

            // Log success and fire hooks
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_request_to_proposal_conversion('info', 
                "Successfully converted Request #{$request_id} to Proposal #{$proposal_id}");
            do_action('arsol_proposal_created_from_request', $proposal_id, $request_id, $request_post);

            // Set success notice and redirect
            $this->set_conversion_success_notice('request', 'proposal', $request_id, $proposal_id, $request_post->post_title);
            $this->safe_redirect(admin_url('post.php?action=edit&post=' . $proposal_id));

        } catch (Exception $e) {
            // Handle errors
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_request_to_proposal_conversion('error', 
                "Failed to convert Request #{$request_id}: " . $e->getMessage());

            $this->rollback_workflow_transaction($request_id, $e->getMessage());

            if ($request_post) {
                $this->set_conversion_failure_notice('request', 'proposal', $request_id, $request_post->post_title, $e->getMessage());
            }

            $this->safe_redirect(admin_url('post.php?action=edit&post=' . $request_id));
        }
    }

    /**
     * Convert proposal to project
     */
    public function convert_proposal_to_project($proposal_id = 0, $is_internal_call = false): void {
        if (!$is_internal_call && !wp_verify_nonce($_GET['_wpnonce'], 'arsol_convert_to_project_nonce')) {
            wp_die(__('Invalid nonce.', 'arsol-pfw'));
        }

        $proposal_id = $proposal_id ?: intval($_GET['proposal_id']);
        $proposal_post = get_post($proposal_id);
        
        if (!$proposal_post || $proposal_post->post_type !== 'arsol-pfw-proposal') {
            wp_die(__('Invalid proposal.', 'arsol-pfw'));
        }

        if (!current_user_can('edit_post', $proposal_id)) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
        }

        try {
            // Validate proposal stage
            $current_stage = wp_get_object_terms($proposal_id, 'arsol-pfw-proposal-stage', array('fields' => 'slugs'));
            if (empty($current_stage) || $current_stage[0] !== 'approved') {
                throw new Exception(sprintf(
                    __('This proposal cannot be converted. The stage is "%s", must be "approved".', 'arsol-pfw'),
                    empty($current_stage) ? 'none' : $current_stage[0]
                ));
            }

            // Start conversion transaction
            $this->start_workflow_transaction($proposal_id, 'conversion', 'proposal_to_project');
            
            // Create project
            $project_args = array(
                'post_title'   => $proposal_post->post_title,
                'post_content' => $proposal_post->post_content,
                'post_type'    => 'arsol-pfw-project',
                'post_status'  => 'publish',
                'post_author'  => $proposal_post->post_author,
                'post_parent'  => $proposal_id,
                'meta_input'   => array(
                    '_arsol_parent_proposal_id' => $proposal_id,
                    '_arsol_created_method' => 'admin_conversion',
                    '_arsol_created_by' => get_current_user_id()
                )
            );

            $project_id = wp_insert_post($project_args);

            if (is_wp_error($project_id)) {
                throw new Exception(sprintf(
                    __('Failed to create project: %s', 'arsol-pfw'),
                    $project_id->get_error_message()
                ));
            }

            // Copy metadata and complete conversion
            $this->record_transaction_entity($proposal_id, $project_id);
            $this->copy_proposal_metadata_to_project($proposal_id, $project_id);
            wp_set_object_terms($project_id, 'not-started', 'arsol-pfw-project-stage');
            $this->complete_workflow_transaction($proposal_id);

            // Log success and fire hooks
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_proposal_to_project_conversion('info', 
                "Successfully converted Proposal #{$proposal_id} to Project #{$project_id}");
            do_action('arsol_project_created_from_proposal', $project_id, $proposal_id, $proposal_post);

            // Set success notice and redirect
            $this->set_conversion_success_notice('proposal', 'project', $proposal_id, $project_id, $proposal_post->post_title);
            $this->safe_redirect(admin_url('post.php?action=edit&post=' . $project_id));

        } catch (Exception $e) {
            // Handle errors
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_proposal_to_project_conversion('error', 
                "Failed to convert Proposal #{$proposal_id}: " . $e->getMessage());

            $this->rollback_workflow_transaction($proposal_id, $e->getMessage());
            $this->set_conversion_failure_notice('proposal', 'project', $proposal_id, $proposal_post->post_title, $e->getMessage());
            $this->safe_redirect(admin_url('post.php?action=edit&post=' . $proposal_id));
        }
    }

    /**
     * Customer cancel request
     */
    public function customer_cancel_request(): void {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'arsol_cancel_request_nonce')) {
            wp_die(__('Invalid nonce.', 'arsol-pfw'));
        }

        $request_id = intval($_GET['request_id']);
        $request_post = get_post($request_id);
        
        if (!$request_post || $request_post->post_type !== 'arsol-pfw-request') {
            wp_die(__('Invalid request.', 'arsol-pfw'));
        }

        if (!current_user_can('edit_post', $request_id)) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
        }

        wp_set_object_terms($request_id, 'cancelled', 'arsol-pfw-request-stage');
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_customer_action('info', 
            "Customer cancelled request #{$request_id}");
        $this->safe_redirect(wp_get_referer() ?: home_url());
    }

    /**
     * Customer approve proposal
     */
    public function customer_approve_proposal(): void {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'arsol_approve_proposal_nonce')) {
            wp_die(__('Invalid nonce.', 'arsol-pfw'));
        }

        $proposal_id = intval($_GET['proposal_id']);
        $proposal_post = get_post($proposal_id);
        
        if (!$proposal_post || $proposal_post->post_type !== 'arsol-pfw-proposal') {
            wp_die(__('Invalid proposal.', 'arsol-pfw'));
        }

        if (!current_user_can('edit_post', $proposal_id)) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
        }

        wp_set_object_terms($proposal_id, 'approved', 'arsol-pfw-proposal-stage');
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_customer_action('info', 
            "Customer approved proposal #{$proposal_id}");
        $this->safe_redirect(wp_get_referer() ?: home_url());
    }

    /**
     * Customer reject proposal
     */
    public function customer_reject_proposal(): void {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'arsol_reject_proposal_nonce')) {
            wp_die(__('Invalid nonce.', 'arsol-pfw'));
        }

        $proposal_id = intval($_GET['proposal_id']);
        $proposal_post = get_post($proposal_id);
        
        if (!$proposal_post || $proposal_post->post_type !== 'arsol-pfw-proposal') {
            wp_die(__('Invalid proposal.', 'arsol-pfw'));
        }

        if (!current_user_can('edit_post', $proposal_id)) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
        }

        wp_set_object_terms($proposal_id, 'rejected', 'arsol-pfw-proposal-stage');
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_customer_action('info', 
            "Customer rejected proposal #{$proposal_id}");
        $this->safe_redirect(wp_get_referer() ?: home_url());
    }

    /**
     * Handle create request
     */
    public function handle_create_request(): void {
        if (!wp_verify_nonce($_POST['arsol_create_request_nonce'], 'arsol_create_request_action')) {
            wp_die(__('Invalid nonce.', 'arsol-pfw'));
        }

        if (!current_user_can('publish_posts')) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
        }

        $request_data = array(
            'post_title' => sanitize_text_field($_POST['arsol_request_title']),
            'post_content' => wp_kses_post($_POST['arsol_request_content']),
            'post_type' => 'arsol-pfw-request',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        );

        $request_id = wp_insert_post($request_data);

        if (is_wp_error($request_id)) {
            wp_die(__('Failed to create request.', 'arsol-pfw'));
        }

        $this->update_request_meta($request_id, $_POST);
        wp_set_object_terms($request_id, 'pending', 'arsol-pfw-request-stage');

        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_request_creation('info', 
            "Created request #{$request_id}: " . $request_data['post_title']);

        $this->safe_redirect(admin_url('post.php?action=edit&post=' . $request_id));
    }

    /**
     * Handle edit request
     */
    public function handle_edit_request(): void {
        // Similar implementation to create but for editing existing request
        // Implementation would handle updating existing request data
    }

    // ===========================================
    // TRANSACTION MANAGEMENT METHODS
    // ===========================================

    /**
     * Start workflow transaction
     */
    public function start_workflow_transaction($source_id, $workflow_type, $conversion_type): void {
        update_post_meta($source_id, '_arsol_workflow_in_progress', '1');
        update_post_meta($source_id, '_arsol_workflow_type', $workflow_type);
        update_post_meta($source_id, '_arsol_workflow_conversion_type', $conversion_type);
        update_post_meta($source_id, '_arsol_workflow_started', current_time('mysql'));
        update_post_meta($source_id, '_arsol_workflow_step', 'started');
        delete_post_meta($source_id, '_arsol_workflow_created_entities');
    }

    /**
     * Record transaction entity
     */
    public function record_transaction_entity($source_id, $entity_id): void {
        $entities = get_post_meta($source_id, '_arsol_workflow_created_entities', true);
        $entities = is_array($entities) ? $entities : array();
        $entities[] = $entity_id;
        update_post_meta($source_id, '_arsol_workflow_created_entities', $entities);
    }

    /**
     * Complete workflow transaction
     */
    public function complete_workflow_transaction($source_id): void {
        update_post_meta($source_id, '_arsol_workflow_step', 'completed');
        update_post_meta($source_id, '_arsol_workflow_completed', current_time('mysql'));
        delete_post_meta($source_id, '_arsol_workflow_in_progress');
    }

    /**
     * Rollback workflow transaction
     */
    public function rollback_workflow_transaction($source_id, $reason): void {
        update_post_meta($source_id, '_arsol_workflow_step', 'rollback');
        update_post_meta($source_id, '_arsol_workflow_rollback_reason', $reason);
        update_post_meta($source_id, '_arsol_workflow_rollback_time', current_time('mysql'));
        
        $conversion_type = get_post_meta($source_id, '_arsol_workflow_conversion_type', true);
        
        switch ($conversion_type) {
            case 'request_to_proposal':
                $this->rollback_request_to_proposal($source_id);
                break;
            case 'proposal_to_project':
                $this->rollback_proposal_to_project($source_id);
                break;
        }
        
        delete_post_meta($source_id, '_arsol_workflow_in_progress');
        delete_post_meta($source_id, '_arsol_workflow_step');
    }

    /**
     * Rollback request to proposal conversion
     */
    private function rollback_request_to_proposal($source_id): void {
        $entities = get_post_meta($source_id, '_arsol_workflow_created_entities', true);
        
        if (is_array($entities) && !empty($entities)) {
            foreach ($entities as $entity_id) {
                wp_delete_post($entity_id, true);
                \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                    "Rollback: Deleted proposal #{$entity_id} created from request #{$source_id}");
            }
        }
        
        delete_post_meta($source_id, '_arsol_workflow_created_entities');
    }

    /**
     * Rollback proposal to project conversion
     */
    private function rollback_proposal_to_project($source_id): void {
        $entities = get_post_meta($source_id, '_arsol_workflow_created_entities', true);
        
        if (is_array($entities) && !empty($entities)) {
            foreach ($entities as $entity_id) {
                $post = get_post($entity_id);
                
                if ($post && $post->post_type === 'arsol-pfw-project') {
                    wp_delete_post($entity_id, true);
                    \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                        "Rollback: Deleted project #{$entity_id} created from proposal #{$source_id}");
                }
            }
        }
        
        delete_post_meta($source_id, '_arsol_workflow_created_entities');
    }

    /**
     * Check if workflow is in progress
     */
    public function is_workflow_in_progress($source_id): bool {
        $in_progress = get_post_meta($source_id, '_arsol_workflow_in_progress', true);
        return $in_progress === '1';
    }

    /**
     * Handle stuck workflow
     */
    private function handle_stuck_workflow($source_id): void {
        $workflow_started = get_post_meta($source_id, '_arsol_workflow_started', true);
        $is_stuck = false;
        
        if ($workflow_started) {
            $started_time = strtotime($workflow_started);
            $current_time = current_time('timestamp');
            $age_minutes = ($current_time - $started_time) / 60;
            
            if ($age_minutes > 0.5) { // 30 seconds
                $is_stuck = true;
                \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('warning', 
                    "Detected stuck workflow for request #{$source_id}, age: {$age_minutes} minutes. Auto-clearing...");
            }
        }
        
        if ($is_stuck) {
            $this->force_clear_stuck_workflow($source_id);
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                "Successfully cleared stuck workflow for request #{$source_id}. Proceeding with conversion...");
        } else {
            throw new Exception(__('Conversion already in progress.', 'arsol-pfw'));
        }
    }

    /**
     * Force clear stuck workflow
     */
    public function force_clear_stuck_workflow($post_id): void {
        delete_post_meta($post_id, '_arsol_workflow_in_progress');
        delete_post_meta($post_id, '_arsol_workflow_step');
        update_post_meta($post_id, '_arsol_workflow_force_cleared', current_time('mysql'));
        
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
            "Force cleared stuck workflow for post #{$post_id}");
    }

    // ===========================================
    // METADATA COPYING METHODS
    // ===========================================

    /**
     * Copy request metadata to proposal
     */
    private function copy_request_metadata_to_proposal($request_id, $proposal_id): void {
        $metadata_to_copy = array(
            '_arsol_request_budget' => '_arsol_proposal_budget',
            '_arsol_request_timeline' => '_arsol_proposal_timeline',
            '_arsol_request_description' => '_arsol_proposal_description',
            '_arsol_request_requirements' => '_arsol_proposal_requirements',
            '_arsol_request_priority' => '_arsol_proposal_priority',
            '_arsol_request_category' => '_arsol_proposal_category',
            '_arsol_request_files' => '_arsol_proposal_files',
            '_arsol_request_notes' => '_arsol_proposal_notes',
            '_arsol_request_contact_email' => '_arsol_proposal_contact_email',
            '_arsol_request_contact_phone' => '_arsol_proposal_contact_phone',
            '_arsol_request_preferred_start_date' => '_arsol_proposal_preferred_start_date',
            '_arsol_request_additional_info' => '_arsol_proposal_additional_info',
            '_arsol_request_custom_fields' => '_arsol_proposal_custom_fields',
            '_arsol_request_project_type' => '_arsol_proposal_project_type',
            '_arsol_request_estimated_hours' => '_arsol_proposal_estimated_hours',
            '_arsol_request_complexity' => '_arsol_proposal_complexity',
            '_arsol_request_technology_stack' => '_arsol_proposal_technology_stack',
            '_arsol_request_special_requirements' => '_arsol_proposal_special_requirements',
            '_arsol_request_delivery_format' => '_arsol_proposal_delivery_format',
            '_arsol_request_success_criteria' => '_arsol_proposal_success_criteria',
            '_arsol_request_constraints' => '_arsol_proposal_constraints',
            '_arsol_request_stakeholders' => '_arsol_proposal_stakeholders',
            '_arsol_request_approval_process' => '_arsol_proposal_approval_process',
            '_arsol_request_maintenance_requirements' => '_arsol_proposal_maintenance_requirements',
            '_arsol_request_integration_requirements' => '_arsol_proposal_integration_requirements',
            '_arsol_request_security_requirements' => '_arsol_proposal_security_requirements',
            '_arsol_request_performance_requirements' => '_arsol_proposal_performance_requirements',
        );

        foreach ($metadata_to_copy as $request_meta_key => $proposal_meta_key) {
            $value = get_post_meta($request_id, $request_meta_key, true);
            if (!empty($value)) {
                update_post_meta($proposal_id, $proposal_meta_key, $value);
            }
        }
    }

    /**
     * Copy proposal metadata to project
     */
    private function copy_proposal_metadata_to_project($proposal_id, $project_id): void {
        // Preserve proposal content in project meta
        $proposal_post = get_post($proposal_id);
        update_post_meta($project_id, '_arsol_pfw_project_proposal_details', $proposal_post->post_content);
        
        // Request data mapping
        $request_meta_mapping = array(
            '_arsol_pfw_proposal_request_details' => '_arsol_pfw_project_request_details',
            '_arsol_pfw_proposal_request_title' => '_arsol_pfw_project_request_title',
            '_arsol_pfw_proposal_request_date' => '_arsol_pfw_project_request_date',
            '_arsol_pfw_proposal_request_budget' => '_arsol_pfw_project_request_budget',
            '_arsol_pfw_proposal_request_start_date' => '_arsol_pfw_project_request_start_date',
            '_arsol_pfw_proposal_request_delivery_date' => '_arsol_pfw_project_request_delivery_date',
            '_arsol_pfw_proposal_request_attachments' => '_arsol_pfw_project_request_attachments',
        );
        
        // Proposal data mapping
        $proposal_meta_mapping = array(
            '_arsol_pfw_proposal_notes' => '_arsol_pfw_project_proposal_notes',
            '_arsol_pfw_proposal_costing_type' => '_arsol_pfw_project_proposal_costing_type',
            '_arsol_pfw_proposal_project_lead' => '_arsol_pfw_project_lead',
        );
        
        // Type-specific mapping based on proposal type
        $cost_proposal_type = get_post_meta($proposal_id, '_arsol_pfw_proposal_costing_type', true) ?: 'none';
        $type_specific_mapping = array();
        
        if ($cost_proposal_type === 'budget') {
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
            $type_specific_mapping = array(
                '_arsol_pfw_proposal_quotation_line_items' => '_arsol_pfw_project_proposal_quotation_line_items',
                '_arsol_pfw_proposal_quotation_onetime_total' => '_arsol_pfw_project_proposal_quotation_onetime_total',
                '_arsol_pfw_proposal_quotation_recurring_totals_grouped' => '_arsol_pfw_project_proposal_quotation_recurring_average_total',
                '_arsol_pfw_proposal_quotation_currency' => '_arsol_pfw_project_proposal_quotation_currency',
                '_arsol_pfw_proposal_quotation_currency_symbol' => '_arsol_pfw_project_proposal_quotation_currency_symbol',
            );
        }
        
        // Combine all mappings
        $meta_to_copy = array_merge($request_meta_mapping, $proposal_meta_mapping, $type_specific_mapping);
        $meta_to_copy = apply_filters('arsol_project_conversion_meta_mapping', $meta_to_copy, $project_id, $proposal_id, $cost_proposal_type, array());

        // Copy all metadata
        foreach ($meta_to_copy as $proposal_key => $project_key) {
            $value = get_post_meta($proposal_id, $proposal_key, true);
            if ($value) {
                update_post_meta($project_id, $project_key, $value);
            }
        }

        // Store original proposal ID for reference
        update_post_meta($project_id, '_arsol_pfw_project_proposal_id', $proposal_id);
        wp_set_object_terms($project_id, 'not-started', 'arsol-pfw-project-stage');

        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_proposal_to_project_conversion('info', 
            sprintf('Metadata copied from proposal #%d to project #%d (type: %s): %s', 
                $proposal_id, $project_id, $cost_proposal_type, implode(', ', array_keys($meta_to_copy))));

        do_action('arsol_after_project_conversion_metadata_copied', $project_id, $proposal_id, $meta_to_copy, $cost_proposal_type, array());
    }

    // ===========================================
    // UTILITY METHODS
    // ===========================================

    /**
     * Update request metadata
     */
    private function update_request_meta($post_id, $data): void {
        // Update various request metadata fields
        // This would handle sanitization and updating of all request fields
    }

    /**
     * Safe redirect
     */
    private function safe_redirect($url): void {
        wp_redirect($url);
        exit;
    }

    /**
     * Set conversion success notice
     */
    private function set_conversion_success_notice($from_type, $to_type, $from_id, $to_id, $title): void {
        $type_names = array(
            'request' => __('Project Request', 'arsol-pfw'),
            'proposal' => __('Project Proposal', 'arsol-pfw'),
            'project' => __('Project', 'arsol-pfw')
        );
        
        $from_name = isset($type_names[$from_type]) ? $type_names[$from_type] : ucfirst($from_type);
        $to_name = isset($type_names[$to_type]) ? $type_names[$to_type] : ucfirst($to_type);
        
        $message = sprintf(
            __('Successfully converted %s "%s" to %s. You can now edit the new %s.', 'arsol-pfw'),
            $from_name,
            esc_html($title),
            $to_name,
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
    private function set_conversion_failure_notice($from_type, $to_type, $from_id, $title, $error): void {
        $type_names = array(
            'request' => __('Project Request', 'arsol-pfw'),
            'proposal' => __('Project Proposal', 'arsol-pfw'),
            'project' => __('Project', 'arsol-pfw')
        );
        
        $from_name = isset($type_names[$from_type]) ? $type_names[$from_type] : ucfirst($from_type);
        $to_name = isset($type_names[$to_type]) ? $type_names[$to_type] : ucfirst($to_type);
        
        $message = sprintf(
            __('Failed to convert %s "%s" to %s: %s', 'arsol-pfw'),
            $from_name,
            esc_html($title),
            $to_name,
            esc_html($error)
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
    public function display_conversion_notices(): void {
        $user_id = get_current_user_id();
        $notice_data = get_transient('arsol_notice_' . $user_id);
        
        if ($notice_data && is_array($notice_data)) {
            $class = 'notice notice-' . $notice_data['type'] . ' is-dismissible';
            printf('<div class="%s"><p>%s</p></div>', esc_attr($class), esc_html($notice_data['message']));
            
            // Clear the notice after displaying
            delete_transient('arsol_notice_' . $user_id);
        }
    }

    /**
     * Set admin notice
     */
    private function set_admin_notice($type, $message, $details = array()): void {
        $notice_data = array(
            'type' => $type,
            'message' => $message,
            'details' => $details,
            'timestamp' => current_time('timestamp')
        );
        
        $user_id = get_current_user_id();
        set_transient('arsol_notice_' . $user_id, $notice_data, 300); // 5 minutes
    }

    /**
     * Debug proposal conversion
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
} 