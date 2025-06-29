<?php
/**
 * Project Sidebar - Requests
 *
 * Variables passed from endpoint class:
 * $project_request_id, $project_request (WP_Post object), $current_tab, $current_stage, $wrapper_data
 * 
 * @package Arsol_Projects_For_Woo
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

error_log("ARSOL DEBUG: Request sidebar - ID: {$project_request->ID}, Title: '{$project_request->post_title}', Type: {$project_request->post_type}, Stage: '$current_stage'");
?>

<div class="project-sidebar sidebar-request">
    <?php
    /**
     * Hook: arsol_pfw_request_sidebar_before
     * 
     * @param string $project_request->post_type Project type
     * @param string $current_stage Current stage
     * @param int $project_request_id Request ID
     */
    do_action('arsol_pfw_request_sidebar_before', $project_request->post_type, $current_stage, $project_request_id);
    ?>
    
    <?php
    /**
     * Hook: arsol_pfw_project_request_sidebar_meta
     * 
     * @param string $current_stage Current stage
     * @param int $project_request_id Request ID
     */
    do_action('arsol_pfw_project_request_sidebar_meta', $current_stage, $project_request_id);
    ?>
    
    <?php
    /**
     * Hook: arsol_pfw_project_request_sidebar_buttons
     * 
     * @param string $current_stage Current stage
     * @param int $project_request_id Request ID
     */
    do_action('arsol_pfw_project_request_sidebar_buttons', $current_stage, $project_request_id);
    ?>
    
    <?php
    /**
     * Hook: arsol_pfw_request_sidebar_after
     * 
     * @param string $project_request->post_type Project type
     * @param string $current_stage Current stage
     * @param int $project_request_id Request ID
     */
    do_action('arsol_pfw_request_sidebar_after', $project_request->post_type, $current_stage, $project_request_id);
    ?>
</div>
