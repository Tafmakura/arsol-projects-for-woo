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

do_action('arsol_projects_before_request_state', $post->ID);
?>

<div class="project-overview-wrapper">
    <div class="project-content">
        <?php if ($current_status === 'pending-review') : ?>
            <?php
            // Show edit form for pending review requests
            \Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
                'project_request_edit_form',
                ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/form-project-create-request.php',
                ['is_edit' => true, 'post' => $post]
            );
            ?>
        <?php else : ?>
            <div class="arsol-pfw-project-overview-empty">
                <div class="arsol-pfw-empty-state">
                    <div class="arsol-pfw-empty-state__icon">
                        <span class="dashicons dashicons-visibility"></span>
                    </div>
                    <div class="arsol-pfw-empty-state__content">
                        <h1><?php esc_html_e('Request Under Review', 'arsol-pfw'); ?></h1>
                        <p><?php esc_html_e('Your project request is currently being reviewed by our team. We\'ll get back to you soon with a detailed proposal.', 'arsol-pfw'); ?></p>
                        
                        <div class="arsol-pfw-empty-state__project-details">
                            <h3><?php echo esc_html($post->post_title); ?></h3>
                            <div class="project-description">
                                <?php echo wp_kses_post($post->post_content); ?>
                            </div>
                        </div>
                        
                        <div class="arsol-pfw-empty-state__actions">
                            <a href="/contact-us/" class="button button-primary"><?php esc_html_e('Contact Support', 'arsol-pfw'); ?></a>
                            <a href="/services/" class="button"><?php esc_html_e('Contact Sales', 'arsol-pfw'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php do_action('arsol_projects_after_request_state', $post->ID); ?>
