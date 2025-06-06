<?php
/**
 * Single Project Proposal Template
 *
 * This template displays a single project proposal with its details and actions.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
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

// Get proposal status
$status_terms = wp_get_post_terms($proposal_id, 'arsol-proposal-status', array('fields' => 'names'));
$status = !empty($status_terms) ? $status_terms[0] : '';

// Set type for template loading
$_GET['type'] = 'proposal';

// Include the project template which will load the appropriate content
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-project.php';
