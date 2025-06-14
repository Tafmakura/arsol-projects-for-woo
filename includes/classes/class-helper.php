<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * General Helper Class
 * 
 * Contains utility functions for the Arsol Projects for Woo plugin
 * including user management, dropdown generation, and general helpers
 */
class Helper {
    
    /**
     * Get users who can create projects with first + last name display
     * 
     * @return array Array of user objects with formatted display names
     */
    public static function get_project_leads() {
        $admin_users_helper = new \Arsol_Projects_For_Woo\Admin\Users();
        
        $users = get_users(array(
            'fields' => array('ID', 'user_login', 'user_email'),
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'first_name',
                    'value' => '',
                    'compare' => '!='
                ),
                array(
                    'key' => 'last_name',
                    'value' => '',
                    'compare' => '!='
                )
            ),
            'orderby' => 'meta_value',
            'meta_key' => 'first_name'
        ));
        
        $project_leads = array();
        
        foreach ($users as $user) {
            if ($admin_users_helper->can_user_create_projects($user->ID)) {
                $project_leads[] = (object) array(
                    'ID' => $user->ID,
                    'user_login' => $user->user_login,
                    'user_email' => $user->user_email,
                    'display_name' => self::format_user_display_name($user->ID)
                );
            }
        }
        
        return $project_leads;
    }
    
    /**
     * Generate project lead dropdown HTML
     * 
     * @param string $name Field name
     * @param int|string $selected Selected value
     * @param array $attributes Additional HTML attributes
     * @return string HTML dropdown
     */
    public static function generate_project_lead_dropdown($name, $selected = '', $attributes = array()) {
        $default_attributes = array(
            'class' => 'arsol-pfw-admin-select2',
            'data-placeholder' => __('Filter by project lead', 'arsol-pfw'),
            'data-allow_clear' => 'true'
        );
        
        $attributes = array_merge($default_attributes, $attributes);
        $attr_string = '';
        
        foreach ($attributes as $key => $value) {
            $attr_string .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
        }
        
        $project_leads = self::get_project_leads();
        
        $html = sprintf('<select name="%s"%s>', esc_attr($name), $attr_string);
        $html .= '<option value="">' . __('Filter by project lead', 'arsol-pfw') . '</option>';
        
        foreach ($project_leads as $user) {
            $html .= sprintf(
                '<option value="%s"%s>%s</option>',
                esc_attr($user->ID),
                selected($selected, $user->ID, false),
                esc_html($user->display_name)
            );
        }
        
        $html .= '</select>';
        
        return $html;
    }
    
    /**
     * Format user display name from first + last name
     * 
     * @param int $user_id User ID
     * @param bool $include_email Whether to include email in parentheses
     * @return string Formatted display name
     */
    public static function format_user_display_name($user_id, $include_email = false) {
        $first_name = get_user_meta($user_id, 'first_name', true);
        $last_name = get_user_meta($user_id, 'last_name', true);
        $user = get_userdata($user_id);
        
        if (!$user) {
            return '';
        }
        
        // Create display name from first + last name
        $display_name = '';
        if (!empty($first_name) || !empty($last_name)) {
            $display_name = trim($first_name . ' ' . $last_name);
        } else {
            $display_name = $user->user_login;
        }
        
        // Add email if requested
        if ($include_email && !empty($user->user_email)) {
            $display_name .= ' (' . $user->user_email . ')';
        }
        
        return $display_name;
    }
    
    /**
     * Get users with formatted display names
     * 
     * @param array $user_query_args Additional arguments for get_users()
     * @param bool $include_email Whether to include email in display names
     * @return array Array of user objects with formatted display names
     */
    public static function get_users_with_display_names($user_query_args = array(), $include_email = false) {
        $default_args = array(
            'fields' => array('ID', 'user_login', 'user_email'),
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'first_name',
                    'value' => '',
                    'compare' => '!='
                ),
                array(
                    'key' => 'last_name',
                    'value' => '',
                    'compare' => '!='
                )
            ),
            'orderby' => 'meta_value',
            'meta_key' => 'first_name'
        );
        
        $args = array_merge($default_args, $user_query_args);
        $users = get_users($args);
        
        $formatted_users = array();
        
        foreach ($users as $user) {
            $formatted_users[] = (object) array(
                'ID' => $user->ID,
                'user_login' => $user->user_login,
                'user_email' => $user->user_email,
                'display_name' => self::format_user_display_name($user->ID, $include_email)
            );
        }
        
        return $formatted_users;
    }
    
    /**
     * Generate generic user dropdown HTML
     * 
     * @param string $name Field name
     * @param array $users Array of user objects
     * @param int|string $selected Selected value
     * @param string $placeholder Placeholder text
     * @param array $attributes Additional HTML attributes
     * @return string HTML dropdown
     */
    public static function generate_user_dropdown($name, $users, $selected = '', $placeholder = '', $attributes = array()) {
        $default_attributes = array(
            'class' => 'arsol-pfw-admin-select2',
            'data-allow_clear' => 'true'
        );
        
        if (!empty($placeholder)) {
            $default_attributes['data-placeholder'] = $placeholder;
        }
        
        $attributes = array_merge($default_attributes, $attributes);
        $attr_string = '';
        
        foreach ($attributes as $key => $value) {
            $attr_string .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
        }
        
        $html = sprintf('<select name="%s"%s>', esc_attr($name), $attr_string);
        
        if (!empty($placeholder)) {
            $html .= '<option value="">' . esc_html($placeholder) . '</option>';
        }
        
        foreach ($users as $user) {
            $html .= sprintf(
                '<option value="%s"%s>%s</option>',
                esc_attr($user->ID),
                selected($selected, $user->ID, false),
                esc_html($user->display_name)
            );
        }
        
        $html .= '</select>';
        
        return $html;
    }
    
    /**
     * Sanitize and validate user input
     * 
     * @param mixed $input Input to sanitize
     * @param string $type Type of sanitization (text, email, int, etc.)
     * @return mixed Sanitized input
     */
    public static function sanitize_input($input, $type = 'text') {
        switch ($type) {
            case 'email':
                return sanitize_email($input);
            case 'int':
                return absint($input);
            case 'float':
                return floatval($input);
            case 'url':
                return esc_url_raw($input);
            case 'textarea':
                return sanitize_textarea_field($input);
            case 'text':
            default:
                return sanitize_text_field($input);
        }
    }
    
    /**
     * Check if WooCommerce is active
     * 
     * @return bool True if WooCommerce is active
     */
    public static function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }
    
    /**
     * Get plugin option with default value
     * 
     * @param string $option_name Option name
     * @param mixed $default Default value
     * @return mixed Option value or default
     */
    public static function get_option($option_name, $default = null) {
        return get_option($option_name, $default);
    }
    
    /**
     * Update plugin option
     * 
     * @param string $option_name Option name
     * @param mixed $value Option value
     * @return bool True on success
     */
    public static function update_option($option_name, $value) {
        return update_option($option_name, $value);
    }
}
