<?php
/**
 * Project Data Store Class for Arsol Projects for WooCommerce
 *
 * Handles CRUD operations for projects following WooCommerce data store patterns.
 * Provides database abstraction and meta data handling.
 *
 * @package Arsol_PFW\Data_Stores
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Arsol_PFW\Data_Stores;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Project Data Store Class
 *
 * Handles all database operations for projects.
 */
class Project_Data_Store {
    
    /**
     * Create a new project
     *
     * @param array $data Project data
     * @return int|WP_Error Project ID on success, WP_Error on failure
     */
    public function create($data) {
        $defaults = array(
            'post_title' => '',
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'arsol-pfw-project',
            'post_author' => get_current_user_id(),
        );
        
        $post_data = wp_parse_args($data, $defaults);
        
        // Insert post
        $project_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($project_id)) {
            return $project_id;
        }
        
        // Save meta data
        if (!empty($data['meta'])) {
            $this->save_meta_data($project_id, $data['meta']);
        }
        
        // Fire action after create
        do_action('arsol_pfw_project_data_store_created', $project_id, $data);
        
        return $project_id;
    }
    
    /**
     * Read project data
     *
     * @param int $project_id Project ID
     * @return array|false Project data on success, false on failure
     */
    public function read($project_id) {
        $post = get_post($project_id);
        
        if (!$post || $post->post_type !== 'arsol-pfw-project') {
            return false;
        }
        
        $data = array(
            'ID' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'status' => $post->post_status,
            'author' => $post->post_author,
            'date_created' => $post->post_date,
            'date_modified' => $post->post_modified,
            'meta' => $this->read_meta_data($project_id),
        );
        
        return $data;
    }
    
    /**
     * Update project data
     *
     * @param int $project_id Project ID
     * @param array $data Project data
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function update($project_id, $data) {
        // Prepare post data
        $post_data = array('ID' => $project_id);
        
        if (isset($data['post_title'])) {
            $post_data['post_title'] = $data['post_title'];
        }
        
        if (isset($data['post_content'])) {
            $post_data['post_content'] = $data['post_content'];
        }
        
        if (isset($data['post_status'])) {
            $post_data['post_status'] = $data['post_status'];
        }
        
        // Update post
        $result = wp_update_post($post_data, true);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Update meta data
        if (!empty($data['meta'])) {
            $this->save_meta_data($project_id, $data['meta']);
        }
        
        // Fire action after update
        do_action('arsol_pfw_project_data_store_updated', $project_id, $data);
        
        return true;
    }
    
    /**
     * Delete project
     *
     * @param int $project_id Project ID
     * @param bool $force_delete Force delete (bypass trash)
     * @return bool True on success, false on failure
     */
    public function delete($project_id, $force_delete = false) {
        // Fire action before delete
        do_action('arsol_pfw_project_data_store_before_delete', $project_id);
        
        $result = wp_delete_post($project_id, $force_delete);
        
        if ($result) {
            // Fire action after delete
            do_action('arsol_pfw_project_data_store_deleted', $project_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Read meta data for a project
     *
     * @param int $project_id Project ID
     * @return array Meta data
     */
    public function read_meta_data($project_id) {
        $meta_keys = array(
            '_arsol_pfw_customer_id',
            '_arsol_pfw_project_stage',
            '_arsol_pfw_project_priority',
            '_arsol_pfw_project_budget',
            '_arsol_pfw_project_deadline',
        );
        
        $meta_data = array();
        
        foreach ($meta_keys as $meta_key) {
            $meta_data[str_replace('_arsol_pfw_project_', '', $meta_key)] = get_post_meta($project_id, $meta_key, true);
        }
        
        return $meta_data;
    }
    
    /**
     * Save meta data for a project
     *
     * @param int $project_id Project ID
     * @param array $meta_data Meta data to save
     */
    public function save_meta_data($project_id, $meta_data) {
        $meta_mapping = array(
            'customer_id' => '_arsol_pfw_customer_id',
            'stage' => '_arsol_pfw_project_stage',
            'priority' => '_arsol_pfw_project_priority',
            'budget' => '_arsol_pfw_project_budget',
            'deadline' => '_arsol_pfw_project_deadline',
        );
        
        foreach ($meta_data as $key => $value) {
            if (isset($meta_mapping[$key])) {
                update_post_meta($project_id, $meta_mapping[$key], $value);
            }
        }
    }
    
    /**
     * Search projects
     *
     * @param array $args Search arguments
     * @return array Project IDs
     */
    public function search($args = array()) {
        $defaults = array(
            'post_type' => 'arsol-pfw-project',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        );
        
        $query_args = wp_parse_args($args, $defaults);
        
        $posts = get_posts($query_args);
        
        return $posts;
    }
    
    /**
     * Get projects by customer
     *
     * @param int $customer_id Customer ID
     * @param array $args Additional arguments
     * @return array Project IDs
     */
    public function get_by_customer($customer_id, $args = array()) {
        $defaults = array(
            'post_type' => 'arsol-pfw-project',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_arsol_pfw_customer_id',
                    'value' => $customer_id,
                    'compare' => '='
                )
            )
        );
        
        $query_args = wp_parse_args($args, $defaults);
        
        $posts = get_posts($query_args);
        
        return $posts;
    }
    
    /**
     * Get projects count
     *
     * @param array $args Query arguments
     * @return int Projects count
     */
    public function get_count($args = array()) {
        $defaults = array(
            'post_type' => 'arsol-pfw-project',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        );
        
        $query_args = wp_parse_args($args, $defaults);
        
        $posts = get_posts($query_args);
        
        return count($posts);
    }
    
    /**
     * Bulk update projects
     *
     * @param array $project_ids Project IDs
     * @param array $data Data to update
     * @return array Results array
     */
    public function bulk_update($project_ids, $data) {
        $results = array();
        
        foreach ($project_ids as $project_id) {
            $results[$project_id] = $this->update($project_id, $data);
        }
        
        return $results;
    }
    
    /**
     * Bulk delete projects
     *
     * @param array $project_ids Project IDs
     * @param bool $force_delete Force delete (bypass trash)
     * @return array Results array
     */
    public function bulk_delete($project_ids, $force_delete = false) {
        $results = array();
        
        foreach ($project_ids as $project_id) {
            $results[$project_id] = $this->delete($project_id, $force_delete);
        }
        
        return $results;
    }
} 