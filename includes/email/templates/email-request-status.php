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

$status_icon = isset($status_icon) ? $status_icon : '📧';
$color_scheme = isset($color_scheme) ? $color_scheme : array('background' => '#cce5ff', 'text' => '#0073aa', 'cta' => '#0073aa');
?>

<div style="background-color: <?php echo esc_attr($color_scheme['background']); ?>; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h2 style="color: <?php echo esc_attr($color_scheme['text']); ?>; margin: 0 0 15px 0;">
        <?php echo $status_icon; ?> <?php echo esc_html($email_heading); ?>
    </h2>
    
    <p>Hello <?php echo esc_html($customer->first_name ?: $customer->display_name); ?>,</p>
    
    <p>We wanted to update you on the status of your project request.</p>
    
    <div style="background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <h3 style="margin-top: 0;">Request Details:</h3>
        <p><strong>Request ID:</strong> #<?php echo esc_html($request->ID); ?></p>
        <p><strong>Title:</strong> <?php echo esc_html($request->post_title); ?></p>
        <p><strong>Previous Status:</strong> <?php echo esc_html($old_status ? ucfirst(str_replace('-', ' ', $old_status)) : 'N/A'); ?></p>
        <p><strong>New Status:</strong> <span style="color: <?php echo esc_attr($color_scheme['text']); ?>; font-weight: bold;"><?php echo esc_html($status_label); ?></span></p>
    </div>
    
    <?php if ($new_status === 'under-review'): ?>
        <p><strong>What this means:</strong></p>
        <p>Our team is now actively reviewing your request. We're evaluating the requirements and will contact you if we need any additional information.</p>
        
        <p><strong>Next steps:</strong></p>
        <ul>
            <li>We'll complete our review within 2-3 business days</li>
            <li>You may receive follow-up questions from our team</li>
            <li>Once approved, we'll begin creating your detailed proposal</li>
        </ul>
        
    <?php elseif ($new_status === 'on-hold'): ?>
        <p><strong>What this means:</strong></p>
        <p>Your request has been temporarily placed on hold. This may be due to:</p>
        <ul>
            <li>Additional information needed from you</li>
            <li>Current capacity constraints</li>
            <li>Technical clarifications required</li>
        </ul>
        
        <p><strong>Next steps:</strong></p>
        <p>Our team will contact you directly with details about why your request is on hold and what steps are needed to proceed.</p>
        
    <?php elseif ($new_status === 'approved'): ?>
        <p><strong>🎉 Great news!</strong></p>
        <p>Your request has been approved! Our team will now begin creating a detailed proposal for your project.</p>
        
        <p><strong>What happens next:</strong></p>
        <ol>
            <li>A project lead will be assigned to your request</li>
            <li>We'll create a detailed proposal with scope, timeline, and pricing</li>
            <li>You'll receive a notification when the proposal is ready for review</li>
            <li>You can review and approve the proposal in your customer portal</li>
        </ol>
    <?php endif; ?>
    
    <div style="text-align: center; margin: 25px 0;">
        <a href="<?php echo esc_url($portal_url); ?>" 
           style="background-color: <?php echo esc_attr($color_scheme['cta']); ?>; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
            View Request in Portal
        </a>
    </div>
    
    <p>You can always check the current status and view all communications in your customer portal.</p>
    
    <p>If you have any questions, please don't hesitate to contact us.</p>
    
    <p>Best regards,<br>
    The <?php echo esc_html(get_bloginfo('name')); ?> Team</p>
</div>
