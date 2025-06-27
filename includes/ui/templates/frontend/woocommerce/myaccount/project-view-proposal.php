<?php
/**
 * Project View Proposal endpoint template
 * 
 * Handles individual proposal view at /my-account/projects/proposal/{proposal-id}/
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the proposal ID from query vars
global $wp;
$proposal_id = absint($wp->query_vars['project-view-proposal']);

// Validate proposal ID
if (!$proposal_id) {
    wc_add_notice(__('Invalid proposal ID.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
}

// Get and validate proposal
$proposal = get_post($proposal_id);
if (!$proposal || $proposal->post_type !== 'arsol-pfw-proposal') {
    wc_add_notice(__('Proposal not found.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
}

// Get current user
$user_id = get_current_user_id();

// Set type for template loading
$_GET['type'] = 'proposal';

// Set up the global post object
global $post;
$post = $proposal;
setup_postdata($post);

// --- Header Section ---
wc_get_template(
    'partials/project-view-proposal/project-view-proposal-header.php',
    compact('proposal_id', 'proposal', 'user_id'),
    'arsol-pfw/',
    ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/woocommerce/'
);

// Reset post data
wp_reset_postdata();
