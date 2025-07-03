<?php
/**
 * Project Request Content Template: Pending Review Status
 * Shows the edit form for requests that are pending review
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="arsol-pfw-request">
    <div class="arsol-pfw-header">
        <h3 class="arsol-pfw-title"><?php echo esc_html($post->post_title); ?></h3>
    </div>

    <div class="arsol-pfw-content-wrapper">
        <div class="arsol-pfw-content">
            <div class="arsol-pfw-post-content">
                <?php
                // Show edit form for pending-review requests using shortcode
                echo do_shortcode('[arsol_pfw_project_request_form is_edit="true" post_id="' . $post->ID . '"]');
                ?>
            </div>
        </div>
    </div>
</div> 