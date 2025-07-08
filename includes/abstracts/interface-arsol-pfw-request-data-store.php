<?php
/**
 * Request Data Store Interface
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Interfaces
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Request Data Store Interface
 * 
 * Specific interface for Request data store operations
 */
interface ARSOL_PFW_Request_Data_Store_Interface {
    
    /**
     * Get requests by stage
     *
     * @param string $stage Stage slug
     * @param array $args Additional query arguments
     * @return array Array of WP_Post objects
     */
    public function get_requests_by_stage($stage, $args = array());
    
    /**
     * Get requests by customer
     *
     * @param int $customer_id Customer user ID
     * @param array $args Additional query arguments
     * @return array Array of WP_Post objects
     */
    public function get_requests_by_customer($customer_id, $args = array());
    
    /**
     * Get requests ready for conversion to proposals
     *
     * @param array $args Additional query arguments
     * @return array Array of WP_Post objects
     */
    public function get_requests_ready_for_conversion($args = array());
} 