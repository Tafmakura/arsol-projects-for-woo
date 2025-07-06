<?php
/**
 * Frontend Setup Class for Arsol Projects for WooCommerce
 *
 * Initializes all frontend functionality including comments, template overrides,
 * WooCommerce endpoints, and other frontend features.
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
 * Frontend Setup Class
 *
 * Manages initialization of all frontend components.
 */
class Frontend {
    
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
        add_action('arsol_pfw_init', array($this, 'init'), 10);
        add_action('wp', array($this, 'wp'));
    }
    
    /**
     * Initialize frontend classes
     */
    private function init_classes() {
        // Only initialize frontend classes in frontend context
        if (is_admin()) {
            return;
        }
        
        // Include frontend classes
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/frontend/class-arsol-pfw-comments.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/frontend/class-arsol-pfw-template-overrides.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/frontend/class-arsol-pfw-template-sidebar-buttons.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/frontend/class-arsol-pfw-template-sidebar-meta.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/frontend/class-arsol-pfw-woocommerce-checkout.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/frontend/class-arsol-pfw-woocommerce-endpoints.php';
        
        // Initialize frontend classes that need to be loaded immediately
        if (class_exists('\Arsol_PFW\Frontend\Comments')) {
            new \Arsol_PFW\Frontend\Comments();
        }
        
        if (class_exists('\Arsol_PFW\Frontend\Template_Overrides')) {
            new \Arsol_PFW\Frontend\Template_Overrides();
        }
        
        if (class_exists('\Arsol_PFW\Frontend\Template_Sidebar_Buttons')) {
            new \Arsol_PFW\Frontend\Template_Sidebar_Buttons();
        }
        
        if (class_exists('\Arsol_PFW\Frontend\Template_Sidebar_Meta')) {
            new \Arsol_PFW\Frontend\Template_Sidebar_Meta();
        }
        
        if (class_exists('\Arsol_PFW\Frontend\Woocommerce_Checkout')) {
            new \Arsol_PFW\Frontend\Woocommerce_Checkout();
        }
        
        if (class_exists('\Arsol_PFW\Frontend\Woocommerce_Endpoints')) {
            new \Arsol_PFW\Frontend\Woocommerce_Endpoints();
        }
    }
    
    /**
     * Initialize frontend functionality
     */
    public function init() {
        // Frontend initialization actions
        do_action('arsol_pfw_frontend_init');
    }
    
    /**
     * WordPress main query initialized hook
     */
    public function wp() {
        // Frontend-specific initialization after main query
        do_action('arsol_pfw_frontend_wp');
    }
} 