<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get proposal details
$proposal_budget = get_post_meta($post->ID, '_arsol_pfw_proposal_budget_onetime_amount', true);
$proposal_timeline = get_post_meta($post->ID, '_arsol_pfw_proposal_timeline', true);
$related_request_id = get_post_meta($post->ID, '_arsol_pfw_proposal_request_id', true);
$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';

// Get proposal stage to determine which message to show
$stage_terms = get_the_terms($post->ID, 'arsol-pfw-proposal-stage');
$stage = '';
if ($stage_terms && !is_wp_error($stage_terms)) {
    $stage = $stage_terms[0]->slug;
} else {
    $stage = 'processing'; // Default fallback
}

// Determine the message to display with proper hierarchy: Custom Feedback → Settings Defaults → Plugin Defaults
$display_message = '';

// 1. First priority: Custom feedback from metabox (if available)
if ($stage === 'processing') {
    $custom_feedback = get_post_meta($post->ID, '_arsol_pfw_proposal_processing_feedback', true);
    if (!empty($custom_feedback)) {
        $display_message = $custom_feedback;
    }
} elseif ($stage === 'pending-approval') {
    $custom_feedback = get_post_meta($post->ID, '_arsol_pfw_proposal_pending_approval_feedback', true);
    if (!empty($custom_feedback)) {
        $display_message = $custom_feedback;
    }
}

// 2. Second priority: Settings defaults (if no custom feedback)
if (empty($display_message)) {
    if ($stage === 'processing') {
        $display_message = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_default_message('arsol_pfw_proposal_default_empty_content');
    } elseif ($stage === 'pending-approval') {
        $display_message = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_default_message('arsol_pfw_proposal_default_empty_content');
    } else {
        $display_message = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_default_message('arsol_pfw_proposal_default_empty_content');
    }
}
?>

<div class="project-content-wrapper">
    <div class="project-content">
        <h3 class="project-title"><?php echo esc_html($post->post_title); ?></h3>
        <div class="project-description">
            <?php if (empty(get_the_content())) : ?>
                <div class="arsol-pfw-project-overview-empty">
                    <div class="arsol-pfw-empty-state">
                        <div class="arsol-pfw-empty-state__content">
                            <?php echo wp_kses_post(wpautop($display_message)); ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <?php echo wp_kses_post($post->post_content); ?>
            <?php endif; ?>
        </div>
        
        <?php
        // Add Customer Notice section using new three-layer helper function
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
