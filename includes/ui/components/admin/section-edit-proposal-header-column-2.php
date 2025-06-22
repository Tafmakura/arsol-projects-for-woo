<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
    return;
}

$proposal_id = $post->ID;
$original_request_id = get_post_meta($proposal_id, '_arsol_pfw_proposal_request_id', true);
$original_request_budget = get_post_meta($proposal_id, '_arsol_pfw_proposal_request_budget', true);
$original_request_start_date = get_post_meta($proposal_id, '_arsol_pfw_proposal_request_start_date', true);
$original_request_delivery_date = get_post_meta($proposal_id, '_arsol_pfw_proposal_request_delivery_date', true);

$has_original_data = $original_request_id || $original_request_budget || $original_request_start_date || $original_request_delivery_date;
?>

<?php if ($has_original_data): ?>
    <?php include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/subsection-edit-project-proposal-details.php'; ?>
<?php else: ?>
    <p><?php _e('This proposal was created directly without an initial customer request.', 'arsol-pfw'); ?></p>
<?php endif; ?> 