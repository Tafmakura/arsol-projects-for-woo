<?php
/**
 * Project Request Content
 *
 * Shows request information about a project.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// The global $post is set up by the shortcode (same pattern as proposal template)
if (!isset($post) || !$post) {
    echo '<p>' . esc_html__('Request not found.', 'arsol-pfw') . '</p>';
    return;
}

// Get request details
$request_budget = get_post_meta($post->ID, '_arsol_pfw_request_budget', true);
$request_timeline = get_post_meta($post->ID, '_arsol_pfw_request_timeline', true);
$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';

// Get request stage (with proper error handling)
$stage_terms = wp_get_post_terms($post->ID, 'arsol-pfw-request-stage', ['fields' => 'slugs']);
$current_stage = '';
if (!is_wp_error($stage_terms) && !empty($stage_terms)) {
    $current_stage = $stage_terms[0];
}

do_action('arsol_projects_before_request_state', $post->ID);
?>

<?php
// 1. Customer Notice (first)
$customer_notice = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_customer_notice($post->ID, 'request');
if (!empty($customer_notice)) : ?>
    <div class="arsol-pfw-notice arsol-pfw-customer-notice">
        <div class="arsol-pfw-notice-header">
            <h4><?php _e('Important Notice', 'arsol-pfw'); ?></h4>
        </div>
        <div class="arsol-pfw-notice-content">
            <?php echo wp_kses_post(wpautop($customer_notice)); ?>
        </div>
    </div>
<?php endif; ?>

<?php
// 2. Post Content (second)
if (!empty($post->post_content)) : ?>
    <div class="arsol-pfw-post-content">
        <?php echo wp_kses_post($post->post_content); ?>
    </div>
<?php endif; ?>

<?php do_action('arsol_projects_after_request_state', $post->ID); ?>
