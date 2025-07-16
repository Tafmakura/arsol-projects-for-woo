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

// Use factory function to get request object
$request = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($post->ID);
if (!$request) {
    echo '<p>' . esc_html__('Request not found.', 'arsol-pfw') . '</p>';
    return;
}

$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';

do_action('arsol_projects_before_request_state', $request->get_id());
?>

<?php
// 1. Customer Notice (first)
$customer_notice = \Arsol_Projects_For_Woo\Admin\Setup_Defaults\Setup_Defaults::get_effective_customer_notice($request->get_id(), 'request');
if (!empty($customer_notice)) : ?>
    <div class="arsol-pfw-customer-notice">
        <div class="arsol-pfw-notice-content">
            <?php echo wp_kses_post(wpautop($customer_notice)); ?>
        </div>
    </div>
<?php endif; ?>

<?php
// 2. Post Content (request description)
if (!empty($request->get_title()) && !empty($post->post_content)) : ?>
    <div class="arsol-pfw-post-content">
        <h4><?php _e('Request Details', 'arsol-pfw'); ?></h4>
        <?php echo wp_kses_post($post->post_content); ?>
    </div>
<?php endif; ?>

<?php do_action('arsol_projects_after_request_state', $request->get_id()); ?>
