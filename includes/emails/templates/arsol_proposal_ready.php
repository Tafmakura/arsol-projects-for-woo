<?php
/**
 * Proposal Ready Email Template
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
    
    <p><strong>Exciting news!</strong> Your project proposal is now complete and ready for your review.</p>
    
    <div style="background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <h3 style="margin-top: 0;">Proposal Details:</h3>
        <p><strong>Proposal ID:</strong> #<?php echo esc_html($proposal->ID); ?></p>
        <p><strong>Title:</strong> <?php echo esc_html($proposal->post_title); ?></p>
        <p><strong>Status:</strong> <span style="color: <?php echo esc_attr($color_scheme['text']); ?>; font-weight: bold;">Ready for Review</span></p>
        <p><strong>Completed:</strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($proposal->post_modified))); ?></p>
    </div>
    
    <p><strong>What's included in your proposal:</strong></p>
    <ul>
        <li>Detailed project scope and requirements</li>
        <li>Complete timeline and milestones</li>
        <li>Transparent pricing breakdown</li>
        <li>Technical specifications and deliverables</li>
        <li>Terms and conditions</li>
    </ul>
    
    <div style="background-color: #e8f5e8; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0;">
        <h4 style="margin-top: 0; color: #155724;">⏰ Action Required</h4>
        <p style="margin-bottom: 0; color: #155724;">Please review your proposal and let us know if you'd like to proceed. You can approve or request changes directly through your customer portal.</p>
    </div>
    
    <div style="text-align: center; margin: 25px 0;">
        <a href="<?php echo esc_url($portal_url); ?>" 
           style="background-color: <?php echo esc_attr($color_scheme['cta']); ?>; color: #212529; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; font-size: 16px;">
            📋 Review Your Proposal
        </a>
    </div>
    
    <p><strong>Next steps:</strong></p>
    <ol>
        <li>Click the button above to access your proposal</li>
        <li>Review all sections carefully</li>
        <li>Approve the proposal if you're satisfied</li>
        <li>Or request changes if needed</li>
        <li>Once approved, we'll create your project and order</li>
    </ol>
    
    <p><strong>Questions about your proposal?</strong></p>
    <p>Our team is here to help! Feel free to reach out if you have any questions or need clarification on any aspect of the proposal.</p>
    
    <p>We're excited to potentially work with you on this project!</p>
    
    <p>Best regards,<br>
    The <?php echo esc_html(get_bloginfo('name')); ?> Team</p>
</div>
