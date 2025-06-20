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
// Get status and metadata
$status_terms = wp_get_post_terms($proposal_id, 'arsol-review-status', ['fields' => 'slugs']);
$current_status = !empty($status_terms) ? $status_terms[0] : '';

// Get proposal metadata
$proposal_budget = get_post_meta($proposal_id, '_proposal_budget', true);
$start_date = get_post_meta($proposal_id, '_proposal_start_date', true);
$delivery_date = get_post_meta($proposal_id, '_proposal_delivery_date', true);
$expiration_date = get_post_meta($proposal_id, '_proposal_expiration_date', true);
$timeline = get_post_meta($proposal_id, '_proposal_timeline', true);
?>

<?php if ($proposal_budget || $start_date || $delivery_date || $expiration_date || $timeline) : ?>
<div class="arsol-pfw-project-meta">
    <?php if (!empty($proposal_budget)) : ?>
        <p><strong><?php _e('Proposed Budget:', 'arsol-pfw'); ?></strong> <?php echo wc_price($proposal_budget); ?></p>
    <?php endif; ?>
    
    <?php if ($start_date) : ?>
        <p><strong><?php _e('Proposed Start Date:', 'arsol-pfw'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($start_date))); ?></p>
    <?php endif; ?>
    
    <?php if ($delivery_date) : ?>
        <p><strong><?php _e('Proposed Delivery Date:', 'arsol-pfw'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($delivery_date))); ?></p>
    <?php endif; ?>
    
    <?php if ($expiration_date) : ?>
        <p><strong><?php _e('Proposal Expiration Date:', 'arsol-pfw'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($expiration_date))); ?></p>
    <?php endif; ?>
    
    <?php if ($timeline) : ?>
        <p><strong><?php _e('Timeline:', 'arsol-pfw'); ?></strong> <?php echo esc_html($timeline); ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($current_status === 'under-review') : ?>
    <div class="arsol-pfw-project-action">
        <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(['action' => 'arsol_approve_proposal', 'proposal_id' => $proposal_id], admin_url('admin-post.php')), 'arsol_approve_proposal_nonce')); ?>" class="brxe-button bricks-button button-primary arsol-confirm-action" data-message="<?php esc_attr_e('Are you sure you want to approve this proposal? This will convert it into a project.', 'arsol-pfw'); ?>">
            <?php esc_html_e('Approve Proposal', 'arsol-pfw'); ?>
        </a>
    </div>
    
    <div class="arsol-pfw-project-action">
        <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(['action' => 'arsol_reject_proposal', 'proposal_id' => $proposal_id], admin_url('admin-post.php')), 'arsol_reject_proposal_nonce')); ?>" class="brxe-button bricks-button sm outline bricks-color-primary arsol-confirm-action" data-message="<?php esc_attr_e('Are you sure you want to reject this proposal?', 'arsol-pfw'); ?>">
            <?php esc_html_e('Reject Proposal', 'arsol-pfw'); ?>
        </a>
    </div>
<?php endif; ?>

<?php
/**
 * Hook: arsol_pfw_sidebar_after
 * 
 * @param string $type Sidebar type: 'active', 'proposal', 'request'
 * @param array $data All sidebar data
 */
do_action('arsol_pfw_sidebar_after', 'proposal', $sidebar_data);
?>
