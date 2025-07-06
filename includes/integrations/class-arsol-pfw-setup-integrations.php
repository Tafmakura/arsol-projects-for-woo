<?php
/**
 * Integrations Setup Class for Arsol Projects for WooCommerce
 *
 * Initializes all integrations functionality including WooCommerce
 * and third-party service integrations.
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
 * Integrations Setup Class
 *
 * Manages initialization of all integration components.
 */
class Integrations {
    
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
     * Initialize integration classes
     */
    private function init_classes() {
        // Initialize integration classes when they exist
        if (class_exists('\Arsol_PFW\Integrations\Woocommerce')) {
            new \Arsol_PFW\Integrations\Woocommerce();
        }
        
        if (class_exists('\Arsol_PFW\Integrations\Woocommerce_Subscriptions')) {
            new \Arsol_PFW\Integrations\Woocommerce_Subscriptions();
        }
        
        if (class_exists('\Arsol_PFW\Integrations\Woocommerce_Logs')) {
            new \Arsol_PFW\Integrations\Woocommerce_Logs();
        }
        
        if (class_exists('\Arsol_PFW\Integrations\Woocommerce_Biller_Invoice')) {
            new \Arsol_PFW\Integrations\Woocommerce_Biller_Invoice();
        }
    }
    
    /**
     * Initialize integrations functionality
     */
    public function init() {
        // Integrations initialization actions
        do_action('arsol_pfw_integrations_init');
    }
} 