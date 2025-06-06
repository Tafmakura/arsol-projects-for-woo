<?php
/**
 * Template Overrides Class
 *
 * Handles template override logic.
 *
 * @package Arsol_Projects_For_Woo\Admin
 * @version 1.0.0
 */

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

class Template_Overrides {

    /**
     * Get the override shortcode for a given template
     *
     * @param string $template_name The name of the template to override.
     * @return string|false The shortcode to use, or false if not set.
     */
    public static function get_override_shortcode($template_name) {
        $settings = get_option('arsol_projects_advanced_settings', array());
        $shortcode_field_name = $template_name . '_shortcode';

        if (isset($settings[$shortcode_field_name]) && !empty($settings[$shortcode_field_name])) {
            // Validate that the value is in a shortcode format
            if (preg_match('/^\\[[a-zA-Z0-9\\s_-]+\\]$/', $settings[$shortcode_field_name])) {
                return $settings[$shortcode_field_name];
            }
        }

        return false;
    }

    /**
     * Render a template, checking for an override shortcode first.
     *
     * @param string $template_name The name of the template (e.g., 'project_overview').
     * @param string $template_file The full path to the default template file.
     * @param array $args The arguments to pass to the template file.
     * @return void
     */
    public static function render_template($template_name, $template_file, $args = array()) {
        $override_shortcode = self::get_override_shortcode($template_name);

        if ($override_shortcode) {
            echo do_shortcode($override_shortcode);
        } else {
            if (!empty($args)) {
                extract($args);
            }
            include $template_file;
        }
    }
} 