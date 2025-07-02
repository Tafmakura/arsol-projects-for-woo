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
 * the shortcode content is placed inside the <div class="project-content-wrapper">
 * rather than replacing the entire wrapper.
 */
class Frontend_Template_Overrides {

    /**
     * Map of template types to their corresponding advanced settings keys
     */
    private static $template_map = [
        'arsol-pfw-project' => 'arsol_pfw_project_overview',
        'arsol-pfw-proposal' => 'arsol_pfw_proposal_overview',
        'arsol-pfw-request' => 'arsol_pfw_request_overview',
        'arsol-pfw-project-form' => 'arsol_pfw_project_form',
        'arsol-pfw-request-form' => 'arsol_pfw_request_form',
        'arsol-pfw-proposal-form' => 'arsol_pfw_proposal_form',
        'arsol-pfw-projects-list' => 'arsol_pfw_projects_list',
        'arsol-pfw-proposals-list' => 'arsol_pfw_proposals_list',
        'arsol-pfw-requests-list' => 'arsol_pfw_requests_list',
        'arsol-pfw-no-access' => 'arsol_pfw_no_access',
    ];

    /**
    }

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
        $advanced_settings = get_option('arsol_projects_templates_settings', []);
        
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
     * Checks if the given project type supports overrides
     * 
     * @param string $project_type The project type (CPT slug: arsol-pfw-project, arsol-pfw-proposal, arsol-pfw-request)
     * @return bool
     */
    public static function has_project_overview_override($project_type = 'active') {
        
        return self::has_template_override($project_type);
    }

    /**
     * Get the project overview shortcode override for a specific project type
     *
     * @param string $project_type The project type (CPT slug: arsol-pfw-project, arsol-pfw-proposal, arsol-pfw-request)
     * @return string The rendered shortcode or empty string if none
     */
    public static function get_project_overview_override($project_type = 'active') {
        
        return self::get_template_override($project_type);
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
        $advanced_settings = get_option('arsol_projects_templates_settings', []);
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
        $advanced_settings = get_option('arsol_projects_templates_settings', []);
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
        $advanced_settings = get_option('arsol_projects_templates_settings', []);
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
        $advanced_settings = get_option('arsol_projects_templates_settings', []);
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

    /**
     * Get shortcode override with validation
     *
     * @param string $default_shortcode The shortcode to check for override (e.g., '[arsol_pfw_projects_list]')
     * @return string|false The override shortcode if valid, or false if no valid override exists
     */
    public static function get_shortcode_override($default_shortcode) {
        // Extract shortcode name from the default shortcode
        preg_match('/^\[([^\s\]]+)/', $default_shortcode, $matches);
        $shortcode_name = isset($matches[1]) ? $matches[1] : '';
        
        if (empty($shortcode_name)) {
            return false;
        }
        
        // Check if this is a valid plugin shortcode (must start with arsol_pfw_)
        if (strpos($shortcode_name, 'arsol_pfw_') !== 0) {
            return false;
        }
        
        // The setting key is the same as the shortcode name
        $advanced_settings = get_option('arsol_projects_templates_settings', []);
        
        // Check if there's an override in the settings
        if (isset($advanced_settings[$shortcode_name])) {
            $override_shortcode = trim($advanced_settings[$shortcode_name]);
            
            // Validate that the override shortcode is registered in WordPress
            if (!empty($override_shortcode) && self::is_registered_shortcode($override_shortcode)) {
                return $override_shortcode;
            } else {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    preg_match('/^\[([^\s\]]+)/', $override_shortcode, $debug_matches);
                    $override_name = isset($debug_matches[1]) ? $debug_matches[1] : 'unknown';
                    error_log("Arsol Projects: Override shortcode '{$override_name}' is not registered in WordPress");
                }
            }
        }
        
        return false;
    }

    /**
     * Map shortcode names to their corresponding admin setting keys
     *
     * @return array Mapping of shortcode names to setting keys
     */

    /**
     * Check if shortcode is properly formatted and registered in WordPress
     *
     * @param string $shortcode The shortcode to validate
     * @return bool True if shortcode is registered
     */
    private static function is_registered_shortcode($shortcode) {
        // Basic format validation
        if (!preg_match('/^\[[\w\s_-]+.*\]$/', $shortcode)) {
            return false;
        }
        
        // Extract shortcode name
        preg_match('/^\[([^\s\]]+)/', $shortcode, $matches);
        $shortcode_name = isset($matches[1]) ? $matches[1] : '';
        
        if (empty($shortcode_name)) {
            return false;
        }
        
        // Use WordPress native function to check if shortcode exists
        return shortcode_exists($shortcode_name);
    }

    /**
     * Render shortcode with override check
     *
     * @param string $default_shortcode The default shortcode to render
     * @return string The rendered shortcode output
     */
    public static function render_with_override($default_shortcode) {
        $override = self::get_shortcode_override($default_shortcode);
        return do_shortcode($override ?: $default_shortcode);
    }

    /**
     * Get the WooCommerce endpoint for the given project type
     * 
     * @param string $project_type The project type (CPT slug: arsol-pfw-project, arsol-pfw-proposal, arsol-pfw-request)
     * @return string|null
     */

    // ==========================================================================
    // DISPLAY CONTROL METHODS
    // ==========================================================================

