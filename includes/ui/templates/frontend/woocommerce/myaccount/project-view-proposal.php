<?php
/**
 * Project View Proposal endpoint template
 * 
 * Handles /my-account/project-view-proposal/{proposal_id}/ endpoint
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables should be available from endpoint handler
// $proposal_id should be available

// Validate proposal exists and user has access (done in endpoint handler)
$proposal = get_post($proposal_id);

if (!$proposal || $proposal->post_type !== 'arsol-pfw-proposal') {
    echo '<p>' . esc_html__('Proposal not found.', 'arsol-pfw') . '</p>';
    return;
}

// Set project_id for template compatibility
$project_id = $proposal_id;

// Load the proposal template
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project-proposal.php'; 