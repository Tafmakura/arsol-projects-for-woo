<?php
/**
 * Single Project Proposal Template
 *
 * This template displays a single project proposal with its details and actions.
 * Note: Proposal validation is handled by the endpoint handler before this template is loaded.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// The proposal ID and validation are handled by the endpoint handler
// Get the proposal ID from query vars (already validated by endpoint handler)
global $wp;
$proposal_id = absint($wp->query_vars['project-view-proposal']);

// Get the validated proposal (we know it exists since we passed endpoint validation)
$proposal = get_post($proposal_id);

// Get proposal status
$post_status = get_post_status($proposal->ID);
$status = '';
if ($post_status === 'draft') {
    $status = __('Draft', 'arsol-pfw');
} else {
    $review_status_terms = wp_get_post_terms($proposal->ID, 'arsol-review-status', array('fields' => 'names'));
    if (!is_wp_error($review_status_terms) && !empty($review_status_terms)) {
        $status = $review_status_terms[0];
    } else {
        $status = __('Published', 'arsol-pfw');
    }
}

// Set type for template loading
$_GET['type'] = 'proposal';

// Set up the global post object
global $post;
$post = $proposal;
setup_postdata($post);

// Include the project template which will load the appropriate content
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project.php';

// Reset post data
wp_reset_postdata();

?>
<section class="arsol-project-section">
    <h2><?php _e('Notes', 'arsol-pfw'); ?></h2>
    <div class="arsol-project-section-content">
        <?php
        $notes = get_post_meta($proposal_id, '_arsol_proposal_notes', true);
        if (!empty($notes)) :
            echo wpautop(wp_kses_post($notes));
        endif;
        ?>
    </div>
</section>

<?php
// Action hook for adding custom content after the proposal details
do_action('arsol_after_project_proposal_details', $proposal_id);
