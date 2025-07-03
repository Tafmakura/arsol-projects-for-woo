<?php
/**
 * Project Sidebar - Requests
 *
 * Variables passed from endpoint class:
 * $request_id, $request (WP_Post object), $current_tab, $current_stage, $wrapper_data
 * 
 * @package Arsol_Projects_For_Woo
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

error_log("ARSOL DEBUG: Request sidebar - ID: {$request->ID}, Title: '{$request->post_title}', Type: {$request->post_type}, Stage: '$current_stage'");
?>

<div class="project-sidebar sidebar-request">
    <?php
    /**
     * Hook: arsol_pfw_request_sidebar_before
     * 
     * @param string $request->post_type Project type
     * @param string $current_stage Current stage
     * @param int $request_id Request ID
     */
    do_action('arsol_pfw_request_sidebar_before', $request->post_type, $current_stage, $request_id);
    ?>
    
    <?php
    /**
     * Hook: arsol_pfw_project_request_sidebar_meta
     * 
     * @param string $current_stage Current stage
     * @param int $request_id Request ID
     */
    do_action('arsol_pfw_project_request_sidebar_meta', $current_stage, $request_id);
    ?>
    
    <?php
    /**
     * Hook: arsol_pfw_project_request_sidebar_buttons
     * 
     * @param string $current_stage Current stage
     * @param int $request_id Request ID
     */
    do_action('arsol_pfw_project_request_sidebar_buttons', $current_stage, $request_id);
    ?>
    
    <?php
    /**
     * Hook: arsol_pfw_request_sidebar_after
     * 
     * @param string $request->post_type Project type
     * @param string $current_stage Current stage
     * @param int $request_id Request ID
     */
    do_action('arsol_pfw_request_sidebar_after', $request->post_type, $current_stage, $request_id);
    ?>
</div>
