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
                        <p><?php printf(esc_html__('Your project request "%s" is currently being reviewed by our team. We\'ll get back to you soon with a detailed proposal.', 'arsol-pfw'), '<strong>' . esc_html($post->post_title) . '</strong>'); ?></p>
                        
                        <div class="arsol-pfw-empty-state__request-meta">
                            <?php if (!empty($request_budget) && is_array($request_budget)) : ?>
                                <p><strong><?php _e('Budget:', 'arsol-pfw'); ?></strong> <?php echo wc_price($request_budget['amount'], array('currency' => $request_budget['currency'])); ?></p>
                            <?php endif; ?>
                            
                            <?php 
                            $start_date = get_post_meta($post->ID, '_request_start_date', true);
                            if ($start_date) : ?>
                                <p><strong><?php _e('Requested Start Date:', 'arsol-pfw'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($start_date))); ?></p>
                            <?php endif; ?>
                            
                            <?php 
                            $delivery_date = get_post_meta($post->ID, '_request_delivery_date', true);
                            if ($delivery_date) : ?>
                                <p><strong><?php _e('Requested Delivery Date:', 'arsol-pfw'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($delivery_date))); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php do_action('arsol_projects_after_request_state', $post->ID); ?>
