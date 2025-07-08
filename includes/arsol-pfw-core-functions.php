<?php
/**
 * Core Functions
 *
 * Factory functions for CRUD entities
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Functions
 */

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Request Factory Functions
|--------------------------------------------------------------------------
*/

/**
 * Get request by ID
 *
 * @param int $request_id Request ID
 * @return ARSOL_PFW_Request|false Request object or false if not found
 */
function arsol_pfw_get_request($request_id) {
    try {
        return new ARSOL_PFW_Request($request_id);
    } catch (Exception $e) {
        wc_get_logger()->error('Failed to get request: ' . $e->getMessage(), array('source' => 'arsol-pfw'));
        return false;
    }
}

/**
 * Create new request
 *
 * @param array $args Request data
 * @return ARSOL_PFW_Request Request object
 */
function arsol_pfw_create_request($args = array()) {
    $request = new ARSOL_PFW_Request();
    
    // Set properties from args
    foreach ($args as $key => $value) {
        if (is_callable(array($request, "set_{$key}"))) {
            $request->{"set_{$key}"}($value);
        }
    }
    
    return $request;
}

/**
 * Get requests by stage
 *
 * @param string $stage Stage slug
 * @param array $args Additional arguments
 * @return ARSOL_PFW_Request[] Array of request objects
 */
function arsol_pfw_get_requests_by_stage($stage, $args = array()) {
    $data_store = WC_Data_Store::load('arsol-pfw-request');
    $posts = $data_store->get_requests_by_stage($stage, $args);
    
    $requests = array();
    foreach ($posts as $post) {
        $requests[] = arsol_pfw_get_request($post->ID);
    }
    
    return array_filter($requests); // Remove any false values
}

/**
 * Get requests by customer
 *
 * @param int $customer_id Customer user ID
 * @param array $args Additional arguments
 * @return ARSOL_PFW_Request[] Array of request objects
 */
function arsol_pfw_get_requests_by_customer($customer_id, $args = array()) {
    $data_store = WC_Data_Store::load('arsol-pfw-request');
    $posts = $data_store->get_requests_by_customer($customer_id, $args);
    
    $requests = array();
    foreach ($posts as $post) {
        $requests[] = arsol_pfw_get_request($post->ID);
    }
    
    return array_filter($requests); // Remove any false values
}

/**
 * Get requests ready for conversion to proposals
 *
 * @param array $args Additional arguments
 * @return ARSOL_PFW_Request[] Array of request objects
 */
function arsol_pfw_get_requests_ready_for_conversion($args = array()) {
    return arsol_pfw_get_requests_by_stage('approved', $args);
}

/*
|--------------------------------------------------------------------------
| Proposal Factory Functions (Placeholder for future implementation)
|--------------------------------------------------------------------------
*/

/**
 * Get proposal by ID
 *
 * @param int $proposal_id Proposal ID
 * @return ARSOL_PFW_Proposal|false Proposal object or false if not found
 */
function arsol_pfw_get_proposal($proposal_id) {
    // TODO: Implement in Phase 2
    return false;
}

/**
 * Create new proposal
 *
 * @param array $args Proposal data
 * @return ARSOL_PFW_Proposal Proposal object
 */
function arsol_pfw_create_proposal($args = array()) {
    // TODO: Implement in Phase 2
    return false;
}

/*
|--------------------------------------------------------------------------
| Project Factory Functions (Placeholder for future implementation)
|--------------------------------------------------------------------------
*/

/**
 * Get project by ID
 *
 * @param int $project_id Project ID
 * @return ARSOL_PFW_Project|false Project object or false if not found
 */
function arsol_pfw_get_project($project_id) {
    // TODO: Implement in Phase 3
    return false;
}

/**
 * Create new project
 *
 * @param array $args Project data
 * @return ARSOL_PFW_Project Project object
 */
function arsol_pfw_create_project($args = array()) {
    // TODO: Implement in Phase 3
    return false;
}

/*
|--------------------------------------------------------------------------
| Utility Functions
|--------------------------------------------------------------------------
*/

/**
 * Check if a post is a valid request
 *
 * @param int|WP_Post $post Post ID or post object
 * @return bool
 */
function arsol_pfw_is_request($post) {
    $post = get_post($post);
    return $post && 'arsol-pfw-request' === $post->post_type;
}

/**
 * Check if a post is a valid proposal
 *
 * @param int|WP_Post $post Post ID or post object
 * @return bool
 */
function arsol_pfw_is_proposal($post) {
    $post = get_post($post);
    return $post && 'arsol-pfw-proposal' === $post->post_type;
}

/**
 * Check if a post is a valid project
 *
 * @param int|WP_Post $post Post ID or post object
 * @return bool
 */
function arsol_pfw_is_project($post) {
    $post = get_post($post);
    return $post && 'arsol-pfw-project' === $post->post_type;
}

/**
 * Get available request stages
 *
 * @return array Array of stage_slug => stage_name
 */
function arsol_pfw_get_request_stages() {
    $data_store = WC_Data_Store::load('arsol-pfw-request');
    return $data_store->get_available_stages();
}

/**
 * Get request stage counts
 *
 * @return array Array of stage_slug => count
 */
function arsol_pfw_get_request_stage_counts() {
    $data_store = WC_Data_Store::load('arsol-pfw-request');
    return $data_store->get_stage_counts();
} 