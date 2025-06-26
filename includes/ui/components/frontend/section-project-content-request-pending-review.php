<?php
/**
 * Project Request Content Template: Pending Review Status
 * Shows the edit form for requests that are pending review
 */

if (!defined('ABSPATH')) {
    exit;
}

// Show edit form for pending-review requests using shortcode
echo do_shortcode('[arsol_pfw_project_request_form is_edit="true" post_id="' . $post->ID . '"]'); 