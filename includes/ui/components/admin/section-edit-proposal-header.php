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

// Debug output - will be visible in the admin
echo '<!-- ARSOL DEBUG: Header template loaded for post ID: ' . $post->ID . ' -->';
echo '<div style="background: #ccffcc; padding: 5px; margin: 10px 0; font-size: 12px;">DEBUG: Header container loaded for proposal #' . $post->ID . '</div>';

// Test message - very obvious
echo '<div style="background: #ff0000; color: white; padding: 20px; margin: 20px 0; font-size: 16px; font-weight: bold; text-align: center;">
    🚨 HEADER CONTAINER IS WORKING! 🚨<br/>
    If you see this, the header system is loaded correctly!
</div>';
?>

<div class="arsol-proposal-header-container" style="margin: 20px 0;">

    <div class="arsol-proposal-header-container-inner">

            <h1><?php echo get_the_title($post->ID); ?></h1>
    </div>


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
