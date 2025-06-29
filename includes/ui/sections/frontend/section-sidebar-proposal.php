<?php
/**
 * Project Sidebar Template: Proposal Status
 * Shows proposal metadata, forms, and action buttons for proposals
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get proposal data from parent template variables
// Variables available: $project_proposal_id, $current_stage, $stages, $wrapper_data
$post_id = isset($project_proposal_id) ? $project_proposal_id : get_queried_object_id();
$post_type = get_post_type($post_id);
$stage = isset($current_stage) ? $current_stage : '';
$cpt = 'proposal'; // Internal CPT identifier

error_log("ARSOL DEBUG: Proposal sidebar - Post ID: $post_id, Post Type: $post_type, Stage: '$stage'");
error_log("ARSOL DEBUG: Proposal sidebar - Using variables from parent: project_proposal_id=" . (isset($project_proposal_id) ? $project_proposal_id : 'not set') . ", current_stage=" . (isset($current_stage) ? "'$current_stage'" : 'not set'));

// Initialize stage_terms for debugging
$stage_terms = isset($stages) ? $stages : array();

// If no stage from parent, try to get it directly
if (empty($stage)) {
    $stage_terms = wp_get_post_terms($post_id, 'arsol-pfw-proposal-stage');
    $current_stage = '';
    if (!is_wp_error($stage_terms) && !empty($stage_terms)) {
        $current_stage = $stage_terms[0]->slug;
        $stage = $current_stage;
    }
    error_log("ARSOL DEBUG: Proposal sidebar - Fallback stage detection: '$current_stage'");
}

error_log("ARSOL DEBUG: Proposal sidebar - Stage terms: " . print_r($stage_terms, true));
error_log("ARSOL DEBUG: Proposal sidebar - Final stage: '$stage'");
?>

<div class="arsol-pfw-project-sidebar">
    <?php
    /**
     * Hook: arsol_pfw_proposal_sidebar_before
     * 
     * @param string $post_type Project type: 'proposal'
     * @param string $stage Current stage
     * @param int $post_id Post ID
     * @param string $cpt Custom post type
     */
    do_action('arsol_pfw_proposal_sidebar_before', $post_type, $stage, $post_id, $cpt);
    ?>

    <div class="arsol-pfw-sidebar-section arsol-pfw-sidebar-meta">
        <?php
        /**
         * Hook: arsol_pfw_project_proposal_sidebar_meta
         * 
         * Display sidebar metadata (stage, dates, budget, etc.)
         * 
         * @param string $stage The current stage
         * @param int $post_id The post ID
         */
        do_action('arsol_pfw_project_proposal_sidebar_meta', $stage, $post_id);
        ?>
    </div>

    <div class="arsol-pfw-sidebar-section arsol-pfw-sidebar-form">
        <?php
        /**
         * Hook: arsol_pfw_proposal_sidebar_form
         * 
         * @param string $post_type Project type: 'proposal'
         * @param string $stage Current stage
         * @param int $post_id Post ID
         * @param string $cpt Custom post type
         */
        do_action('arsol_pfw_proposal_sidebar_form', $post_type, $stage, $post_id, $cpt);
        ?>
    </div>

    <div class="arsol-pfw-sidebar-section arsol-pfw-sidebar-buttons">
        <?php
        /**
         * Hook: arsol_pfw_project_proposal_sidebar_buttons
         * 
         * Display sidebar action buttons
         * 
         * @param string $stage The current stage
         * @param int $post_id The post ID
         */
        do_action('arsol_pfw_project_proposal_sidebar_buttons', $stage, $post_id);
        ?>
    </div>

    <?php
    /**
     * Hook: arsol_pfw_proposal_sidebar_after
     * 
     * @param string $post_type Project type: 'proposal'
     * @param string $stage Current stage
     * @param int $post_id Post ID
     * @param string $cpt Custom post type
     */
    do_action('arsol_pfw_proposal_sidebar_after', $post_type, $stage, $post_id, $cpt);
    ?>
    </div>
