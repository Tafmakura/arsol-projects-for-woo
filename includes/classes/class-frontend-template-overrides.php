<?php
/**
 * Frontend Template Overrides Class
 *
 * Handles template overrides using shortcodes from advanced settings.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class responsible for handling template overrides
 * 
 * Template overrides are placed INSIDE existing wrapper structures to preserve
 * page layout and styling. For example, when overriding 'project_overview',
 * the shortcode content is placed inside the <div class="project-overview-wrapper">
 * rather than replacing the entire wrapper.
 */
class Frontend_Template_Overrides {

    /**
     * Map of template types to their corresponding advanced settings keys
     */
    private static $template_map = [
        'project_overview_active' => 'project_overview_active_shortcode',
        'project_overview_proposal' => 'project_overview_proposal_shortcode',
        'project_overview_request' => 'project_overview_request_shortcode',
        'create_project_form' => 'create_project_form_shortcode',
        'request_project_form' => 'request_project_form_shortcode',
        'projects_listing' => 'projects_listing_shortcode',
        'project_proposal_listings' => 'project_proposal_listings_shortcode',
        'project_requests_listings' => 'project_requests_listings_shortcode',
        'access_denied' => 'access_denied_shortcode',
    ];

    /**
     * Get template map with conditional subscription support
     *
     * @return array Template mapping array
     */
    public static function get_template_map() {
        $map = self::$template_map;
        
        // Only include subscription-related templates if WooCommerce Subscriptions is active
        if (class_exists('WC_Subscriptions')) {
            $map['project_subscriptions'] = 'project_subscriptions_shortcode';
        }
        
        return $map;
    }

    /**
     * Render a template with potential shortcode override
     * 
     * NOTE: This method is designed for templates that don't have their own wrappers.
     * For templates with wrappers, use has_template_override() and get_template_override()
     * to place the override content INSIDE the existing wrapper structure.
     *
     * @param string $template_type The type of template to render
     * @param string $default_template_path The path to the default template file
     * @param array $template_args Optional arguments to pass to the template
     * @return void
     */
    public static function render_template($template_type, $default_template_path, $template_args = []) {
        // Get the advanced settings
        $advanced_settings = get_option('arsol_projects_advanced_settings', []);
        
        // Check if there's a shortcode override for this template type
        $template_map = self::get_template_map();
        $setting_key = isset($template_map[$template_type]) ? $template_map[$template_type] : '';
        $shortcode_override = '';
        
        if (!empty($setting_key) && isset($advanced_settings[$setting_key])) {
            $shortcode_override = trim($advanced_settings[$setting_key]);
        }

        // If there's a valid shortcode override, use it
        if (!empty($shortcode_override) && self::is_valid_shortcode($shortcode_override)) {
            echo do_shortcode($shortcode_override);
        } else {
            // Use the default template
            self::load_default_template($default_template_path, $template_args);
        }
    }

    /**
     * Check if a specific project overview type should be overridden
     *
     * @param string $project_type The project type ('active', 'proposal', 'request')
     * @return bool True if override exists, false otherwise
     */
    public static function has_project_overview_override($project_type = 'active') {
        $template_type = 'project_overview_' . $project_type;
        return self::has_template_override($template_type);
    }

    /**
     * Get the project overview shortcode override for a specific project type
     *
     * @param string $project_type The project type ('active', 'proposal', 'request')
     * @return string The rendered shortcode or empty string if none
     */
    public static function get_project_overview_override($project_type = 'active') {
        $template_type = 'project_overview_' . $project_type;
        return self::get_template_override($template_type);
    }

    /**
     * Load the default template file
     *
     * @param string $template_path The path to the template file
     * @param array $template_args Optional arguments to pass to the template
     * @return void
     */
    private static function load_default_template($template_path, $template_args = []) {
        if (!empty($template_args)) {
            extract($template_args, EXTR_SKIP);
        }

        if (file_exists($template_path)) {
            include $template_path;
        } else {
            // Log error or display fallback content
            error_log("Arsol Projects: Template file not found: " . $template_path);
            echo '<p>' . esc_html__('Template not found.', 'arsol-pfw') . '</p>';
        }
    }

