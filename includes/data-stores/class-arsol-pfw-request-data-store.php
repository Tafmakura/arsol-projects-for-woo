<?php
/**
 * Request Data Store
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Data_Stores
 */

namespace Arsol_Projects_For_Woo;

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
     * Internal meta keys which are not saved to the database
     * @var array
     */
    protected $internal_meta_keys = array(
        '_arsol_pfw_request_version',
    );
    
    /**
     * Meta keys mapped to props
     * @var array
     */
    protected $meta_key_to_props = array(
        '_arsol_pfw_request_customer_id' => 'customer_id',
        '_arsol_pfw_request_project_id' => 'project_id',
        '_arsol_pfw_request_budget' => 'budget',
        '_arsol_pfw_request_deadline' => 'deadline',
        '_arsol_pfw_request_start_date' => 'start_date',
    );
    
    /**
     * Request stage taxonomy
     * @var string
     */
    protected $stage_taxonomy = 'arsol-pfw-request-stage';
    
    /**
     * Core data keys (properties that exist in wp_posts table)
     * @var array
     */
    protected $core_data_keys = array('name', 'description', 'date_created', 'date_modified');
    
    /**
     * Create a new request in the database
     *
     * @param ARSOL_PFW_Request $request Request object
     */
    public function create(&$request) {
        $request->set_date_created(current_time('mysql'));
        
        $post_data = array(
            'post_type'     => 'arsol-pfw-request',
            'post_status'   => 'private',
            'post_title'    => $request->get_name() ? $request->get_name() : 'Request',
            'post_content'  => $request->get_description(),
            'post_date'     => gmdate('Y-m-d H:i:s', $request->get_date_created('edit')->getOffsetTimestamp()),
            'post_date_gmt' => gmdate('Y-m-d H:i:s', $request->get_date_created('edit')->getTimestamp()),
            'post_author'   => $request->get_customer_id(),
        );
        
        $post_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($post_id)) {
            throw new \WC_Data_Exception('db_insert_error', $post_id->get_error_message());
        }
        
        $request->set_id($post_id);
        
        // Save stage to taxonomy
        $this->save_stage_to_taxonomy($post_id, $request->get_stage(), $this->stage_taxonomy);
        
        $this->update_post_meta($request);
        
        $request->save_meta_data();
        $request->apply_changes();
        
        // Clear cache
        wp_cache_delete($post_id, $this->cache_group);
        
        do_action('arsol_pfw_request_created', $request);
        
        // Log creation
        if (class_exists('Arsol_Projects_For_Woo\Woocommerce_Logs')) {
            Woocommerce_Logs::log_request('info', 'Request created', $request->get_id());
        }
    }
    
    /**
     * Read a request from the database
     *
     * @param ARSOL_PFW_Request $request Request object
     * @throws \WC_Data_Exception If request does not exist
     */
    public function read(&$request) {
        $request->set_defaults();
        
        $post_object = get_post($request->get_id());
        
        if (!$post_object || 'arsol-pfw-request' !== $post_object->post_type) {
            throw new \WC_Data_Exception('invalid_request', __('Invalid request.', 'arsol-pfw'));
        }
        
        $request->set_props(array(
            'name'          => $post_object->post_title,
            'description'   => $post_object->post_content,
            'customer_id'   => $post_object->post_author,
            'date_created'  => $this->string_to_timestamp($post_object->post_date_gmt),
            'date_modified' => $this->string_to_timestamp($post_object->post_modified_gmt),
        ));
        
        // Load stage from taxonomy
        $stage = $this->get_stage_from_taxonomy($request->get_id(), $this->stage_taxonomy, 'pending-review');
        $request->set_stage($stage);
        
        $this->read_request_data($request);
        
        $request->read_meta_data();
        $request->set_object_read(true);
        
        do_action('arsol_pfw_request_loaded', $request);
    }
    
    /**
     * Update a request in the database
     *
     * @param ARSOL_PFW_Request $request Request object
     */
    public function update(&$request) {
        $request->save_meta_data();
        $changes = $request->get_changes();
        
        if (!$request->get_date_created('edit')) {
            $request->set_date_created(current_time('mysql'));
        }
        
        $request->set_date_modified(current_time('mysql'));
        
        // Update core post data if needed
        if (array_intersect($this->core_data_keys, array_keys($changes))) {
            $post_data = array(
                'ID'            => $request->get_id(),
                'post_title'    => $request->get_name(),
                'post_content'  => $request->get_description(),
                'post_modified' => gmdate('Y-m-d H:i:s', $request->get_date_modified('edit')->getOffsetTimestamp()),
                'post_modified_gmt' => gmdate('Y-m-d H:i:s', $request->get_date_modified('edit')->getTimestamp()),
            );
            
            wp_update_post($post_data);
        }
        
        // Update stage if changed
        if (array_key_exists('stage', $changes)) {
            $this->save_stage_to_taxonomy($request->get_id(), $request->get_stage(), $this->stage_taxonomy);
        }
        
        $this->update_post_meta($request);
        
        $request->apply_changes();
        
        // Clear cache
        wp_cache_delete($request->get_id(), $this->cache_group);
        
        do_action('arsol_pfw_request_updated', $request);
        
        // Log update
        if (class_exists('Arsol_Projects_For_Woo\Woocommerce_Logs')) {
            Woocommerce_Logs::log_request('info', 'Request updated', $request->get_id());
        }
    }
    
    /**
     * Delete a request from the database
     *
     * @param ARSOL_PFW_Request $request Request object
     * @param array $args Delete arguments
     */
    public function delete(&$request, $args = array()) {
        $args = wp_parse_args($args, array(
            'force_delete' => false,
        ));
        
        $id = $request->get_id();
        
        if (!$id) {
            return;
        }
        
        if ($args['force_delete']) {
            wp_delete_post($id, true);
            $request->set_id(0);
            
            do_action('arsol_pfw_request_deleted', $id);
            
            // Log deletion
            if (class_exists('Arsol_Projects_For_Woo\Woocommerce_Logs')) {
                Woocommerce_Logs::log_request('info', 'Request deleted', $id);
            }
        } else {
            wp_trash_post($id);
            $request->set_status('trash');
            
            do_action('arsol_pfw_request_trashed', $id);
        }
        
        // Clear cache
        wp_cache_delete($id, $this->cache_group);
    }
    
    /**
     * Read request-specific data
     *
     * @param ARSOL_PFW_Request $request Request object
     */
    protected function read_request_data(&$request) {
        $meta_values = get_post_meta($request->get_id());
        
        foreach ($this->meta_key_to_props as $meta_key => $prop) {
            $meta_key = substr($meta_key, 1); // Remove leading underscore
            $value = isset($meta_values[$meta_key]) ? $meta_values[$meta_key][0] : '';
            
            if ($prop === 'budget') {
                $value = maybe_unserialize($value);
                if (!is_array($value)) {
                    $value = array();
                }
            }
            
            $request->set_prop($prop, $value);
        }
    }
    
    /**
     * Convert string to timestamp
     *
     * @param string $time_string Time string
     * @return \WC_DateTime|null
     */
    protected function string_to_timestamp($time_string) {
        if (empty($time_string)) {
            return null;
        }
        
        return new \WC_DateTime($time_string, new \DateTimeZone('UTC'));
    }
    
    /**
     * Get props to meta keys mapping
     *
     * @return array
     */
    protected function get_props_to_meta_keys() {
        return $this->meta_key_to_props;
    }
    
    /**
     * Get requests by stage
     *
     * @param string $stage Stage slug
     * @param array $args Additional query arguments
     * @return array Array of WP_Post objects
     */
    public function get_requests_by_stage($stage, $args = array()) {
        $defaults = array(
            'post_type' => 'arsol-pfw-request',
            'post_status' => 'private',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => $this->stage_taxonomy,
                    'field' => 'slug',
                    'terms' => $stage,
                ),
            ),
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $query = new \WP_Query($args);
        return $query->posts;
    }
    
    /**
     * Get requests by customer
     *
     * @param int $customer_id Customer user ID
     * @param array $args Additional query arguments
     * @return array Array of WP_Post objects
     */
    public function get_requests_by_customer($customer_id, $args = array()) {
        $defaults = array(
            'post_type' => 'arsol-pfw-request',
            'post_status' => 'private',
            'posts_per_page' => -1,
            'author' => $customer_id,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $query = new \WP_Query($args);
        return $query->posts;
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
} 