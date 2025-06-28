<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get proposal details
$proposal_budget = get_post_meta($post->ID, '_arsol_pfw_proposal_budget_onetime_amount', true);
$proposal_timeline = get_post_meta($post->ID, '_arsol_pfw_proposal_timeline', true);
$related_request_id = get_post_meta($post->ID, '_arsol_pfw_proposal_request_id', true);
$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';

// Get proposal status to determine which message to show
$status_terms = get_the_terms($post->ID, 'arsol-pfw-proposal-status');
$status = $status_terms && !is_wp_error($status_terms) ? $status_terms[0]->slug : 'processing';

// Determine the message to display with proper hierarchy: Custom Feedback → Settings Defaults → Plugin Defaults
$display_message = '';

// 1. First priority: Custom feedback from metabox (if available)
if ($status === 'processing') {
    $custom_feedback = get_post_meta($post->ID, '_arsol_pfw_proposal_processing_feedback', true);
    if (!empty($custom_feedback)) {
        $display_message = $custom_feedback;
    }
} elseif ($status === 'pending-approval') {
    $custom_feedback = get_post_meta($post->ID, '_arsol_pfw_proposal_pending_approval_feedback', true);
    if (!empty($custom_feedback)) {
        $display_message = $custom_feedback;
    }
}

// 2. Second priority: Settings defaults (if no custom feedback)
if (empty($display_message)) {
    if ($status === 'processing') {
        $display_message = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_default_message('arsol_pfw_proposal_default_empty_content');
    } elseif ($status === 'pending-approval') {
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
    </div>
</div>
