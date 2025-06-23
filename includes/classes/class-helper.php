<?php

namespace Arsol_Projects_For_Woo\Classes;

/**
 * Helper class for Arsol Projects for WooCommerce
 * Contains reusable methods used across different CPTs and admin areas
 */

if (!defined('ABSPATH')) {
    exit;
}

class Helper {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // AJAX handler for project lead search
        add_action('wp_ajax_arsol_json_search_project_leads', array($this, 'json_search_project_leads'));
    }
    
    /**
     * AJAX handler for project lead search
     * Returns JSON formatted project leads for Select2 AJAX
     */
    public function json_search_project_leads() {
        check_ajax_referer('search-project-leads', 'security');
        
        $term = isset($_GET['term']) ? sanitize_text_field($_GET['term']) : '';
        $limit = isset($_GET['limit']) ? absint($_GET['limit']) : 20;
        
        $results = array();
        
        if (strlen($term) < 1) {
            wp_die();
        }
        
        // Get roles from Project Manager Roles setting
        $settings = get_option('arsol_projects_settings', array());
        $manage_roles = isset($settings['manage_roles']) ? $settings['manage_roles'] : array('administrator');
        $create_roles = isset($settings['create_roles']) ? $settings['create_roles'] : array('administrator');
        
        // Combine both role types for project leads
        $allowed_roles = array_unique(array_merge($manage_roles, $create_roles));
        
        // Search for users with the configured roles
        $user_args = array(
            'search' => '*' . $term . '*',
            'search_columns' => array('display_name', 'user_login', 'user_email', 'user_nicename'),
            'number' => $limit,
            'fields' => array('ID', 'display_name', 'user_email', 'first_name', 'last_name'),
            'role__in' => $allowed_roles
        );
        
        $users = get_users($user_args);
        
        foreach ($users as $user) {
            $display_name = '';
            if (!empty($user->display_name)) {
                $display_name = $user->display_name;
            } elseif (!empty($user->first_name) || !empty($user->last_name)) {
                $display_name = trim($user->first_name . ' ' . $user->last_name);
            } else {
                $display_name = $user->user_email;
            }
            
            $results[$user->ID] = $display_name;
        }
        
        wp_send_json($results);
    }
    
    /**
     * Render a project lead search select field
     * AJAX-enabled search field for project leads
     *
     * @param array $args {
     *     Array of arguments for the select field
     *     @type string $name         Field name attribute
     *     @type string $id           Field id attribute
     *     @type string $placeholder  Placeholder text
     *     @type int    $selected     Currently selected user ID
     *     @type string $class        Additional CSS classes
     * }
     * @return void
     */
    public static function render_project_lead_search_field($args = array()) {
        $defaults = array(
            'name' => 'project_lead',
            'id' => 'project_lead',
            'placeholder' => __('Search for project lead...', 'arsol-pfw'),
            'selected' => '',
            'class' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $classes = 'arsol-project-lead-search';
        if (!empty($args['class'])) {
            $classes .= ' ' . esc_attr($args['class']);
        }
        
        echo '<select name="' . esc_attr($args['name']) . '" id="' . esc_attr($args['id']) . '" class="' . esc_attr($classes) . '" data-placeholder="' . esc_attr($args['placeholder']) . '" data-action="arsol_json_search_project_leads" data-security="' . wp_create_nonce('search-project-leads') . '">';
        echo '<option value="">' . esc_html($args['placeholder']) . '</option>';
        
        // If there's a selected value, add it as an option
        if (!empty($args['selected'])) {
            $selected_user = get_userdata($args['selected']);
            if ($selected_user) {
                $display_name = self::format_project_lead_display($args['selected']);
                $display_name = wp_strip_all_tags($display_name);
                echo '<option value="' . esc_attr($args['selected']) . '" selected="selected">' . esc_html($display_name) . '</option>';
            }
        }
        
        echo '</select>';
    }
    
    /**
     * Get users who can manage projects
     * Uses roles from Project Manager Roles setting
     *
     * @return array Array of user IDs who can manage projects
     */
    public static function get_project_lead_user_ids() {
        // Get roles from Project Manager Roles setting
        $settings = get_option('arsol_projects_settings', array());
        $manage_roles = isset($settings['manage_roles']) ? $settings['manage_roles'] : array('administrator');
        $create_roles = isset($settings['create_roles']) ? $settings['create_roles'] : array('administrator');
        
        // Combine both role types for project leads
        $allowed_roles = array_unique(array_merge($manage_roles, $create_roles));
        
        // Get users with the configured roles
        $project_lead_users = get_users(array(
            'fields' => 'ID',
            'role__in' => $allowed_roles
        ));
        
        return $project_lead_users;
    }
    
    /**
     * Format user display name for project lead fields
     * Consistent formatting across all areas
     *
     * @param int $user_id User ID
     * @return string Formatted display name or fallback
     */
    public static function format_project_lead_display($user_id) {
        if (empty($user_id)) {
            return '<span class="na">&ndash;</span>';
        }
        
        $user = get_userdata($user_id);
        if (!$user) {
            return '<span class="na">&ndash;</span>';
        }
        
        // Priority: display_name -> first/last name -> email
        $display_name = '';
        
        if (!empty($user->display_name)) {
            $display_name = $user->display_name;
        } elseif (!empty($user->first_name) || !empty($user->last_name)) {
            $display_name = trim($user->first_name . ' ' . $user->last_name);
        } elseif (!empty($user->user_email)) {
            $display_name = $user->user_email;
        } else {
            $display_name = __('Unknown Lead', 'arsol-pfw');
        }
        
        return esc_html($display_name);
    }
    
    /**
     * Create a filter link for project lead in admin columns
     * Reusable method for creating clickable filter links
     *
     * @param int    $user_id   User ID
     * @param string $post_type Post type for the filter URL
     * @return string HTML link or fallback display
     */
    public static function create_project_lead_filter_link($user_id, $post_type = 'arsol-project') {
        if (empty($user_id)) {
            return '<span class="na">&ndash;</span>';
        }
        
        $user = get_userdata($user_id);
        if (!$user) {
            return '<span class="na">&ndash;</span>';
        }
        
        $display_name = self::format_project_lead_display($user_id);
        
        // Create filter URL
        $filter_url = add_query_arg(array(
            'post_type' => $post_type,
            'project_lead' => $user->ID
        ), admin_url('edit.php'));
        
        return sprintf(
            '<a href="%s">%s</a>',
            esc_url($filter_url),
            $display_name
        );
    }
} 