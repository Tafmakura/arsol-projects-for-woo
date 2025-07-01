<?php
/**
 * Project Request Content Template: Default
 * Shows request content when not in any special state
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get request details
$request_budget = get_post_meta($post->ID, '_arsol_pfw_request_budget', true);
$request_timeline = get_post_meta($post->ID, '_arsol_pfw_request_timeline', true);
$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';

// Get request stage (with proper error handling)
$stage_terms = wp_get_post_terms($post->ID, 'arsol-pfw-request-stage', ['fields' => 'slugs']);
$current_stage = '';
if (!is_wp_error($stage_terms) && !empty($stage_terms)) {
    $current_stage = $stage_terms[0];
}

do_action('arsol_projects_before_request_state', $post->ID);
?>

<div class="project-content-wrapper">
    <div class="project-content">
        <h3 class="project-title"><?php echo esc_html($post->post_title); ?></h3>
        <div class="project-description">
            <?php if (empty(get_the_content())) : ?>
                <div class="arsol-pfw-project-overview-empty">
                    <div class="arsol-pfw-empty-state">
                        <div class="arsol-pfw-empty-state__content">
                            <p><?php echo esc_html(__('Your request is being processed. We will update you with more details soon.', 'arsol-pfw')); ?></p>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <?php echo wp_kses_post($post->post_content); ?>
            <?php endif; ?>
        </div>
        
        <?php
        // Show Customer Notice unconditionally
        $customer_notice = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_customer_notice($post->ID, 'request');
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

<div class="arsol-pfw-request-actions">
    <?php
    $wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';
    $cancel_url = add_query_arg(array(
        'action' => 'arsol_cancel_request',
        'request_id' => $post->ID,
        '_wpnonce' => wp_create_nonce('arsol_cancel_request_nonce')
    ), admin_url('admin-post.php'));
    ?>
    <a href="<?php echo esc_url($cancel_url); ?>" class="button<?php echo esc_attr($wp_button_class); ?> arsol-pfw-cancel-button" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to cancel this request? This action cannot be undone.', 'arsol-pfw')); ?>')">
        <?php esc_html_e('Cancel Request', 'arsol-pfw'); ?>
    </a>
</div>

<?php do_action('arsol_projects_after_request_state', $post->ID); ?>
