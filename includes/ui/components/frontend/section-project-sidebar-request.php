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

<p><?php esc_html_e('Information about this request.', 'arsol-pfw'); ?></p>

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
// Add cancel button for 'pending' status
$status_terms = wp_get_post_terms($request_id, 'arsol-request-status', ['fields' => 'slugs']);
$current_status = !empty($status_terms) ? $status_terms[0] : '';
if ($current_status === 'pending') {
    $cancel_url = wp_nonce_url(add_query_arg(['action' => 'arsol_cancel_request', 'request_id' => $request_id], admin_url('admin-post.php')), 'arsol_cancel_request_nonce');
    $confirm_message = esc_attr__('Are you sure you want to cancel this request? This action cannot be undone.', 'arsol-pfw');
    ?>
    <div class="arsol-sidebar-actions">
        <a href="<?php echo esc_url($cancel_url); ?>" class="button is-danger arsol-confirm-action" data-message="<?php echo $confirm_message; ?>"><?php esc_html_e('Cancel Request', 'arsol-pfw'); ?></a>
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
do_action('arsol_pfw_sidebar_after', 'request', $sidebar_data);
?> 