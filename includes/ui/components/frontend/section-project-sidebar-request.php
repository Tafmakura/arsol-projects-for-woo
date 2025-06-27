<?php
/**
 * Project Sidebar: Request
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */
if (!defined('ABSPATH')) {
    exit;
}

// Get request ID from global $post or passed variable
$request_id = isset($post) ? $post->ID : (isset($request_id) ? $request_id : 0);

// Prepare comprehensive data for efficient hook usage
$sidebar_data = compact('request_id');
?>

<?php
/**
 * Hook: arsol_pfw_sidebar_before
 * 
 * @param string $type Sidebar type: 'active', 'proposal', 'request'
 * @param array $data All sidebar data
 */
do_action('arsol_pfw_sidebar_before', 'request', $sidebar_data);
?>

<?php
/**
 * Hook: arsol_pfw_sidebar_fields_start
 * 
 * @param string $type Sidebar type: 'active', 'proposal', 'request'
 * @param array $data All sidebar data
 */
do_action('arsol_pfw_sidebar_fields_start', 'request', $sidebar_data);
?>

<?php
/**
 * Hook: arsol_pfw_sidebar_fields_end
 * 
 * @param string $type Sidebar type: 'active', 'proposal', 'request'
 * @param array $data All sidebar data
 */
do_action('arsol_pfw_sidebar_fields_end', 'request', $sidebar_data);
?>

<?php
// Get status for action buttons and metadata
$status_terms = wp_get_post_terms($request_id, 'arsol-request-status', ['fields' => 'slugs']);
$current_status = !empty($status_terms) ? $status_terms[0] : '';

// Get request metadata
$request_budget = get_post_meta($request_id, '_arsol_pfw_request_budget', true);
$start_date = get_post_meta($request_id, '_arsol_pfw_request_start_date', true);
$delivery_date = get_post_meta($request_id, '_arsol_pfw_request_delivery_date', true);
?>

<?php if ($request_budget || $start_date || $delivery_date) : ?>
<div class="arsol-pfw-project-meta">
    <?php if (!empty($request_budget) && is_array($request_budget)) : ?>
        <p><strong><?php _e('Budget:', 'arsol-pfw'); ?></strong> <?php echo wc_price($request_budget['amount'], array('currency' => $request_budget['currency'])); ?></p>
    <?php endif; ?>
    
    <?php if ($start_date) : ?>
        <p><strong><?php _e('Requested Start Date:', 'arsol-pfw'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($start_date))); ?></p>
    <?php endif; ?>
    
    <?php if ($delivery_date) : ?>
        <p><strong><?php _e('Requested Delivery Date:', 'arsol-pfw'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($delivery_date))); ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
/**
 * Hook: arsol_pfw_sidebar_after
 * 
 * @param string $type Sidebar type: 'active', 'proposal', 'request'
 * @param array $data All sidebar data
 */
do_action('arsol_pfw_sidebar_after', 'request', $sidebar_data);
?>

<?php if ($current_status === 'pending-review') : ?>
    <?php include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-sidebar-request-pending-review.php'; ?>
<?php endif; ?>

<?php if ($current_status === 'on-hold') : ?>
    <?php include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-sidebar-request-on-hold.php'; ?>
<?php endif; ?>

<?php if ($current_status === 'under-review') : ?>
    <?php include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-sidebar-request-under-review.php'; ?>
<?php endif; ?>

<?php if ($current_status === 'approved') : ?>
    <?php include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-sidebar-request-approved.php'; ?>
<?php endif; ?>

