<?php
/**
 * Data Store Setup
 *
 * Registers custom data stores with WooCommerce
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Classes
 */

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Data Store Setup
 * 
 * Registers custom data stores with WooCommerce data store system
 */
class ARSOL_PFW_Data_Stores {
    
    /**
     * Initialize data stores
     */
    public static function init() {
        add_filter('woocommerce_data_stores', array(__CLASS__, 'register_data_stores'));
    }
    
    /**
     * Register data stores with WooCommerce
     *
     * @param array $stores Existing data stores
     * @return array Modified data stores
     */
    public static function register_data_stores($stores) {
        $stores['arsol-pfw-request'] = 'Arsol_Projects_For_Woo\ARSOL_PFW_Request_Data_Store';
        // TODO: Add other data stores in future phases
        // $stores['arsol-pfw-proposal'] = 'Arsol_Projects_For_Woo\ARSOL_PFW_Proposal_Data_Store';
        // $stores['arsol-pfw-project'] = 'Arsol_Projects_For_Woo\ARSOL_PFW_Project_Data_Store';
        
        return $stores;
    }
} 