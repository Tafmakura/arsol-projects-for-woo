<?php
/**
 * Project Request Content Template: Approved Status
 * Shows congratulations message and next steps for approved requests
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
            // Add Customer Notice section (always show if content exists, regardless of stage)
            $customer_notice = get_post_meta($post->ID, '_arsol_pfw_request_customer_notice', true);
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
                <div class="arsol-pfw-approved-header">
                    <h1><?php esc_html_e('🎉 Your Project Request Has Been Approved!', 'arsol-pfw'); ?></h1>
                    <p class="arsol-pfw-approved-description"><?php printf(esc_html__('Congratulations! Your project request "%s" has been approved and will be converted to a project proposal soon.', 'arsol-pfw'), '<strong>' . esc_html($post->post_title) . '</strong>'); ?></p>
                </div>

                <div class="arsol-pfw-approved-info">
                    <h2><?php esc_html_e('What happens next?', 'arsol-pfw'); ?></h2>
                    <div class="arsol-pfw-approval-timeline">
                        <div class="timeline-step completed">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h3><?php esc_html_e('Request Submitted', 'arsol-pfw'); ?></h3>
                                <p><?php esc_html_e('You submitted your project request', 'arsol-pfw'); ?></p>
                            </div>
                        </div>
                        <div class="timeline-step completed">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h3><?php esc_html_e('Request Approved', 'arsol-pfw'); ?></h3>
                                <p><?php esc_html_e('Our team has reviewed and approved your request', 'arsol-pfw'); ?></p>
                            </div>
                        </div>
                        <div class="timeline-step active">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h3><?php esc_html_e('Proposal Creation', 'arsol-pfw'); ?></h3>
                                <p><?php esc_html_e('We\'re creating a detailed proposal for your project', 'arsol-pfw'); ?></p>
                            </div>
                        </div>
                        <div class="timeline-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h3><?php esc_html_e('Proposal Review', 'arsol-pfw'); ?></h3>
                                <p><?php esc_html_e('You\'ll review and approve the proposal', 'arsol-pfw'); ?></p>
                            </div>
                        </div>
                        <div class="timeline-step">
                            <div class="step-number">5</div>
                            <div class="step-content">
                                <h3><?php esc_html_e('Project Start', 'arsol-pfw'); ?></h3>
                                <p><?php esc_html_e('We begin working on your project', 'arsol-pfw'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="arsol-pfw-approved-details">
                    <h2><?php esc_html_e('Your Original Request Details', 'arsol-pfw'); ?></h2>
                    <div class="arsol-pfw-request-summary">
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

                <div class="arsol-pfw-approved-next-steps">
                    <h2><?php esc_html_e('Expected Timeline', 'arsol-pfw'); ?></h2>
                    <ul>
                        <li><?php esc_html_e('Proposal creation: 2-3 business days', 'arsol-pfw'); ?></li>
                        <li><?php esc_html_e('You\'ll receive an email notification when the proposal is ready', 'arsol-pfw'); ?></li>
                        <li><?php esc_html_e('The proposal will include detailed scope, timeline, and pricing', 'arsol-pfw'); ?></li>
                    </ul>
                </div>

                <div class="arsol-pfw-contact-info">
                    <h3><?php esc_html_e('Questions about your approved request?', 'arsol-pfw'); ?></h3>
                    <p><?php esc_html_e('Contact our project team at projects@example.com or call (555) 123-4567', 'arsol-pfw'); ?></p>
                    <p><strong><?php esc_html_e('Your project reference:', 'arsol-pfw'); ?></strong> #<?php echo esc_html($post->ID); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
