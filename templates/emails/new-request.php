<?php
/**
 * New Request Email Template
 * 
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$status_icon = isset($status_icon) ? $status_icon : '��';
$color_scheme = isset($color_scheme) ? $color_scheme : array('background' => '#cce5ff', 'text' => '#0073aa', 'cta' => '#0073aa');
?>

<div style="background-color: <?php echo esc_attr($color_scheme['background']); ?>; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h2 style="color: <?php echo esc_attr($color_scheme['text']); ?>; margin: 0 0 15px 0;">
        <?php echo $status_icon; ?> <?php echo esc_html($email_heading); ?>
    </h2>
    
    <p>Hello <?php echo esc_html($customer->first_name ?: $customer->display_name); ?>,</p>
    
    <p>Thank you for submitting your project request! We've received your request and our team will review it shortly.</p>
    
    <div style="background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <h3 style="margin-top: 0;">Request Details:</h3>
        <p><strong>Request ID:</strong> #<?php echo esc_html($request->ID); ?></p>
        <p><strong>Title:</strong> <?php echo esc_html($request->post_title); ?></p>
        <p><strong>Submitted:</strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($request->post_date))); ?></p>
    </div>
    
    <p><strong>What happens next?</strong></p>
    <ol>
        <li>Our team will review your request (typically within 1-2 business days)</li>
        <li>We'll contact you if we need any additional information</li>
        <li>Once approved, we'll create a detailed proposal for your project</li>
        <li>You can review and approve the proposal in your customer portal</li>
    </ol>
    
    <div style="text-align: center; margin: 25px 0;">
        <a href="<?php echo esc_url($portal_url); ?>" 
           style="background-color: <?php echo esc_attr($color_scheme['cta']); ?>; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
            View Request in Portal
        </a>
    </div>
    
    <p>You can track the progress of your request and view all communications in your customer portal at any time.</p>
    
    <p>If you have any questions, please don't hesitate to contact us.</p>
    
    <p>Best regards,<br>
    The <?php echo esc_html(get_bloginfo('name')); ?> Team</p>
</div>
