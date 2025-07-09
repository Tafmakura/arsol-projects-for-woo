<?php
/**
 * Arsol Projects For Woo Global Helper Functions
 * 
 * Following WooCommerce pattern for global utility functions
 *
 * @package Arsol_Projects_For_Woo
 */

if (!defined('ABSPATH')) {
    exit;
}

// ========================================
// CUSTOMER HELPER FUNCTIONS
// ========================================

/**
 * Format customer display name with priority: Display Name → First Last → Email
 * 
 * @param int|\WP_User $user User ID or user object
 * @return string Formatted customer name
 */
function arsol_pfw_format_customer_name($user) {
    return \Arsol_Projects_For_Woo\Woocommerce::format_customer_name($user);
}

/**
 * Format customer display for admin dropdowns (includes ID and email)
 * Format: "Display Name (#ID – email)" or "First Last (#ID – email)"
 * 
 * @param int|\WP_User $user User ID or user object
 * @return string Formatted customer display for admin
 */
function arsol_pfw_format_customer_admin_display($user) {
    return \Arsol_Projects_For_Woo\Woocommerce::format_customer_admin_display($user);
}

/**
 * Create a customer filter link for admin columns
 * 
 * @param int|\WP_User $user User ID or user object
 * @param string $post_type Post type for the filter URL
 * @return string HTML link or fallback display
 */
function arsol_pfw_create_customer_filter_link($user, $post_type) {
    return \Arsol_Projects_For_Woo\Woocommerce::create_customer_filter_link($user, $post_type);
}

// ========================================
// PROJECT LEAD HELPER FUNCTIONS
// ========================================

/**
 * Format project lead display name
 *
 * @param int $user_id User ID
 * @return string Formatted display name
 */
function arsol_pfw_format_project_lead_display($user_id) {
    return \Arsol_Projects_For_Woo\Admin\Users::format_project_lead_display($user_id);
}

/**
 * Create project lead filter link for admin columns
 *
 * @param int $user_id User ID
 * @param string $post_type Post type
 * @return string HTML link or fallback display
 */
function arsol_pfw_create_project_lead_filter_link($user_id, $post_type = 'arsol-pfw-project') {
    if (!$user_id) {
        return '<span class="na">&ndash;</span>';
    }
    
    $user = get_userdata($user_id);
    if (!$user) {
        return '<span class="na">&ndash;</span>';
    }
    
    $display_name = arsol_pfw_format_project_lead_display($user_id);
    $display_name = wp_strip_all_tags($display_name);
    
    // Create filter URL
    $filter_url = add_query_arg(array(
        'post_type' => $post_type,
        'author' => $user_id
    ), admin_url('edit.php'));
    
    return sprintf(
        '<a href="%s">%s</a>',
        esc_url($filter_url),
        esc_html($display_name)
    );
}

/**
 * Get users who can manage projects (WordPress-native capabilities)
 *
 * @return array Array of user IDs who can manage projects
 */
function arsol_pfw_get_project_lead_user_ids() {
    return \Arsol_Projects_For_Woo\Admin\Users::get_project_lead_user_ids();
}

// ========================================
// ADMIN LINK HELPER FUNCTIONS
// ========================================

/**
 * Create an edit link for any post type
 *
 * @param int $post_id Post ID
 * @param string $title Link title
 * @param string $fallback Fallback text if link can't be created
 * @return string HTML link or fallback
 */
function arsol_pfw_create_edit_link($post_id, $title = '', $fallback = '') {
    if (!$post_id) {
        return $fallback ?: '<span class="na">&ndash;</span>';
    }
    
    $post = get_post($post_id);
    if (!$post) {
        return $fallback ?: '<span class="na">&ndash;</span>';
    }
    
    $title = $title ?: $post->post_title;
    $edit_url = get_edit_post_link($post_id);
    
    if ($edit_url && current_user_can('edit_post', $post_id)) {
        return sprintf(
            '<a href="%s"><strong>%s</strong></a>',
            esc_url($edit_url),
            esc_html($title)
        );
    }
    
    return esc_html($title);
}

/**
 * Create a view link for any post type
 *
 * @param int $post_id Post ID
 * @param string $title Link title
 * @param string $fallback Fallback text if link can't be created
 * @return string HTML link or fallback
 */
function arsol_pfw_create_view_link($post_id, $title = '', $fallback = '') {
    if (!$post_id) {
        return $fallback ?: '<span class="na">&ndash;</span>';
    }
    
    $post = get_post($post_id);
    if (!$post) {
        return $fallback ?: '<span class="na">&ndash;</span>';
    }
    
    $title = $title ?: $post->post_title;
    $view_url = get_permalink($post_id);
    
    if ($view_url) {
        return sprintf(
            '<a href="%s">%s</a>',
            esc_url($view_url),
            esc_html($title)
        );
    }
    
    return esc_html($title);
}

