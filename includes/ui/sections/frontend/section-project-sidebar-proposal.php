<?php
/**
 * Project Sidebar Template: Proposal Status
 * Shows proposal metadata, forms, and action buttons for proposals
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get proposal data
$post_id = get_queried_object_id();
$post_type = get_post_type($post_id);
$cpt = 'proposal'; // Internal CPT identifier

// Get current status
$status_terms = wp_get_post_terms($post_id, 'arsol-proposal-status');
$status = !empty($status_terms) && !is_wp_error($status_terms) ? $status_terms[0]->slug : '';
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
        
        /**
         * Generic sidebar form section (backward compatibility)
         */
        do_action('arsol_pfw_sidebar_form', $post_type, $status, $post_id);
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
