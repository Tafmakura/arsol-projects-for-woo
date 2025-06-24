<?php
/**
 * Customer Project Completion Email Template
 * 
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php printf(__('Hello %s,', 'arsol-projects-for-woo'), esc_html($customer->first_name ?: $customer->display_name)); ?></p>

<p><?php _e('🎉 <strong>Fantastic news!</strong> Your project has been completed successfully and all deliverables are now ready for your review.', 'arsol-projects-for-woo'); ?></p>

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
            <td class="td" style="text-align:left;"><strong style="color: #96588a;"><?php _e('✅ Completed', 'arsol-projects-for-woo'); ?></strong></td>
        </tr>
    </tbody>
</table>

<h3><?php _e('Your Deliverables Are Ready!', 'arsol-projects-for-woo'); ?></h3>

<p><?php _e('All project work has been completed according to your specifications. Here\'s what you can do now:', 'arsol-projects-for-woo'); ?></p>

<ul>
    <li><strong><?php _e('Access your deliverables:', 'arsol-projects-for-woo'); ?></strong> <?php _e('All final files and documentation are available in your customer portal', 'arsol-projects-for-woo'); ?></li>
    <li><strong><?php _e('Review everything:', 'arsol-projects-for-woo'); ?></strong> <?php _e('Take time to review all deliverables and documentation', 'arsol-projects-for-woo'); ?></li>
    <li><strong><?php _e('Download your files:', 'arsol-projects-for-woo'); ?></strong> <?php _e('Save all project files to your local system', 'arsol-projects-for-woo'); ?></li>
    <li><strong><?php _e('Get support:', 'arsol-projects-for-woo'); ?></strong> <?php _e('Contact us if you have any questions or need assistance', 'arsol-projects-for-woo'); ?></li>
</ul>

<div style="text-align: center; margin: 25px 0;">
    <a class="link" href="<?php echo esc_url($portal_url); ?>" style="background-color: #96588a; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
        <?php _e('Access Your Completed Project', 'arsol-projects-for-woo'); ?>
    </a>
</div>

<blockquote style="border-left: 4px solid #96588a; padding: 15px; margin: 20px 0; background-color: #f9f9f9;">
    <p style="margin: 0;"><strong><?php _e('Thank You!', 'arsol-projects-for-woo'); ?></strong></p>
    <p style="margin: 5px 0 0 0;"><?php _e('We hope you\'re thrilled with the results. Your project was a pleasure to work on, and we\'d love to help with any future projects you might have.', 'arsol-projects-for-woo'); ?></p>
</blockquote>

<p><?php _e('If you\'re happy with our work, we\'d be grateful if you could take a moment to leave us a review. Your feedback helps us continue to improve our services.', 'arsol-projects-for-woo'); ?></p>

<p>
    <?php _e('Best regards,', 'arsol-projects-for-woo'); ?><br>
    <?php printf(__('The %s Team', 'arsol-projects-for-woo'), esc_html(get_bloginfo('name'))); ?>
</p>

<?php do_action('woocommerce_email_footer', $email); 