    /**
     * Validate if a string is a properly formatted shortcode
     *
     * @param string $shortcode The shortcode to validate
     * @return bool True if valid shortcode format, false otherwise
     */
    private static function is_valid_shortcode($shortcode) {
        // Check if the shortcode is in the proper format [shortcode_name] or [shortcode_name attr="value"]
        return preg_match('/^\[[\w\s_-]+.*\]$/', $shortcode);
    }

    /**
     * Get available template override types
     *
     * @return array Array of template types
     */
    public static function get_template_types() {
        return array_keys(self::get_template_map());
    }

    /**
     * Check if a specific template type has an override
     *
     * @param string $template_type The template type to check
     * @return bool True if override exists, false otherwise
     */
    public static function has_template_override($template_type) {
        $advanced_settings = get_option('arsol_projects_advanced_settings', []);
        $template_map = self::get_template_map();
        $setting_key = isset($template_map[$template_type]) ? $template_map[$template_type] : '';
        
        if (empty($setting_key)) {
            return false;
        }
        
        $shortcode = isset($advanced_settings[$setting_key]) ? trim($advanced_settings[$setting_key]) : '';
        
        return !empty($shortcode) && self::is_valid_shortcode($shortcode);
    }

    /**
     * Get the shortcode override for a specific template type
     *
     * @param string $template_type The template type
     * @return string The rendered shortcode or empty string if none
     */
    public static function get_template_override($template_type) {
        $advanced_settings = get_option('arsol_projects_advanced_settings', []);
        $template_map = self::get_template_map();
        $setting_key = isset($template_map[$template_type]) ? $template_map[$template_type] : '';
        
        if (empty($setting_key)) {
            return '';
        }
        
        $shortcode = isset($advanced_settings[$setting_key]) ? trim($advanced_settings[$setting_key]) : '';
        
        if (!empty($shortcode) && self::is_valid_shortcode($shortcode)) {
            return do_shortcode($shortcode);
        }
        
        return '';
    }

    /**
     * Get all active template overrides for debugging
     *
     * @return array Array of active overrides with template type as key and shortcode as value
     */
    public static function get_active_overrides() {
        $advanced_settings = get_option('arsol_projects_advanced_settings', []);
        $active_overrides = [];
        $template_map = self::get_template_map();
        
        foreach ($template_map as $template_type => $setting_key) {
            if (isset($advanced_settings[$setting_key])) {
                $shortcode = trim($advanced_settings[$setting_key]);
                if (!empty($shortcode) && self::is_valid_shortcode($shortcode)) {
                    $active_overrides[$template_type] = $shortcode;
                }
            }
        }
        
        return $active_overrides;
    }

    /**
     * Check if template overrides are working correctly (for debugging)
     *
     * @return array Debug information about template overrides
     */
    public static function debug_overrides() {
        $advanced_settings = get_option('arsol_projects_advanced_settings', []);
        $template_map = self::get_template_map();
        $debug_info = [
            'settings_exist' => !empty($advanced_settings),
            'template_map' => $template_map,
            'raw_settings' => $advanced_settings,
            'active_overrides' => self::get_active_overrides(),
            'invalid_shortcodes' => [],
            'woocommerce_subscriptions_active' => class_exists('WC_Subscriptions')
        ];

        // Check for invalid shortcodes
        foreach ($template_map as $template_type => $setting_key) {
            if (isset($advanced_settings[$setting_key])) {
                $shortcode = trim($advanced_settings[$setting_key]);
                if (!empty($shortcode) && !self::is_valid_shortcode($shortcode)) {
                    $debug_info['invalid_shortcodes'][$template_type] = $shortcode;
                }
            }
        }
        
        return $debug_info;
    }
} 