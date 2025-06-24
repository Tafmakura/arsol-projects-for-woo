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

do_action('woocommerce_email_header', $email_heading, $email);
?>

<p><?php printf(__('Hello %s,', 'arsol-projects-for-woo'), esc_html($customer->first_name ?: $customer->display_name)); ?></p>

<p><?php _e('Great news! We\'ve started working on your proposal and wanted to keep you informed of our progress.', 'arsol-projects-for-woo'); ?></p>

<h2><?php _e('Proposal Details', 'arsol-projects-for-woo'); ?></h2>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
    <tbody>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Proposal ID:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;">#<?php echo esc_html($proposal->ID); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Title:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html($proposal->post_title); ?></td>
        </tr>
        <?php if ($project_lead): ?>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Project Lead:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html($project_lead->first_name . ' ' . $project_lead->last_name); ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Status:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;"><strong><?php _e('In Progress', 'arsol-projects-for-woo'); ?></strong></td>
        </tr>
    </tbody>
</table>

<h3><?php _e('What we\'re working on:', 'arsol-projects-for-woo'); ?></h3>
<ul>
    <li><?php _e('Detailed project scope and requirements analysis', 'arsol-projects-for-woo'); ?></li>
    <li><?php _e('Resource allocation and timeline planning', 'arsol-projects-for-woo'); ?></li>
    <li><?php _e('Accurate pricing and cost estimation', 'arsol-projects-for-woo'); ?></li>
    <li><?php _e('Technical specifications and deliverables', 'arsol-projects-for-woo'); ?></li>
</ul>

<h3><?php _e('Timeline:', 'arsol-projects-for-woo'); ?></h3>
<p><?php _e('We typically complete proposals within 3-5 business days. You\'ll receive a notification as soon as your proposal is ready for review.', 'arsol-projects-for-woo'); ?></p>

<?php if ($project_lead): ?>
<h3><?php _e('Your Project Lead', 'arsol-projects-for-woo'); ?></h3>
<p><strong><?php echo esc_html($project_lead->first_name . ' ' . $project_lead->last_name); ?></strong> <?php _e('has been assigned to your project and will be your primary point of contact throughout the proposal process.', 'arsol-projects-for-woo'); ?></p>
<?php endif; ?>

<div style="text-align: center; margin: 25px 0;">
    <a class="link" href="<?php echo esc_url($portal_url); ?>" style="background-color: #96588a; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
        <?php _e('Track Progress in Portal', 'arsol-projects-for-woo'); ?>
    </a>
</div>

<p><?php _e('You can monitor the progress and view all updates in your customer portal. We\'ll keep you informed of any major milestones.', 'arsol-projects-for-woo'); ?></p>

<p><?php _e('If you have any questions or additional requirements, please don\'t hesitate to reach out.', 'arsol-projects-for-woo'); ?></p>

<p>
    <?php _e('Best regards,', 'arsol-projects-for-woo'); ?><br>
    <?php printf(__('The %s Team', 'arsol-projects-for-woo'), esc_html(get_bloginfo('name'))); ?>
</p>

<?php do_action('woocommerce_email_footer', $email);
