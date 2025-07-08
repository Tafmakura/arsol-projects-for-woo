<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Project Requests Collection Management Class
 * Handles operations on collections of project request instances
 */
class Project_Requests_CPT {
    
    /**
     * Get all project requests
     */
    public static function get_all($args = array()) {
        $defaults = array(
            'post_type' => 'arsol-pfw-request',
            'post_status' => array('publish', 'private', 'draft'),
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        return get_posts($args);
    }
    
    /**
     * Get requests by user
     */
    public static function get_by_user($user_id, $args = array()) {
        $args['author'] = $user_id;
        return self::get_all($args);
    }
    
    /**
     * Get requests by status
     */
    public static function get_by_status($status_slug, $args = array()) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'arsol-pfw-request-stage',
                'field' => 'slug',
                'terms' => $status_slug
            )
        );
        return self::get_all($args);
    }
    
    /**
     * Get requests by customer
     */
    public static function get_by_customer($customer_id, $args = array()) {
        return self::get_by_user($customer_id, $args);
    }
    
    /**
     * Search requests
     */
    public static function search($search_term, $args = array()) {
        $args['s'] = $search_term;
        return self::get_all($args);
    }
    
    /**
     * Get request count
     */
    public static function get_count($args = array()) {
        $args['posts_per_page'] = -1;
        $args['fields'] = 'ids';
        $requests = self::get_all($args);
        return count($requests);
    }
    
    /**
     * Get request count by status
     */
    public static function get_count_by_status($status_slug, $args = array()) {
        return self::get_count(array_merge($args, array(
            'tax_query' => array(
                array(
                    'taxonomy' => 'arsol-pfw-request-stage',
                    'field' => 'slug',
                    'terms' => $status_slug
                )
            )
        )));
    }
    
    /**
     * Get request count by user
     */
    public static function get_count_by_user($user_id, $args = array()) {
        $args['author'] = $user_id;
        return self::get_count($args);
    }
    
    /**
     * Get recent requests
     */
    public static function get_recent($limit = 5, $args = array()) {
        $args['posts_per_page'] = $limit;
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        return self::get_all($args);
    }
    
    /**
     * Get pending review requests
     */
    public static function get_pending_review($args = array()) {
        return self::get_by_status('pending-review', $args);
    }
    
    /**
     * Get under review requests
     */
    public static function get_under_review($args = array()) {
        return self::get_by_status('under-review', $args);
    }
    
    /**
     * Get approved requests
     */
    public static function get_approved($args = array()) {
        return self::get_by_status('approved', $args);
    }
    
    /**
     * Get on hold requests
     */
    public static function get_on_hold($args = array()) {
        return self::get_by_status('on-hold', $args);
    }
    
    /**
     * Get available status options
     */
    public static function get_status_options() {
        $terms = get_terms(array(
            'taxonomy' => 'arsol-pfw-request-stage',
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
     * Bulk update request status
     */
    public static function bulk_update_status($request_ids, $status_slug) {
        if (!is_array($request_ids)) {
            $request_ids = array($request_ids);
        }
        
        $updated = 0;
        foreach ($request_ids as $request_id) {
            $request = new Project_Request_CPT($request_id);
            if ($request->exists()) {
                if ($request->set_status($status_slug)) {
                    $updated++;
                }
            }
        }
        
        return $updated;
    }
    
    /**
     * Get requests ready for conversion to proposals
     */
    public static function get_ready_for_conversion($args = array()) {
        return self::get_approved($args);
    }
    
    /**
     * Get requests by date range
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
    
    /**
     * Get requests that have been converted to proposals
     */
    public static function get_converted($args = array()) {
        $args['meta_query'] = array(
            array(
                'key' => '_arsol_converted_to_proposal_id',
                'compare' => 'EXISTS'
            )
        );
        return self::get_all($args);
    }
    
    /**
     * Get requests that haven't been converted yet
     */
    public static function get_not_converted($args = array()) {
        $args['meta_query'] = array(
            array(
                'key' => '_arsol_converted_to_proposal_id',
                'compare' => 'NOT EXISTS'
            )
        );
        return self::get_all($args);
    }
} 