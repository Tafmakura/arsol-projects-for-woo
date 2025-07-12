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
    
    return new Arsol_PFW_Proposal($the_proposal);
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
 * Get proposals by user
 * 
 * @param int $user_id User ID
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_proposals_by_user($user_id, $args = array()) {
    $args['author'] = $user_id;
    return arsol_pfw_get_proposals($args);
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
 * Get proposals by customer
 * 
 * @param int $customer_id Customer ID
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_proposals_by_customer($customer_id, $args = array()) {
    $args['meta_query'] = array(
        array(
            'key' => '_arsol_pfw_proposal_customer_id',
            'value' => $customer_id,
            'compare' => '=',
        ),
    );
    return arsol_pfw_get_proposals($args);
}

/**
 * Search proposals
 * 
 * @param string $search_term Search term
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_search_proposals($search_term, $args = array()) {
    $args['s'] = $search_term;
    return arsol_pfw_get_proposals($args);
}

/**
 * Get proposal count
 * 
 * @param array $args Query arguments
 * @return int Number of proposals
 */
function arsol_pfw_get_proposal_count($args = array()) {
    $args['posts_per_page'] = -1;
    $args['fields'] = 'ids';
    $posts = get_posts($args);
    return count($posts);
}

/**
 * Get count by user
 * 
 * @param int $user_id User ID
 * @param array $args Additional query arguments
 * @return int Number of proposals
 */
function arsol_pfw_get_proposal_count_by_user($user_id, $args = array()) {
    $args['author'] = $user_id;
    return arsol_pfw_get_proposal_count($args);
}

/**
 * Get recent proposals
 * 
 * @param int $limit Number of proposals to return
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_recent_proposals($limit = 5, $args = array()) {
    $args['posts_per_page'] = $limit;
    return arsol_pfw_get_proposals($args);
}

/**
 * Get processing proposals
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_processing_proposals($args = array()) {
    return arsol_pfw_get_proposals_by_stage('processing', $args);
}

/**
 * Get pending approval proposals
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_pending_approval_proposals($args = array()) {
    return arsol_pfw_get_proposals_by_stage('pending-approval', $args);
}

/**
 * Get approved proposals
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_approved_proposals($args = array()) {
    return arsol_pfw_get_proposals_by_stage('approved', $args);
}

/**
 * Get rejected proposals
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_rejected_proposals($args = array()) {
    return arsol_pfw_get_proposals_by_stage('rejected', $args);
}

/**
 * Get expired proposals
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_expired_proposals($args = array()) {
    return arsol_pfw_get_proposals_by_stage('expired', $args);
}

/**
 * Get ready for conversion proposals
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_ready_for_conversion_proposals($args = array()) {
    return arsol_pfw_get_proposals_by_stage('approved', $args);
}

/**
 * Get converted proposals
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_converted_proposals($args = array()) {
    $args['meta_query'] = array(
        array(
            'key' => '_arsol_pfw_proposal_converted_to_project',
            'compare' => 'EXISTS',
        ),
    );
    return arsol_pfw_get_proposals($args);
}

/**
 * Get not converted proposals
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_not_converted_proposals($args = array()) {
    $args['meta_query'] = array(
        array(
            'key' => '_arsol_pfw_proposal_converted_to_project',
            'compare' => 'NOT EXISTS',
        ),
    );
    return arsol_pfw_get_proposals($args);
}

/**
 * Get proposals with quotations
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Proposal objects
 */
function arsol_pfw_get_proposals_with_quotations($args = array()) {
    $args['meta_query'] = array(
        array(
            'key' => '_arsol_pfw_proposal_quotation',
            'compare' => 'EXISTS',
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
 * Get stage counts
 * 
 * @return array Array of stage counts
 */
function arsol_pfw_get_proposal_stage_counts() {
    $stages = get_terms(array(
        'taxonomy' => 'arsol-pfw-proposal-stage',
        'hide_empty' => false,
    ));
    
    $counts = array();
    foreach ($stages as $stage) {
        $counts[$stage->slug] = $stage->count;
    }
    
    return $counts;
}

/**
 * Get available stages
 * 
 * @return array Array of available stages
 */
function arsol_pfw_get_available_proposal_stages() {
    $stages = get_terms(array(
        'taxonomy' => 'arsol-pfw-proposal-stage',
        'hide_empty' => false,
    ));
    
    $stage_options = array();
    foreach ($stages as $stage) {
        $stage_options[$stage->slug] = $stage->name;
    }
    
    return $stage_options;
}

/**
 * Bulk update proposal stages
 * 
 * @param array $proposal_ids Array of proposal IDs
 * @param string $stage_slug Stage slug to set
 * @return int Number of proposals updated
 */
function arsol_pfw_bulk_update_proposal_stages($proposal_ids, $stage_slug) {
    $updated = 0;
    
    foreach ($proposal_ids as $proposal_id) {
        $proposal = arsol_pfw_get_proposal($proposal_id);
        if ($proposal) {
            $proposal->update_stage($stage_slug);
            $updated++;
        }
    }
    
    return $updated;
} 