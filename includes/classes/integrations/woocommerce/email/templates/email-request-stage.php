<?php
/**
 * Request Status Email Template
 * 
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php printf(__('Hello %s,', 'arsol-pfw'), esc_html($customer->first_name ?: $customer->display_name)); ?></p>
    
<p><?php _e('We wanted to update you on the status of your project request.', 'arsol-pfw'); ?></p>
    
<h2><?php _e('Request Details', 'arsol-pfw'); ?></h2>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
    <tbody>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Request ID:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;">#<?php echo esc_html($request->ID); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Title:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html($request->post_title); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Previous Status:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html($old_status ? ucfirst(str_replace('-', ' ', $old_status)) : 'N/A'); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('New Status:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;"><strong><?php echo esc_html($status_label); ?></strong></td>
        </tr>
    </tbody>
</table>
    
    <?php if ($new_status === 'under-review'): ?>
    <h3><?php _e('What this means', 'arsol-pfw'); ?></h3>
    <p><?php _e('Our team is now actively reviewing your request. We\'re evaluating the requirements and will contact you if we need any additional information.', 'arsol-pfw'); ?></p>
        
    <?php 
    // Feedback section removed
    ?>
    
    <h3><?php _e('Next steps', 'arsol-pfw'); ?></h3>
        <ul>
        <li><?php _e('We\'ll complete our review within 2-3 business days', 'arsol-pfw'); ?></li>
        <li><?php _e('You may receive follow-up questions from our team', 'arsol-pfw'); ?></li>
        <li><?php _e('Once approved, we\'ll begin creating your detailed proposal', 'arsol-pfw'); ?></li>
        </ul>
        
    <?php elseif ($new_status === 'on-hold'): ?>
    <h3><?php _e('What this means', 'arsol-pfw'); ?></h3>
    <p><?php _e('Your request has been temporarily placed on hold. This may be due to:', 'arsol-pfw'); ?></p>
        <ul>
        <li><?php _e('Additional information needed from you', 'arsol-pfw'); ?></li>
        <li><?php _e('Current capacity constraints', 'arsol-pfw'); ?></li>
        <li><?php _e('Technical clarifications required', 'arsol-pfw'); ?></li>
        </ul>
        
    <h3><?php _e('Next steps', 'arsol-pfw'); ?></h3>
    <p><?php _e('Our team will contact you directly with details about what steps are needed to proceed.', 'arsol-pfw'); ?></p>
        
    <?php elseif ($new_status === 'approved'): ?>
    <h3><?php _e('🎉 Great news!', 'arsol-pfw'); ?></h3>
    <p><?php _e('Your request has been approved! Our team will now begin creating a detailed proposal for your project.', 'arsol-pfw'); ?></p>
        
    <h3><?php _e('What happens next', 'arsol-pfw'); ?></h3>
        <ol>
        <li><?php _e('A project lead will be assigned to your request', 'arsol-pfw'); ?></li>
        <li><?php _e('We\'ll create a detailed proposal with scope, timeline, and pricing', 'arsol-pfw'); ?></li>
        <li><?php _e('You\'ll receive a notification when the proposal is ready for review', 'arsol-pfw'); ?></li>
        <li><?php _e('You can review and approve the proposal in your customer portal', 'arsol-pfw'); ?></li>
        </ol>
    <?php endif; ?>
    
    <div style="text-align: center; margin: 25px 0;">
    <a class="link" href="<?php echo esc_url($portal_url); ?>" style="background-color: #96588a; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
        <?php _e('View Request in Portal', 'arsol-pfw'); ?>
        </a>
    </div>
    
<p><?php _e('You can always check the current status and view all communications in your customer portal.', 'arsol-pfw'); ?></p>

<p><?php _e('If you have any questions, please don\'t hesitate to contact us.', 'arsol-pfw'); ?></p>
    
<p>
    <?php _e('Best regards,', 'arsol-pfw'); ?><br>
    <?php printf(__('The %s Team', 'arsol-pfw'), esc_html(get_bloginfo('name'))); ?>
</p>
    
<?php do_action('woocommerce_email_footer', $email);
