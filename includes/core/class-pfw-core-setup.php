<?php

namespace Arsol_Projects_For_Woo\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Core Setup Class
 * 
 * Handles only core plugin functionality (assets, shortcodes, basic hooks)
 */
class Setup {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->require_core_files();
        $this->instantiate_core_classes();
        $this->setup_core_hooks();
    }

    /**
     * Setup core hooks
     */
    private function setup_core_hooks() {
        // Schedule automatic conversion cleanup
        add_action('wp', array($this, 'schedule_conversion_cleanup'));
        add_action('arsol_cleanup_stuck_conversions', array($this, 'cleanup_stuck_conversions'));
    }

    /**
     * Schedule conversion cleanup cron job
     */
    public function schedule_conversion_cleanup() {
        if (!wp_next_scheduled('arsol_cleanup_stuck_conversions')) {
            wp_schedule_event(time(), 'hourly', 'arsol_cleanup_stuck_conversions');
        }
    }

    /**
     * Cleanup stuck conversions via cron
     */
    public function cleanup_stuck_conversions() {
        $cleaned = \Arsol_Projects_For_Woo\Workflows\Standard::cleanup_stuck_workflows(30);
        if ($cleaned > 0) {
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                "Automatic cleanup: removed {$cleaned} stuck conversions");
        }
    }

    /**
     * Include only core files (assets, shortcodes)
     */
    private function require_core_files() {
        // Core Classes Only
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/class-shortcodes.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/class-assets.php';
    }

    /**
     * Instantiate only core classes
     */
    private function instantiate_core_classes() {
        // Initialize core classes only
        new \Arsol_Projects_For_Woo\Shortcodes();
        new \Arsol_Projects_For_Woo\Assets();
    }

    /**
     * Get core status
     */
    public function get_core_status() {
        return [
            'shortcodes' => class_exists('Arsol_Projects_For_Woo\Shortcodes'),
            'assets' => class_exists('Arsol_Projects_For_Woo\Assets'),
            'cleanup_scheduled' => wp_next_scheduled('arsol_cleanup_stuck_conversions') !== false,
        ];
    }
}
