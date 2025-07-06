<?php
/**
 * Admin Setup Defaults Class - Compatibility Layer
 *
 * This is a compatibility layer that provides methods for the old namespace
 * references while the new setup system is being implemented.
 *
 * @package Arsol_Projects_For_Woo\Admin
 * @since   1.0.0
 * @deprecated Use Arsol_PFW\Admin\Setup_Defaults instead
 */

namespace Arsol_Projects_For_Woo\Admin;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Setup Defaults Class - Compatibility Layer
 *
 * Provides missing methods for old namespace references.
 */
class Setup_Defaults {
    
    /**
     * Get effective customer notice for a post
     *
     * @param int $post_id Post ID
     * @param string $notice_type Notice type (project, proposal, request)
     * @return string Customer notice content
     */
    public static function get_effective_customer_notice($post_id, $notice_type) {
        // Get custom notice for this specific post
        $custom_notice = get_post_meta($post_id, '_arsol_pfw_customer_notice', true);
        
        if (!empty($custom_notice)) {
            return wp_kses_post($custom_notice);
        }
        
        // Get default notice from settings
        $default_notices = get_option('arsol_pfw_default_notices', array());
        $notice_key = $notice_type . '_customer_notice';
        
        if (!empty($default_notices[$notice_key])) {
            return wp_kses_post($default_notices[$notice_key]);
        }
        
        // Return fallback notice based on type
        return self::get_fallback_customer_notice($notice_type);
    }
    
    /**
     * Get fallback customer notice
     *
     * @param string $notice_type Notice type
     * @return string Fallback notice content
     */
    private static function get_fallback_customer_notice($notice_type) {
        $fallback_notices = array(
            'project' => __('This is your project overview. Here you can view project details, track progress, and communicate with our team.', 'arsol-pfw'),
            'proposal' => __('This is your project proposal. Please review the details and let us know if you have any questions or would like to proceed.', 'arsol-pfw'),
            'request' => __('This is your project request. We will review it and get back to you with a proposal or questions.', 'arsol-pfw'),
        );
        
        return isset($fallback_notices[$notice_type]) ? $fallback_notices[$notice_type] : '';
    }
    
    /**
     * Get default content for a post type
     *
     * @param string $post_type Post type
     * @param string $content_type Content type (customer_notice, admin_notes, etc.)
     * @return string Default content
     */
    public static function get_default_content($post_type, $content_type) {
        $defaults = get_option('arsol_pfw_content_defaults', array());
        $key = $post_type . '_' . $content_type;
        
        if (!empty($defaults[$key])) {
            return wp_kses_post($defaults[$key]);
        }
        
        // Return empty string if no default is set
        return '';
    }
    
    /**
     * Set default content for a post type
     *
     * @param string $post_type Post type
     * @param string $content_type Content type
     * @param string $content Content
     * @return bool Whether the setting was saved successfully
     */
    public static function set_default_content($post_type, $content_type, $content) {
        $defaults = get_option('arsol_pfw_content_defaults', array());
        $key = $post_type . '_' . $content_type;
        $defaults[$key] = wp_kses_post($content);
        
        return update_option('arsol_pfw_content_defaults', $defaults);
    }
} 