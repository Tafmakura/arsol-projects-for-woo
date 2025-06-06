<?php
/**
 * Single Project Request Template
 *
 * This template displays a single project request with its details and actions.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the request ID from query vars
global $wp;
$request_id = absint($wp->query_vars['project-view-request']);

// Validate request ID
if (!$request_id) {
    wc_add_notice(__('Invalid request ID.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
}

// Get and validate request
$request = get_post($request_id);
if (!$request || $request->post_type !== 'arsol-pfw-request') {
    wc_add_notice(__('Request not found.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
}

// Get request status
$status_terms = wp_get_post_terms($request_id, 'arsol-request-status', array('fields' => 'names'));
$status = !empty($status_terms) ? $status_terms[0] : '';

// Set type for template loading
$_GET['type'] = 'request';

// Include the project template which will load the appropriate content
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project.php';
