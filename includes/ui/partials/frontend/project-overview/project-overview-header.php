<?php
/**
 * Project Overview Header
 * 
 * Header section for individual project pages.
 * Variables: $post_id, $post_type, $status, $status_label
 */

if (!defined('ABSPATH')) exit;

// Set header type for individual project items
$header_type = 'project-item';

// Include the reusable project header
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/partials/frontend/project/project-header.php'; 