<?php
/**
 * Request Stage Email Template
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/*
 * Template variables:
 * $request - The request post object
 * $request_id - Request ID
 * $old_stage - Previous stage
 * $new_stage - New stage 
 * $customer_id - Customer ID
 * $email_heading - Email heading
 * $email - Email object
 */

$customer = get_userdata($customer_id);

// Define contextual messages based on stage transitions
$stage_messages = array(
    'pending-review' => array(
        'title' => 'Request Under Review',
        'icon' => '🔍',
        'message' => 'Your request is now being reviewed by our team.'
    ),
    'under-review' => array(
        'title' => 'Request Processing',
        'icon' => '⚙️', 
        'message' => 'We are actively processing your request.'
    ),
    'on-hold' => array(
        'title' => 'Request On Hold',
        'icon' => '⏸️',
        'message' => 'Your request has been temporarily placed on hold.'
    ),
    'approved' => array(
        'title' => 'Request Approved',
        'icon' => '✅',
        'message' => 'Great news! Your request has been approved.'
    ),
    'rejected' => array(
        'title' => 'Request Update',
        'icon' => '❌',
        'message' => 'Your request stage has been updated.'
    )
);

$current_message = $stage_messages[$new_stage] ?? $stage_messages['pending-review'];
$stage_icon = $current_message['icon'];

echo "= " . $email_heading . " =\n\n";

?>

<p><?php echo esc_html($stage_icon); ?> <?php printf(__('Hello %s!', 'arsol-pfw'), esc_html($customer->display_name)); ?></p>

<p><?php echo esc_html($current_message['message']); ?></p>

<h2><?php _e('Request Details', 'arsol-pfw'); ?></h2>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
    <tbody>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Request:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html($request->post_title); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Request ID:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;">#<?php echo esc_html($request_id); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Previous Stage:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html(ucfirst(str_replace('-', ' ', $old_stage))); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Current Stage:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html(ucfirst(str_replace('-', ' ', $new_stage))); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Date Updated:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html(date_i18n(wc_date_format(), current_time('timestamp'))); ?></td>
        </tr>
    </tbody>
</table>

<?php if ($new_stage === 'approved'): ?>
<h2><?php _e('Next Steps', 'arsol-pfw'); ?></h2>
<p><?php _e('Your request has been approved! Our team will now begin preparing a detailed proposal for your project.', 'arsol-pfw'); ?></p>

<p><?php _e('You will receive another notification once your proposal is ready for review.', 'arsol-pfw'); ?></p>

<?php elseif ($new_stage === 'on-hold'): ?>
<h2><?php _e('Why Is My Request On Hold?', 'arsol-pfw'); ?></h2>
<p><?php _e('Your request has been temporarily placed on hold. This typically happens when:', 'arsol-pfw'); ?></p>
<ul>
    <li><?php _e('We need additional information or clarification from you', 'arsol-pfw'); ?></li>
    <li><?php _e('We are waiting for external dependencies', 'arsol-pfw'); ?></li>
    <li><?php _e('We need to review technical requirements', 'arsol-pfw'); ?></li>
</ul>
<p><?php _e('Our team will contact you soon with more details about what is needed to proceed.', 'arsol-pfw'); ?></p>

<?php elseif ($new_stage === 'under-review'): ?>
<h2><?php _e('What This Means', 'arsol-pfw'); ?></h2>
<p><?php _e('Your request is now being actively reviewed by our team. We are evaluating:', 'arsol-pfw'); ?></p>
<ul>
    <li><?php _e('Project feasibility and requirements', 'arsol-pfw'); ?></li>
    <li><?php _e('Timeline and resource allocation', 'arsol-pfw'); ?></li>
    <li><?php _e('Technical specifications and approach', 'arsol-pfw'); ?></li>
</ul>
<p><?php _e('We will update you on the progress soon.', 'arsol-pfw'); ?></p>

<?php endif; ?>

<h2><?php _e('Stay Connected', 'arsol-pfw'); ?></h2>

<p><?php _e('If you have any questions about this stage change or would like to discuss your request, please don\'t hesitate to contact our team.', 'arsol-pfw'); ?></p>

<p><?php _e('You can always check the current stage and view all details in your customer portal.', 'arsol-pfw'); ?></p>

<p>
    <a href="<?php echo esc_url(wc_get_account_endpoint_url('project-view-request')); ?>" target="_blank">
        <?php _e('View Request Details', 'arsol-pfw'); ?>
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
