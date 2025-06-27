<?php
/**
 * Project Sidebar - Active Projects
 * 
 * @package Arsol_Projects_For_Woo
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Use the post ID and status from the main template context if available
// Otherwise fall back to getting them directly
$post_id = isset($project_id) ? $project_id : get_the_ID();
$post_type = 'active';

// Use the status from main template context if available, otherwise get it from taxonomy
if (isset($current_status)) {
    $status = $current_status;
} else {
    $status_terms = wp_get_object_terms($post_id, 'arsol-project-status', array('fields' => 'slugs'));
    $status = !empty($status_terms) ? $status_terms[0] : '';
}

// Debug: uncomment to see what status is detected
// error_log("Active project sidebar - Post: $post_id, Status: $status");

?>

<div class="project-sidebar sidebar-active">
    <?php
    /**
     * Sidebar metadata section
     */
    do_action('arsol_pfw_sidebar_meta', $post_type, $status, $post_id);
    
    /**
     * Sidebar form section (unified form with filterable fields)
     */
    do_action('arsol_pfw_sidebar_form', $post_type, $status, $post_id);
    
    /**
     * Sidebar secondary actions section
     */
    do_action('arsol_pfw_sidebar_actions', $post_type, $status, $post_id);
    ?>
</div>
