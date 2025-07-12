<?php
/**
 * Request Functions
 * 
 * Global functions for working with requests, following WooCommerce patterns
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get a single request
 * 
 * @param int|WP_Post|Arsol_Pfw_CPT_Request $the_request Request ID, post object, or request object
 * @return Arsol_Pfw_CPT_Request|false Request object or false if not found
 */
function arsol_pfw_get_request($the_request = false) {
    if (empty($the_request)) {
        return false;
    }
    
    if ($the_request instanceof Arsol_Pfw_CPT_Request) {
        return $the_request;
    }
    
    if (is_numeric($the_request)) {
        $the_request = get_post($the_request);
    }
    
    if (!$the_request || !is_object($the_request) || $the_request->post_type !== 'arsol-pfw-request') {
        return false;
    }
    
    return new Arsol_Pfw_CPT_Request($the_request);
}

/**
 * Get multiple requests
 * 
 * @param array $args Query arguments
 * @return array Array of Arsol_Pfw_CPT_Request objects
 */
function arsol_pfw_get_requests($args = array()) {
    $defaults = array(
        'post_type' => 'arsol-pfw-request',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    $args = wp_parse_args($args, $defaults);
    $posts = get_posts($args);
    
    return array_map('arsol_pfw_get_request', $posts);
}

/**
 * Get requests by user
 * 
 * @param int $user_id User ID
 * @param array $args Additional query arguments
 * @return array Array of Arsol_Pfw_CPT_Request objects
 */
function arsol_pfw_get_requests_by_user($user_id, $args = array()) {
    $args['author'] = $user_id;
    return arsol_pfw_get_requests($args);
}

/**
 * Get requests by stage
 * 
 * @param string $stage Stage slug
 * @param array $args Additional query arguments
 * @return array Array of Arsol_Pfw_CPT_Request objects
 */
function arsol_pfw_get_requests_by_stage($stage, $args = array()) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'arsol-pfw-request-stage',
            'field' => 'slug',
            'terms' => $stage,
        ),
    );
    return arsol_pfw_get_requests($args);
}

/**
 * Get requests by customer
 * 
 * @param int $customer_id Customer ID
 * @param array $args Additional query arguments
 * @return array Array of Arsol_Pfw_CPT_Request objects
 */
function arsol_pfw_get_requests_by_customer($customer_id, $args = array()) {
    $args['meta_query'] = array(
        array(
            'key' => '_arsol_pfw_request_customer_id',
            'value' => $customer_id,
            'compare' => '=',
        ),
    );
    return arsol_pfw_get_requests($args);
}

/**
 * Search requests
 * 
 * @param string $search_term Search term
 * @param array $args Additional query arguments
 * @return array Array of Arsol_Pfw_CPT_Request objects
 */
function arsol_pfw_search_requests($search_term, $args = array()) {
    $args['s'] = $search_term;
    return arsol_pfw_get_requests($args);
}

/**
 * Get request count
 * 
 * @param array $args Query arguments
 * @return int Number of requests
 */
function arsol_pfw_get_request_count($args = array()) {
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
 * @return int Number of requests
 */
function arsol_pfw_get_request_count_by_user($user_id, $args = array()) {
    $args['author'] = $user_id;
    return arsol_pfw_get_request_count($args);
}

/**
 * Get recent requests
 * 
 * @param int $limit Number of requests to return
 * @param array $args Additional query arguments
 * @return array Array of Arsol_Pfw_CPT_Request objects
 */
function arsol_pfw_get_recent_requests($limit = 5, $args = array()) {
    $args['posts_per_page'] = $limit;
    return arsol_pfw_get_requests($args);
}

/**
 * Get pending review requests
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_Pfw_CPT_Request objects
 */
function arsol_pfw_get_pending_review_requests($args = array()) {
    return arsol_pfw_get_requests_by_stage('pending-review', $args);
}

/**
 * Get under review requests
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_Pfw_CPT_Request objects
 */
function arsol_pfw_get_under_review_requests($args = array()) {
    return arsol_pfw_get_requests_by_stage('under-review', $args);
}

/**
 * Get approved requests
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_Pfw_CPT_Request objects
 */
function arsol_pfw_get_approved_requests($args = array()) {
    return arsol_pfw_get_requests_by_stage('approved', $args);
}

/**
 * Get rejected requests
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_Pfw_CPT_Request objects
 */
function arsol_pfw_get_rejected_requests($args = array()) {
    return arsol_pfw_get_requests_by_stage('rejected', $args);
}

/**
 * Get on hold requests
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_Pfw_CPT_Request objects
 */
function arsol_pfw_get_on_hold_requests($args = array()) {
    return arsol_pfw_get_requests_by_stage('on-hold', $args);
}

/**
 * Get ready for conversion requests
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_Pfw_CPT_Request objects
 */
function arsol_pfw_get_ready_for_conversion_requests($args = array()) {
    return arsol_pfw_get_requests_by_stage('approved', $args);
}

/**
 * Get converted requests
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_Pfw_CPT_Request objects
 */
function arsol_pfw_get_converted_requests($args = array()) {
    $args['meta_query'] = array(
        array(
            'key' => '_arsol_pfw_request_converted_to_proposal',
            'compare' => 'EXISTS',
        ),
    );
    return arsol_pfw_get_requests($args);
}

/**
 * Get not converted requests
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_Pfw_CPT_Request objects
 */
function arsol_pfw_get_not_converted_requests($args = array()) {
    $args['meta_query'] = array(
        array(
            'key' => '_arsol_pfw_request_converted_to_proposal',
            'compare' => 'NOT EXISTS',
        ),
    );
    return arsol_pfw_get_requests($args);
}

/**
 * Get requests by date range
 * 
 * @param string $start_date Start date
 * @param string $end_date End date
 * @param array $args Additional query arguments
 * @return array Array of Arsol_Pfw_CPT_Request objects
 */
function arsol_pfw_get_requests_by_date_range($start_date, $end_date, $args = array()) {
    $args['date_query'] = array(
        array(
            'after' => $start_date,
            'before' => $end_date,
            'inclusive' => true,
        ),
    );
    return arsol_pfw_get_requests($args);
}

/**
 * Get stage counts
 * 
 * @return array Array of stage counts
 */
function arsol_pfw_get_request_stage_counts() {
    $stages = get_terms(array(
        'taxonomy' => 'arsol-pfw-request-stage',
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
function arsol_pfw_get_available_request_stages() {
    $stages = get_terms(array(
        'taxonomy' => 'arsol-pfw-request-stage',
        'hide_empty' => false,
    ));
    
    $stage_options = array();
    foreach ($stages as $stage) {
        $stage_options[$stage->slug] = $stage->name;
    }
    
    return $stage_options;
}

/**
 * Bulk update request stages
 * 
 * @param array $request_ids Array of request IDs
 * @param string $stage_slug Stage slug to set
 * @return int Number of requests updated
 */
function arsol_pfw_bulk_update_request_stages($request_ids, $stage_slug) {
    $updated = 0;
    
    foreach ($request_ids as $request_id) {
        $request = arsol_pfw_get_request($request_id);
        if ($request) {
            $request->update_stage($stage_slug);
            $updated++;
        }
    }
    
    return $updated;
} 