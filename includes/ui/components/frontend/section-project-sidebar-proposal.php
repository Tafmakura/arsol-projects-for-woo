<?php
/**
 * Project Sidebar: Proposal
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */
if (!defined('ABSPATH')) {
    exit;
}

// Get proposal ID from global $post or passed variable
$proposal_id = isset($post) ? $post->ID : (isset($proposal_id) ? $proposal_id : 0);

// Prepare comprehensive data for efficient hook usage
$sidebar_data = compact('proposal_id');
?>

<?php
/**
 * Hook: arsol_pfw_sidebar_before
 * 
 * @param string $type Sidebar type: 'active', 'proposal', 'request'
 * @param array $data All sidebar data
 */
do_action('arsol_pfw_sidebar_before', 'proposal', $sidebar_data);
?>

<?php
/**
 * Hook: arsol_pfw_sidebar_fields_start
 * 
 * @param string $type Sidebar type: 'active', 'proposal', 'request'
 * @param array $data All sidebar data
 */
do_action('arsol_pfw_sidebar_fields_start', 'proposal', $sidebar_data);
?>

<p><?php esc_html_e('Information about this proposal.', 'arsol-pfw'); ?></p>

<?php
/**
 * Hook: arsol_pfw_sidebar_fields_end
 * 
 * @param string $type Sidebar type: 'active', 'proposal', 'request'
 * @param array $data All sidebar data
 */
do_action('arsol_pfw_sidebar_fields_end', 'proposal', $sidebar_data);
?>

<?php
// Add approve/reject buttons for 'under-review' status
$status_terms = wp_get_post_terms($proposal_id, 'arsol-review-status', ['fields' => 'slugs']);
$current_status = !empty($status_terms) ? $status_terms[0] : '';
if ($current_status === 'under-review') {
    $approve_url = wp_nonce_url(add_query_arg(['action' => 'arsol_approve_proposal', 'proposal_id' => $proposal_id], admin_url('admin-post.php')), 'arsol_approve_proposal_nonce');
    $reject_url = wp_nonce_url(add_query_arg(['action' => 'arsol_reject_proposal', 'proposal_id' => $proposal_id], admin_url('admin-post.php')), 'arsol_reject_proposal_nonce');
    $approve_message = esc_attr__('Are you sure you want to approve this proposal? This will convert it into a project.', 'arsol-pfw');
    $reject_message = esc_attr__('Are you sure you want to reject this proposal?', 'arsol-pfw');
    ?>
    <div class="arsol-sidebar-actions">
        <a href="<?php echo esc_url($approve_url); ?>" class="button is-success arsol-confirm-action" data-message="<?php echo $approve_message; ?>"><?php esc_html_e('Approve Proposal', 'arsol-pfw'); ?></a>
        <a href="<?php echo esc_url($reject_url); ?>" class="button is-danger arsol-confirm-action" data-message="<?php echo $reject_message; ?>"><?php esc_html_e('Reject Proposal', 'arsol-pfw'); ?></a>
    </div>
    <?php
}
?>

<?php
/**
 * Hook: arsol_pfw_sidebar_after
 * 
 * @param string $type Sidebar type: 'active', 'proposal', 'request'
 * @param array $data All sidebar data
 */
do_action('arsol_pfw_sidebar_after', 'proposal', $sidebar_data);
?>
