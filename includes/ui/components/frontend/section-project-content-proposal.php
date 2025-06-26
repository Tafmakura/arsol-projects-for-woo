<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get proposal details
$proposal_budget = get_post_meta($post->ID, '_arsol_pfw_proposal_budget_onetime_amount', true);
$proposal_timeline = get_post_meta($post->ID, '_arsol_pfw_proposal_timeline', true);
$related_request_id = get_post_meta($post->ID, '_arsol_pfw_proposal_request_id', true);
$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';

// Get the default message for empty proposals
$default_message = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_default_message('project_proposals_message');
?>

<div class="project-overview-wrapper">
    <div class="project-content">
        <h3 class="project-title"><?php echo esc_html($post->post_title); ?></h3>
        <div class="project-description">
            <?php if (empty(get_the_content())) : ?>
                <div class="arsol-pfw-project-overview-empty">
                    <div class="arsol-pfw-empty-state">
                        <div class="arsol-pfw-empty-state__content">
                            <?php echo wp_kses_post(wpautop($default_message)); ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <?php echo wp_kses_post($post->post_content); ?>
            <?php endif; ?>
        </div>
    </div>
</div>
