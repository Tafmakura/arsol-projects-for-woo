<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get request by ID
 *
 * @param int $request_id Request ID
 * @return \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Project_Request_CPT|false
 */
function arsol_pfw_get_request($request_id) {
    if (!$request_id) {
        return false;
    }
    
    $request = new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Project_Request_CPT($request_id);
    return $request->get_id() ? $request : false;
}

/**
 * Create new request
 *
 * @param array $args Request arguments
 * @return \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Project_Request_CPT|WP_Error
 */
function arsol_pfw_create_request($args = array()) {
    $defaults = array(
        'name'        => '',
        'customer_id' => 0,
        'budget'      => 0,
        'deadline'    => '',
        'description' => '',
        'stage'       => 'pending-review',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Validate required fields
    if (empty($args['name'])) {
        return new WP_Error('missing_name', 'Request name is required');
    }
    
    if (empty($args['customer_id'])) {
        return new WP_Error('missing_customer', 'Customer ID is required');
    }
    
    $request = new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Project_Request_CPT();
    
    // Set properties
    $request->set_title($args['name']);
    $request->set_customer_id($args['customer_id']);
    $request->set_budget($args['budget']);
    $request->set_deadline($args['deadline']);
    $request->set_description($args['description']);
    $request->set_stage($args['stage']);
    
    return $request;
}

/**
 * Get requests by stage
 *
 * @param string $stage Stage slug
 * @param array $args Query arguments
 * @return array
 */
function arsol_pfw_get_requests_by_stage($stage, $args = array()) {
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_entities_by_stage('request', $stage, $args);
}

/**
 * Get request stage counts
 *
 * @return array
 */
function arsol_pfw_get_request_stage_counts() {
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_stage_counts('request');
}

/**
 * Get available request stages
 *
 * @return array
 */
function arsol_pfw_get_request_available_stages() {
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_available_stages('request');
}

/**
 * Update request stage
 *
 * @param int $request_id Request ID
 * @param string $new_stage New stage slug
 * @return bool|WP_Error
 */
function arsol_pfw_update_request_stage($request_id, $new_stage) {
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::update_stage($request_id, 'request', $new_stage);
}

/**
 * Approve request
 *
 * @param int $request_id Request ID
 * @return bool|WP_Error
 */
function arsol_pfw_approve_request($request_id) {
    $request = arsol_pfw_get_request($request_id);
    if (!$request) {
        return new WP_Error('invalid_request', 'Request not found');
    }
    
    return $request->approve();
}

/**
 * Reject request
 *
 * @param int $request_id Request ID
 * @param string $reason Rejection reason
 * @return bool|WP_Error
 */
function arsol_pfw_reject_request($request_id, $reason = '') {
    $request = arsol_pfw_get_request($request_id);
    if (!$request) {
        return new WP_Error('invalid_request', 'Request not found');
    }
    
    return $request->reject($reason);
}

/**
 * Get requests by customer
 *
 * @param int $customer_id Customer ID
 * @param array $args Query arguments
 * @return array
 */
function arsol_pfw_get_requests_by_customer($customer_id, $args = array()) {
    $default_args = array(
        'post_type'   => 'arsol-pfw-request',
        'post_status' => 'publish',
        'author'      => $customer_id,
        'posts_per_page' => -1,
    );
    
    $args = wp_parse_args($args, $default_args);
    return get_posts($args);
} 