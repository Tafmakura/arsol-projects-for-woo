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

do_action('woocommerce_email_header', $email_heading, $email);
?>

<p><?php printf(__('Hello %s,', 'arsol-projects-for-woo'), esc_html($customer->first_name ?: $customer->display_name)); ?></p>

<p><?php echo $status_info['icon']; ?> <?php echo esc_html($status_info['message']); ?></p>

<h2><?php echo esc_html($status_info['title']); ?></h2>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
    <tbody>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Project ID:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;">#<?php echo esc_html($project->ID); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Title:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html($project->post_title); ?></td>
        </tr>
        <?php if ($project_lead): ?>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Project Lead:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html($project_lead->first_name . ' ' . $project_lead->last_name); ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Previous Status:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html($old_status ? ucfirst(str_replace('-', ' ', $old_status)) : 'N/A'); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Current Status:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;"><strong><?php echo esc_html($status_label); ?></strong></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Updated:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'))); ?></td>
        </tr>
    </tbody>
</table>

<?php if ($new_status === 'in-progress'): ?>
    <h3><?php _e('🎯 What\'s Happening Now', 'arsol-projects-for-woo'); ?></h3>
    <ul>
        <li><?php _e('Your dedicated project team is actively working on your requirements', 'arsol-projects-for-woo'); ?></li>
        <li><?php _e('Regular progress updates will be shared in your portal', 'arsol-projects-for-woo'); ?></li>
        <li><?php _e('You can expect milestone updates as work progresses', 'arsol-projects-for-woo'); ?></li>
        <li><?php _e('Your project lead will reach out if any input is needed', 'arsol-projects-for-woo'); ?></li>
    </ul>
    
<?php elseif ($new_status === 'on-hold'): ?>
    <h3><?php _e('⏸️ Why Is My Project On Hold?', 'arsol-projects-for-woo'); ?></h3>
    <p><?php _e('Common reasons for project holds include:', 'arsol-projects-for-woo'); ?></p>
    <ul>
        <li><?php _e('Additional information or clarification needed from you', 'arsol-projects-for-woo'); ?></li>
        <li><?php _e('External dependencies or third-party requirements', 'arsol-projects-for-woo'); ?></li>
        <li><?php _e('Resource reallocation or technical considerations', 'arsol-projects-for-woo'); ?></li>
        <li><?php _e('Changes to project scope or requirements', 'arsol-projects-for-woo'); ?></li>
    </ul>
    <p><strong><?php _e('Next Steps:', 'arsol-projects-for-woo'); ?></strong> <?php _e('Your project lead will contact you with specific details about the hold and what\'s needed to resume work.', 'arsol-projects-for-woo'); ?></p>
    
<?php elseif ($new_status === 'completed'): ?>
    <h3><?php _e('🎊 Project Deliverables Ready!', 'arsol-projects-for-woo'); ?></h3>
    <ul>
        <li><?php _e('All project deliverables have been completed', 'arsol-projects-for-woo'); ?></li>
        <li><?php _e('Final files and documentation are available in your portal', 'arsol-projects-for-woo'); ?></li>
        <li><?php _e('Please review everything and confirm completion', 'arsol-projects-for-woo'); ?></li>
        <li><?php _e('Our team is available for any questions or support', 'arsol-projects-for-woo'); ?></li>
    </ul>
    
<?php elseif ($new_status === 'cancelled'): ?>
    <h3><?php _e('Project Cancellation', 'arsol-projects-for-woo'); ?></h3>
    <p><?php _e('If you have any questions about this status change or would like to discuss next steps, please don\'t hesitate to contact our team.', 'arsol-projects-for-woo'); ?></p>
<?php endif; ?>

<div style="text-align: center; margin: 25px 0;">
    <a class="link" href="<?php echo esc_url($portal_url); ?>" style="background-color: #96588a; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
        <?php _e('View Project Details', 'arsol-projects-for-woo'); ?>
    </a>
</div>

<p><?php _e('You can always access your project details, view progress updates, and communicate with your team through your customer portal.', 'arsol-projects-for-woo'); ?></p>

<?php if ($new_status === 'completed'): ?>
    <p><?php _e('Thank you for choosing us for your project. We hope you\'re pleased with the results!', 'arsol-projects-for-woo'); ?></p>
<?php else: ?>
    <p><?php _e('Thank you for your continued trust in our team. We\'re committed to delivering excellent results.', 'arsol-projects-for-woo'); ?></p>
<?php endif; ?>

<p>
    <?php _e('Best regards,', 'arsol-projects-for-woo'); ?><br>
    <?php printf(__('The %s Team', 'arsol-projects-for-woo'), esc_html(get_bloginfo('name'))); ?>
</p>

<?php do_action('woocommerce_email_footer', $email);
