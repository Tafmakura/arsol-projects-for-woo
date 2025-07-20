<?php

if (!defined('ABSPATH')) exit;

/**
 * Consolidated user formatting function
 * 
 * Handles all user display formatting with consistent priority order:
 * display_name → first_name + last_name → user_email → user_login
 * 
 * @param int|\WP_User $user User ID or user object
 * @param string $format Format type: 'display_name', 'first_name', 'full_name', 'last_name'
 * @param bool $id Whether to show user ID in secondary info
 * @param bool $email Whether to include email in output
 * @param bool $link Whether to make the output a clickable link (for admin columns)
 * @param array $args Additional arguments (post_type for links, etc.)
 * @return string Formatted user display
 */
function arsol_pfw_format_user($user, $format = 'display_name', $id = false, $email = true, $link = false, $args = []) {
    // Handle user ID or user object
    if (is_numeric($user)) {
        $user = get_userdata($user);
    }
    
    if (!$user) {
        return __('Unknown User', 'arsol-pfw');
    }
    
    // Get the best available name with consistent priority
    $name = arsol_pfw_get_user_name($user);
    
    // Generate the formatted text based on format and fallback logic
    $formatted_text = '';
    $secondary_info = '';
    
    // Determine primary display based on format and availability
    switch ($format) {
        case 'display_name':
            if (!empty($user->display_name)) {
                $formatted_text = $user->display_name;
            } elseif (!empty($user->first_name) || !empty($user->last_name)) {
                // Fallback to first/last name
                $first = trim($user->first_name ?? '');
                $last = trim($user->last_name ?? '');
                $formatted_text = trim($first . ' ' . $last);
            } else {
                // Fallback to email
                $formatted_text = $user->user_email;
                if ($id) {
                    $secondary_info = '#' . $user->ID;
                }
            }
            break;
            
        case 'first_name':
            if (!empty($user->first_name)) {
                $formatted_text = $user->first_name;
            } else {
                // Fallback to email
                $formatted_text = $user->user_email;
                if ($id) {
                    $secondary_info = '#' . $user->ID;
                }
            }
            break;
            
        case 'full_name':
            if (!empty($user->first_name) || !empty($user->last_name)) {
                $first = trim($user->first_name ?? '');
                $last = trim($user->last_name ?? '');
                $formatted_text = trim($first . ' ' . $last);
            } else {
                // Fallback to email
                $formatted_text = $user->user_email;
                if ($id) {
                    $secondary_info = '#' . $user->ID;
                }
            }
            break;
            
        case 'last_name':
            if (!empty($user->last_name)) {
                $formatted_text = $user->last_name;
            } else {
                // Fallback to email
                $formatted_text = $user->user_email;
                if ($id) {
                    $secondary_info = '#' . $user->ID;
                }
            }
            break;
            
        default:
            // Default to display_name logic
            if (!empty($user->display_name)) {
                $formatted_text = $user->display_name;
            } elseif (!empty($user->first_name) || !empty($user->last_name)) {
                $first = trim($user->first_name ?? '');
                $last = trim($user->last_name ?? '');
                $formatted_text = trim($first . ' ' . $last);
            } else {
                $formatted_text = $user->user_email;
                if ($id) {
                    $secondary_info = '#' . $user->ID;
                }
            }
            break;
    }
    
    // Add email if requested and not already the primary display
    if ($email && $formatted_text !== $user->user_email && !empty($user->user_email)) {
        if (!empty($secondary_info)) {
            // Primary (email) (secondary)
            $formatted_text = sprintf('%s (%s) (%s)', $formatted_text, $user->user_email, $secondary_info);
        } else {
            // Primary (email)
            $formatted_text = sprintf('%s (%s)', $formatted_text, $user->user_email);
        }
    } elseif (!empty($secondary_info)) {
        // Primary (secondary) - when email is primary or not requested
        $formatted_text = sprintf('%s (%s)', $formatted_text, $secondary_info);
    }
    
    // Make it linkable if requested
    if ($link) {
        $post_type = isset($args['post_type']) ? $args['post_type'] : 'arsol-pfw-project';
        $filter_url = add_query_arg([
            'post_type' => $post_type,
            'customer' => $user->ID
        ], admin_url('edit.php'));
        
        return sprintf(
            '<a href="%s">%s</a>',
            esc_url($filter_url),
            esc_html($formatted_text)
        );
    }
    
    return $formatted_text;
}

/**
 * Get user name with consistent priority order
 * 
 * Priority: display_name → first_name + last_name → user_email → user_login
 * 
 * @param \WP_User $user User object
 * @return string Best available name
 */
function arsol_pfw_get_user_name($user) {
    // Priority 1: Display name
    if (!empty($user->display_name)) {
        return trim($user->display_name);
    }
    
    // Priority 2: First and Last name
    if (!empty($user->first_name) || !empty($user->last_name)) {
        $first = trim($user->first_name ?? '');
        $last = trim($user->last_name ?? '');
        $full_name = trim($first . ' ' . $last);
        
        if (!empty($full_name)) {
            return $full_name;
        }
    }
    
    // Priority 3: Email (fallback)
    if (!empty($user->user_email)) {
        return $user->user_email;
    }
    
    // Priority 4: Username (last resort)
    if (!empty($user->user_login)) {
        return $user->user_login;
    }
    
    return __('Unknown User', 'arsol-pfw');
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
 * Legacy function for backward compatibility
 * 
 * @param int|\WP_User $user User ID or user object
 * @param string $format Format type
 * @param array $args Additional arguments
 * @return string Formatted user display
 */
function arsol_pfw_format_user_display($user, $format = 'basic', $args = []) {
    // Map old format names to new ones
    $format_map = [
        'basic' => 'display_name',
        'name_email' => 'display_name',
        'admin' => 'display_name',
        'full' => 'full_name',
        'email_only' => 'display_name',
        'id_only' => 'display_name'
    ];
    
    $new_format = isset($format_map[$format]) ? $format_map[$format] : 'display_name';
    
    return arsol_pfw_format_user($user, $new_format, false, true, false, $args);
}
