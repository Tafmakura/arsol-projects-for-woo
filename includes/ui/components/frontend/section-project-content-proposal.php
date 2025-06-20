<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get proposal details
$proposal_budget = get_post_meta($post->ID, '_proposal_budget', true);
$proposal_timeline = get_post_meta($post->ID, '_proposal_timeline', true);
$related_request_id = get_post_meta($post->ID, '_related_request_id', true);
$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';
?>

<div class="project-overview-wrapper">
    <div class="project-content">
        <h3 class="project-title"><?php echo esc_html($post->post_title); ?></h3>
        <div class="project-description">
            <?php echo wp_kses_post($post->post_content); ?>
        </div>
    </div>
</div>
