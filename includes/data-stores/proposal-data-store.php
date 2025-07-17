<?php

namespace Arsol_Projects_For_Woo\Data_Stores;

if (!defined('ABSPATH')) {
    exit;
}

class Proposal_Data_Store {
    
    // Simple meta keys (individual fields)
    protected $meta_keys = array(
        'description' => '_arsol_pfw_proposal_description',
        'project_lead' => '_arsol_pfw_proposed_project_lead',
        'start_date'  => '_arsol_pfw_proposed_project_start_date',
        'due_date'    => '_arsol_pfw_proposed_project_due_date',
        'expiration_date' => '_arsol_pfw_proposal_expiration_date',
        'costing_type' => '_arsol_pfw_proposal_costing_type',
        'parent_project_id' => '_arsol_pfw_parent_project_id',
        'customer_notice' => '_arsol_pfw_proposal_customer_notice',
        'secondary_status' => '_arsol_pfw_proposal_secondary_status',
    );
    
    // Complex data structure meta keys (array-based)
    protected $complex_meta_keys = array(
        'budget_data' => '_arsol_pfw_proposed_project_budget_line_items',
        'quotation_data' => '_arsol_pfw_proposed_project_quotation_line_items',
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
            'post_content' => $proposal->get_prop('description'),
            'post_status'  => 'publish',
            'post_author'  => $user_id, // Creator (post_author)
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Set ID
        $proposal->set_prop('id', $post_id);
        
        // Store customer_id and created_via using entity methods
        $proposal->set_customer_id($customer_id);
        $proposal->set_created_via('admin_creation');
        
        // Save meta
        $this->save_simple_meta($proposal);
        $this->save_complex_meta($proposal);
        
        // Set initial stage
        $stage = $proposal->get_prop('stage') ?: 'processing';
        \Arsol_Projects_For_Woo\Core\Stage_Handler::set_stage($post_id, 'proposal', $stage);
        
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
        $proposal->set_prop('date_created', $post->post_date);
        $proposal->set_prop('date_modified', $post->post_modified);
        
        // Load customer_id and created_via using entity methods
        $customer_id = $proposal->get_customer_id();
        $created_via = $proposal->get_created_via();
        
        if ($customer_id) {
            $proposal->set_prop('customer_id', $customer_id);
        }
        if ($created_via) {
            $proposal->set_prop('created_via', $created_via);
        }
        
        // Load simple meta data
        foreach ($this->meta_keys as $prop => $meta_key) {
            $value = $proposal->get_meta($meta_key);
            $proposal->set_prop($prop, $value);
        }
        
        // Load complex meta data
        foreach ($this->complex_meta_keys as $prop => $meta_key) {
            $value = $proposal->get_meta($meta_key);
            $proposal->set_prop($prop, $value ?: array());
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
        $changes = $proposal->get_changes();
        
        if (empty($changes)) {
            return true;
        }
        
        // Update post if needed
        $post_updates = array();
        
        if (isset($changes['name'])) {
            $post_updates['post_title'] = $changes['name'];
        }
        
        if (isset($changes['description'])) {
            $post_updates['post_content'] = $changes['description'];
        }
        
        if (!empty($post_updates)) {
            $post_updates['ID'] = $proposal->get_id();
            $result = wp_update_post($post_updates);
            
            if (is_wp_error($result)) {
                return $result;
            }
        }
        
        // Update customer_id and created_via using entity methods
        if (isset($changes['customer_id'])) {
            $proposal->set_customer_id($changes['customer_id']);
        }
        
        if (isset($changes['created_via'])) {
            $proposal->set_created_via($changes['created_via']);
        }
        
        // Update meta
        $this->save_simple_meta($proposal);
        $this->save_complex_meta($proposal);
        
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
     * Save simple meta data
     *
     * @param object $proposal Proposal object
     */
    protected function save_simple_meta($proposal) {
        foreach ($this->meta_keys as $prop => $meta_key) {
            $value = $proposal->get_prop($prop);
            if ($value !== null) {
                $proposal->set_meta($meta_key, $value);
            }
        }
    }
    
    /**
     * Save complex meta data (array-based structures)
     *
     * @param object $proposal Proposal object
     */
    protected function save_complex_meta($proposal) {
        foreach ($this->complex_meta_keys as $prop => $meta_key) {
            $value = $proposal->get_prop($prop);
            if ($value !== null) {
                $proposal->set_meta($meta_key, $value);
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