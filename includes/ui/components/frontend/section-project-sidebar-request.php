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
// Get status for action buttons
$status_terms = wp_get_post_terms($request_id, 'arsol-request-status', ['fields' => 'slugs']);
$current_status = !empty($status_terms) ? $status_terms[0] : '';
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

<?php if ($current_status === 'pending-review') : ?>
    <div class="arsol-pfw-project-action">
        <button type="submit" form="request-edit-form" class="brxe-button bricks-button button-primary request-action-btn">
            <?php esc_html_e('Update Request', 'arsol-pfw'); ?>
        </button>
    </div>
    
    <div class="arsol-pfw-project-action">
        <button type="button" class="brxe-button bricks-button sm outline bricks-color-primary cancel-request-btn" data-confirm-text="<?php esc_attr_e('Are you sure you want to cancel this request?', 'arsol-pfw'); ?>">
            <?php esc_html_e('Cancel Request', 'arsol-pfw'); ?>
        </button>
    </div>
<?php endif; ?>

<?php if ($current_status === 'under-review') : ?>
    <div class="arsol-pfw-project-action">
        <a href="/contact-us/" class="brxe-button bricks-button sm outline bricks-color-primary"><?php esc_html_e('Contact Support', 'arsol-pfw'); ?></a>
    </div>
    
    <div class="arsol-pfw-project-action">
        <a href="/services/" class="brxe-button bricks-button sm outline bricks-color-primary"><?php esc_html_e('Contact Sales', 'arsol-pfw'); ?></a>
    </div>
<?php endif; ?> 