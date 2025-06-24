<?php
/**
 * Project Lead Proposal Processing Email Template
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
        <?php echo $status_icon; ?> New Proposal Assignment Active
    </h2>
    
    <p>Hello <?php echo esc_html($project_lead->first_name ?: $project_lead->display_name); ?>,</p>
    
    <p>You've been assigned as the project lead for a new proposal that's now in the processing phase.</p>
    
    <div style="background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <h3 style="margin-top: 0;">Assignment Details:</h3>
        <p><strong>Proposal ID:</strong> #<?php echo esc_html($proposal->ID); ?></p>
        <p><strong>Title:</strong> <?php echo esc_html($proposal->post_title); ?></p>
        <p><strong>Customer:</strong> <?php echo esc_html($customer->display_name); ?> (<?php echo esc_html($customer->user_email); ?>)</p>
        <p><strong>Status:</strong> <span style="color: <?php echo esc_attr($color_scheme['text']); ?>; font-weight: bold;">Processing</span></p>
        <p><strong>Assigned:</strong> <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'))); ?></p>
    </div>
    
    <?php if (!empty($proposal->post_content)): ?>
    <div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid <?php echo esc_attr($color_scheme['cta']); ?>; margin: 15px 0;">
        <h4 style="margin-top: 0;">Original Request Details:</h4>
        <div style="max-height: 200px; overflow-y: auto;">
            <?php echo wp_kses_post(wpautop($proposal->post_content)); ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div style="background-color: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 15px 0;">
        <h4 style="margin-top: 0; color: #1976d2;">📋 Your Responsibilities</h4>
        <ul style="color: #1976d2; margin-bottom: 0;">
            <li><strong>Scope Analysis:</strong> Review requirements and define project scope</li>
            <li><strong>Resource Planning:</strong> Estimate time, resources, and team needs</li>
            <li><strong>Pricing:</strong> Develop accurate cost estimates</li>
            <li><strong>Timeline:</strong> Create realistic project timeline and milestones</li>
            <li><strong>Documentation:</strong> Prepare detailed proposal documentation</li>
        </ul>
    </div>
    
    <p><strong>Expected Timeline:</strong></p>
    <p>Proposals should typically be completed within 3-5 business days. Please update the proposal status and notify the customer when ready for review.</p>
    
    <div style="background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0;">
        <h4 style="margin-top: 0; color: #856404;">💡 Important Notes</h4>
        <ul style="color: #856404; margin-bottom: 0;">
            <li>You are now the primary point of contact for this customer</li>
            <li>The customer has been notified that proposal work has begun</li>
            <li>Contact the customer directly if you need additional information</li>
            <li>Update proposal status regularly to keep all stakeholders informed</li>
        </ul>
    </div>
    
    <div style="text-align: center; margin: 25px 0;">
        <a href="<?php echo esc_url($admin_url); ?>" 
           style="background-color: <?php echo esc_attr($color_scheme['cta']); ?>; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
            Start Working on Proposal
        </a>
    </div>
    
    <p>If you have any questions about this assignment or need additional resources, please reach out to the project management team.</p>
    
    <p>Thank you for your dedication to delivering excellent proposals!</p>
    
    <p>Best regards,<br>
    The <?php echo esc_html(get_bloginfo('name')); ?> Team</p>
</div>