    /**
     * Check if content should be displayed based on content display rules
     *
     * @param int $post_id The post ID
     * @param int $current_stage_id The current stage term ID
     * @return bool Whether content should be displayed
     */
    public static function should_show_content($post_id, $current_stage_id) {
        $post_type = get_post_type($post_id);
        $phase_type = self::get_phase_type_from_post_type($post_type);
        
        if (!$phase_type) {
            return true; // Default to show if we can't determine phase type
        }
        
        $settings = get_option('arsol_content_display_settings', array());
        $visibility_key = $phase_type . '_visibility';
        $stages_key = $phase_type . '_stages';
        
        $visibility = isset($settings[$visibility_key]) ? $settings[$visibility_key] : 'hide';
        $selected_stages = isset($settings[$stages_key]) ? $settings[$stages_key] : array();
        
        return self::apply_visibility_rules($visibility, $selected_stages, $current_stage_id);
    }

    /**
     * Check if sidebar should be displayed based on sidebar display rules
     *
     * @param int $post_id The post ID
     * @param int $current_stage_id The current stage term ID
     * @return bool Whether sidebar should be displayed
     */
    public static function should_show_sidebar($post_id, $current_stage_id) {
        $post_type = get_post_type($post_id);
        $phase_type = self::get_phase_type_from_post_type($post_type);
        
        if (!$phase_type) {
            return true; // Default to show if we can't determine phase type
        }
        
        $settings = get_option('arsol_sidebar_display_settings', array());
        $visibility_key = $phase_type . '_visibility';
        $stages_key = $phase_type . '_stages';
        
        $visibility = isset($settings[$visibility_key]) ? $settings[$visibility_key] : 'hide';
        $selected_stages = isset($settings[$stages_key]) ? $settings[$stages_key] : array();
        
        return self::apply_visibility_rules($visibility, $selected_stages, $current_stage_id);
    }

    /**
     * Check if form should be displayed based on form display rules
     *
     * @param int $post_id The post ID
     * @param int $current_stage_id The current stage term ID
     * @param string $form_type The form type (request_form, edit_request_form, project_form, edit_project_form)
     * @return bool Whether form should be displayed
     */
    public static function should_show_form($post_id, $current_stage_id, $form_type) {
        $settings = get_option('arsol_form_display_settings', array());
        $visibility_key = $form_type . '_visibility';
        $stages_key = $form_type . '_stages';
        
        $visibility = isset($settings[$visibility_key]) ? $settings[$visibility_key] : 'hide';
        $selected_stages = isset($settings[$stages_key]) ? $settings[$stages_key] : array();
        
        return self::apply_visibility_rules($visibility, $selected_stages, $current_stage_id);
    }

    /**
     * Get the display mode for a post (form, content, or empty)
     *
     * @param int $post_id The post ID
     * @param int $current_stage_id The current stage term ID
     * @param string $form_type The form type to check
     * @return string 'form', 'content', or 'empty'
     */
    public static function get_display_mode($post_id, $current_stage_id, $form_type) {
        // Form rules override everything
        if (self::should_show_form($post_id, $current_stage_id, $form_type)) {
            return 'form';
        }
        
        // Check content rules
        if (self::should_show_content($post_id, $current_stage_id)) {
            return 'content';
        }
        
        // Nothing to show
        return 'empty';
    }

    /**
     * Apply visibility rules logic
     *
     * @param string $visibility 'hide' or 'show'
     * @param array $selected_stages Array of selected stage term IDs
     * @param int $current_stage_id Current stage term ID
     * @return bool Whether content should be displayed
     */
    private static function apply_visibility_rules($visibility, $selected_stages, $current_stage_id) {
        // Convert selected stages to integers for comparison
        $selected_stages = array_map('intval', $selected_stages);
        $current_stage_id = intval($current_stage_id);
        
        if ($visibility === 'hide') {
            if (empty($selected_stages)) {
                return true; // Hide rule with no stages = show on all stages
            } else {
                return !in_array($current_stage_id, $selected_stages); // Hide on selected stages
            }
        } else { // 'show'
            if (empty($selected_stages)) {
                return false; // Show rule with no stages = show on no stages (hidden everywhere)
            } else {
                return in_array($current_stage_id, $selected_stages); // Show only on selected stages
            }
        }
    }

    /**
     * Get phase type from post type
     *
     * @param string $post_type WordPress post type
     * @return string|false Phase type ('request', 'proposal', 'project') or false if not found
     */
    private static function get_phase_type_from_post_type($post_type) {
        $map = array(
            'arsol-pfw-request' => 'request',
            'arsol-pfw-proposal' => 'proposal',
            'arsol-pfw-project' => 'project'
        );
        
        return isset($map[$post_type]) ? $map[$post_type] : false;
    }

    /**
     * Get current stage term ID from post
     *
     * @param int $post_id The post ID
     * @return int Stage term ID or 0 if not found
     */
    public static function get_current_stage_id($post_id) {
        $post_type = get_post_type($post_id);
        
        // Map post types to their stage taxonomies
        $taxonomy_map = array(
            'arsol-pfw-project' => 'arsol-pfw-project-stage',
            'arsol-pfw-proposal' => 'arsol-pfw-proposal-stage',
            'arsol-pfw-request' => 'arsol-pfw-request-stage'
        );
        
        if (!isset($taxonomy_map[$post_type])) {
            return 0;
        }
        
        $taxonomy = $taxonomy_map[$post_type];
        $stage_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'ids'));
        
        if (is_wp_error($stage_terms) || empty($stage_terms)) {
            return 0;
        }
        
        return intval($stage_terms[0]);
    }
} 