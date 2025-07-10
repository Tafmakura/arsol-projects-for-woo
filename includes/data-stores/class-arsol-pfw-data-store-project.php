<?php

namespace Arsol_Projects_For_Woo\Data_Stores;

if (!defined('ABSPATH')) {
    exit;
}

class Project_Data_Store {
    
    protected $meta_keys = array(
        'customer_id' => '_arsol_pfw_project_customer_id',
        'budget'      => '_arsol_pfw_project_budget',
        'deadline'    => '_arsol_pfw_project_deadline',
        'description' => '_arsol_pfw_project_description',
        'timeline'    => '_arsol_pfw_project_timeline',
        'progress'    => '_arsol_pfw_project_progress',
        'start_date'  => '_arsol_pfw_project_start_date',
        'project_lead' => '_arsol_pfw_project_lead',
    );
    
    /**
     * Create new project
     *
     * @param object $project Project object
     * @return int|WP_Error Post ID or error
     */
    public function create($project) {
        $post_data = array(
            'post_type'    => 'arsol-pfw-project',
            'post_title'   => $project->get_name(),
            'post_content' => $project->get_prop('description'),
            'post_status'  => 'publish',
            'post_author'  => $project->get_customer_id(),
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Set ID
        $project->set_prop('id', $post_id);
        
        // Save meta
        $this->save_meta($project);
        
        // Set initial stage
        $stage = $project->get_prop('stage') ?: 'not-started';
        \Arsol_Projects_For_Woo\Core\Stage_Handler::set_stage($post_id, 'project', $stage);
        
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
        
        $project->set_prop('name', $post->post_title);
        $project->set_prop('description', $post->post_content);
        $project->set_prop('customer_id', $post->post_author);
        $project->set_prop('date_created', $post->post_date);
        $project->set_prop('date_modified', $post->post_modified);
        
        // Load meta data
        foreach ($this->meta_keys as $prop => $meta_key) {
            $value = get_post_meta($post->ID, $meta_key, true);
            $project->set_prop($prop, $value);
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
        
        if (isset($changes['customer_id'])) {
            $post_updates['post_author'] = $changes['customer_id'];
        }
        
        if (!empty($post_updates)) {
            $post_updates['ID'] = $project->get_id();
            $result = wp_update_post($post_updates);
            
            if (is_wp_error($result)) {
                return $result;
            }
        }
        
        // Update meta
        $this->save_meta($project);
        
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
     * Save meta data
     *
     * @param object $project Project object
     */
    protected function save_meta($project) {
        foreach ($this->meta_keys as $prop => $meta_key) {
            $value = $project->get_prop($prop);
            if ($value !== null) {
                update_post_meta($project->get_id(), $meta_key, $value);
            }
        }
    }
    
    /**
     * Get meta keys mapping
     *
     * @return array
     */
    public function get_meta_keys() {
        return $this->meta_keys;
    }
} 