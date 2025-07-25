<?php

namespace Arsol_Projects_For_Woo\Data_Stores;

if (!defined('ABSPATH')) {
    exit;
}

class Proposal_Data_Store {
    
    // Simple meta keys mapping
    protected $meta_keys = array(
        'budget'      => '_arsol_pfw_proposal_budget',
        'quotation'   => '_arsol_pfw_proposal_quotation',
        'due_date'    => '_arsol_pfw_proposal_due_date',
        'customer_id' => '_arsol_pfw_proposal_customer_id',
        'start_date'  => '_arsol_pfw_proposal_start_date',
        'project_manager' => '_arsol_pfw_proposed_project_manager',
        'costing_type' => '_arsol_pfw_proposal_costing_type',
        'customer_notice' => '_arsol_pfw_proposal_customer_notice',
        'created_via' => '_arsol_pfw_proposal_created_via',
        'parent_project_id' => '_arsol_pfw_parent_project_id',
    );
    
    // Complex data structure meta keys (array-based)
    protected $complex_meta_keys = array(
        'budget_data' => '_arsol_pfw_proposal_budget',
        'quotation_data' => '_arsol_pfw_proposal_quotation',
        'request_data' => '_arsol_pfw_proposal_request',
        'original_request_data' => '_arsol_pfw_proposal_original_request_data',
        'woocommerce_data' => '_arsol_pfw_proposal_woocommerce_data',
        'workflow_data' => '_arsol_pfw_proposal_workflow_data',
    );
    
    /**
     * Create new proposal
     *
     * @param object $proposal Proposal object
     * @return int|WP_Error Post ID or error
     */
    public function create($proposal) {
        $user_id = get_current_user_id();
        $customer_id = $proposal->get_customer_id();
        
        $post_data = array(
            'post_type'    => 'arsol-pfw-proposal',
            'post_title'   => $proposal->get_title(),
            'post_content' => $proposal->get_description(),
            'post_status'  => 'publish',
            'post_author'  => $user_id, // Creator (post_author)
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Set customer ID
        $proposal->set_customer_id($customer_id);
        
        // Save meta
        $this->save_meta($proposal);
        
        // Set initial stage
        $stage = $proposal->get_stage() ?: 'processing';
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post_id);
        $proposal->set_stage($stage);
        
        do_action('arsol_pfw_proposal_created', $post_id, $proposal);
        
        return $post_id;
    }
    
    /**
     * Read proposal data
     *
     * @param object $proposal Proposal object
     * @return bool
     */
    public function read($proposal) {
        $post = get_post($proposal->get_id());
        
        if (!$post || 'arsol-pfw-proposal' !== $post->post_type) {
            return false;
        }
        
        // Set basic properties
        $proposal->set_title($post->post_title);
        $proposal->set_description($post->post_content);
        // Set dates
        $proposal->set_date_created($post->post_date);
        $proposal->set_date_modified($post->post_modified);
        
        // Load customer_id and created_via using entity methods
        $customer_id = $proposal->get_customer_id();
        $created_via = $proposal->get_created_via();
        
        if ($customer_id) {
            $proposal->set_customer_id($customer_id);
        }
        if ($created_via) {
            $proposal->set_created_via($created_via);
        }
        
        return true;
    }
    
    /**
     * Update proposal
     *
     * @param object $proposal Proposal object
     * @return bool|WP_Error
     */
    public function update($proposal) {
        // Update post if needed
        $post_updates = array();
        
        // Check if title or description need updating
        $current_title = $proposal->get_title();
        $current_description = $proposal->get_description();
        
        if ($current_title && $current_title !== get_the_title($proposal->get_id())) {
            $post_updates['post_title'] = $current_title;
        }
        
        if ($current_description && $current_description !== get_post_field('post_content', $proposal->get_id())) {
            $post_updates['post_content'] = $current_description;
        }
        
        if (!empty($post_updates)) {
            $post_updates['ID'] = $proposal->get_id();
            $result = wp_update_post($post_updates);
            
            if (is_wp_error($result)) {
                return $result;
            }
        }
        
        // Update meta
        $this->save_meta($proposal);
        
        do_action('arsol_pfw_proposal_updated', $proposal->get_id(), $proposal);
        
        return true;
    }
    
    /**
     * Delete proposal
     *
     * @param object $proposal Proposal object
     * @return bool
     */
    public function delete($proposal) {
        $post_id = $proposal->get_id();
        
        do_action('arsol_pfw_proposal_before_delete', $post_id, $proposal);
        
        $result = wp_delete_post($post_id, true);
        
        if ($result) {
            do_action('arsol_pfw_proposal_deleted', $post_id);
        }
        
        return (bool) $result;
    }
    
    /**
     * Save meta data
     *
     * @param object $proposal Proposal object
     */
    protected function save_meta($proposal) {
        // Save budget
        $budget = $proposal->get_budget();
        if ($budget !== null) {
            $proposal->set_meta('_arsol_pfw_proposal_budget', $budget);
        }
        
        // Save quotation
        $quotation = $proposal->get_quotation();
        if ($quotation !== null) {
            $proposal->set_meta('_arsol_pfw_proposal_quotation', $quotation);
        }
        
        // Save due date
        $due_date = $proposal->get_due_date();
        if ($due_date !== null) {
            $proposal->set_meta('_arsol_pfw_proposal_due_date', $due_date);
        }
        
        // Save start date
        $start_date = $proposal->get_start_date();
        if ($start_date !== null) {
            $proposal->set_meta('_arsol_pfw_proposal_start_date', $start_date);
        }
        
        // Save project manager
        $project_manager = $proposal->get_project_manager();
        if ($project_manager !== null) {
            $proposal->set_meta('_arsol_pfw_proposed_project_manager', $project_manager);
        }
        
        // Save expiration date
        $expiration_date = $proposal->get_expiration_date();
        if ($expiration_date !== null) {
            $proposal->set_meta('_arsol_pfw_proposal_expiration_date', $expiration_date);
        }
        
        // Save costing type
        $costing_type = $proposal->get_costing_type();
        if ($costing_type !== null) {
            $proposal->set_meta('_arsol_pfw_proposal_costing_type', $costing_type);
        }
        
        // Save customer notice
        $customer_notice = $proposal->get_customer_notice();
        if ($customer_notice !== null) {
            $proposal->set_meta('_arsol_pfw_proposal_customer_notice', $customer_notice);
        }
        
        // Save notes based on costing type
        $costing_type = $proposal->get_costing_type();
        if ($costing_type === 'budget') {
            $budget_notes = $proposal->get_budget_notes();
            if ($budget_notes !== null) {
                $proposal->set_meta('_arsol_pfw_proposal_budget_notes', $budget_notes);
            }
        } elseif ($costing_type === 'quotation') {
            $quotation_notes = $proposal->get_quotation_notes();
            if ($quotation_notes !== null) {
                $proposal->set_meta('_arsol_pfw_proposal_quotation_notes', $quotation_notes);
            }
        }
    }
    
    /**
     * Get simple meta keys mapping
     *
     * @return array
     */
    public function get_meta_keys() {
        return $this->meta_keys;
    }
    
    /**
     * Get complex meta keys mapping
     *
     * @return array
     */
    public function get_complex_meta_keys() {
        return $this->complex_meta_keys;
    }
    
    /**
     * Get all meta keys mapping
     *
     * @return array
     */
    public function get_all_meta_keys() {
        return array_merge($this->meta_keys, $this->complex_meta_keys);
    }
} 