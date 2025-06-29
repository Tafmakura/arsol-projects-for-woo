<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\Project;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Projects Collection Management Class
 * Handles operations on collections of project instances
 */
class Projects_CPT {
    
    /**
     * Get all projects
     */
    public static function get_all($args = array()) {
        $defaults = array(
            'post_type' => 'arsol-pfw-project',
            'post_status' => array('publish', 'private', 'draft'),
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        return get_posts($args);
    }
    
    /**
     * Get projects by user
     */
    public static function get_by_user($user_id, $args = array()) {
        $args['author'] = $user_id;
        return self::get_all($args);
    }
    
    /**
     * Get projects by status
     */
    public static function get_by_status($status_slug, $args = array()) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'arsol-pfw-project-status',
                'field' => 'slug',
                'terms' => $status_slug
            )
        );
        return self::get_all($args);
    }
    
    /**
     * Get projects by customer
     */
    public static function get_by_customer($customer_id, $args = array()) {
        return self::get_by_user($customer_id, $args);
    }
    
    /**
     * Search projects
     */
    public static function search($search_term, $args = array()) {
        $args['s'] = $search_term;
        return self::get_all($args);
    }
    
    /**
     * Get project count
     */
    public static function get_count($args = array()) {
        $args['posts_per_page'] = -1;
        $args['fields'] = 'ids';
        $projects = self::get_all($args);
        return count($projects);
    }
    
    /**
     * Get project count by status
     */
    public static function get_count_by_status($status_slug, $args = array()) {
        return self::get_count(array_merge($args, array(
            'tax_query' => array(
                array(
                    'taxonomy' => 'arsol-pfw-project-status',
                    'field' => 'slug',
                    'terms' => $status_slug
                )
            )
        )));
    }
    
    /**
     * Get project count by user
     */
    public static function get_count_by_user($user_id, $args = array()) {
        $args['author'] = $user_id;
        return self::get_count($args);
    }
    
    /**
     * Get recent projects
     */
    public static function get_recent($limit = 5, $args = array()) {
        $args['posts_per_page'] = $limit;
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        return self::get_all($args);
    }
    
    /**
     * Get projects with related orders
     */
    public static function get_with_orders($args = array()) {
        $projects = self::get_all($args);
        $projects_with_orders = array();
        
        foreach ($projects as $project) {
            $order_ids = get_post_meta($project->ID, '_arsol_related_order_ids', true);
            if (!empty($order_ids)) {
                $projects_with_orders[] = $project;
            }
        }
        
        return $projects_with_orders;
    }
    
    /**
     * Get projects with subscriptions
     */
    public static function get_with_subscriptions($args = array()) {
        $projects = self::get_all($args);
        $projects_with_subscriptions = array();
        
        foreach ($projects as $project) {
            $subscription_ids = get_post_meta($project->ID, '_arsol_related_subscription_ids', true);
            if (!empty($subscription_ids)) {
                $projects_with_subscriptions[] = $project;
            }
        }
        
        return $projects_with_subscriptions;
    }
    
    /**
     * Get available status options
     */
    public static function get_status_options() {
        $terms = get_terms(array(
            'taxonomy' => 'arsol-pfw-project-status',
            'hide_empty' => false,
        ));
        
        $options = array();
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $options[$term->slug] = $term->name;
            }
        }
        
        return $options;
    }
    
    /**
     * Bulk update project status
     */
    public static function bulk_update_status($project_ids, $status_slug) {
        if (!is_array($project_ids)) {
            $project_ids = array($project_ids);
        }
        
        $updated = 0;
        foreach ($project_ids as $project_id) {
            $project = new Project_CPT($project_id);
            if ($project->exists()) {
                if ($project->set_status($status_slug)) {
                    $updated++;
                }
            }
        }
        
        return $updated;
    }
    
    /**
     * Get projects by date range
     */
    public static function get_by_date_range($start_date, $end_date, $args = array()) {
        $args['date_query'] = array(
            array(
                'after' => $start_date,
                'before' => $end_date,
                'inclusive' => true,
            ),
        );
        return self::get_all($args);
    }
}
