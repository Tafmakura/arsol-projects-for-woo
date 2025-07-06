<?php
/**
 * Taxonomies Setup Class for Arsol Projects for WooCommerce
 *
 * Initializes all taxonomies functionality including project stages,
 * proposal stages, and request stages.
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
 * Taxonomies Setup Class
 *
 * Manages initialization of all taxonomy components.
 */
class Taxonomies {
    
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
     * Initialize taxonomy classes
     */
    private function init_classes() {
        // Include existing taxonomy setup class
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/taxonomies/class-taxonomies-setup.php';
        
        // Initialize existing taxonomy classes
        if (class_exists('\Arsol_Projects_For_Woo\Taxonomies\Taxonomies_Setup')) {
            new \Arsol_Projects_For_Woo\Taxonomies\Taxonomies_Setup();
        }
    }
    
    /**
     * Initialize taxonomies functionality
     */
    public function init() {
        // Taxonomies initialization actions
        do_action('arsol_pfw_taxonomies_init');
    }
} 