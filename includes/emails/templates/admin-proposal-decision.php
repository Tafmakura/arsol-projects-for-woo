<?php
/**
 * Admin Proposal Decision Email Template
 * 
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$status_icon = isset($status_icon) ? $status_icon : '📧';
$color_scheme = isset($color_scheme) ? $color_scheme : array('background' => '#cce5ff', 'text' => '#0073aa', 'cta' => '#0073aa');
$is_approved = ($decision === 'approved');
?>

<div style="background-color: <?php echo esc_attr($color_scheme['background']); ?>; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h2 style="color: <?php echo esc_attr($color_scheme['text']); ?>; margin: 0 0 15px 0;">
        <?php echo $is_approved ? '✅' : '❌'; ?> Customer <?php echo ucfirst($decision); ?> Proposal
    </h2>
    
    <p>A customer has made a decision on their proposal and action may be required.</p>
    
    <div style="background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <h3 style="margin-top: 0;">Proposal Details:</h3>
        <p><strong>Proposal ID:</strong> #<?php echo esc_html($proposal->ID); ?></p>
        <p><strong>Title:</strong> <?php echo esc_html($proposal->post_title); ?></p>
        <p><strong>Customer:</strong> <?php echo esc_html($customer->display_name); ?> (<?php echo esc_html($customer->user_email); ?>)</p>
        <p><strong>Decision:</strong> <span style="color: <?php echo $is_approved ? '#28a745' : '#dc3545'; ?>; font-weight: bold;"><?php echo ucfirst($decision); ?></span></p>
        <p><strong>Decision Date:</strong> <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'))); ?></p>
    </div>
    
    <?php if ($is_approved): ?>
        <div style="background-color: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0;">
            <h4 style="margin-top: 0; color: #155724;">🎉 Proposal Approved - Action Required</h4>
            <p style="color: #155724;"><strong>Next Steps:</strong></p>
            <ul style="color: #155724; margin-bottom: 0;">
                <li>Convert the proposal to a project</li>
                <li>Create the WooCommerce order</li>
                <li>Send order details to customer for payment</li>
                <li>Assign final project team and resources</li>
            </ul>
        </div>
        
        <p><strong>Conversion Process:</strong></p>
        <p>The proposal is ready for conversion to a project and order. Use the admin panel to initiate the conversion process.</p>
        
    <?php else: ?>
        <div style="background-color: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0;">
            <h4 style="margin-top: 0; color: #721c24;">❌ Proposal Rejected</h4>
            <p style="color: #721c24;"><strong>Customer Feedback:</strong></p>
            <p style="color: #721c24;">The customer has declined this proposal. Consider reaching out to understand their concerns or offer alternative solutions.</p>
        </div>
        
        <p><strong>Recommended Actions:</strong></p>
        <ul>
            <li>Review the proposal details to understand potential issues</li>
            <li>Contact the customer to gather feedback</li>
            <li>Consider creating a revised proposal if appropriate</li>
            <li>Document lessons learned for future proposals</li>
        </ul>
    <?php endif; ?>
    
    <div style="text-align: center; margin: 25px 0;">
        <a href="<?php echo esc_url($admin_url); ?>" 
           style="background-color: <?php echo esc_attr($color_scheme['cta']); ?>; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
            <?php echo $is_approved ? 'Convert to Project' : 'Review Proposal'; ?>
        </a>
    </div>
    
    <p style="font-size: 12px; color: #666; margin-top: 30px;">
        This email was sent to you because you have permission to manage Arsol projects. 
        <br>To manage your email preferences, visit the WooCommerce email settings.
    </p>
</div>
