<?php
/**
 * Project Stage Email Template
 * 
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/*
 * Template variables:
 * $project - The project post object
 * $project_id - Project ID
 * $old_stage - Previous stage
 * $new_stage - New stage 
 * $project_lead_id - Project lead ID
 * $email_heading - Email heading
 * $email - Email object
 */

$customer = get_userdata($project->post_author);
$project_lead = get_userdata($project_lead_id);

// Define contextual messages based on stage transitions
$stage_messages = array(
    'not-started' => array(
        'title' => 'Project Stage Update',
        'icon' => '📋',
        'message' => 'Your project stage has been updated. Please see the details below.'
    ),
    'in-progress' => array(
        'title' => 'Project Stage Update',
        'icon' => '🚀',
        'message' => 'We wanted to update you on your project stage.'
    ),
    'completed' => array(
        'title' => 'Project Completed',
        'icon' => '✅',
        'message' => 'Congratulations! Your project has been completed.'
    ),
    'on-hold' => array(
        'title' => 'Project On Hold',
        'icon' => '⏸️',
        'message' => 'Your project has been temporarily placed on hold.'
    ),
    'cancelled' => array(
        'title' => 'Project Cancelled',
        'icon' => '❌',
        'message' => 'Your project has been cancelled.'
    )
);

$current_message = $stage_messages[$new_stage] ?? $stage_messages['not-started'];
$status_icon = $current_message['icon'];

echo "= " . $email_heading . " =\n\n";

?>

<p><?php echo esc_html($status_icon); ?> <?php printf(__('Hello %s!', 'arsol-pfw'), esc_html($customer->display_name)); ?></p>

<p><?php echo esc_html($current_message['message']); ?></p>

<h2><?php _e('Project Details', 'arsol-pfw'); ?></h2>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
    <tbody>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Project:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html($project->post_title); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Project ID:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;">#<?php echo esc_html($project_id); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Previous Stage:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html(ucfirst(str_replace('-', ' ', $old_stage))); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Current Stage:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html(ucfirst(str_replace('-', ' ', $new_stage))); ?></td>
        </tr>
        <?php if ($project_lead): ?>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Project Lead:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html($project_lead->display_name); ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Date Updated:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html(date_i18n(wc_date_format(), current_time('timestamp'))); ?></td>
        </tr>
    </tbody>
</table>
    
<?php if ($new_stage === 'completed'): ?>
<h2><?php _e('Project Completion', 'arsol-pfw'); ?></h2>
<p><?php _e('Thank you for working with us on this project. We hope you are satisfied with the results.', 'arsol-pfw'); ?></p>

<p><?php _e('If you have any questions or feedback about the completed project, please don\'t hesitate to contact us.', 'arsol-pfw'); ?></p>

<?php elseif ($new_stage === 'on-hold'): ?>
<h2><?php _e('Next Steps', 'arsol-pfw'); ?></h2>
<p><?php _e('Your project has been temporarily placed on hold. We will contact you soon with more information about when work will resume.', 'arsol-pfw'); ?></p>

<?php elseif ($new_stage === 'in-progress'): ?>
<h2><?php _e('What This Means', 'arsol-pfw'); ?></h2>
<p><?php _e('Great news! Work has begun on your project. Our team is now actively working to bring your vision to life.', 'arsol-pfw'); ?></p>

<p><?php _e('We will keep you updated as the project progresses and will reach out if we need any additional information from you.', 'arsol-pfw'); ?></p>

    <?php endif; ?>
    
<h2><?php _e('Stay Updated', 'arsol-pfw'); ?></h2>

<p><?php _e('If you have any questions about this stage change or would like to discuss next steps, please don\'t hesitate to contact our team.', 'arsol-pfw'); ?></p>

<p><?php _e('You can always check the current stage and view all communications in your customer portal.', 'arsol-pfw'); ?></p>

<p>
    <a href="<?php echo esc_url(wc_get_account_endpoint_url('view-project')); ?>" target="_blank">
        <?php _e('View Project Dashboard', 'arsol-pfw'); ?>
    </a>
</p>

<?php

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ($additional_content) {
    echo wp_kses_post($additional_content);
}

?>
