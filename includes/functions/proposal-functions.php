<?php
/**
 * Proposal Functions
 * 
 * Global functions for working with proposals, following WooCommerce patterns
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get a single proposal
 *
 * @param int|WP_Post|Arsol_PFW_Proposal $the_proposal Proposal ID, post object, or proposal object
 * @return Arsol_PFW_Proposal|false Proposal object or false if not found
 */
function arsol_pfw_get_proposal($the_proposal = false) {
    if (empty($the_proposal)) {
        return false;
    }
    
    if ($the_proposal instanceof Arsol_PFW_Proposal) {
        return $the_proposal;
    }
    
    if (is_numeric($the_proposal)) {
        $the_proposal = get_post($the_proposal);
    }
    
    if (!$the_proposal || !is_object($the_proposal) || $the_proposal->post_type !== 'arsol-pfw-proposal') {
        return false;
    }
    
    return new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($the_proposal);
}

/**
 * Get multiple proposals
 * 
 * @param array $args Query arguments
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_proposals($args = array()) {
    $defaults = array(
        'post_type' => 'arsol-pfw-proposal',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    $args = wp_parse_args($args, $defaults);
    $posts = get_posts($args);
    
    return array_map('arsol_pfw_get_proposal', $posts);
}

/**
 * Get proposals by customer ID
 * 
 * @param int $customer_id Customer ID
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_proposals_by_customer($customer_id) {
    $args = array(
        'post_type' => 'arsol-pfw-proposal',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_arsol_pfw_customer_id',
                'value' => $customer_id,
                'compare' => '='
            )
        )
    );
    
    $posts = get_posts($args);
    $proposals = array();
    
    foreach ($posts as $post) {
        $proposals[] = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post);
    }
    
    return $proposals;
}

/**
 * Get proposals by post author
 * 
 * @param int $user_id User ID
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_proposals_by_post_author($user_id) {
    $args = array(
        'post_type' => 'arsol-pfw-proposal',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'author' => $user_id, // Query by post_author
    );
    
    $posts = get_posts($args);
    $proposals = array();
    
    foreach ($posts as $post) {
        $proposals[] = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post);
    }
    
    return $proposals;
}

/**
 * Get proposals by user (customer or post author)
 * 
 * @param int $user_id User ID
 * @param string $role 'customer' or 'post_author' or 'both'
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_proposals_by_user($user_id, $role = 'both') {
    if ($role === 'customer') {
        return arsol_pfw_get_proposals_by_customer($user_id);
    } elseif ($role === 'post_author') {
        return arsol_pfw_get_proposals_by_post_author($user_id);
    } else {
        // Get both customer and post author proposals
        $customer_proposals = arsol_pfw_get_proposals_by_customer($user_id);
        $post_author_proposals = arsol_pfw_get_proposals_by_post_author($user_id);
        
        // Merge and remove duplicates
        $all_proposals = array_merge($customer_proposals, $post_author_proposals);
        $unique_proposals = array();
        
        foreach ($all_proposals as $proposal) {
            $unique_proposals[$proposal->get_id()] = $proposal;
        }
        
        return array_values($unique_proposals);
    }
}

/**
 * Get proposals by stage
 *
 * @param string $stage Stage slug
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_proposals_by_stage($stage, $args = array()) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'arsol-pfw-proposal-stage',
            'field' => 'slug',
            'terms' => $stage,
        ),
    );
    return arsol_pfw_get_proposals($args);
}

/**
 * Get proposals by budget range
 * 
 * @param float|null $min_budget Minimum budget
 * @param float|null $max_budget Maximum budget
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_proposals_by_budget_range($min_budget = null, $max_budget = null, $args = array()) {
    $meta_query = array();
    
    if ($min_budget !== null) {
        $meta_query[] = array(
            'key' => '_arsol_pfw_proposal_budget',
            'value' => $min_budget,
            'compare' => '>=',
            'type' => 'NUMERIC',
        );
    }
    
    if ($max_budget !== null) {
        $meta_query[] = array(
            'key' => '_arsol_pfw_proposal_budget',
            'value' => $max_budget,
            'compare' => '<=',
            'type' => 'NUMERIC',
        );
    }
    
    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
    }
    
    return arsol_pfw_get_proposals($args);
}

/**
 * Get proposals by date range
 * 
 * @param string $start_date Start date
 * @param string $end_date End date
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_proposals_by_date_range($start_date, $end_date, $args = array()) {
    $args['date_query'] = array(
        array(
            'after' => $start_date,
            'before' => $end_date,
            'inclusive' => true,
        ),
    );
    return arsol_pfw_get_proposals($args);
}

/**
 * Get stage counts using OOP stage entities
 * 
 * @return array Array of stage counts
 */
function arsol_pfw_get_proposal_stage_counts() {
    $stage_entity = new \Arsol_Projects_For_Woo\Taxonomies\Stages\Proposal_Stage(0);
    $statistics = $stage_entity->get_stage_statistics();
    
    $counts = array();
    foreach ($statistics as $stage_slug => $stage_data) {
        $counts[$stage_slug] = $stage_data['count'];
    }
    
    return $counts;
}

/**
 * Get available stages using OOP stage entities
 * 
 * @return array Array of available stages
 */
function arsol_pfw_get_available_proposal_stages() {
    $stage_entity = new \Arsol_Projects_For_Woo\Taxonomies\Stages\Proposal_Stage(0);
    $stages = $stage_entity->get_available_stages();
    
    $stage_options = array();
    foreach ($stages as $stage_slug => $stage_data) {
        $stage_options[$stage_slug] = $stage_data['label'];
    }
    
    return $stage_options;
}

/**
 * Bulk update proposal stages using OOP stage entities
 * 
 * @param array $proposal_ids Array of proposal IDs
 * @param string $stage_slug Stage slug to set
 * @return int Number of proposals updated
 */
function arsol_pfw_bulk_update_proposal_stages($proposal_ids, $stage_slug) {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Proposal_Stage::bulk_update_stage($proposal_ids, $stage_slug);
} 