<?php
/**
 * Project View Request Header
 * 
 * Header section for viewing individual requests.
 * Variables: $request_id, $request_title (optional)
 */

if (!defined('ABSPATH')) exit;

// Set variables for the reusable header component
$header_type = 'project-item';
$post_id = $request_id;
$post_type = 'arsol-pfw-request';

// Ensure request_title is set if not provided
if (empty($request_title) && !empty($request_id)) {
    $request_title = get_the_title($request_id);
}

// Include the reusable project header
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/partials/frontend/project/project-header.php';
