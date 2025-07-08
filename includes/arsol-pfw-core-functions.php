<?php
/**
 * Core Functions
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Functions
 */

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get a request by ID
 *
 * @param int $request_id Request ID
 * @return ARSOL_PFW_Request|false Request object or false if not found
 */
function arsol_pfw_get_request($request_id) {
    try {
        return new ARSOL_PFW_Request($request_id);
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * Create a new request
 *
 * @param array $args Request data
 * @return ARSOL_PFW_Request|WP_Error Request object or error
 */
function arsol_pfw_create_request($args = array()) {
    try {
        $request = new ARSOL_PFW_Request();
        
        // Set default values
        $defaults = array(
            'name' => '',
            'description' => '',
            'customer_id' => get_current_user_id(),
            'stage' => 'pending-review',
            'budget' => array(),
            'project_id' => 0,
            'deadline' => null,
            'start_date' => null,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Set properties
        foreach ($args as $key => $value) {
            $setter = "set_$key";
            if (method_exists($request, $setter)) {
                $request->$setter($value);
            }
        }
        
        // Save the request
        $request->save();
        
        return $request;
    } catch (\Exception $e) {
        return new \WP_Error('request_creation_failed', $e->getMessage());
    }
}

/**
 * Get requests by stage
 *
 * @param string $stage Stage slug
 * @param array $args Additional query arguments
 * @return array Array of ARSOL_PFW_Request objects
 */
function arsol_pfw_get_requests_by_stage($stage, $args = array()) {
    $data_store = \WC_Data_Store::load('arsol-pfw-request');
    $posts = $data_store->get_requests_by_stage($stage, $args);
    
    $requests = array();
    foreach ($posts as $post) {
        $requests[] = new ARSOL_PFW_Request($post->ID);
    }
    
    return $requests;
}

/**
 * Get requests by customer
 *
 * @param int $customer_id Customer user ID
 * @param array $args Additional query arguments
 * @return array Array of ARSOL_PFW_Request objects
 */
function arsol_pfw_get_requests_by_customer($customer_id, $args = array()) {
    $data_store = \WC_Data_Store::load('arsol-pfw-request');
    $posts = $data_store->get_requests_by_customer($customer_id, $args);
    
    $requests = array();
    foreach ($posts as $post) {
        $requests[] = new ARSOL_PFW_Request($post->ID);
    }
    
    return $requests;
}

/**
 * Get requests ready for conversion
 *
 * @param array $args Additional query arguments
 * @return array Array of ARSOL_PFW_Request objects
 */
function arsol_pfw_get_requests_ready_for_conversion($args = array()) {
    $data_store = \WC_Data_Store::load('arsol-pfw-request');
    $posts = $data_store->get_requests_ready_for_conversion($args);
    
    $requests = array();
    foreach ($posts as $post) {
        $requests[] = new ARSOL_PFW_Request($post->ID);
    }
    
    return $requests;
}

/**
 * Get available request stages
 *
 * @return array Array of stage_slug => stage_name
 */
function arsol_pfw_get_request_stages() {
    $data_store = \WC_Data_Store::load('arsol-pfw-request');
    return $data_store->get_available_stages();
}

/**
 * Get request stage counts
 *
 * @return array Array of stage_slug => count
 */
function arsol_pfw_get_request_stage_counts() {
    $data_store = \WC_Data_Store::load('arsol-pfw-request');
    return $data_store->get_stage_counts();
}

/**
 * Utility function to check if a request can be converted to a proposal
 *
 * @param ARSOL_PFW_Request $request Request object
 * @return bool True if request can be converted
 */
function arsol_pfw_can_convert_request($request) {
    if (!$request instanceof ARSOL_PFW_Request) {
        return false;
    }
    
    // Check if request is approved
    if ($request->get_stage() !== 'approved') {
        return false;
    }
    
    // Check if customer exists
    if (!$request->get_customer_id()) {
        return false;
    }
    
    // Check if not already converted
    if ($request->get_project_id()) {
        return false;
    }
    
    return true;
}

/**
 * Log a request action
 *
 * @param string $level Log level (info, warning, error)
 * @param string $message Log message
 * @param int $request_id Request ID
 */
function arsol_pfw_log_request($level, $message, $request_id = 0) {
    if (class_exists('Arsol_Projects_For_Woo\Woocommerce_Logs')) {
        Woocommerce_Logs::log_request($level, $message, $request_id);
    }
} 