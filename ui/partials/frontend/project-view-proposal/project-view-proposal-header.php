<?php
/**
 * Project View Proposal Header
 * 
 * Header section for viewing individual proposals.
 * Variables: $proposal_id, $proposal_title (optional)
 */

if (!defined('ABSPATH')) exit;

// Set variables for the unified header component
$post_id = $proposal_id;
$post_type = 'arsol-pfw-proposal';

// Ensure proposal_title is set if not provided
if (empty($proposal_title) && !empty($proposal_id)) {
    $proposal_title = get_the_title($proposal_id);
}

// Include the unified project header
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/partials/frontend/project/project-header.php';
