<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get request details
$request_budget = get_post_meta($post->ID, '_request_budget', true);
$request_timeline = get_post_meta($post->ID, '_request_timeline', true);
$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';

$status_terms = wp_get_post_terms($post->ID, 'arsol-request-status', ['fields' => 'slugs']);
$current_status = !empty($status_terms) ? $status_terms[0] : '';
?>

<div class="project-overview-wrapper">
    <div class="project-content">
        <?php if ($current_status === 'pending') : ?>
            <?php
            // Use the new template override for the edit form
            Frontend_Template_Overrides::render_template(
                'project_request_edit_form',
                ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/form-project-create-request.php',
                ['is_edit' => true, 'post' => $post]
            );
            ?>
        <?php else : ?>
            <h3 class="project-title"><?php echo esc_html($post->post_title); ?></h3>
            <div class="project-description">
                <?php echo wp_kses_post($post->post_content); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
