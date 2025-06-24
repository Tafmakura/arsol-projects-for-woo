<?php
/**
 * Admin New Request Email Template
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
    
    <p>A new project request has been submitted and requires review.</p>
    
    <div style="background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <h3 style="margin-top: 0;">Request Details:</h3>
        <p><strong>Request ID:</strong> #<?php echo esc_html($request->ID); ?></p>
        <p><strong>Title:</strong> <?php echo esc_html($request->post_title); ?></p>
        <p><strong>Customer:</strong> <?php echo esc_html($customer->display_name); ?> (<?php echo esc_html($customer->user_email); ?>)</p>
        <p><strong>Submitted:</strong> <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($request->post_date))); ?></p>
        <p><strong>Status:</strong> Pending Review</p>
    </div>
    
    <?php if (!empty($request->post_content)): ?>
    <div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid <?php echo esc_attr($color_scheme['cta']); ?>; margin: 15px 0;">
        <h4 style="margin-top: 0;">Request Description:</h4>
        <p><?php echo wp_kses_post(wpautop($request->post_content)); ?></p>
    </div>
    <?php endif; ?>
    
    <p><strong>Required Actions:</strong></p>
    <ul>
        <li>Review the request details and customer requirements</li>
        <li>Contact customer if additional information is needed</li>
        <li>Change status to "Under Review" when processing begins</li>
        <li>Approve or put on hold based on evaluation</li>
    </ul>
    
    <div style="text-align: center; margin: 25px 0;">
        <a href="<?php echo esc_url($admin_url); ?>" 
           style="background-color: <?php echo esc_attr($color_scheme['cta']); ?>; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
            Review Request in Admin
        </a>
    </div>
    
    <p style="font-size: 12px; color: #666; margin-top: 30px;">
        This email was sent to you because you have permission to manage Arsol projects. 
        <br>To manage your email preferences, visit the WooCommerce email settings.
    </p>
</div>
