<?php

if (!defined('ABSPATH')) {
    exit;
}

// ==========================================
// ADMIN LINK HELPER FUNCTIONS
// ==========================================

/**
 * Get project edit link
 *
 * @param int $project_id Project ID
 * @return string Edit link URL
 */
function arsol_pfw_get_project_edit_link($project_id) {
    return get_edit_post_link($project_id);
}

/**
 * Get project admin link with title
 *
 * @param int $project_id Project ID
 * @return string HTML link or title
 */
function arsol_pfw_get_project_admin_link($project_id) {
    $project = arsol_pfw_get_project($project_id);
    if (!$project) {
        return '';
    }
    
    $edit_url = arsol_pfw_get_project_edit_link($project_id);
    if ($edit_url && current_user_can('edit_post', $project_id)) {
        return sprintf(
            '<a href="%s"><strong>%s</strong></a>',
            esc_url($edit_url),
            esc_html($project->get_title())
        );
    }
    
    return esc_html($project->get_title());
}

/**
 * Get proposal edit link
 *
 * @param int $proposal_id Proposal ID
 * @return string Edit link URL
 */
function arsol_pfw_get_proposal_edit_link($proposal_id) {
    return get_edit_post_link($proposal_id);
}

/**
 * Get proposal admin link with title
 *
 * @param int $proposal_id Proposal ID
 * @return string HTML link or title
 */
function arsol_pfw_get_proposal_admin_link($proposal_id) {
    $proposal = arsol_pfw_get_proposal($proposal_id);
    if (!$proposal) {
        return '';
    }
    
    $edit_url = arsol_pfw_get_proposal_edit_link($proposal_id);
    if ($edit_url && current_user_can('edit_post', $proposal_id)) {
        return sprintf(
            '<a href="%s"><strong>%s</strong></a>',
            esc_url($edit_url),
            esc_html($proposal->get_title())
        );
    }
    
    return esc_html($proposal->get_title());
}

/**
 * Get request edit link
 *
 * @param int $request_id Request ID
 * @return string Edit link URL
 */
function arsol_pfw_get_request_edit_link($request_id) {
    return get_edit_post_link($request_id);
}

/**
 * Get request admin link with title
 *
 * @param int $request_id Request ID
 * @return string HTML link or title
 */
function arsol_pfw_get_request_admin_link($request_id) {
    $request = arsol_pfw_get_request($request_id);
    if (!$request) {
        return '';
    }
    
    $edit_url = arsol_pfw_get_request_edit_link($request_id);
    if ($edit_url && current_user_can('edit_post', $request_id)) {
        return sprintf(
            '<a href="%s"><strong>%s</strong></a>',
            esc_url($edit_url),
            esc_html($request->get_title())
        );
    }
    
    return esc_html($request->get_title());
}

// ==========================================
// CUSTOMER LINK HELPER FUNCTIONS
// ==========================================

/**
 * Get customer admin filter link
 *
 * @param int $customer_id Customer ID
 * @param string $post_type Post type for filtering
 * @return string HTML link or fallback
 */
function arsol_pfw_get_customer_admin_link($customer_id, $post_type = 'arsol-pfw-project') {
    return \Arsol_Projects_For_Woo\Woocommerce::create_customer_filter_link($customer_id, $post_type);
}

/**
 * Format customer name for display
 *
 * @param int|WP_User $user User ID or user object
 * @return string Formatted customer name
 */
function arsol_pfw_format_customer_name($user) {
    return \Arsol_Projects_For_Woo\Woocommerce::format_customer_name($user);
}

/**
 * Format customer name for admin display
 *
 * @param int|WP_User $user User ID or user object
 * @return string Formatted customer name for admin
 */
function arsol_pfw_format_customer_admin_display($user) {
    return \Arsol_Projects_For_Woo\Woocommerce::format_customer_admin_display($user);
}

// ==========================================
// PROJECT LEAD HELPER FUNCTIONS
// ==========================================

/**
 * Get project lead admin filter link
 *
 * @param int $user_id User ID
 * @param string $post_type Post type for filtering
 * @return string HTML link or fallback
 */
