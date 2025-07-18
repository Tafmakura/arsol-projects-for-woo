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
 * @param int|WP_Post|Arsol_PFW_Request $the_request Request ID, post object, or request object
 * @return Arsol_PFW_Request|false Request object or false if not found
 */
function arsol_pfw_get_request($the_request = false) {
    if (empty($the_request)) {
        return false;
    }
    
    if ($the_request instanceof Arsol_PFW_Request) {
        return $the_request;
    }
    
    if (is_numeric($the_request)) {
        $the_request = get_post($the_request);
    }
    
    if (!$the_request || !is_object($the_request) || $the_request->post_type !== 'arsol-pfw-request') {
        return false;
    }
    
    return new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($the_request);
}

/**
 * Get multiple requests
 * 
 * @param array $args Query arguments
 * @return array Array of Arsol_PFW_Request objects
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
 * Get requests by customer ID
 * 
 * @param int $customer_id Customer ID
 * @return array Array of Arsol_PFW_Request objects
 */
function arsol_pfw_get_requests_by_customer($customer_id) {
    $args = array(
        'post_type' => 'arsol-pfw-request',
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
    $requests = array();
    
    foreach ($posts as $post) {
        $requests[] = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($post);
    }
    
    return $requests;
}

/**
 * Get requests by post author
 * 
 * @param int $user_id User ID
 * @return array Array of Arsol_PFW_Request objects
 */
function arsol_pfw_get_requests_by_post_author($user_id) {
    $args = array(
        'post_type' => 'arsol-pfw-request',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'author' => $user_id, // Query by post_author
    );
    
    $posts = get_posts($args);
    $requests = array();
    
    foreach ($posts as $post) {
        $requests[] = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($post);
    }
    
    return $requests;
}

/**
 * Get requests by user (customer or post author)
 * 
 * @param int $user_id User ID
 * @param string $role 'customer' or 'post_author' or 'both'
 * @return array Array of Arsol_PFW_Request objects
 */
function arsol_pfw_get_requests_by_user($user_id, $role = 'both') {
    if ($role === 'customer') {
        return arsol_pfw_get_requests_by_customer($user_id);
    } elseif ($role === 'post_author') {
        return arsol_pfw_get_requests_by_post_author($user_id);
    } else {
        // Get both customer and post author requests
        $customer_requests = arsol_pfw_get_requests_by_customer($user_id);
        $post_author_requests = arsol_pfw_get_requests_by_post_author($user_id);
        
        // Merge and remove duplicates
        $all_requests = array_merge($customer_requests, $post_author_requests);
        $unique_requests = array();
        
        foreach ($all_requests as $request) {
            $unique_requests[$request->get_id()] = $request;
        }
        
        return array_values($unique_requests);
    }
}

/**
 * Get requests by stage
 *
 * @param string $stage Stage slug
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Request objects
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
 * Search requests
 * 
 * @param string $search_term Search term
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Request objects
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
 * @return array Array of Arsol_PFW_Request objects
 */
function arsol_pfw_get_recent_requests($limit = 5, $args = array()) {
    $args['posts_per_page'] = $limit;
    return arsol_pfw_get_requests($args);
}

/**
 * Get pending review requests
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Request objects
 */
function arsol_pfw_get_pending_review_requests($args = array()) {
    return arsol_pfw_get_requests_by_stage('pending-review', $args);
}

/**
 * Get under review requests
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Request objects
 */
function arsol_pfw_get_under_review_requests($args = array()) {
    return arsol_pfw_get_requests_by_stage('under-review', $args);
}

/**
 * Get approved requests
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Request objects
 */
function arsol_pfw_get_approved_requests($args = array()) {
    return arsol_pfw_get_requests_by_stage('approved', $args);
}

/**
 * Get rejected requests
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Request objects
 */
function arsol_pfw_get_rejected_requests($args = array()) {
    return arsol_pfw_get_requests_by_stage('rejected', $args);
}

/**
 * Get on hold requests
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Request objects
 */
function arsol_pfw_get_on_hold_requests($args = array()) {
    return arsol_pfw_get_requests_by_stage('on-hold', $args);
}

/**
 * Get ready for conversion requests
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Request objects
 */
function arsol_pfw_get_ready_for_conversion_requests($args = array()) {
    return arsol_pfw_get_requests_by_stage('approved', $args);
}

/**
 * Get converted requests
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Request objects
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
 * @return array Array of Arsol_PFW_Request objects
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
 * @return array Array of Arsol_PFW_Request objects
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
 * Set request stage for one or more requests
 * 
 * @param int|array $request_ids Single request ID or array of request IDs
 * @param string $stage_slug Stage slug
 * @param bool $create_if_missing Whether to create the stage if it doesn't exist
 * @return bool Success status
 */
function arsol_pfw_set_request_stage($request_ids, $stage_slug, $create_if_missing = false) {
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::set_stage($request_ids, $stage_slug, 'arsol-pfw-request-stage', $create_if_missing);
}

/**
 * Get request stage counts using native WordPress term count properties
 * 
 * @return array Array of stage counts with native term properties
 */
function arsol_pfw_get_request_stage_counts() {
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_stage_count('arsol-pfw-request-stage', 'arsol-pfw-request');
}

/**
 * Get available request stages
 * 
 * @return array Array of available stages
 */
function arsol_pfw_get_available_request_stages() {
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_available_stages('arsol-pfw-request-stage');
}

/**
 * Bulk update request stages
 * 
 * @param array $request_ids Array of request IDs
 * @param string $stage_slug Stage slug
 * @return bool Success status
 */
function arsol_pfw_bulk_update_request_stages($request_ids, $stage_slug) {
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::bulk_update_stage($request_ids, $stage_slug, 'arsol-pfw-request-stage');
} 