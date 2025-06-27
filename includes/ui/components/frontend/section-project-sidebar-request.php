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

$post_id = get_the_ID();
$post_type = 'request';

// Get actual request status from taxonomy
$status_terms = wp_get_object_terms($post_id, 'arsol-request-status', array('fields' => 'slugs'));
$current_status = !empty($status_terms) ? $status_terms[0] : 'pending-review';

?>

<div class="project-sidebar sidebar-request">
    <?php
    /**
     * Sidebar metadata section
     */
    do_action('arsol_pfw_sidebar_meta', $post_type, $current_status, $post_id);
    
    /**
     * Sidebar form section (unified form with filterable fields)
     */
    do_action('arsol_pfw_sidebar_form', $post_type, $current_status, $post_id);
    
    /**
     * Sidebar secondary actions section
     */
    do_action('arsol_pfw_sidebar_actions', $post_type, $current_status, $post_id);
    ?>
</div>
