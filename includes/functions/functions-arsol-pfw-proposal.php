<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get proposal by ID
 *
 * @param int $proposal_id Proposal ID
 * @return \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Project_Proposal_CPT|false
 */
function arsol_pfw_get_proposal($proposal_id) {
    if (!$proposal_id) {
        return false;
    }
    
    $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Project_Proposal_CPT($proposal_id);
    return $proposal->get_id() ? $proposal : false;
}

/**
 * Create new proposal
 *
 * @param array $args Proposal arguments
 * @return \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Project_Proposal_CPT|WP_Error
 */
function arsol_pfw_create_proposal($args = array()) {
    $defaults = array(
        'name'        => '',
        'customer_id' => 0,
        'budget'      => 0,
        'description' => '',
        'quotation'   => array(),
        'stage'       => 'processing',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Validate required fields
    if (empty($args['name'])) {
        return new WP_Error('missing_name', 'Proposal name is required');
    }
    
    if (empty($args['customer_id'])) {
        return new WP_Error('missing_customer', 'Customer ID is required');
    }
    
    $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Project_Proposal_CPT();
    
    // Set properties
    $proposal->set_name($args['name']);
    $proposal->set_customer_id($args['customer_id']);
    $proposal->set_budget($args['budget']);
    $proposal->set_quotation($args['quotation']);
    $proposal->set_prop('description', $args['description']);
    $proposal->set_stage($args['stage']);
    
    return $proposal;
}

/**
 * Get proposals by stage
 *
 * @param string $stage Stage slug
 * @param array $args Query arguments
 * @return array
 */
function arsol_pfw_get_proposals_by_stage($stage, $args = array()) {
    return \Arsol_Projects_For_Woo\Core\Stage_Manager::get_entities_by_stage('proposal', $stage, $args);
}

/**
 * Get proposal stage counts
 *
 * @return array
 */
function arsol_pfw_get_proposal_stage_counts() {
    return \Arsol_Projects_For_Woo\Core\Stage_Manager::get_stage_counts('proposal');
}

/**
 * Get available proposal stages
 *
 * @return array
 */
function arsol_pfw_get_proposal_available_stages() {
    return \Arsol_Projects_For_Woo\Core\Stage_Manager::get_available_stages('proposal');
}

/**
 * Update proposal stage
 *
 * @param int $proposal_id Proposal ID
 * @param string $new_stage New stage slug
 * @return bool|WP_Error
 */
function arsol_pfw_update_proposal_stage($proposal_id, $new_stage) {
    return \Arsol_Projects_For_Woo\Core\Stage_Manager::update_stage($proposal_id, 'proposal', $new_stage);
}

/**
 * Approve proposal
 *
 * @param int $proposal_id Proposal ID
 * @return bool|WP_Error
 */
function arsol_pfw_approve_proposal($proposal_id) {
    $proposal = arsol_pfw_get_proposal($proposal_id);
    if (!$proposal) {
        return new WP_Error('invalid_proposal', 'Proposal not found');
    }
    
    return $proposal->approve();
}

/**
 * Reject proposal
 *
 * @param int $proposal_id Proposal ID
 * @param string $reason Rejection reason
 * @return bool|WP_Error
 */
function arsol_pfw_reject_proposal($proposal_id, $reason = '') {
    $proposal = arsol_pfw_get_proposal($proposal_id);
    if (!$proposal) {
        return new WP_Error('invalid_proposal', 'Proposal not found');
    }
    
    return $proposal->reject($reason);
}

/**
 * Mark proposal as expired
 *
 * @param int $proposal_id Proposal ID
 * @return bool|WP_Error
 */
function arsol_pfw_expire_proposal($proposal_id) {
    $proposal = arsol_pfw_get_proposal($proposal_id);
    if (!$proposal) {
        return new WP_Error('invalid_proposal', 'Proposal not found');
    }
    
    return $proposal->mark_expired();
}

/**
 * Get proposals by customer
 *
 * @param int $customer_id Customer ID
 * @param array $args Query arguments
 * @return array
 */
function arsol_pfw_get_proposals_by_customer($customer_id, $args = array()) {
    $default_args = array(
        'post_type'   => 'arsol-pfw-proposal',
        'post_status' => 'publish',
        'author'      => $customer_id,
        'posts_per_page' => -1,
    );
    
    $args = wp_parse_args($args, $default_args);
    return get_posts($args);
} 