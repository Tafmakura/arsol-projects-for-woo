<?php
/**
 * Proposal Processing Email Template
 * 
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$status_icon = isset($status_icon) ? $status_icon : '🔧';
$color_scheme = isset($color_scheme) ? $color_scheme : array('background' => '#cce5ff', 'text' => '#0073aa', 'cta' => '#0073aa');
?>

<div style="background-color: <?php echo esc_attr($color_scheme['background']); ?>; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h2 style="color: <?php echo esc_attr($color_scheme['text']); ?>; margin: 0 0 15px 0;">
        <?php echo $status_icon; ?> <?php echo esc_html($email_heading); ?>
    </h2>
    
    <p>Hello <?php echo esc_html($customer->first_name ?: $customer->display_name); ?>,</p>
    
    <p>Great news! We've started working on your proposal and wanted to keep you informed of our progress.</p>
    
    <div style="background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <h3 style="margin-top: 0;">Proposal Details:</h3>
        <p><strong>Proposal ID:</strong> #<?php echo esc_html($proposal->ID); ?></p>
        <p><strong>Title:</strong> <?php echo esc_html($proposal->post_title); ?></p>
        <?php if ($project_lead): ?>
        <p><strong>Project Lead:</strong> <?php echo esc_html($project_lead->first_name . ' ' . $project_lead->last_name); ?></p>
        <?php endif; ?>
        <p><strong>Status:</strong> <span style="color: <?php echo esc_attr($color_scheme['text']); ?>; font-weight: bold;">In Progress</span></p>
    </div>
    
    <p><strong>What we're working on:</strong></p>
    <ul>
        <li>Detailed project scope and requirements analysis</li>
        <li>Resource allocation and timeline planning</li>
        <li>Accurate pricing and cost estimation</li>
        <li>Technical specifications and deliverables</li>
    </ul>
    
    <p><strong>Timeline:</strong></p>
    <p>We typically complete proposals within 3-5 business days. You'll receive a notification as soon as your proposal is ready for review.</p>
    
    <?php if ($project_lead): ?>
    <div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid <?php echo esc_attr($color_scheme['cta']); ?>; margin: 15px 0;">
        <h4 style="margin-top: 0;">Your Project Lead</h4>
        <p><strong><?php echo esc_html($project_lead->first_name . ' ' . $project_lead->last_name); ?></strong> has been assigned to your project and will be your primary point of contact throughout the proposal process.</p>
    </div>
    <?php endif; ?>
    
    <div style="text-align: center; margin: 25px 0;">
        <a href="<?php echo esc_url($portal_url); ?>" 
           style="background-color: <?php echo esc_attr($color_scheme['cta']); ?>; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
            Track Progress in Portal
        </a>
    </div>
    
    <p>You can monitor the progress and view all updates in your customer portal. We'll keep you informed of any major milestones.</p>
    
    <p>If you have any questions or additional requirements, please don't hesitate to reach out.</p>
    
    <p>Best regards,<br>
    The <?php echo esc_html(get_bloginfo('name')); ?> Team</p>
</div>
