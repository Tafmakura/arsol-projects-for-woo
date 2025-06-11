<?php
/**
 * Admin Template: Edit Proposal Header Container
 *
 * This container appears below the title and above the WYSIWYG editor.
 * It uses action hooks to allow modular content insertion.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the current post
global $post;

if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
    return;
}
?>

<div class="arsol-proposal-header-container" style="margin: 20px 0;">
    <?php
    /**
     * Hook: arsol_proposal_header_before
     * 
     * Fires before any proposal header content
     * 
     * @param WP_Post $post The current proposal post object
     */
    do_action('arsol_proposal_header_before', $post);
    ?>

    <?php
    /**
     * Hook: arsol_proposal_header_content
     * 
     * Main content area for proposal header sections
     * 
     * @param WP_Post $post The current proposal post object
     */
    do_action('arsol_proposal_header_content', $post);
    ?>

    <?php
    /**
     * Hook: arsol_proposal_header_after
     * 
     * Fires after all proposal header content
     * 
     * @param WP_Post $post The current proposal post object
     */
    do_action('arsol_proposal_header_after', $post);
    ?>
</div>
