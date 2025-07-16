<?php
/**
 * Admin Settings Integrations Class
 *
 * @package Arsol_Projects_For_Woo\Admin
 * @version 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin\Settings;

if (!defined('ABSPATH')) {
    exit;
}

class Integrations {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Add hooks for future integrations settings
        add_action('admin_init', array($this, 'init_integrations'));
    }
    
    /**
     * Initialize integrations settings
     */
    public function init_integrations() {
        // Future implementation for integrations settings
        // This ensures the class is properly instantiated
    }
    
    /**
     * Get available integrations
     */
    public static function get_available_integrations() {
        return array(
            'woocommerce' => array(
                'name' => 'WooCommerce',
                'active' => class_exists('WooCommerce'),
                'version' => defined('WC_VERSION') ? WC_VERSION : 'Unknown'
            ),
            'woocommerce_subscriptions' => array(
                'name' => 'WooCommerce Subscriptions',
                'active' => class_exists('WC_Subscriptions'),
                'version' => defined('WCS_VERSION') ? WCS_VERSION : 'Unknown'
            )
        );
    }
}
