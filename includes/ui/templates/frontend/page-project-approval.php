<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get the post object based on the type
$type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
$post_id = get_query_var('project-view-' . $type);

if (!$post_id) {
    wc_add_notice(__('Invalid ID.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
}

$post = get_post($post_id);
if (!$post) {
    wc_add_notice(__('Item not found.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
}

// Get status
$status = wp_get_post_terms($post_id, 'arsol-' . $type . '-status', array('fields' => 'names'));
$status = !empty($status) ? $status[0] : '';

// Include the appropriate template based on type
if ($type === 'request') {
    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-approval-request.php';
} else {
    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-approval-proposal.php';
}
