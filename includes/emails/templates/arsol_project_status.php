<?php
/**
 * Project Status Email Template
 * 
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$status_icon = isset($status_icon) ? $status_icon : '📋';
$color_scheme = isset($color_scheme) ? $color_scheme : array('background' => '#cce5ff', 'text' => '#0073aa', 'cta' => '#0073aa');

// Status-specific content
$status_messages = array(
    'in-progress' => array(
        'title' => 'Your Project Has Started',
        'icon' => '🚀',
        'message' => 'Great news! Work has officially begun on your project. Our team is now actively working to deliver your requirements.'
    ),
    'on-hold' => array(
        'title' => 'Project Update Required',
        'icon' => '⏸️',
        'message' => 'Your project has been temporarily placed on hold. This may require your input or additional information.'
    ),
    'completed' => array(
        'title' => 'Project Complete!',
        'icon' => '🎉',
        'message' => 'Congratulations! Your project has been completed successfully. All deliverables are now ready for your review.'
    ),
    'cancelled' => array(
        'title' => 'Project Status Update',
        'icon' => '❌',
        'message' => 'Your project status has been updated. Please see the details below.'
    )
);

$status_info = isset($status_messages[$new_status]) ? $status_messages[$new_status] : array(
    'title' => 'Project Status Update',
    'icon' => '📋',
    'message' => 'We wanted to update you on your project status.'
);
?>

<div style="background-color: <?php echo esc_attr($color_scheme['background']); ?>; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h2 style="color: <?php echo esc_attr($color_scheme['text']); ?>; margin: 0 0 15px 0;">
        <?php echo $status_info['icon']; ?> <?php echo esc_html($status_info['title']); ?>
    </h2>
    
    <p>Hello <?php echo esc_html($customer->first_name ?: $customer->display_name); ?>,</p>
    
    <p><?php echo esc_html($status_info['message']); ?></p>
    
    <div style="background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <h3 style="margin-top: 0;">Project Details:</h3>
        <p><strong>Project ID:</strong> #<?php echo esc_html($project->ID); ?></p>
        <p><strong>Title:</strong> <?php echo esc_html($project->post_title); ?></p>
        <?php if ($project_lead): ?>
        <p><strong>Project Lead:</strong> <?php echo esc_html($project_lead->first_name . ' ' . $project_lead->last_name); ?></p>
        <?php endif; ?>
        <p><strong>Previous Status:</strong> <?php echo esc_html($old_status ? ucfirst(str_replace('-', ' ', $old_status)) : 'N/A'); ?></p>
        <p><strong>Current Status:</strong> <span style="color: <?php echo esc_attr($color_scheme['text']); ?>; font-weight: bold;"><?php echo esc_html($status_label); ?></span></p>
        <p><strong>Updated:</strong> <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'))); ?></p>
    </div>
    
    <?php if ($new_status === 'in-progress'): ?>
        <div style="background-color: #e8f5e8; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0;">
            <h4 style="margin-top: 0; color: #155724;">🎯 What's Happening Now</h4>
            <ul style="margin-bottom: 0; color: #155724;">
                <li>Your dedicated project team is actively working on your requirements</li>
                <li>Regular progress updates will be shared in your portal</li>
                <li>You can expect milestone updates as work progresses</li>
                <li>Your project lead will reach out if any input is needed</li>
            </ul>
        </div>
        
    <?php elseif ($new_status === 'on-hold'): ?>
        <div style="background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0;">
            <h4 style="margin-top: 0; color: #856404;">⏸️ Why Is My Project On Hold?</h4>
            <p style="margin-bottom: 0; color: #856404;">Common reasons for project holds include:</p>
            <ul style="color: #856404;">
                <li>Additional information or clarification needed from you</li>
                <li>External dependencies or third-party requirements</li>
                <li>Resource reallocation or technical considerations</li>
                <li>Changes to project scope or requirements</li>
            </ul>
            <p style="margin-bottom: 0; color: #856404;"><strong>Next Steps:</strong> Your project lead will contact you with specific details about the hold and what's needed to resume work.</p>
        </div>
        
    <?php elseif ($new_status === 'completed'): ?>
        <div style="background-color: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0;">
            <h4 style="margin-top: 0; color: #155724;">🎊 Project Deliverables Ready!</h4>
            <ul style="margin-bottom: 0; color: #155724;">
                <li>All project deliverables have been completed</li>
                <li>Final files and documentation are available in your portal</li>
                <li>Please review everything and confirm completion</li>
                <li>Our team is available for any questions or support</li>
            </ul>
        </div>
        
    <?php elseif ($new_status === 'cancelled'): ?>
        <div style="background-color: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0;">
            <h4 style="margin-top: 0; color: #721c24;">Project Cancellation</h4>
            <p style="margin-bottom: 0; color: #721c24;">If you have any questions about this status change or would like to discuss next steps, please don't hesitate to contact our team.</p>
        </div>
    <?php endif; ?>
    
    <div style="text-align: center; margin: 25px 0;">
        <a href="<?php echo esc_url($portal_url); ?>" 
           style="background-color: <?php echo esc_attr($color_scheme['cta']); ?>; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
            View Project Details
        </a>
    </div>
    
    <p>You can always access your project details, view progress updates, and communicate with your team through your customer portal.</p>
    
    <?php if ($new_status === 'completed'): ?>
        <p>Thank you for choosing us for your project. We hope you're pleased with the results!</p>
    <?php else: ?>
        <p>Thank you for your continued trust in our team. We're committed to delivering excellent results.</p>
    <?php endif; ?>
    
    <p>Best regards,<br>
    The <?php echo esc_html(get_bloginfo('name')); ?> Team</p>
</div>
