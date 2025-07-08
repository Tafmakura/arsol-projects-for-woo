<?php
/**
 * Request Data Store
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Data_Stores
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Request Data Store
 * 
 * Handles all database operations for Request entities
 */
class ARSOL_PFW_Request_Data_Store extends ARSOL_PFW_Data_Store_WP implements ARSOL_PFW_Request_Data_Store_Interface {
    
    /**
     * Internal meta keys which are handled by this data store
     * @var array
     */
    protected $internal_meta_keys = array(
        '_arsol_pfw_request_stage',
        '_arsol_pfw_request_customer_id',
        '_arsol_pfw_request_project_id',
        '_arsol_pfw_request_budget',
        '_arsol_pfw_request_deadline',
        '_arsol_pfw_request_start_date',
        '_arsol_pfw_request_notes',
    );
    
    /**
     * Maps object properties to meta keys
     * @var array
     */
    protected $props_to_meta_keys = array(
        'customer_id' => '_arsol_pfw_request_customer_id',
        'project_id'  => '_arsol_pfw_request_project_id',
        'budget'      => '_arsol_pfw_request_budget',
        'deadline'    => '_arsol_pfw_request_deadline',
        'start_date'  => '_arsol_pfw_request_start_date',
    );
    
    /**
     * Taxonomy name for request stages
     * @var string
     */
    protected $stage_taxonomy = 'arsol-pfw-request-stage';
    
    /*
    |--------------------------------------------------------------------------
    | CRUD Operations
    |--------------------------------------------------------------------------
    */
    
    /**
     * Create a new request in the database
     *
     * @param ARSOL_PFW_Request $request Request object
     */
    public function create(&$request) {
        if (!$request->get_date_created('edit')) {
            $request->set_date_created(current_time('timestamp', true));
        }
        
        if (!$request->get_customer_id('edit')) {
            $request->set_customer_id(get_current_user_id());
        }
        
        $id = wp_insert_post(
            apply_filters('arsol_pfw_new_request_data', array(
                'post_type'   => 'arsol-pfw-request',
                'post_status' => 'publish',
                'post_title'  => $request->get_name('edit'),
                'post_content' => $request->get_description('edit'),
                'post_author' => $request->get_customer_id('edit'),
                'post_date'   => gmdate('Y-m-d H:i:s', $request->get_date_created('edit')->getOffsetTimestamp()),
                'post_date_gmt' => gmdate('Y-m-d H:i:s', $request->get_date_created('edit')->getTimestamp()),
            ), $request)
        );
        
        if ($id && !is_wp_error($id)) {
            $request->set_id($id);
            $this->update_post_meta($request);
            $this->save_stage_to_taxonomy($id, $request->get_stage('edit'), $this->stage_taxonomy);
            
            // Clear any caches
            $this->clear_caches($request);
            
            do_action('arsol_pfw_request_created', $id, $request);
        } else {
            throw new Exception('Failed to create request');
        }
    }
    
    /**
     * Read request data from the database
     *
     * @param ARSOL_PFW_Request $request Request object
     */
    public function read(&$request) {
        $post_object = get_post($request->get_id());
        
        if (!$post_object || 'arsol-pfw-request' !== $post_object->post_type) {
            throw new Exception('Invalid request.');
        }
        
        $request->set_props(array(
            'name'         => $post_object->post_title,
            'description'  => $post_object->post_content,
            'stage'        => $this->get_stage_from_taxonomy($request->get_id(), $this->stage_taxonomy, 'pending-review'),
            'customer_id'  => absint($post_object->post_author),
            'project_id'   => absint(get_post_meta($request->get_id(), '_arsol_pfw_request_project_id', true)),
            'budget'       => get_post_meta($request->get_id(), '_arsol_pfw_request_budget', true),
            'deadline'     => get_post_meta($request->get_id(), '_arsol_pfw_request_deadline', true),
            'start_date'   => get_post_meta($request->get_id(), '_arsol_pfw_request_start_date', true),
            'date_created' => $this->string_to_timestamp($post_object->post_date_gmt),
            'date_modified' => $this->string_to_timestamp($post_object->post_modified_gmt),
        ));
        
        $request->set_object_read(true);
        
        do_action('arsol_pfw_request_loaded', $request);
    }
    
