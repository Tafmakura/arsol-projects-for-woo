<?php
/**
 * Project Creation Email Template
 * 
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$status_icon = isset($status_icon) ? $status_icon : '🎉';
$color_scheme = isset($color_scheme) ? $color_scheme : array('background' => '#fff3cd', 'text' => '#856404', 'cta' => '#ffc107');
?>

<div style="background-color: <?php echo esc_attr($color_scheme['background']); ?>; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h2 style="color: <?php echo esc_attr($color_scheme['text']); ?>; margin: 0 0 15px 0;">
        <?php echo $status_icon; ?> <?php echo esc_html($email_heading); ?>
    </h2>
    
    <p>Hello <?php echo esc_html($customer->first_name ?: $customer->display_name); ?>,</p>
    
    <p><strong>Congratulations!</strong> Your project has been created and your order is ready for completion.</p>
    
    <div style="background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <h3 style="margin-top: 0;">Order Details:</h3>
        <p><strong>Project ID:</strong> #<?php echo esc_html($project->ID); ?></p>
        <p><strong>Order ID:</strong> #<?php echo esc_html($order->get_id()); ?></p>
        <p><strong>Project Title:</strong> <?php echo esc_html($project->post_title); ?></p>
        <p><strong>Total Amount:</strong> <span style="color: <?php echo esc_attr($color_scheme['text']); ?>; font-weight: bold; font-size: 18px;"><?php echo wp_kses_post($order->get_formatted_order_total()); ?></span></p>
        <?php if ($project_lead): ?>
        <p><strong>Project Lead:</strong> <?php echo esc_html($project_lead->first_name . ' ' . $project_lead->last_name); ?></p>
        <?php endif; ?>
    </div>
    
    <div style="background-color: #e8f5e8; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0;">
        <h4 style="margin-top: 0; color: #155724;">�� Complete Your Purchase</h4>
        <p style="margin-bottom: 0; color: #155724;">To begin work on your project, please complete your payment by clicking the button below.</p>
    </div>
    
    <div style="text-align: center; margin: 25px 0;">
        <a href="<?php echo esc_url($checkout_url); ?>" 
           style="background-color: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; font-size: 16px;">
            �� Complete Payment
        </a>
    </div>
    
    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <h4 style="margin-top: 0;">What happens after payment:</h4>
        <ol style="margin-bottom: 0;">
            <li><strong>Project Activation:</strong> Your project will be immediately activated</li>
            <li><strong>Team Assignment:</strong> Your dedicated project team will begin work</li>
            <li><strong>Kickoff Communication:</strong> You'll receive a project kickoff email with next steps</li>
            <li><strong>Regular Updates:</strong> We'll keep you informed throughout the project lifecycle</li>
        </ol>
    </div>
    
    <?php if ($project_lead): ?>
    <div style="background-color: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 15px 0;">
        <h4 style="margin-top: 0; color: #1976d2;">👥 Your Project Team</h4>
        <p style="margin-bottom: 0; color: #1976d2;"><strong><?php echo esc_html($project_lead->first_name . ' ' . $project_lead->last_name); ?></strong> will be leading your project and will be your primary point of contact once work begins.</p>
    </div>
    <?php endif; ?>
    
    <p><strong>Need to review the details?</strong></p>
    <div style="text-align: center; margin: 15px 0;">
        <a href="<?php echo esc_url($portal_url); ?>" 
           style="background-color: <?php echo esc_attr($color_scheme['cta']); ?>; color: #212529; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
            View Project Details
        </a>
    </div>
    
    <p>You can always view your project details, track progress, and communicate with your team through your customer portal.</p>
    
    <p>Thank you for choosing us for your project. We're excited to get started!</p>
    
    <p>Best regards,<br>
    The <?php echo esc_html(get_bloginfo('name')); ?> Team</p>
</div>