// ========================================
// STAGE HELPER FUNCTIONS
// ========================================

/**
 * Get stage label for display
 *
 * @param string $stage_slug Stage slug
 * @param string $entity_type Entity type (project, proposal, request)
 * @return string Stage label
 */
function arsol_pfw_get_stage_label($stage_slug, $entity_type) {
    return \Arsol_Projects_For_Woo\Core\Stage_Manager::get_stage_label($stage_slug, $entity_type);
}

/**
 * Get available stages for an entity type
 *
 * @param string $entity_type Entity type (project, proposal, request)
 * @return array Available stages
 */
function arsol_pfw_get_available_stages($entity_type) {
    return \Arsol_Projects_For_Woo\Core\Stage_Manager::get_available_stages($entity_type);
}

/**
 * Create a stage badge/span for display
 *
 * @param string $stage_slug Stage slug
 * @param string $entity_type Entity type (project, proposal, request)
 * @param string $fallback Fallback text if stage is empty
 * @return string HTML stage badge
 */
function arsol_pfw_create_stage_badge($stage_slug, $entity_type, $fallback = '') {
    if (!$stage_slug) {
        $fallback = $fallback ?: ucfirst($entity_type);
        return '<span class="stage stage-' . esc_attr($entity_type) . '">' . esc_html($fallback) . '</span>';
    }
    
    $stage_label = arsol_pfw_get_stage_label($stage_slug, $entity_type);
    return '<span class="stage stage-' . esc_attr($stage_slug) . '">' . esc_html($stage_label) . '</span>';
}

// ========================================
// CURRENCY & FORMATTING HELPER FUNCTIONS
// ========================================

/**
 * Format price with WooCommerce currency
 *
 * @param float $price Price to format
 * @param array $args WooCommerce price arguments
 * @return string Formatted price
 */
function arsol_pfw_format_price($price, $args = array()) {
    if (!function_exists('wc_price')) {
        return '$' . number_format($price, 2);
    }
    
    return wc_price($price, $args);
}

/**
 * Format date for display
 *
 * @param string $date Date string
 * @param string $format Date format (default: WordPress date format)
 * @return string Formatted date
 */
function arsol_pfw_format_date($date, $format = '') {
    if (!$date) {
        return '';
    }
    
    $format = $format ?: get_option('date_format');
    return wp_date($format, strtotime($date));
}

/**
 * Format datetime for display
 *
 * @param string $datetime DateTime string
 * @param string $format DateTime format (default: WordPress date + time format)
 * @return string Formatted datetime
 */
function arsol_pfw_format_datetime($datetime, $format = '') {
    if (!$datetime) {
        return '';
    }
    
    $format = $format ?: (get_option('date_format') . ' ' . get_option('time_format'));
    return wp_date($format, strtotime($datetime));
}

// ========================================
// PERMISSION HELPER FUNCTIONS
// ========================================

/**
 * Check if user can view project
 *
 * @param int $user_id User ID
 * @param int $project_id Project ID
 * @return bool
 */
function arsol_pfw_user_can_view_project($user_id, $project_id) {
    return \Arsol_Projects_For_Woo\Core\Permissions::user_can_view_project($user_id, $project_id);
}

/**
 * Check if user can edit project
 *
 * @param int $user_id User ID
 * @param int $project_id Project ID
 * @return bool
 */
function arsol_pfw_user_can_edit_project($user_id, $project_id) {
    return \Arsol_Projects_For_Woo\Core\Permissions::user_can_edit_project($user_id, $project_id);
}

/**
 * Check if user can create projects
 *
 * @param int $user_id User ID
 * @return bool
 */
function arsol_pfw_user_can_create_projects($user_id) {
    return \Arsol_Projects_For_Woo\Admin\Users::can_user_create_projects($user_id);
}

/**
 * Check if user can request projects
 *
 * @param int $user_id User ID
 * @return bool
 */
function arsol_pfw_user_can_request_projects($user_id) {
    return \Arsol_Projects_For_Woo\Admin\Users::can_user_request_projects($user_id);
}

// ========================================
// VALIDATION HELPER FUNCTIONS
// ========================================

/**
 * Validate email address
 *
 * @param string $email Email address
 * @return bool
 */
function arsol_pfw_is_valid_email($email) {
    return is_email($email);
}

/**
 * Validate numeric value
 *
 * @param mixed $value Value to validate
 * @return bool
 */
function arsol_pfw_is_valid_number($value) {
    return is_numeric($value) && $value >= 0;
}

/**
 * Validate date string
 *
 * @param string $date Date string
 * @param string $format Date format (default: Y-m-d)
 * @return bool
 */
