<?php
/**
 * Custom Post Types Setup Class for Arsol Projects for WooCommerce
 *
 * Initializes all custom post types functionality including projects,
 * proposals, and requests.
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
 * Custom Post Types Setup Class
 *
 * Manages initialization of all custom post type components.
 */
class Custom_Post_Types {
    
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
     * Initialize custom post type classes
     */
    private function init_classes() {
        // Include custom post type setup classes
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/custom-post-types/project/class-arsol-pfw-project-cpt-setup.php';
        // Note: Proposal and Request setup classes still use old namespaces, 
        // will be updated in future refactoring phase
        
        // Initialize setup classes for each custom post type
        if (class_exists('\Arsol_PFW\Custom_Post_Types\Project\Setup')) {
            new \Arsol_PFW\Custom_Post_Types\Project\Setup();
        }
        
        // TODO: Create proper setup classes for Proposal and Request
        // These still use old namespace structure and need to be refactored
    }
    
    /**
     * Initialize custom post types functionality
     */
    public function init() {
        // Custom post types initialization actions
        do_action('arsol_pfw_custom_post_types_init');
    }
} 