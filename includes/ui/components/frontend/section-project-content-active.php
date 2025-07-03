<?php
/**
 * Project Overview Content
 *
 * Shows overview information about a project.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// The global $post is set up by the shortcode (same pattern as proposal and request templates)
if (!isset($post) || !$post) {
    echo '<p>' . esc_html__('Project not found.', 'arsol-pfw') . '</p>';
    return;
}

// Get project details
$project_budget = get_post_meta($post->ID, '_arsol_pfw_project_budget', true);
$project_timeline = get_post_meta($post->ID, '_arsol_pfw_project_timeline', true);
$related_request_id = get_post_meta($post->ID, '_arsol_pfw_related_request_id', true);
$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';
?>

<div class="arsol-pfw-project">
    <h3 class="project-title"><?php echo esc_html($post->post_title); ?></h3>

    <?php
    // Show Customer Notice unconditionally
    $customer_notice = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_customer_notice($post->ID, 'project');
    if (!empty($customer_notice)) : ?>
        <div class="arsol-pfw-customer-notice">
            <div class="arsol-pfw-notice-header">
                <h4><?php _e('Important Notice', 'arsol-pfw'); ?></h4>
            </div>
            <div class="arsol-pfw-notice-content">
                <?php echo wp_kses_post(wpautop($customer_notice)); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="arsol-pfw-project-content">
        <?php if (!empty($post->post_content)) : ?>
            <?php echo wp_kses_post($post->post_content); ?>
        <?php endif; ?>
    </div>
</div> 