function arsol_pfw_get_project_lead_admin_link($user_id, $post_type = 'arsol-pfw-project') {
    if (!$user_id) {
        return '<span class="na">&ndash;</span>';
    }
    
    $user = get_userdata($user_id);
    if (!$user) {
        return '<span class="na">&ndash;</span>';
    }
    
    $display_name = arsol_pfw_format_project_lead_name($user);
    
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
 * Format project lead name for display
 *
 * @param int|WP_User $user User ID or user object
 * @return string Formatted project lead name
 */
function arsol_pfw_format_project_lead_name($user) {
    if (is_numeric($user)) {
        $user = get_userdata($user);
    }
    
    if (!$user) {
        return __('Unknown User', 'arsol-pfw');
    }
    
    if (!empty($user->display_name)) {
        return $user->display_name;
    } elseif (!empty($user->first_name) || !empty($user->last_name)) {
        return trim($user->first_name . ' ' . $user->last_name);
    } else {
        return $user->user_email;
    }
}

/**
 * Format project lead name for admin display (includes email)
 *
 * @param int|WP_User $user User ID or user object
 * @return string Formatted project lead name for admin
 */
function arsol_pfw_format_project_lead_admin_display($user) {
    if (is_numeric($user)) {
        $user = get_userdata($user);
    }
    
    if (!$user) {
        return __('Unknown User', 'arsol-pfw');
    }
    
    $display_name = arsol_pfw_format_project_lead_name($user);
    
    return sprintf(
        '%s (%s)',
        $display_name,
        $user->user_email
    );
}

// ==========================================
// STAGE HELPER FUNCTIONS
// ==========================================

/**
 * Get formatted stage display
 *
 * @param string $stage_slug Stage slug
 * @param string $type Entity type (project|proposal|request)
 * @return string HTML formatted stage
 */
function arsol_pfw_get_stage_display($stage_slug, $type) {
    if (!$stage_slug) {
        $default_stages = array(
            'project' => 'not-started',
            'proposal' => 'processing',
            'request' => 'pending-review'
        );
        $stage_slug = $default_stages[$type] ?? 'unknown';
    }
    
    $stage_label = \Arsol_Projects_For_Woo\Core\Stage_Manager::get_stage_label($stage_slug, $type);
    
    return sprintf(
        '<span class="stage stage-%s">%s</span>',
        esc_attr($stage_slug),
        esc_html($stage_label)
    );
}

/**
 * Get available stages for entity type
 *
 * @param string $type Entity type (project|proposal|request)
 * @return array Available stages
 */
function arsol_pfw_get_available_stages($type) {
    return \Arsol_Projects_For_Woo\Core\Stage_Manager::get_available_stages($type);
}

/**
 * Get stage label
 *
 * @param string $stage_slug Stage slug
 * @param string $type Entity type (project|proposal|request)
 * @return string Stage label
 */
function arsol_pfw_get_stage_label($stage_slug, $type) {
    return \Arsol_Projects_For_Woo\Core\Stage_Manager::get_stage_label($stage_slug, $type);
}

// ==========================================
// BUDGET/PRICE HELPER FUNCTIONS
// ==========================================

/**
 * Format budget for display
 *
 * @param mixed $budget Budget value (float or array)
 * @return string Formatted budget
 */
function arsol_pfw_format_budget($budget) {
    if (!$budget) {
        return '<span class="na">&ndash;</span>';
    }
    
    if (is_array($budget) && isset($budget['amount'])) {
        $currency = isset($budget['currency']) ? $budget['currency'] : get_woocommerce_currency();
        return wc_price($budget['amount'], array('currency' => $currency));
    } else {
        // Legacy support for simple numeric values
        return wc_price($budget);
    }
}

// ==========================================
// UTILITY HELPER FUNCTIONS
// ==========================================

/**
 * Get formatted date for display
 *
 * @param string $date Date string
 * @param string $format Date format (default: WordPress date format)
 * @return string Formatted date
 */
function arsol_pfw_format_date($date, $format = '') {
    if (!$date) {
        return '<span class="na">&ndash;</span>';
    }
    
    if (!$format) {
        $format = get_option('date_format');
    }
    
    return date_i18n($format, strtotime($date));
}

/**
 * Get "no data" placeholder
 *
 * @return string HTML placeholder
 */
function arsol_pfw_get_no_data_placeholder() {
    return '<span class="na">&ndash;</span>';
}

/**
 * Check if user can view entity
 *
 * @param int $user_id User ID
 * @param int $entity_id Entity ID
 * @param string $entity_type Entity type (project|proposal|request)
 * @return bool Can view status
 */
function arsol_pfw_user_can_view($user_id, $entity_id, $entity_type) {
    switch ($entity_type) {
        case 'project':
            return \Arsol_Projects_For_Woo\Core\Permissions::user_can_view_project($user_id, $entity_id);
        case 'proposal':
            return \Arsol_Projects_For_Woo\Core\Permissions::user_can_view_proposal($user_id, $entity_id);
        case 'request':
            return \Arsol_Projects_For_Woo\Core\Permissions::user_can_view_request($user_id, $entity_id);
        default:
            return false;
    }
} 