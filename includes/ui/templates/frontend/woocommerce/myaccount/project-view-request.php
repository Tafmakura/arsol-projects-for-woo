<?php
/**
 * Project View Request endpoint template
 * 
 * Handles /my-account/project-view-request/{request_id}/ endpoint
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables should be available from endpoint handler
// $request_id should be available

// Validate request exists and user has access (done in endpoint handler)
$request = get_post($request_id);

if (!$request || $request->post_type !== 'arsol-pfw-request') {
    echo '<p>' . esc_html__('Request not found.', 'arsol-pfw') . '</p>';
    return;
}

// Set project_id for template compatibility
$project_id = $request_id;

// Load the request template
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project-request.php'; 