<?php
/**
 * Project Proposal Content
 *
 * Shows proposal information about a project.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// The global $post is set up by the shortcode (same pattern as request template)
if (!isset($post) || !$post) {
    echo '<p>' . esc_html__('Proposal not found.', 'arsol-pfw') . '</p>';
    return;
}

// Get proposal details
$proposal_budget = get_post_meta($post->ID, '_arsol_pfw_proposal_budget', true);
$proposal_timeline = get_post_meta($post->ID, '_arsol_pfw_proposal_timeline', true);
$related_request_id = get_post_meta($post->ID, '_arsol_pfw_related_request_id', true);
$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';
?>
        
        <?php
// 1. Customer Notice (first)
        $customer_notice = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_customer_notice($post->ID, 'proposal');
        if (!empty($customer_notice)) : ?>
    <div class="arsol-pfw-customer-notice">
                <div class="arsol-pfw-notice-content">
                    <?php echo wp_kses_post(wpautop($customer_notice)); ?>
                </div>
            </div>
        <?php endif; ?>

<?php
// 2. Post Content (second)
if (!empty($post->post_content)) : ?>
    <div class="arsol-pfw-post-content">
        <h4><?php _e('Proposal Details', 'arsol-pfw'); ?></h4>
        <?php echo wp_kses_post($post->post_content); ?>
    </div>
<?php endif; ?>
