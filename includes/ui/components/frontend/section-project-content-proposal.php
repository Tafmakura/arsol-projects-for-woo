<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get proposal details
$proposal_budget = get_post_meta($post->ID, '_arsol_pfw_proposal_budget_onetime_amount', true);
$proposal_timeline = get_post_meta($post->ID, '_arsol_pfw_proposal_timeline', true);
$related_request_id = get_post_meta($post->ID, '_arsol_pfw_proposal_request_id', true);
$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';
?>

<div class="project-content-wrapper">
    <div class="project-content">
        <h3 class="project-title"><?php echo esc_html($post->post_title); ?></h3>
        <div class="project-description">
            <?php if (!empty($post->post_content)) : ?>
                <?php echo wp_kses_post($post->post_content); ?>
            <?php endif; ?>
        </div>
        
        <?php
        // Show Customer Notice unconditionally (no stage logic)
        $customer_notice = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_customer_notice($post->ID, 'proposal');
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
    </div>
</div>
