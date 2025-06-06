<?php
/**
 * Project Sidebar
 *
 * This template displays the sidebar on a single project page.
 * It shows project details like status, budget, and key dates.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
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
?>

<div class="project-meta">
    <p>
        <strong><?php _e('Status:', 'arsol-pfw'); ?></strong>
        <span><?php echo esc_html($status); ?></span>
    </p>
    <?php if ($budget): ?>
    <p>
        <strong><?php _e('Budget:', 'arsol-pfw'); ?></strong>
        <span><?php echo wc_price($budget); ?></span>
    </p>
    <?php endif; ?>
    <p>
        <strong><?php _e('Start Date:', 'arsol-pfw'); ?></strong>
        <span><?php echo esc_html($start_date_formatted); ?></span>
    </p>
    <p>
        <strong><?php _e('Delivery Date:', 'arsol-pfw'); ?></strong>
        <span><?php echo esc_html($delivery_date_formatted); ?></span>
    </p>
</div> 