<?php
/**
 * Project Sidebar - Requests
 *
 * Variables passed from endpoint class:
 * $request_id, $request (Arsol_PFW_Request object), $current_tab, $current_stage, $wrapper_data
 * 
 * @package Arsol_Projects_For_Woo
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

error_log("ARSOL DEBUG: Request sidebar - ID: {$request->get_id()}, Title: '{$request->get_title()}', Type: {$request->get_post()->post_type}, Stage: '$current_stage'");
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
    do_action('arsol_pfw_request_sidebar_before', $request->get_post()->post_type, $current_stage, $request_id);
    ?>
    
    <?php
    /**
     * Hook: arsol_pfw_project_request_sidebar_meta
     * 
     * @param string $current_stage Current stage
     * @param int $request_id Request ID
     * @param object $request Request entity instance
     */
    do_action('arsol_pfw_project_request_sidebar_meta', $current_stage, $request_id, $request);
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
    do_action('arsol_pfw_request_sidebar_after', $request->get_post()->post_type, $current_stage, $request_id);
    ?>
</div>
