<?php

if (!defined('ABSPATH')) exit;

/**
 * Format user display name with different styles
 * 
 * @param int|\WP_User $user User ID or user object
 * @param string $format Format type: 'basic', 'admin', 'filter_link', 'full', 'name_email'
 * @param array $args Additional arguments for specific formats
 * @return string Formatted user display
 */
function arsol_pfw_format_user_display($user, $format = 'basic', $args = []) {
    if (is_numeric($user)) {
        $user = get_userdata($user);
    }
    
    if (!$user) {
        return __('Unknown User', 'arsol-pfw');
    }
    
    switch ($format) {
        case 'basic':
            return arsol_pfw_format_basic_name($user);
            
        case 'admin':
            return arsol_pfw_format_admin_display($user);
            
        case 'filter_link':
            $post_type = isset($args['post_type']) ? $args['post_type'] : 'arsol-pfw-project';
            return arsol_pfw_create_filter_link($user, $post_type);
            
        case 'full':
            return arsol_pfw_format_full_display($user);
            
        case 'email_only':
            return $user->user_email;
            
        case 'id_only':
            return '#' . $user->ID;
            
        case 'name_email':
            return arsol_pfw_format_name_email($user);
            
        case 'name_id':
            return arsol_pfw_format_name_id($user);
            
        default:
            return arsol_pfw_format_basic_name($user);
    }
}

/**
 * Format basic user name with priority: first/last → display_name → email
 * 
 * @param \WP_User $user User object
 * @return string Formatted name
 */
function arsol_pfw_format_basic_name($user) {
    // Priority 1: First and Last name
    if (!empty($user->first_name) || !empty($user->last_name)) {
        $first = trim($user->first_name ?? '');
        $last = trim($user->last_name ?? '');
        $full_name = trim($first . ' ' . $last);
        
        if (!empty($full_name)) {
            return $full_name;
        }
    }
    
    // Priority 2: Display name (but only if it looks like a real name)
    if (!empty($user->display_name)) {
        $display_name = trim($user->display_name);
        
        // Check if display_name looks like a proper name
        if (strlen($display_name) > 2 && 
            !preg_match('/^[a-z0-9_]+$/i', $display_name) && // Not just alphanumeric
            strpos($display_name, ' ') !== false) { // Contains space (first last)
            return $display_name;
        }
    }
    
    // Priority 3: Display name (even if username-like)
    if (!empty($user->display_name)) {
        return trim($user->display_name);
    }
    
    // Priority 4: Email (fallback)
    if (!empty($user->user_email)) {
        return $user->user_email;
    }
    
    // Priority 5: Username (last resort)
    if (!empty($user->user_login)) {
        return $user->user_login;
    }
    
    return __('Unknown User', 'arsol-pfw');
}

/**
 * Format admin display (Name (#ID – email))
 * 
 * @param \WP_User $user User object
 * @return string Formatted admin display
 */
function arsol_pfw_format_admin_display($user) {
    $name = arsol_pfw_format_basic_name($user);
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
function arsol_pfw_format_full_display($user) {
    $name = arsol_pfw_format_basic_name($user);
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
 * Format name with email: "First Name (email@domain.com)" or "DisplayName (email@domain.com)"
 * 
 * @param \WP_User $user User object
 * @return string Formatted name with email
 */
function arsol_pfw_format_name_email($user) {
    // Get the best available name
    $name = '';
    
    // Priority 1: First and Last name
    if (!empty($user->first_name) || !empty($user->last_name)) {
        $first = trim($user->first_name ?? '');
        $last = trim($user->last_name ?? '');
        $full_name = trim($first . ' ' . $last);
        
        if (!empty($full_name)) {
            $name = $full_name;
        }
    }
    
    // Priority 2: Display name (if no first/last name or as fallback)
    if (empty($name) && !empty($user->display_name)) {
        $name = trim($user->display_name);
    }
    
    // If we still don't have a name, use email or username
    if (empty($name)) {
        if (!empty($user->user_email)) {
            $name = $user->user_email;
        } elseif (!empty($user->user_login)) {
            $name = $user->user_login;
        } else {
            $name = __('Unknown User', 'arsol-pfw');
        }
    }
    
    // Format: "Name (email@domain.com)"
    if (!empty($user->user_email)) {
        return sprintf('%s (%s)', $name, $user->user_email);
    } else {
        return $name;
    }
}

/**
 * Format name with ID
 * 
 * @param \WP_User $user User object
 * @return string Formatted name with ID
 */
function arsol_pfw_format_name_id($user) {
    $name = arsol_pfw_format_basic_name($user);
    return sprintf('%s (#%s)', $name, $user->ID);
}

/**
 * Create filter link for admin columns
 * 
 * @param \WP_User $user User object
 * @param string $post_type Post type for filter URL
 * @return string HTML link
 */
function arsol_pfw_create_filter_link($user, $post_type) {
    $name = arsol_pfw_format_name_email($user); // Use name_email format for filter links
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
function arsol_pfw_get_user($identifier) {
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
function arsol_pfw_is_valid_user($user) {
    if (is_object($user) && $user instanceof \WP_User) {
        return $user->exists();
    }
    
    $user_obj = arsol_pfw_get_user($user);
    return $user_obj && $user_obj->exists();
}

/**
 * Get user role display name
 * 
 * @param \WP_User $user User object
 * @return string Role display name
 */
function arsol_pfw_get_user_role_display($user) {
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
function arsol_pfw_get_user_meta($user, $meta_key, $default = '') {
    if (!$user || !$user->exists()) {
        return $default;
    }
    
    $meta_value = get_user_meta($user->ID, $meta_key, true);
    return !empty($meta_value) ? $meta_value : $default;
}

/**
 * Convenience function for user formatting (main factory function)
 * 
 * @param mixed $user User ID, email, username, or user object
 * @param string $format Format type
 * @param array $args Additional arguments
 * @return string Formatted user display
 */
function arsol_pfw_format_user($user, $format = 'basic', $args = []) {
    return arsol_pfw_format_user_display($user, $format, $args);
}