function arsol_pfw_is_valid_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// ========================================
// CLEANUP HELPER FUNCTIONS
// ========================================

/**
 * Clean amount input by removing non-numeric characters except decimal points
 *
 * @param string|float|int $amount The amount to clean
 * @return string The cleaned amount as a string
 */
function arsol_pfw_clean_amount($amount) {
    return \Arsol_Projects_For_Woo\Woocommerce::clean_amount($amount);
}

/**
 * Sanitize and validate stage slug
 *
 * @param string $stage Stage slug
 * @return string Sanitized stage slug
 */
function arsol_pfw_sanitize_stage($stage) {
    return sanitize_key($stage);
}

/**
 * Sanitize HTML content for safe output
 *
 * @param string $content Content to sanitize
 * @return string Sanitized content
 */
function arsol_pfw_sanitize_html($content) {
    return wp_kses_post($content);
}

// ========================================
// LOGGING HELPER FUNCTIONS
// ========================================

/**
 * Log general messages
 *
 * @param string $level Log level (info, warning, error)
 * @param string $message Log message
 */
function arsol_pfw_log($level, $message) {
    if (class_exists('\Arsol_Projects_For_Woo\Woocommerce_Logs')) {
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_general($level, $message);
    }
}

/**
 * Log workflow messages
 *
 * @param string $level Log level (info, warning, error)
 * @param string $message Log message
 */
function arsol_pfw_log_workflow($level, $message) {
    if (class_exists('\Arsol_Projects_For_Woo\Woocommerce_Logs')) {
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow($level, $message);
    }
}

/**
 * Log WooCommerce billing operations
 *
 * @param string $level Log level (info, warning, error)
 * @param string $message Log message
 */
function arsol_pfw_log_billing($level, $message) {
    if (class_exists('\Arsol_Projects_For_Woo\Woocommerce_Logs')) {
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_woocommerce_billing($level, $message);
    }
}

// ========================================
// UTILITY HELPER FUNCTIONS
// ========================================

/**
 * Get plugin version
 *
 * @return string Plugin version
 */
function arsol_pfw_get_version() {
    return defined('ARSOL_PROJECTS_VERSION') ? ARSOL_PROJECTS_VERSION : '1.0.0';
}

/**
 * Get plugin path
 *
 * @return string Plugin path
 */
function arsol_pfw_get_plugin_path() {
    return defined('ARSOL_PROJECTS_PLUGIN_DIR') ? ARSOL_PROJECTS_PLUGIN_DIR : plugin_dir_path(__FILE__);
}

/**
 * Get plugin URL
 *
 * @return string Plugin URL
 */
function arsol_pfw_get_plugin_url() {
    return defined('ARSOL_PROJECTS_PLUGIN_URL') ? ARSOL_PROJECTS_PLUGIN_URL : plugin_dir_url(__FILE__);
}

/**
 * Check if WooCommerce is active
 *
 * @return bool
 */
function arsol_pfw_is_woocommerce_active() {
    return class_exists('WooCommerce');
}

/**
 * Check if WooCommerce Subscriptions is active
 *
 * @return bool
 */
function arsol_pfw_is_wc_subscriptions_active() {
    return class_exists('WC_Subscriptions') && function_exists('wcs_create_subscription');
}

/**
 * Get current user ID safely
 *
 * @return int Current user ID or 0 if not logged in
 */
function arsol_pfw_get_current_user_id() {
    return get_current_user_id();
}

/**
 * Check if current user is admin
 *
 * @return bool
 */
function arsol_pfw_is_admin() {
    return current_user_can('manage_options');
}

/**
 * Check if we're in admin area
 *
 * @return bool
 */
function arsol_pfw_is_admin_area() {
    return is_admin();
}

/**
 * Check if we're in AJAX request
 *
 * @return bool
 */
function arsol_pfw_is_ajax() {
    return wp_doing_ajax();
}

/**
 * Get nonce for action
 *
 * @param string $action Action name
 * @return string Nonce value
 */
function arsol_pfw_create_nonce($action) {
    return wp_create_nonce($action);
}

/**
 * Verify nonce for action
 *
 * @param string $nonce Nonce value
 * @param string $action Action name
 * @return bool
 */
function arsol_pfw_verify_nonce($nonce, $action) {
    return wp_verify_nonce($nonce, $action);
}

/**
 * Get setting value
 *
 * @param string $option_name Option name
 * @param mixed $default Default value
 * @return mixed Option value
 */
function arsol_pfw_get_setting($option_name, $default = false) {
    return get_option($option_name, $default);
}

/**
 * Update setting value
 *
 * @param string $option_name Option name
 * @param mixed $value Option value
 * @return bool Success
 */
function arsol_pfw_update_setting($option_name, $value) {
    return update_option($option_name, $value);
} 