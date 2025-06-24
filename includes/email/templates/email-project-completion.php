<?php
/**
 * Project Completion Email Template
 * 
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php printf(__('Hello %s,', 'arsol-projects-for-woo'), esc_html($customer->first_name ?: $customer->display_name)); ?></p>

<p><?php _e('Great news! Your project has been completed successfully. We\'re excited to share the final deliverables with you.', 'arsol-projects-for-woo'); ?></p>

<h2><?php _e('Project Summary', 'arsol-projects-for-woo'); ?></h2>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
    <tbody>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Project ID:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;">#<?php echo esc_html($project->ID); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Project Title:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html($project->post_title); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Completed Date:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html(date_i18n(get_option('date_format'))); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Status:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;"><strong><?php _e('Completed', 'arsol-projects-for-woo'); ?></strong></td>
        </tr>
    </tbody>
</table>

<h3><?php _e('What\'s Next?', 'arsol-projects-for-woo'); ?></h3>

<ul>
    <li><?php _e('Access your project deliverables in the customer portal', 'arsol-projects-for-woo'); ?></li>
    <li><?php _e('Review the final project documentation', 'arsol-projects-for-woo'); ?></li>
    <li><?php _e('Contact us if you have any questions or need support', 'arsol-projects-for-woo'); ?></li>
    <li><?php _e('Consider leaving a review of our services', 'arsol-projects-for-woo'); ?></li>
</ul>

<div style="text-align: center; margin: 25px 0;">
    <a class="link" href="<?php echo esc_url($portal_url); ?>" style="background-color: #96588a; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
        <?php _e('View Project Details', 'arsol-projects-for-woo'); ?>
    </a>
</div>

<p><?php _e('Thank you for choosing us for your project. We hope you\'re pleased with the results and look forward to working with you again in the future.', 'arsol-projects-for-woo'); ?></p>

<p>
    <?php _e('Best regards,', 'arsol-projects-for-woo'); ?><br>
    <?php printf(__('The %s Team', 'arsol-projects-for-woo'), esc_html(get_bloginfo('name'))); ?>
</p>

<?php do_action('woocommerce_email_footer', $email); 