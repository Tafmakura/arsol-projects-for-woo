<?php
/**
 * Project Sidebar - Proposals
 *
 * Variables passed from endpoint class:
 * $proposal_id, $proposal (Arsol_PFW_Proposal object), $current_tab, $current_stage, $wrapper_data
 * 
 * @package Arsol_Projects_For_Woo
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

error_log("ARSOL DEBUG: Proposal sidebar - ID: {$proposal->get_id()}, Title: '{$proposal->get_title()}', Type: {$proposal->get_post()->post_type}, Stage: '$current_stage'");
?>

<div class="project-sidebar sidebar-proposal">
    <?php
    /**
     * Hook: arsol_pfw_proposal_sidebar_before
     * 
     * @param string $proposal->post_type Project type
     * @param string $current_stage Current stage
     * @param int $proposal_id Proposal ID
     */
    do_action('arsol_pfw_proposal_sidebar_before', $proposal->get_post()->post_type, $current_stage, $proposal_id);
    ?>

        <?php
        /**
         * Hook: arsol_pfw_project_proposal_sidebar_meta
         * 
     * @param string $current_stage Current stage
     * @param int $proposal_id Proposal ID
     * @param object $proposal Proposal entity instance
         */
    do_action('arsol_pfw_project_proposal_sidebar_meta', $current_stage, $proposal_id, $proposal);
        ?>
    
        <?php
        /**
         * Hook: arsol_pfw_project_proposal_sidebar_buttons
         * 
     * @param string $current_stage Current stage
     * @param int $proposal_id Proposal ID
         */
    do_action('arsol_pfw_project_proposal_sidebar_buttons', $current_stage, $proposal_id);
        ?>

    <?php
    /**
     * Hook: arsol_pfw_proposal_sidebar_after
     * 
     * @param string $proposal->post_type Project type
     * @param string $current_stage Current stage
     * @param int $proposal_id Proposal ID
     */
    do_action('arsol_pfw_proposal_sidebar_after', $proposal->get_post()->post_type, $current_stage, $proposal_id);
    ?>
    </div>