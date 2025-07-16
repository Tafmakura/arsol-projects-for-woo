<?php
/**
 * Project Overview Sidebar Partial
 * 
 * Clean sidebar template using CPT-specific hooks.
 * All conditional logic is handled in the classes that hook into these actions.
 *
 * @package Arsol_Projects_For_Woo
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current post info
$post_id = get_queried_object_id();
$post_type = get_post_type($post_id);

// Determine the internal post type for our system
$internal_post_type = 'project'; // Default for projects
if ($post_type === 'arsol-pfw-proposal') {
    $internal_post_type = 'proposal';
} elseif ($post_type === 'arsol-pfw-request') {
    $internal_post_type = 'request';
}

// Get status terms based on post type
$status_terms = wp_get_post_terms($post_id, $internal_post_type === 'project' ? 'arsol-pfw-project-stage' :
    ($internal_post_type === 'proposal' ? 'arsol-pfw-proposal-stage' : 'arsol-pfw-request-stage'), 
    array('fields' => 'names')
);
$current_status = !empty($status_terms) && !is_wp_error($status_terms) ? $status_terms[0] : '';
?>

<div class="project-sidebar">
    <?php 
    /**
     * Hook: arsol_pfw_{$internal_post_type}_sidebar_meta
     * 
     * Display sidebar metadata (status, dates, budget, etc.)
     * Classes hook into this with conditional logic based on status.
     * 
     * @param string $current_status The current status
     * @param int $post_id The post ID
     */
    do_action("arsol_pfw_{$internal_post_type}_sidebar_meta", $current_status, $post_id);
    ?>
    
    <?php 
    /**
     * Hook: arsol_pfw_{$internal_post_type}_sidebar_buttons
     * 
     * Display sidebar action buttons
     * Classes hook into this with conditional logic based on status.
     * 
     * @param string $current_status The current status
     * @param int $post_id The post ID
     */
    do_action("arsol_pfw_{$internal_post_type}_sidebar_buttons", $current_status, $post_id);
    ?>
</div> 