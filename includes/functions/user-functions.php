<?php

namespace Arsol_Projects_For_Woo\Functions;

if (!defined('ABSPATH')) exit;

/**
 * Format user display name with different styles
 * 
 * @param int|\WP_User $user User ID or user object
 * @param string $format Format type: 'basic', 'admin', 'filter_link', 'full'
 * @param array $args Additional arguments for specific formats
 * @return string Formatted user display
 */
function format_user_display($user, $format = 'basic', $args = []) {
    if (is_numeric($user)) {
        $user = get_userdata($user);
    }
    
    if (!$user) {
        return __('Unknown User', 'arsol-pfw');
    }
    
    switch ($format) {
        case 'basic':
            return format_basic_name($user);
            
        case 'admin':
            return format_admin_display($user);
            
        case 'filter_link':
            $post_type = isset($args['post_type']) ? $args['post_type'] : 'arsol-pfw-project';
            return create_filter_link($user, $post_type);
            
        case 'full':
            return format_full_display($user);
            
        case 'email_only':
            return $user->user_email;
            
        case 'id_only':
            return '#' . $user->ID;
            
        case 'name_email':
            return format_name_email($user);
            
        case 'name_id':
            return format_name_id($user);
            
        default:
            return format_basic_name($user);
    }
}

/**
 * Format basic user name (display_name → first/last → email)
 * 
 * @param \WP_User $user User object
 * @return string Formatted name
 */
function format_basic_name($user) {
    if (!empty($user->display_name)) {
        return $user->display_name;
    } elseif (!empty($user->first_name) || !empty($user->last_name)) {
        return trim($user->first_name . ' ' . $user->last_name);
    } elseif (!empty($user->user_email)) {
        return $user->user_email;
    } else {
        return __('Unknown User', 'arsol-pfw');
    }
}

/**
 * Format admin display (Name (#ID – email))
 * 
 * @param \WP_User $user User object
 * @return string Formatted admin display
 */
function format_admin_display($user) {
    $name = format_basic_name($user);
    return sprintf(
        '%s (#%s – %s)',
        $name,
        $user->ID,
        $user->user_email
    );
}

/**
 * Format full display with all details
 * 
 * @param \WP_User $user User object
 * @return string Formatted full display
 */
function format_full_display($user) {
    $name = format_basic_name($user);
    $details = [];
    
    if (!empty($user->first_name) && !empty($user->last_name)) {
        $details[] = sprintf('First: %s, Last: %s', $user->first_name, $user->last_name);
    }
    
    if (!empty($user->user_email)) {
        $details[] = 'Email: ' . $user->user_email;
    }
    
    if (!empty($user->user_registered)) {
        $details[] = 'Registered: ' . date('Y-m-d', strtotime($user->user_registered));
    }
    
    $details_str = !empty($details) ? ' (' . implode(' | ', $details) . ')' : '';
    
    return $name . $details_str;
}

/**
 * Format name with email
 * 
 * @param \WP_User $user User object
 * @return string Formatted name with email
 */
function format_name_email($user) {
    $name = format_basic_name($user);
    return sprintf('%s (%s)', $name, $user->user_email);
}

/**
 * Format name with ID
 * 
 * @param \WP_User $user User object
 * @return string Formatted name with ID
 */
function format_name_id($user) {
    $name = format_basic_name($user);
    return sprintf('%s (#%s)', $name, $user->ID);
}

/**
 * Create filter link for admin columns
 * 
 * @param \WP_User $user User object
 * @param string $post_type Post type for filter URL
 * @return string HTML link
 */
function create_filter_link($user, $post_type) {
    $name = format_basic_name($user);
    $filter_url = add_query_arg([
        'post_type' => $post_type,
        'customer' => $user->ID
    ], admin_url('edit.php'));
    
    return sprintf(
        '<a href="%s">%s</a>',
        esc_url($filter_url),
        esc_html($name)
    );
}

/**
 * Get user by various identifiers
 * 
 * @param mixed $identifier User ID, email, or username
 * @return \WP_User|false User object or false
 */
function get_user($identifier) {
    if (is_numeric($identifier)) {
        return get_userdata($identifier);
    } elseif (is_email($identifier)) {
        return get_user_by('email', $identifier);
    } else {
        return get_user_by('login', $identifier);
    }
}

/**
 * Check if user exists and is valid
 * 
 * @param mixed $user User ID, email, username, or user object
 * @return bool True if user exists and is valid
 */
function is_valid_user($user) {
    if (is_object($user) && $user instanceof \WP_User) {
        return $user->exists();
    }
    
    $user_obj = get_user($user);
    return $user_obj && $user_obj->exists();
}

/**
 * Get user role display name
 * 
 * @param \WP_User $user User object
 * @return string Role display name
 */
function get_user_role_display($user) {
    if (!$user || !$user->exists()) {
        return __('Unknown', 'arsol-pfw');
    }
    
    $roles = $user->roles;
    if (empty($roles)) {
        return __('No Role', 'arsol-pfw');
    }
    
    $role_names = [];
    foreach ($roles as $role) {
        $role_obj = get_role($role);
        if ($role_obj) {
            $role_names[] = translate_user_role($role_obj->name);
        }
    }
    
    return implode(', ', $role_names);
}

/**
 * Get user meta with fallback
 * 
 * @param \WP_User $user User object
 * @param string $meta_key Meta key
 * @param mixed $default Default value if meta not found
 * @return mixed Meta value or default
 */
function get_user_meta($user, $meta_key, $default = '') {
    if (!$user || !$user->exists()) {
        return $default;
    }
    
    $meta_value = get_user_meta($user->ID, $meta_key, true);
    return !empty($meta_value) ? $meta_value : $default;
}

/**
 * Convenience function for user formatting (global namespace)
 * 
 * @param mixed $user User ID, email, username, or user object
 * @param string $format Format type
 * @param array $args Additional arguments
 * @return string Formatted user display
 */
function arsol_pfw_format_user($user, $format = 'basic', $args = []) {
    return \Arsol_Projects_For_Woo\Functions\format_user_display($user, $format, $args);
}

/**
 * Convenience function to get user object (global namespace)
 * 
 * @param mixed $identifier User ID, email, or username
 * @return \WP_User|false User object or false
 */
function arsol_pfw_get_user($identifier) {
    return \Arsol_Projects_For_Woo\Functions\get_user($identifier);
}

/**
 * Convenience function to check if user is valid (global namespace)
 * 
 * @param mixed $user User identifier or object
 * @return bool True if user is valid
 */
function arsol_pfw_is_valid_user($user) {
    return \Arsol_Projects_For_Woo\Functions\is_valid_user($user);
}
