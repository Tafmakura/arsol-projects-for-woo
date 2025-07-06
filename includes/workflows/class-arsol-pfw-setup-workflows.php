<?php
/**
 * Workflows Setup Class for Arsol Projects for WooCommerce
 *
 * Initializes all workflow functionality including default workflows
 * and custom workflow handlers.
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
 * Workflows Setup Class
 *
 * Manages initialization of all workflow components.
 */
class Workflows {
    
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
    }
    
    /**
     * Initialize workflow classes
     */
    private function init_classes() {
        // Initialize workflow classes when they exist
        if (class_exists('\Arsol_PFW\Workflows\Handler')) {
            new \Arsol_PFW\Workflows\Handler();
        }
    }
    
    /**
     * Initialize workflows functionality
     */
    public function init() {
        // Workflows initialization actions
        do_action('arsol_pfw_workflows_init');
    }
} 