<?php
/**
 * Data Stores Setup Class for Arsol Projects for WooCommerce
 *
 * Initializes all data store functionality for CRUD operations
 * following WooCommerce patterns.
 *
 * @package Arsol_PFW\Setup
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Arsol_PFW\Setup;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Data Stores Setup Class
 *
 * Manages initialization of all data store components.
 */
class Data_Stores {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->init_classes();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('arsol_pfw_init', array($this, 'init'), 5);
    }
    
    /**
     * Initialize data store classes
     */
    private function init_classes() {
        // Include data store classes
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/data-stores/class-arsol-pfw-project-data-store.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/data-stores/class-arsol-pfw-proposal-data-store.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/data-stores/class-arsol-pfw-request-data-store.php';
        
        // Initialize data store classes when they exist
        if (class_exists('\Arsol_PFW\Data_Stores\Project_Data_Store')) {
            new \Arsol_PFW\Data_Stores\Project_Data_Store();
        }
        
        if (class_exists('\Arsol_PFW\Data_Stores\Proposal_Data_Store')) {
            new \Arsol_PFW\Data_Stores\Proposal_Data_Store();
        }
        
        if (class_exists('\Arsol_PFW\Data_Stores\Request_Data_Store')) {
            new \Arsol_PFW\Data_Stores\Request_Data_Store();
        }
    }
    
    /**
     * Initialize data stores functionality
     */
    public function init() {
        // Data stores initialization actions
        do_action('arsol_pfw_data_stores_init');
    }
} 