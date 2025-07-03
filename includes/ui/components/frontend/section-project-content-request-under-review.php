<?php
/**
 * Project Request Content Template: Under Review Status
 * Shows review progress and information for requests being reviewed
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="arsol-pfw-request-content arsol-pfw-under-review-content">
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

    <div class="arsol-pfw-request-details arsol-pfw-review-details">
        <h2><?php esc_html_e('Your Request Details', 'arsol-pfw'); ?></h2>
        <div class="arsol-pfw-request-summary arsol-pfw-review-summary">
            <p><?php echo wp_kses_post($post->post_content); ?></p>
            
            <?php
            $request_budget = get_post_meta($post->ID, '_arsol_pfw_request_budget', true);
            $start_date = get_post_meta($post->ID, '_arsol_pfw_request_start_date', true);
            $delivery_date = get_post_meta($post->ID, '_arsol_pfw_request_delivery_date', true);
            ?>
            
            <?php if (!empty($request_budget) && is_array($request_budget)) : ?>
                <p><strong><?php esc_html_e('Budget:', 'arsol-pfw'); ?></strong> <?php echo wc_price($request_budget['amount'], array('currency' => $request_budget['currency'])); ?></p>
            <?php endif; ?>
            
            <?php if ($start_date) : ?>
                <p><strong><?php esc_html_e('Requested Start Date:', 'arsol-pfw'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($start_date))); ?></p>
            <?php endif; ?>
            
            <?php if ($delivery_date) : ?>
                <p><strong><?php esc_html_e('Requested Delivery Date:', 'arsol-pfw'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($delivery_date))); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
