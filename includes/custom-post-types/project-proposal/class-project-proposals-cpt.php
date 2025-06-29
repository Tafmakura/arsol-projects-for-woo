<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Project Proposals Collection Management Class
 * Handles operations on collections of project proposal instances
 */
class Project_Proposals_CPT {
    
    /**
     * Get all project proposals
     */
    public static function get_all($args = array()) {
        $defaults = array(
            'post_type' => 'arsol-pfw-proposal',
            'post_status' => array('publish', 'private', 'draft'),
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        return get_posts($args);
    }
    
    /**
     * Get proposals by user
     */
    public static function get_by_user($user_id, $args = array()) {
        $args['author'] = $user_id;
        return self::get_all($args);
    }
    
    /**
     * Get proposals by status
     */
    public static function get_by_status($status_slug, $args = array()) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'arsol-pfw-proposal-stage',
                'field' => 'slug',
                'terms' => $status_slug
            )
        );
        return self::get_all($args);
    }
    
    /**
     * Get proposals by customer
     */
    public static function get_by_customer($customer_id, $args = array()) {
        return self::get_by_user($customer_id, $args);
    }
    
    /**
     * Search proposals
     */
    public static function search($search_term, $args = array()) {
        $args['s'] = $search_term;
        return self::get_all($args);
    }
    
    /**
     * Get proposal count
     */
    public static function get_count($args = array()) {
        $args['posts_per_page'] = -1;
        $args['fields'] = 'ids';
        $proposals = self::get_all($args);
        return count($proposals);
    }
    
    /**
     * Get proposal count by status
     */
    public static function get_count_by_status($status_slug, $args = array()) {
        return self::get_count(array_merge($args, array(
            'tax_query' => array(
                array(
                    'taxonomy' => 'arsol-pfw-proposal-stage',
                    'field' => 'slug',
                    'terms' => $status_slug
                )
            )
        )));
    }
    
    /**
     * Get proposal count by user
     */
    public static function get_count_by_user($user_id, $args = array()) {
        $args['author'] = $user_id;
        return self::get_count($args);
    }
    
    /**
     * Get recent proposals
     */
    public static function get_recent($limit = 5, $args = array()) {
        $args['posts_per_page'] = $limit;
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        return self::get_all($args);
    }
    
    /**
     * Get processing proposals
     */
    public static function get_processing($args = array()) {
        return self::get_by_status('processing', $args);
    }
    
    /**
     * Get pending approval proposals
     */
    public static function get_pending_approval($args = array()) {
        return self::get_by_status('pending-approval', $args);
    }
    
    /**
     * Get approved proposals
     */
    public static function get_approved($args = array()) {
        return self::get_by_status('approved', $args);
    }
    
    /**
     * Get rejected proposals
     */
    public static function get_rejected($args = array()) {
        return self::get_by_status('rejected', $args);
    }
    
    /**
     * Get available status options
     */
    public static function get_status_options() {
        $terms = get_terms(array(
            'taxonomy' => 'arsol-pfw-proposal-stage',
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
     * Bulk update proposal status
     */
    public static function bulk_update_status($proposal_ids, $status_slug) {
        if (!is_array($proposal_ids)) {
            $proposal_ids = array($proposal_ids);
        }
        
        $updated = 0;
        foreach ($proposal_ids as $proposal_id) {
            $proposal = new Project_Proposal_CPT($proposal_id);
            if ($proposal->exists()) {
                if ($proposal->set_status($status_slug)) {
                    $updated++;
                }
            }
        }
        
        return $updated;
    }
    
    /**
     * Get proposals ready for conversion to projects
     */
    public static function get_ready_for_conversion($args = array()) {
        return self::get_approved($args);
    }
    
    /**
     * Get proposals by date range
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
     * Get proposals that have been converted to projects
     */
    public static function get_converted($args = array()) {
        $args['meta_query'] = array(
            array(
                'key' => '_arsol_converted_to_project_id',
                'compare' => 'EXISTS'
            )
        );
        return self::get_all($args);
    }
    
    /**
     * Get proposals that haven't been converted yet
     */
    public static function get_not_converted($args = array()) {
        $args['meta_query'] = array(
            array(
                'key' => '_arsol_converted_to_project_id',
                'compare' => 'NOT EXISTS'
            )
        );
        return self::get_all($args);
    }
    
    /**
     * Get proposals with quotations
     */
    public static function get_with_quotations($args = array()) {
        $args['meta_query'] = array(
            array(
                'key' => '_arsol_proposal_quotation_data',
                'compare' => 'EXISTS'
            )
        );
        return self::get_all($args);
    }
    
    /**
     * Get proposals by budget range
     */
    public static function get_by_budget_range($min_budget = null, $max_budget = null, $args = array()) {
        $meta_query = array();
        
        if ($min_budget !== null) {
            $meta_query[] = array(
                'key' => '_arsol_proposal_budget_total',
                'value' => $min_budget,
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        if ($max_budget !== null) {
            $meta_query[] = array(
                'key' => '_arsol_proposal_budget_total',
                'value' => $max_budget,
                'compare' => '<=',
                'type' => 'NUMERIC'
            );
        }
        
        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }
        
        return self::get_all($args);
    }
} 