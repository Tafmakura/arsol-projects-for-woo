<?php
/**
 * Project Request Content Template: On Hold Status
 * Shows custom content and edit form for requests that are on hold
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="arsol-pfw-request">
    <div class="arsol-pfw-header">
        <h3 class="arsol-pfw-title"><?php echo esc_html($post->post_title); ?></h3>
    </div>

    <div class="arsol-pfw-content-wrapper">
        <div class="arsol-pfw-content">
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
            
            <div class="arsol-pfw-post-content">
                <?php if (!empty($post->post_content)) : ?>
                    <?php echo wp_kses_post($post->post_content); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="arsol-pfw-form-section arsol-pfw-on-hold-form-section">
        <h2><?php esc_html_e('Update Your Request', 'arsol-pfw'); ?></h2>
        <p><?php esc_html_e('You can make changes to your request while it\'s on hold. Any updates will be reviewed when we resume processing.', 'arsol-pfw'); ?></p>
        
        <?php
        // Show edit form for on-hold requests using shortcode
        echo do_shortcode('[arsol_pfw_project_request_form is_edit="true" post_id="' . $post->ID . '"]');
        ?>
    </div>
</div>
