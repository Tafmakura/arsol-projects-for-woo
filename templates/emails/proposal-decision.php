<?php
/**
 * Proposal Decision Email Template
 * 
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php printf(__('Hello %s,', 'arsol-projects-for-woo'), esc_html($project_lead->first_name ?: $project_lead->display_name)); ?></p>

<p><?php printf(__('A decision has been made on proposal #%d.', 'arsol-projects-for-woo'), $proposal->ID); ?></p>

<h2><?php _e('Proposal Decision', 'arsol-projects-for-woo'); ?></h2>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
    <tbody>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Proposal ID:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;">#<?php echo esc_html($proposal->ID); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Proposal Title:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html($proposal->post_title); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Customer:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html($customer->first_name . ' ' . $customer->last_name); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Decision:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;"><strong><?php echo esc_html(ucfirst($decision)); ?></strong></td>
        </tr>
    </tbody>
</table>

<?php if ($decision === 'approved'): ?>
    <h3><?php _e('Next Steps', 'arsol-projects-for-woo'); ?></h3>
    <ul>
        <li><?php _e('Create project from approved proposal', 'arsol-projects-for-woo'); ?></li>
        <li><?php _e('Set up project timeline and milestones', 'arsol-projects-for-woo'); ?></li>
        <li><?php _e('Notify customer of project creation', 'arsol-projects-for-woo'); ?></li>
        <li><?php _e('Begin project work according to timeline', 'arsol-projects-for-woo'); ?></li>
    </ul>
<?php else: ?>
    <h3><?php _e('Follow-up Actions', 'arsol-projects-for-woo'); ?></h3>
    <ul>
        <li><?php _e('Review customer feedback on proposal', 'arsol-projects-for-woo'); ?></li>
        <li><?php _e('Consider revisions if requested', 'arsol-projects-for-woo'); ?></li>
        <li><?php _e('Discuss alternative approaches with customer', 'arsol-projects-for-woo'); ?></li>
    </ul>
<?php endif; ?>

<div style="text-align: center; margin: 25px 0;">
    <a class="link" href="<?php echo esc_url($admin_url); ?>" style="background-color: #96588a; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
        <?php _e('View Proposal Details', 'arsol-projects-for-woo'); ?>
    </a>
</div>

<p>
    <?php _e('Best regards,', 'arsol-projects-for-woo'); ?><br>
    <?php printf(__('The %s Team', 'arsol-projects-for-woo'), esc_html(get_bloginfo('name'))); ?>
</p>

<?php do_action('woocommerce_email_footer', $email); 