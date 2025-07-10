<?php

namespace Arsol_Projects_For_Woo\Data_Stores;

if (!defined('ABSPATH')) {
    exit;
}

class Request_Data_Store {
    
    protected $meta_keys = array(
        'customer_id' => '_arsol_pfw_request_customer_id',
        'budget'      => '_arsol_pfw_request_budget',
        'deadline'    => '_arsol_pfw_request_delivery_date',
        'description' => '_arsol_pfw_request_description',
        'priority'    => '_arsol_pfw_request_priority',
        'start_date'  => '_arsol_pfw_request_start_date',
        'project_lead' => '_arsol_pfw_request_project_lead',
        'name' => '_arsol_pfw_request_name',
        'customer_notice' => '_arsol_pfw_request_customer_notice',
    );
    
    /**
     * Create new request
     *
     * @param object $request Request object
     * @return int|WP_Error Post ID or error
     */
    public function create($request) {
        $post_data = array(
            'post_type'    => 'arsol-pfw-request',
            'post_title'   => $request->get_name(),
            'post_content' => $request->get_prop('description'),
            'post_status'  => 'publish',
            'post_author'  => $request->get_customer_id(),
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Set ID
        $request->set_prop('id', $post_id);
        
        // Save meta
        $this->save_meta($request);
        
        // Set initial stage
        $stage = $request->get_prop('stage') ?: 'pending-review';
        \Arsol_Projects_For_Woo\Core\Stage_Handler::set_stage($post_id, 'request', $stage);
        
        do_action('arsol_pfw_request_created', $post_id, $request);
        
        return $post_id;
    }
    
    /**
     * Read request data
     *
     * @param object $request Request object
     * @return bool
     */
    public function read($request) {
        $post = get_post($request->get_id());
        
        if (!$post || 'arsol-pfw-request' !== $post->post_type) {
            return false;
        }
        
        $request->set_prop('name', $post->post_title);
        $request->set_prop('description', $post->post_content);
        $request->set_prop('customer_id', $post->post_author);
        $request->set_prop('date_created', $post->post_date);
        $request->set_prop('date_modified', $post->post_modified);
        
        // Load meta data
        foreach ($this->meta_keys as $prop => $meta_key) {
            $value = get_post_meta($post->ID, $meta_key, true);
            $request->set_prop($prop, $value);
        }
        
        return true;
    }
    
    /**
     * Update request
     *
     * @param object $request Request object
     * @return bool|WP_Error
     */
    public function update($request) {
        $changes = $request->get_changes();
        
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
            $post_updates['ID'] = $request->get_id();
            $result = wp_update_post($post_updates);
            
            if (is_wp_error($result)) {
                return $result;
            }
        }
        
        // Update meta
        $this->save_meta($request);
        
        do_action('arsol_pfw_request_updated', $request->get_id(), $request);
        
        return true;
    }
    
    /**
     * Delete request
     *
     * @param object $request Request object
     * @return bool
     */
    public function delete($request) {
        $post_id = $request->get_id();
        
        do_action('arsol_pfw_request_before_delete', $post_id, $request);
        
        $result = wp_delete_post($post_id, true);
        
        if ($result) {
            do_action('arsol_pfw_request_deleted', $post_id);
        }
        
        return (bool) $result;
    }
    
    /**
     * Save meta data
     *
     * @param object $request Request object
     */
    protected function save_meta($request) {
        foreach ($this->meta_keys as $prop => $meta_key) {
            $value = $request->get_prop($prop);
            if ($value !== null) {
                update_post_meta($request->get_id(), $meta_key, $value);
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