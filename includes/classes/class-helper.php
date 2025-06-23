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
        // Removed AJAX handler - using simple dropdown instead
    }
    
    /**
     * Render a project lead search select field
     * Simple WordPress user dropdown with basic Select2 enhancement
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
        
        // Get valid project lead user IDs
        $valid_user_ids = self::get_project_lead_user_ids();
        
        $classes = 'arsol-user-select2';
        if (!empty($args['class'])) {
            $classes .= ' ' . esc_attr($args['class']);
        }
        
        // Use WordPress native dropdown
        wp_dropdown_users(array(
            'name' => $args['name'],
            'id' => $args['id'],
            'class' => $classes,
            'selected' => $args['selected'],
            'include' => $valid_user_ids,
            'show_option_none' => $args['placeholder'],
            'option_none_value' => ''
        ));
    }
    
    /**
     * Get users who can create projects
     * Reusable method for getting valid project lead users
     *
     * @return array Array of user IDs who can create projects
     */
    public static function get_project_lead_user_ids() {
        $admin_users_helper = new \Arsol_Projects_For_Woo\Admin\Users();
        
        // Get users who can create projects based on Project Manager Roles setting
        $project_lead_users = get_users(array(
            'fields' => array('ID', 'display_name'),
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'wp_capabilities',
                    'value' => 'manage_projects',
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'wp_capabilities', 
                    'value' => 'create_projects',
                    'compare' => 'LIKE'
                )
            )
        ));
        
        // Filter to only users who can actually create projects
        $valid_user_ids = array();
        foreach ($project_lead_users as $user) {
            if ($admin_users_helper->can_user_create_projects($user->ID)) {
                $valid_user_ids[] = $user->ID;
            }
        }
        
        return $valid_user_ids;
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
