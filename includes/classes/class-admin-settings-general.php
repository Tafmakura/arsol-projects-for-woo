<?php
/**
 * Admin Settings General Class - Compatibility Layer
 *
 * This is a compatibility layer that provides methods for the old namespace
 * references while the new settings system is being implemented.
 *
 * @package Arsol_Projects_For_Woo\Admin
 * @since   1.0.0
 * @deprecated Use Arsol_PFW\Admin\Settings_General instead
 */

namespace Arsol_Projects_For_Woo\Admin;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Settings General Class - Compatibility Layer
 *
 * Provides missing methods for old namespace references.
 */
class Settings_General {
    
    /**
     * Check if comments are enabled for a specific post type
     *
     * @param string $post_type The post type to check
     * @return bool Whether comments are enabled for the post type
     */
    public static function is_comments_enabled_for_post_type($post_type) {
        // Get the general settings
        $general_settings = get_option('arsol_pfw_general_settings', array());
        
        // Default to true for comments on all post types if no setting exists
        $comments_setting_key = $post_type . '_comments_enabled';
        
        // Check if the specific setting exists, default to true
        if (isset($general_settings[$comments_setting_key])) {
            return !empty($general_settings[$comments_setting_key]);
        }
        
        // Default behavior: enable comments for all project-related post types
        $project_post_types = array(
            'arsol-pfw-project',
            'arsol-pfw-proposal', 
            'arsol-pfw-request'
        );
        
        return in_array($post_type, $project_post_types);
    }
    
    /**
     * Get a general setting value
     *
     * @param string $key Setting key
     * @param mixed $default Default value if setting doesn't exist
     * @return mixed Setting value
     */
    public static function get_setting($key, $default = null) {
        $general_settings = get_option('arsol_pfw_general_settings', array());
        return isset($general_settings[$key]) ? $general_settings[$key] : $default;
    }
    
    /**
     * Set a general setting value
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool Whether the setting was saved successfully
     */
    public static function set_setting($key, $value) {
        $general_settings = get_option('arsol_pfw_general_settings', array());
        $general_settings[$key] = $value;
        return update_option('arsol_pfw_general_settings', $general_settings);
    }
    
    /**
     * Get all general settings
     *
     * @return array All general settings
     */
    public static function get_all_settings() {
        return get_option('arsol_pfw_general_settings', array());
    }
} 