<?php

namespace Arsol_Projects_For_Woo\Data_Stores;

if (!defined('ABSPATH')) {
    exit;
}

class Project_Data_Store {
    
    protected $meta_keys = array(
        'budget'      => '_arsol_pfw_project_budget',
        'due_date'    => '_arsol_pfw_project_due_date',
        'description' => '_arsol_pfw_project_description',
        'start_date'  => '_arsol_pfw_project_start_date',
        'project_lead' => '_arsol_pfw_project_lead',
        'customer_notice' => '_arsol_pfw_project_customer_notice',
    );
    
    // Complex data structure meta keys (array-based) - inherited from proposal
    protected $complex_meta_keys = array(
        'proposal_budget_data' => '_arsol_pfw_proposed_project_budget_line_items',
        'proposal_quotation_data' => '_arsol_pfw_proposed_project_quotation_line_items',
        'woocommerce_data' => '_arsol_pfw_project_woocommerce_data',
        'workflow_data' => '_arsol_pfw_project_workflow_data',
    );
    
    /**
     * Create new project
     *
     * @param object $project Project object
     * @return int|WP_Error Post ID or error
     */
    public function create($project) {
        $user_id = get_current_user_id();
        $customer_id = $project->get_customer_id();
        
        $post_data = array(
            'post_type'    => 'arsol-pfw-project',
            'post_title'   => $project->get_title(),
            'post_content' => $project->get_prop('description'),
            'post_status'  => 'publish',
            'post_author'  => $user_id, // Creator (post_author)
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Set ID
        $project->set_prop('id', $post_id);
        
        // Store customer_id and created_via using entity methods
        $project->set_customer_id($customer_id);
        $project->set_created_via('admin_creation');
        
        // Save meta
        $this->save_simple_meta($project);
        $this->save_complex_meta($project);
        
        // Set initial stage
        $stage = $project->get_prop('stage') ?: 'not-started';
        $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($post_id);
        $project->set_stage($stage);
        
        do_action('arsol_pfw_project_created', $post_id, $project);
        
        return $post_id;
    }
    
    /**
     * Read project data
     *
     * @param object $project Project object
     * @return bool
     */
    public function read($project) {
        $post = get_post($project->get_id());
        
        if (!$post || 'arsol-pfw-project' !== $post->post_type) {
            return false;
        }
        
        // Set basic properties
        $project->set_title($post->post_title);
        $project->set_description($post->post_content);
        $project->set_prop('date_created', $post->post_date);
        $project->set_prop('date_modified', $post->post_modified);
        
        // Load customer_id and created_via using entity methods
        $customer_id = $project->get_customer_id();
        $created_via = $project->get_created_via();
        
        if ($customer_id) {
            $project->set_prop('customer_id', $customer_id);
        }
        if ($created_via) {
            $project->set_prop('created_via', $created_via);
        }
        
        // Load simple meta data
        foreach ($this->meta_keys as $prop => $meta_key) {
            $value = $project->get_meta($meta_key);
            $project->set_prop($prop, $value);
        }
        
        // Load complex meta data
        foreach ($this->complex_meta_keys as $prop => $meta_key) {
            $value = $project->get_meta($meta_key);
            $project->set_prop($prop, $value ?: array());
        }
        
        return true;
    }
    
    /**
     * Update project
     *
     * @param object $project Project object
     * @return bool|WP_Error
     */
    public function update($project) {
        $changes = $project->get_changes();
        
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
            $post_updates['ID'] = $project->get_id();
            $result = wp_update_post($post_updates);
            
            if (is_wp_error($result)) {
                return $result;
            }
        }
        
        // Update customer_id and created_via using entity methods
        if (isset($changes['customer_id'])) {
            $project->set_customer_id($changes['customer_id']);
        }
        
        if (isset($changes['created_via'])) {
            $project->set_created_via($changes['created_via']);
        }
        
        // Update meta
        $this->save_simple_meta($project);
        $this->save_complex_meta($project);
        
        do_action('arsol_pfw_project_updated', $project->get_id(), $project);
        
        return true;
    }
    
    /**
     * Delete project
     *
     * @param object $project Project object
     * @return bool
     */
    public function delete($project) {
        $post_id = $project->get_id();
        
        do_action('arsol_pfw_project_before_delete', $post_id, $project);
        
        $result = wp_delete_post($post_id, true);
        
        if ($result) {
            do_action('arsol_pfw_project_deleted', $post_id);
        }
        
        return (bool) $result;
    }
    
    /**
     * Save simple meta data
     *
     * @param object $project Project object
     */
    protected function save_simple_meta($project) {
        foreach ($this->meta_keys as $prop => $meta_key) {
            $value = $project->get_prop($prop);
            if ($value !== null) {
                $project->set_meta($meta_key, $value);
            }
        }
    }
    
    /**
     * Save complex meta data (array-based structures)
     *
     * @param object $project Project object
     */
    protected function save_complex_meta($project) {
        foreach ($this->complex_meta_keys as $prop => $meta_key) {
            $value = $project->get_prop($prop);
            if ($value !== null) {
                $project->set_meta($meta_key, $value);
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