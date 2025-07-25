<?php

namespace Arsol_Projects_For_Woo\Data_Stores;

if (!defined('ABSPATH')) {
    exit;
}

class Request_Data_Store {
    
    // Simple meta keys mapping
    protected $meta_keys = array(
        'budget'      => '_arsol_pfw_requested_project_budget',
        'due_date'    => '_arsol_pfw_requested_project_due_date',
        'customer_id' => '_arsol_pfw_request_customer_id',
        'start_date'  => '_arsol_pfw_requested_project_start_date',
        'customer_notice' => '_arsol_pfw_request_customer_notice',
        'attachments' => '_arsol_pfw_request_attachments',
        'created_via' => '_arsol_pfw_request_created_via',
        'parent_project_id' => '_arsol_pfw_parent_project_id',
    );
    
    /**
     * Create new request
     *
     * @param object $request Request object
     * @return int|WP_Error Post ID or error
     */
    public function create($request) {
        $user_id = get_current_user_id();
        $customer_id = $request->get_customer_id();
        
        $post_data = array(
            'post_type'    => 'arsol-pfw-request',
            'post_title'   => $request->get_title(),
            'post_content' => $request->get_description(),
            'post_status'  => 'publish',
            'post_author'  => $user_id, // Creator (post_author)
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Set customer ID
        $request->set_customer_id($customer_id);
        
        // Save meta
        $this->save_meta($request);
        
        // Set initial stage
        $stage = $request->get_stage() ?: 'pending-review';
        $request = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($post_id);
        $request->set_stage($stage);
        
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
        
        // Set basic properties
        $request->set_title($post->post_title);
        $request->set_description($post->post_content);
        // Set dates
        $request->set_date_created($post->post_date);
        $request->set_date_modified($post->post_modified);
        
        // Load customer_id and created_via using entity methods
        $customer_id = $request->get_customer_id();
        $created_via = $request->get_created_via();
        
        if ($customer_id) {
            $request->set_customer_id($customer_id);
        }
        if ($created_via) {
            $request->set_created_via($created_via);
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
        // Update post if needed
        $post_updates = array();
        
        // Check if title or description need updating
        $current_title = $request->get_title();
        $current_description = $request->get_description();
        
        if ($current_title && $current_title !== get_the_title($request->get_id())) {
            $post_updates['post_title'] = $current_title;
        }
        
        if ($current_description && $current_description !== get_post_field('post_content', $request->get_id())) {
            $post_updates['post_content'] = $current_description;
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
        // Save budget
        $budget = $request->get_requested_project_budget();
        if ($budget !== null) {
            $request->set_meta('_arsol_pfw_requested_project_budget', $budget);
        }
        
        // Save due date
        $due_date = $request->get_requested_project_due_date();
        if ($due_date !== null) {
            $request->set_meta('_arsol_pfw_requested_project_due_date', $due_date);
        }
        
        // Save start date
        $start_date = $request->get_requested_project_start_date();
        if ($start_date !== null) {
            $request->set_meta('_arsol_pfw_requested_project_start_date', $start_date);
        }
        
        // Save customer notice
        $customer_notice = $request->get_customer_notice();
        if ($customer_notice !== null) {
            $request->set_meta('_arsol_pfw_request_customer_notice', $customer_notice);
        }
        
        // Save attachments
        $attachments = $request->get_attachments();
        if ($attachments !== null) {
            $request->set_meta('_arsol_pfw_request_attachments', $attachments);
        }
        
        // Save parent project ID
        $parent_project_id = $request->get_parent_project_id();
        if ($parent_project_id !== null) {
            $request->set_meta('_arsol_pfw_parent_project_id', $parent_project_id);
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