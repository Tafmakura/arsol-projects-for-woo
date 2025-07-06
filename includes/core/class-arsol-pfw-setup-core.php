<?php
/**
 * Core Setup Class for Arsol Projects for WooCommerce
 *
 * Initializes all core system functionality including assets, capabilities,
 * shortcodes, and other fundamental plugin features.
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
 * Core Setup Class
 *
 * Manages initialization of all core system components.
 */
class Core {
    
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
     * Initialize core classes
     */
    private function init_classes() {
        // Include core classes
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/core/class-arsol-pfw-assets.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/core/class-arsol-pfw-capabilities.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/core/class-arsol-pfw-shortcodes.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/core/class-arsol-pfw-stages-handler.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/core/class-arsol-pfw-project-phases.php';
        
        // Initialize core classes that need to be loaded immediately
        if (class_exists('\Arsol_PFW\Core\Assets')) {
            new \Arsol_PFW\Core\Assets();
        }
        
        if (class_exists('\Arsol_PFW\Core\Capabilities')) {
            new \Arsol_PFW\Core\Capabilities();
        }
        
        if (class_exists('\Arsol_PFW\Core\Shortcodes')) {
            new \Arsol_PFW\Core\Shortcodes();
        }
        
        if (class_exists('\Arsol_PFW\Core\Stages_Handler')) {
            new \Arsol_PFW\Core\Stages_Handler();
        }
        
        if (class_exists('\Arsol_PFW\Core\Project_Phases')) {
            new \Arsol_PFW\Core\Project_Phases();
        }
    }
    
    /**
     * Initialize core functionality
     */
    public function init() {
        // Core initialization actions
        do_action('arsol_pfw_core_init');
    }
} 