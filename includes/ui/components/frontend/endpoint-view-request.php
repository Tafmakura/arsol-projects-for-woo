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
$request = arsol_pfw_get_request($post->ID);
if (!$request) {
    echo '<p>' . esc_html__('Request not found.', 'arsol-pfw') . '</p>';
    return;
}

// Get request details using CRUD methods
$request_budget = $request->get_budget();
$request_timeline = $request->get_prop('timeline');
$request_stage = $request->get_stage();
$request_deadline = $request->get_deadline();
$request_priority = $request->get_prop('priority');

$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';

do_action('arsol_projects_before_request_state', $request->get_id());
?>

<?php
// 1. Customer Notice (first)
$customer_notice = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_customer_notice($request->get_id(), 'request');
if (!empty($customer_notice)) : ?>
    <div class="arsol-pfw-customer-notice">
        <div class="arsol-pfw-notice-content">
            <?php echo wp_kses_post(wpautop($customer_notice)); ?>
        </div>
    </div>
<?php endif; ?>

<?php
// 2. Request Status and Details
?>
<div class="arsol-pfw-request-status">
    <?php if ($request_stage) : ?>
        <p><strong><?php _e('Status:', 'arsol-pfw'); ?></strong> <?php echo esc_html(\Arsol_Projects_For_Woo\Core\Stage_Manager::get_stage_label('request', $request_stage)); ?></p>
    <?php endif; ?>
    
    <?php if ($request_budget) : ?>
        <p><strong><?php _e('Budget:', 'arsol-pfw'); ?></strong> <?php echo wp_kses_post(wc_price($request_budget)); ?></p>
    <?php endif; ?>
    
    <?php if ($request_deadline) : ?>
        <p><strong><?php _e('Deadline:', 'arsol-pfw'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($request_deadline))); ?></p>
    <?php endif; ?>
    
    <?php if ($request_priority) : ?>
        <p><strong><?php _e('Priority:', 'arsol-pfw'); ?></strong> <?php echo esc_html($request_priority); ?></p>
    <?php endif; ?>
</div>

<?php
// 3. Post Content (request description)
if (!empty($request->get_name()) && !empty($post->post_content)) : ?>
    <div class="arsol-pfw-post-content">
        <h4><?php _e('Request Details', 'arsol-pfw'); ?></h4>
        <?php echo wp_kses_post($post->post_content); ?>
    </div>
<?php endif; ?>

<?php do_action('arsol_projects_after_request_state', $request->get_id()); ?>
