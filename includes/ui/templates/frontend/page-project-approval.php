<?php
/**
 * Project Approval Template
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the post type and ID
$type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
$post_id = 0;

if ($type === 'proposal') {
    global $wp;
    $post_id = absint($wp->query_vars['project-view-proposal']);
} elseif ($type === 'request') {
    global $wp;
    $post_id = absint($wp->query_vars['project-view-request']);
}

// Validate post ID
if (!$post_id) {
    wc_add_notice(__('Invalid ID.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
}

// Get and validate post
$post = get_post($post_id);
if (!$post) {
    wc_add_notice(__('Item not found.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
}

// Get status
$status = '';
if ($type === 'proposal') {
    $status_terms = wp_get_post_terms($post_id, 'arsol-proposal-status', array('fields' => 'names'));
    $status = !empty($status_terms) ? $status_terms[0] : '';
} elseif ($type === 'request') {
    $status_terms = wp_get_post_terms($post_id, 'arsol-request-status', array('fields' => 'names'));
    $status = !empty($status_terms) ? $status_terms[0] : '';
}

// Include the appropriate content component
$component_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-content-' . $type . '.php';
if (file_exists($component_path)) {
    // Pass the post object to the component
    $args = array('post' => $post, 'status' => $status);
    \Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
        'project_approval_' . $type,
        $component_path,
        $args
    );
} else {
    wc_add_notice(__('Template not found.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
}
