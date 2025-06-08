<?php
/**
 * Project Sidebar: Active Project
 *
 * This template displays the sidebar on a single project page.
 * It shows project details like status, budget, and key dates.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// The $project_id variable is passed from page-project-active.php
if (!isset($project_id)) {
    return;
}

// Get project meta data
$budget = get_post_meta($project_id, '_project_budget', true);
$start_date = get_post_meta($project_id, '_project_start_date', true);
$delivery_date = get_post_meta($project_id, '_project_delivery_date', true);
$status_terms = wp_get_post_terms($project_id, 'arsol-project-status', array('fields' => 'names'));
$status = !empty($status_terms) ? $status_terms[0] : __('N/A', 'arsol-pfw');

// Format dates
$start_date_formatted = $start_date ? date_i18n(get_option('date_format'), strtotime($start_date)) : __('N/A', 'arsol-pfw');
$delivery_date_formatted = $delivery_date ? date_i18n(get_option('date_format'), strtotime($delivery_date)) : __('N/A', 'arsol-pfw');

// Prepare comprehensive data for efficient hook usage
$sidebar_data = compact(
    'project_id', 'budget', 'start_date', 'delivery_date', 'status',
    'start_date_formatted', 'delivery_date_formatted'
);
?>

<?php
/**
 * Hook: arsol_pfw_sidebar_before
 * 
 * @param string $type Sidebar type: 'active', 'proposal', 'request'
 * @param array $data All sidebar data
 */
do_action('arsol_pfw_sidebar_before', 'active', $sidebar_data);
?>

<?php
/**
 * Hook: arsol_pfw_sidebar_fields_start
 * 
 * @param string $type Sidebar type: 'active', 'proposal', 'request'
 * @param array $data All sidebar data
 */
do_action('arsol_pfw_sidebar_fields_start', 'active', $sidebar_data);
?>

<div class="arsol-pfw-project-meta">
    <p><strong><?php _e('Status:', 'arsol-pfw'); ?></strong> <?php echo esc_html($status); ?></p>
    
    <?php if ($budget): ?>
        <p><strong><?php _e('Budget:', 'arsol-pfw'); ?></strong> <?php echo wc_price($budget); ?></p>
    <?php endif; ?>
    
    <p><strong><?php _e('Start Date:', 'arsol-pfw'); ?></strong> <?php echo esc_html($start_date_formatted); ?></p>
    
    <p><strong><?php _e('Delivery Date:', 'arsol-pfw'); ?></strong> <?php echo esc_html($delivery_date_formatted); ?></p>
</div>

<?php
/**
 * Hook: arsol_pfw_sidebar_fields_end
 * 
 * @param string $type Sidebar type: 'active', 'proposal', 'request'
 * @param array $data All sidebar data
 */
do_action('arsol_pfw_sidebar_fields_end', 'active', $sidebar_data);
?>

<?php
/**
 * Hook: arsol_pfw_sidebar_after
 * 
 * @param string $type Sidebar type: 'active', 'proposal', 'request'
 * @param array $data All sidebar data
 */
do_action('arsol_pfw_sidebar_after', 'active', $sidebar_data);
?> 