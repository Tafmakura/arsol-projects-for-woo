<?php
/**
 * Object Data Store Interface
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Interfaces
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Object Data Store Interface
 * 
 * Extends WooCommerce's data store interface for our entities
 */
interface ARSOL_PFW_Object_Data_Store_Interface extends WC_Object_Data_Store_Interface {
    
    /**
     * Get available stages for the entity
     *
     * @return array Array of stage_slug => stage_name
     */
    public function get_available_stages();
    
    /**
     * Get count of entities by stage
     *
     * @return array Array of stage_slug => count
     */
    public function get_stage_counts();
} 