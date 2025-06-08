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
        
        <?php
        // Display comments if enabled for proposals
        if (\Arsol_Projects_For_Woo\Admin\Settings_General::is_comments_enabled_for_post_type('arsol-pfw-proposal') && 
            post_type_supports('arsol-pfw-proposal', 'comments') && 
            (comments_open() || get_comments_number())) :
        ?>
            <div class="project-comments-section">
                <?php
                // Load WordPress native comments template
                comments_template();
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>
