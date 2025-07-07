<?php

namespace Arsol_Projects_For_Woo\Frontend;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend Setup Class
 * 
 * Manages all frontend-related classes for the plugin
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
        // Main includes setup handles all file includes and instantiations
        // This setup now only handles frontend-specific coordination
    }

    /**
     * Check if we're in frontend area
     */
    public function is_frontend_area() {
        return !\is_admin() || \wp_doing_ajax();
    }

    /**
     * Check if WooCommerce is active
     */
    public function is_woocommerce_active() {
        return \class_exists('WooCommerce');
    }

    /**
     * Get frontend class instances
     */
    public function get_frontend_classes() {
        return [
            'comments' => 'Frontend_Comments',
            'template_overrides' => 'Frontend_Template_Overrides',
            'template_sidebar_buttons' => 'Frontend_Template_Sidebar_Buttons',
            'template_sidebar_meta' => 'Frontend_Template_Sidebar_Meta',
            'woocommerce_checkout' => 'Frontend_Woocommerce_Checkout',
            'woocommerce_endpoints' => 'Frontend_Woocommerce_Endpoints',
        ];
    }

    /**
     * Get frontend status
     */
    public function get_frontend_status() {
        return [
            'frontend_area' => $this->is_frontend_area(),
            'woocommerce_active' => $this->is_woocommerce_active(),
            'classes_loaded' => \count($this->get_frontend_classes()),
        ];
    }

    /**
     * Check if current page is a project-related page
     */
    public function is_project_page() {
        global $post;
        
        if (!$post) {
            return false;
        }
        
        return \in_array($post->post_type, ['project', 'project-request', 'project-proposal']);
    }

    /**
     * Get current project context
     */
    public function get_project_context() {
        global $post;
        
        if (!$this->is_project_page()) {
            return null;
        }
        
        return [
            'post_type' => $post->post_type,
            'post_id' => $post->ID,
            'post_status' => $post->post_status,
        ];
    }
} 