    /**
     * Update request data in the database
     *
     * @param ARSOL_PFW_Request $request Request object
     */
    public function update(&$request) {
        $changes = $request->get_changes();
        
        $post_data_keys = array('name', 'description');
        
        // Update core post data if changed
        if (array_intersect($post_data_keys, array_keys($changes))) {
            $post_data = array('ID' => $request->get_id());
            
            if (array_key_exists('name', $changes)) {
                $post_data['post_title'] = $request->get_name('edit');
            }
            
            if (array_key_exists('description', $changes)) {
                $post_data['post_content'] = $request->get_description('edit');
            }
            
            wp_update_post($post_data);
        }
        
        // Update stage taxonomy if changed
        if (array_key_exists('stage', $changes)) {
            $this->save_stage_to_taxonomy($request->get_id(), $request->get_stage('edit'), $this->stage_taxonomy);
        }
        
        // Update meta data
        $this->update_post_meta($request);
        
        // Update modified date
        $request->set_date_modified(current_time('timestamp', true));
        
        // Clear any caches
        $this->clear_caches($request);
        
        do_action('arsol_pfw_request_updated', $request->get_id(), $request);
    }
    
    /**
     * Delete request from the database
     *
     * @param ARSOL_PFW_Request $request Request object
     * @param array $args Additional arguments
     */
    public function delete(&$request, $args = array()) {
        $id = $request->get_id();
        
        if (!$id) {
            return false;
        }
        
        $args = wp_parse_args($args, array(
            'force_delete' => false,
        ));
        
        if ($args['force_delete']) {
            wp_delete_post($id, true);
        } else {
            wp_trash_post($id);
        }
        
        // Clear any caches
        $this->clear_caches($request);
        
        do_action('arsol_pfw_request_deleted', $id, $request);
        
        return true;
    }
    
    /*
    |--------------------------------------------------------------------------
    | Query Methods (ARSOL_PFW_Request_Data_Store_Interface Implementation)
    |--------------------------------------------------------------------------
    */
    
    /**
     * Get requests by stage
     *
     * @param string $stage Stage slug
     * @param array $args Additional query arguments
     * @return array Array of WP_Post objects
     */
    public function get_requests_by_stage($stage, $args = array()) {
        $args = array_merge(array(
            'post_type'      => 'arsol-pfw-request',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'tax_query'      => array(
                array(
                    'taxonomy' => $this->stage_taxonomy,
                    'field'    => 'slug',
                    'terms'    => $stage,
                ),
            ),
        ), $args);
        
        return get_posts($args);
    }
    
    /**
     * Get requests by customer
     *
     * @param int $customer_id Customer user ID
     * @param array $args Additional query arguments
     * @return array Array of WP_Post objects
     */
    public function get_requests_by_customer($customer_id, $args = array()) {
        $args = array_merge(array(
            'post_type'      => 'arsol-pfw-request',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'author'         => absint($customer_id),
        ), $args);
        
        return get_posts($args);
    }
    
    /**
     * Get requests ready for conversion to proposals
     *
     * @param array $args Additional query arguments
     * @return array Array of WP_Post objects
     */
    public function get_requests_ready_for_conversion($args = array()) {
        return $this->get_requests_by_stage('approved', $args);
    }
    
    /**
     * Get requests by project ID
     *
     * @param int $project_id Project ID
     * @param array $args Additional query arguments
     * @return array Array of WP_Post objects
     */
    public function get_requests_by_project($project_id, $args = array()) {
        $args = array_merge(array(
            'post_type'      => 'arsol-pfw-request',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'   => '_arsol_pfw_request_project_id',
                    'value' => absint($project_id),
                ),
            ),
        ), $args);
        
        return get_posts($args);
    }
    
    /*
    |--------------------------------------------------------------------------
    | Stage Management
    |--------------------------------------------------------------------------
    */
    
    /**
     * Get available stages for requests
     *
     * @return array Array of stage_slug => stage_name
     */
    public function get_available_stages() {
        return parent::get_available_stages($this->stage_taxonomy);
    }
    
    /**
     * Get stage counts for requests
     *
     * @return array Array of stage_slug => count
     */
    public function get_stage_counts() {
        return parent::get_stage_counts($this->stage_taxonomy);
    }
    
    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */
    
    /**
     * Get props to meta keys mapping
     *
     * @return array Array of prop => meta_key
     */
    protected function get_props_to_meta_keys() {
        return $this->props_to_meta_keys;
    }
    
    /**
     * Clear caches for request
     *
     * @param ARSOL_PFW_Request $request Request object
     */
    protected function clear_caches($request) {
        wp_cache_delete('arsol-pfw-request-' . $request->get_id(), $this->cache_group);
        wp_cache_delete('arsol-pfw-requests-by-customer-' . $request->get_customer_id(), $this->cache_group);
        wp_cache_delete('arsol-pfw-requests-by-stage-' . $request->get_stage(), $this->cache_group);
    }
    
    /**
     * Convert string to timestamp
     *
     * @param string $date_string Date string
     * @return WC_DateTime|null
     */
    protected function string_to_timestamp($date_string) {
        if (empty($date_string) || '0000-00-00 00:00:00' === $date_string) {
            return null;
        }
        
        try {
            return new WC_DateTime($date_string, new DateTimeZone('UTC'));
        } catch (Exception $e) {
            return null;
        }
    }
} 