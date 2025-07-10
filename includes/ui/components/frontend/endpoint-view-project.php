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

// Use factory function to get project object
$project = arsol_pfw_get_project($post->ID);
if (!$project) {
    echo '<p>' . esc_html__('Project not found.', 'arsol-pfw') . '</p>';
    return;
}

$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';
?>

<?php
// 1. Customer Notice (first)
$customer_notice = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_customer_notice($project->get_id(), 'project');
if (!empty($customer_notice)) : ?>
    <div class="arsol-pfw-customer-notice">
        <div class="arsol-pfw-notice-content">
            <?php echo wp_kses_post(wpautop($customer_notice)); ?>
        </div>
    </div>
<?php endif; ?>

<?php
// 2. Post Content (project description)
if (!empty($project->get_title()) && !empty($post->post_content)) : ?>
    <div class="arsol-pfw-post-content">
        <h4><?php _e('Project Details', 'arsol-pfw'); ?></h4>
        <?php echo wp_kses_post($post->post_content); ?>
    </div>
<?php endif; ?> 