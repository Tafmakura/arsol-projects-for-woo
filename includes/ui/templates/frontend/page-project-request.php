<?php
/**
 * Single Project Request Template
 *
 * This template displays a single project request with its details and actions.
 * Note: Request validation is handled by the endpoint handler before this template is loaded.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// The request ID and validation are handled by the endpoint handler
// Get the request ID from query vars (already validated by endpoint handler)
global $wp;
$request_id = absint($wp->query_vars['project-view-request']);

// Get the validated request (we know it exists since we passed endpoint validation)
$request = get_post($request_id);

// Get request status
$status_terms = wp_get_post_terms($request_id, 'arsol-request-status', array('fields' => 'names'));
$status = !empty($status_terms) ? $status_terms[0] : '';

// Set type for template loading
$_GET['type'] = 'request';

// Set up the global post object
global $post;
$post = $request;
setup_postdata($post);

// Include the project template which will load the appropriate content
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project.php';

// Reset post data
wp_reset_postdata();
