<?php
/**
 * New Proposal Email Template
 * 
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php printf(__('Hello %s,', 'arsol-projects-for-woo'), esc_html($customer->first_name ?: $customer->display_name)); ?></p>

<p><?php _e('A new project proposal has been created for your request. Our team has prepared a detailed proposal for your review.', 'arsol-projects-for-woo'); ?></p>

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
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Created:', 'arsol-projects-for-woo'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($proposal->post_date))); ?></td>
        </tr>
    </tbody>
</table>

<p><?php _e('The proposal includes detailed information about:', 'arsol-projects-for-woo'); ?></p>

<ul>
    <li><?php _e('Project scope and deliverables', 'arsol-projects-for-woo'); ?></li>
    <li><?php _e('Timeline and milestones', 'arsol-projects-for-woo'); ?></li>
    <li><?php _e('Pricing and payment terms', 'arsol-projects-for-woo'); ?></li>
    <li><?php _e('Technical specifications', 'arsol-projects-for-woo'); ?></li>
</ul>

<div style="text-align: center; margin: 25px 0;">
    <a class="link" href="<?php echo esc_url($portal_url); ?>" style="background-color: #96588a; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
        <?php _e('Review Proposal', 'arsol-projects-for-woo'); ?>
    </a>
</div>

<p><?php _e('Please review the proposal carefully and let us know if you have any questions or need clarification on any aspect.', 'arsol-projects-for-woo'); ?></p>

<p>
    <?php _e('Best regards,', 'arsol-projects-for-woo'); ?><br>
    <?php printf(__('The %s Team', 'arsol-projects-for-woo'), esc_html(get_bloginfo('name'))); ?>
</p>

<?php do_action('woocommerce_email_footer', $email); 