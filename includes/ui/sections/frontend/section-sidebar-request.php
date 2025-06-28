<?php
/**
 * Project Sidebar - Requests
 *
 * @package Arsol_Projects_For_Woo
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Variables provided by parent template (project-overview.php):
// $project_id, $project_title, $current_post, $current_post_type, 
// $project_type, $current_status, $wrapper_data

$post_id = $project_id;
$post_type = $project_type;
$status = $current_status;
$cpt = 'arsol-pfw-request';

error_log("ARSOL DEBUG: Request sidebar - Post ID: $post_id, Post Type: $post_type, Status: '$status', CPT: $cpt");
error_log("ARSOL DEBUG: Request sidebar - Original project_id: $project_id, project_type: $project_type, current_status: '$current_status'");

?>

<div class="project-sidebar sidebar-request">
    <?php
    /**
     * Hook: arsol_pfw_request_sidebar_before
     * 
     * @param string $post_type Project type: 'request'
     * @param string $status Current status
     * @param int $post_id Post ID
     * @param string $cpt Custom post type
     */
    do_action('arsol_pfw_request_sidebar_before', $post_type, $status, $post_id, $cpt);
    ?>
    
    <?php
    /**
     * Hook: arsol_pfw_project_request_sidebar_meta
     * 
     * @param string $post_type Project type: 'request'
     * @param string $status Current status
     * @param int $post_id Post ID
     * @param string $cpt Custom post type
     */
    do_action('arsol_pfw_project_request_sidebar_meta', $status, $post_id);
    
    /**
     * Generic sidebar metadata section (backward compatibility)
     */
    ?>
    
    <?php
    /**
     * Hook: arsol_pfw_request_sidebar_form
     * 
     * @param string $post_type Project type: 'request'
     * @param string $status Current status
     * @param int $post_id Post ID
     * @param string $cpt Custom post type
     */
    do_action('arsol_pfw_request_sidebar_form', $post_type, $status, $post_id, $cpt);
    ?>
    
    <?php
    /**
     * Hook: arsol_pfw_project_request_sidebar_buttons
     * 
     * @param string $post_type Project type: 'request'
     * @param string $status Current status
     * @param int $post_id Post ID
     * @param string $cpt Custom post type
     */
    do_action('arsol_pfw_project_request_sidebar_buttons', $status, $post_id);
    
    /**
     * Generic sidebar secondary actions section (backward compatibility)
     */
    ?>
    
    <?php
    /**
     * Hook: arsol_pfw_request_sidebar_after
     * 
     * @param string $post_type Project type: 'request'
     * @param string $status Current status
     * @param int $post_id Post ID
     * @param string $cpt Custom post type
     */
    do_action('arsol_pfw_request_sidebar_after', $post_type, $status, $post_id, $cpt);
    ?>
</div>
