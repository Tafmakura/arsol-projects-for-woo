<?php
/**
 * Project Sidebar - Proposals
 *
 * @package Arsol_Projects_For_Woo
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$post_id = get_the_ID();
$post_type = 'proposal';

// Get actual proposal status from taxonomy
$status_terms = wp_get_object_terms($post_id, 'arsol-proposal-status', array('fields' => 'slugs'));
$current_status = !empty($status_terms) ? $status_terms[0] : 'processing';

// Debug: uncomment to see what status is detected
// error_log("Proposal $post_id status: " . $current_status . " (terms: " . print_r($status_terms, true) . ")");

?>

<div class="project-sidebar sidebar-proposal">
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
