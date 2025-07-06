<?php

namespace Arsol_Projects_For_Woo\Integrations;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Integrations Setup Class
 * 
 * Manages all third-party integrations for the plugin
 */
class Setup {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Get the singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->require_integration_files();
        $this->initialize_integrations();
    }

    /**
     * Require integration files
     */
    private function require_integration_files() {
        // WooCommerce Core Integration
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/class-woocommerce.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/class-woocommerce-logs.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/class-woocommerce-biller-invoice.php';
        
        // WooCommerce Subscriptions Integration
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce-subscriptions/class-woocommerce-subscriptions.php';
    }

    /**
     * Initialize all integrations
     */
    private function initialize_integrations() {
        // Check if WooCommerce is active before initializing integrations
        if (!class_exists('WooCommerce')) {
            return;
        }

        // Initialize WooCommerce Core Integration
        new \Arsol_Projects_For_Woo\Woocommerce();
        new \Arsol_Projects_For_Woo\Woocommerce_Biller();
        
        // Initialize WooCommerce Subscriptions Integration
        new \Arsol_Projects_For_Woo\Woocommerce_Subscriptions();
    }

    /**
     * Check if WooCommerce is active
     */
    public function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }

    /**
     * Check if WooCommerce Subscriptions is active
     */
    public function is_woocommerce_subscriptions_active() {
        return class_exists('WC_Subscriptions');
    }

    /**
     * Get active integrations
     */
    public function get_active_integrations() {
        $integrations = [];
        
        if ($this->is_woocommerce_active()) {
            $integrations[] = 'woocommerce';
        }
        
        if ($this->is_woocommerce_subscriptions_active()) {
            $integrations[] = 'woocommerce-subscriptions';
        }
        
        return $integrations;
    }

    /**
     * Get integration status
     */
    public function get_integration_status() {
        return [
            'woocommerce' => [
                'name' => 'WooCommerce',
                'active' => $this->is_woocommerce_active(),
                'required' => true,
            ],
            'woocommerce-subscriptions' => [
                'name' => 'WooCommerce Subscriptions',
                'active' => $this->is_woocommerce_subscriptions_active(),
                'required' => false,
            ],
        ];
    }
} 