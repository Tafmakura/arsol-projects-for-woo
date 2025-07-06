<?php
/**
 * Projects Repository Class for Arsol Projects for WooCommerce
 *
 * Handles collection management, complex queries, and business-focused
 * methods for projects following WooCommerce patterns.
 *
 * @package Arsol_PFW\Custom_Post_Types\Project
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Arsol_PFW\Custom_Post_Types\Project;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Projects Repository Class
 *
 * Manages collections of projects with business-focused methods.
 */
class Repository {
    
    /**
     * Get projects by customer
     *
     * @param int $customer_id Customer ID
     * @param array $args Additional query arguments
     * @return Entity[] Array of project entities
     */
    public static function get_by_customer($customer_id, $args = array()) {
        $defaults = array(
            'status' => 'any',
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $query_args = array(
            'post_type' => 'arsol-pfw-project',
            'post_status' => $args['status'],
            'posts_per_page' => $args['limit'],
            'orderby' => $args['orderby'],
            'order' => $args['order'],
            'meta_query' => array(
                array(
                    'key' => '_arsol_pfw_customer_id',
                    'value' => $customer_id,
                    'compare' => '='
                )
            )
        );
        
        $posts = get_posts($query_args);
        
        return array_map(function($post) {
            return new Entity($post);
        }, $posts);
    }
    
    /**
     * Get projects by status
     *
     * @param string $status Project status
     * @param array $args Additional query arguments
     * @return Entity[] Array of project entities
     */
    public static function get_by_status($status, $args = array()) {
        $defaults = array(
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $query_args = array(
            'post_type' => 'arsol-pfw-project',
            'post_status' => $status,
            'posts_per_page' => $args['limit'],
            'orderby' => $args['orderby'],
            'order' => $args['order'],
        );
        
        $posts = get_posts($query_args);
        
        return array_map(function($post) {
            return new Entity($post);
        }, $posts);
    }
    
    /**
     * Get projects by stage
     *
     * @param string $stage Project stage
     * @param array $args Additional query arguments
     * @return Entity[] Array of project entities
     */
    public static function get_by_stage($stage, $args = array()) {
        $defaults = array(
            'status' => 'publish',
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $query_args = array(
            'post_type' => 'arsol-pfw-project',
            'post_status' => $args['status'],
            'posts_per_page' => $args['limit'],
            'orderby' => $args['orderby'],
            'order' => $args['order'],
            'meta_query' => array(
                array(
                    'key' => '_arsol_pfw_project_stage',
                    'value' => $stage,
                    'compare' => '='
                )
            )
        );
        
        $posts = get_posts($query_args);
        
        return array_map(function($post) {
            return new Entity($post);
        }, $posts);
    }
    
    /**
     * Search projects
     *
     * @param string $search_term Search term
     * @param array $args Additional query arguments
     * @return Entity[] Array of project entities
     */
    public static function search($search_term, $args = array()) {
        $defaults = array(
            'status' => 'publish',
            'limit' => -1,
            'orderby' => 'relevance',
            'order' => 'DESC',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $query_args = array(
            'post_type' => 'arsol-pfw-project',
            'post_status' => $args['status'],
            'posts_per_page' => $args['limit'],
            'orderby' => $args['orderby'],
            'order' => $args['order'],
            's' => sanitize_text_field($search_term),
        );
        
        $posts = get_posts($query_args);
        
        return array_map(function($post) {
            return new Entity($post);
        }, $posts);
    }
    
    /**
     * Get recent projects
     *
     * @param int $limit Number of projects to get
     * @param array $args Additional query arguments
     * @return Entity[] Array of project entities
     */
    public static function get_recent($limit = 5, $args = array()) {
        $defaults = array(
            'status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        $args = wp_parse_args($args, $defaults);
        $args['limit'] = $limit;
        
        $query_args = array(
            'post_type' => 'arsol-pfw-project',
            'post_status' => $args['status'],
            'posts_per_page' => $limit,
            'orderby' => $args['orderby'],
            'order' => $args['order'],
        );
        
        $posts = get_posts($query_args);
        
        return array_map(function($post) {
            return new Entity($post);
        }, $posts);
    }
    
    /**
     * Get projects count
     *
     * @param array $args Query arguments
     * @return int Project count
     */
    public static function get_count($args = array()) {
        $defaults = array(
            'status' => 'publish',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $query_args = array(
            'post_type' => 'arsol-pfw-project',
            'post_status' => $args['status'],
            'posts_per_page' => -1,
            'fields' => 'ids',
        );
        
        // Add customer filter if specified
        if (!empty($args['customer_id'])) {
            $query_args['meta_query'] = array(
                array(
                    'key' => '_arsol_pfw_customer_id',
                    'value' => $args['customer_id'],
                    'compare' => '='
                )
            );
        }
        
        // Add stage filter if specified
        if (!empty($args['stage'])) {
            $query_args['meta_query'][] = array(
                'key' => '_arsol_pfw_project_stage',
                'value' => $args['stage'],
                'compare' => '='
            );
        }
        
        $posts = get_posts($query_args);
        
        return count($posts);
    }
    
    /**
     * Get projects with deadlines approaching
     *
     * @param int $days_ahead Number of days to look ahead
     * @param array $args Additional query arguments
     * @return Entity[] Array of project entities
     */
    public static function get_with_approaching_deadlines($days_ahead = 7, $args = array()) {
        $defaults = array(
            'status' => 'publish',
            'limit' => -1,
            'orderby' => 'meta_value',
            'order' => 'ASC',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $date_ahead = date('Y-m-d', strtotime("+{$days_ahead} days"));
        
        $query_args = array(
            'post_type' => 'arsol-pfw-project',
            'post_status' => $args['status'],
            'posts_per_page' => $args['limit'],
            'orderby' => $args['orderby'],
            'order' => $args['order'],
            'meta_key' => '_arsol_pfw_project_deadline',
            'meta_query' => array(
                array(
                    'key' => '_arsol_pfw_project_deadline',
                    'value' => array(date('Y-m-d'), $date_ahead),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                )
            )
        );
        
        $posts = get_posts($query_args);
        
        return array_map(function($post) {
            return new Entity($post);
        }, $posts);
    }
    
    /**
     * Get overdue projects
     *
     * @param array $args Additional query arguments
     * @return Entity[] Array of project entities
     */
    public static function get_overdue($args = array()) {
        $defaults = array(
            'status' => 'publish',
            'limit' => -1,
            'orderby' => 'meta_value',
            'order' => 'ASC',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $query_args = array(
            'post_type' => 'arsol-pfw-project',
            'post_status' => $args['status'],
            'posts_per_page' => $args['limit'],
            'orderby' => $args['orderby'],
            'order' => $args['order'],
            'meta_key' => '_arsol_pfw_project_deadline',
            'meta_query' => array(
                array(
                    'key' => '_arsol_pfw_project_deadline',
                    'value' => date('Y-m-d'),
                    'compare' => '<',
                    'type' => 'DATE'
                )
            )
        );
        
        $posts = get_posts($query_args);
        
        return array_map(function($post) {
            return new Entity($post);
        }, $posts);
    }
    
    /**
     * Create new project
     *
     * @param array $data Project data
     * @return Entity|false Project entity on success, false on failure
     */
    public static function create($data) {
        $project = new Entity();
        
        // Set data
        foreach ($data as $key => $value) {
            $setter = 'set_' . $key;
            if (method_exists($project, $setter)) {
                $project->$setter($value);
            }
        }
        
        // Save project
        if ($project->save()) {
            return $project;
        }
        
        return false;
    }
    
    /**
     * Delete multiple projects
     *
     * @param array $project_ids Array of project IDs
     * @param bool $force_delete Force delete (bypass trash)
     * @return int Number of projects deleted
     */
    public static function delete_multiple($project_ids, $force_delete = false) {
        $deleted_count = 0;
        
        foreach ($project_ids as $project_id) {
            $project = new Entity($project_id);
            if ($project->delete($force_delete)) {
                $deleted_count++;
            }
        }
        
        return $deleted_count;
    }
    
    /**
     * Get projects statistics
     *
     * @return array Statistics array
     */
    public static function get_statistics() {
        $stats = array(
            'total' => self::get_count(),
            'active' => self::get_count(array('status' => 'publish')),
            'completed' => self::get_count(array('status' => 'completed')),
            'on_hold' => self::get_count(array('status' => 'on-hold')),
            'cancelled' => self::get_count(array('status' => 'cancelled')),
        );
        
        return apply_filters('arsol_pfw_projects_statistics', $stats);
    }
} 