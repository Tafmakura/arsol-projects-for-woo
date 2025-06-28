<?php
/**
 * Project Sidebar Template: Proposal Status
 * Shows proposal metadata, forms, and action buttons for proposals
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get proposal data from parent template variables
// Variables available: $project_proposal_id, $current_status, $statuses, $wrapper_data
$post_id = isset($project_proposal_id) ? $project_proposal_id : get_queried_object_id();
$post_type = get_post_type($post_id);
$status = isset($current_status) ? $current_status : '';
$cpt = 'proposal'; // Internal CPT identifier

error_log("ARSOL DEBUG: Proposal sidebar - Post ID: $post_id, Post Type: $post_type, Status: '$status'");
error_log("ARSOL DEBUG: Proposal sidebar - Using variables from parent: project_proposal_id=" . (isset($project_proposal_id) ? $project_proposal_id : 'not set') . ", current_status=" . (isset($current_status) ? "'$current_status'" : 'not set'));

// Initialize status_terms for debugging
$status_terms = isset($statuses) ? $statuses : array();

// If no status from parent, try to get it directly
if (empty($status)) {
$status_terms = wp_get_post_terms($post_id, 'arsol-proposal-status');
$status = !empty($status_terms) && !is_wp_error($status_terms) ? $status_terms[0]->slug : '';
    error_log("ARSOL DEBUG: Proposal sidebar - Fallback status detection: '$status'");
}

error_log("ARSOL DEBUG: Proposal sidebar - Status terms: " . print_r($status_terms, true));
error_log("ARSOL DEBUG: Proposal sidebar - Final status: '$status'");
?>

<div class="arsol-pfw-project-sidebar">
    <?php
    /**
     * Hook: arsol_pfw_proposal_sidebar_before
     * 
     * @param string $post_type Project type: 'proposal'
     * @param string $status Current status
     * @param int $post_id Post ID
     * @param string $cpt Custom post type
     */
    do_action('arsol_pfw_proposal_sidebar_before', $post_type, $status, $post_id, $cpt);
    ?>

    <div class="arsol-pfw-sidebar-section arsol-pfw-sidebar-meta">
        <?php
        /**
         * Hook: arsol_pfw_project_proposal_sidebar_meta
         * 
         * Display sidebar metadata (status, dates, budget, etc.)
         * 
         * @param string $status The current status
         * @param int $post_id The post ID
         */
        do_action('arsol_pfw_project_proposal_sidebar_meta', $status, $post_id);
        ?>
    </div>

    <div class="arsol-pfw-sidebar-section arsol-pfw-sidebar-form">
        <?php
        /**
         * Hook: arsol_pfw_proposal_sidebar_form
         * 
         * @param string $post_type Project type: 'proposal'
         * @param string $status Current status
         * @param int $post_id Post ID
         * @param string $cpt Custom post type
         */
        do_action('arsol_pfw_proposal_sidebar_form', $post_type, $status, $post_id, $cpt);
        ?>
    </div>

    <div class="arsol-pfw-sidebar-section arsol-pfw-sidebar-buttons">
        <?php
        /**
         * Hook: arsol_pfw_project_proposal_sidebar_buttons
         * 
         * Display sidebar action buttons
         * 
         * @param string $status The current status
         * @param int $post_id The post ID
         */
        do_action('arsol_pfw_project_proposal_sidebar_buttons', $status, $post_id);
        ?>
    </div>

    <?php
    /**
     * Hook: arsol_pfw_proposal_sidebar_after
     * 
     * @param string $post_type Project type: 'proposal'
     * @param string $status Current status
     * @param int $post_id Post ID
     * @param string $cpt Custom post type
     */
    do_action('arsol_pfw_proposal_sidebar_after', $post_type, $status, $post_id, $cpt);
    ?>
    </div>
