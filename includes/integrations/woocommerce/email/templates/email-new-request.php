<?php
/**
 * New Request Email Template
 * 
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php printf(__('Hello %s,', 'arsol-pfw'), esc_html($customer->first_name ?: $customer->display_name)); ?></p>

<p><?php _e('Thank you for submitting your project request! We\'ve received your request and our team will review it shortly.', 'arsol-pfw'); ?></p>

<h2><?php _e('Request Details', 'arsol-pfw'); ?></h2>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
    <tbody>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Request ID:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;">#<?php echo esc_html($request->ID); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Title:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html($request->post_title); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Submitted:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($request->post_date))); ?></td>
        </tr>
    </tbody>
</table>

<h3><?php _e('What happens next?', 'arsol-pfw'); ?></h3>

<ol>
    <li><?php _e('Our team will review your request (typically within 1-2 business days)', 'arsol-pfw'); ?></li>
    <li><?php _e('We\'ll contact you if we need any additional information', 'arsol-pfw'); ?></li>
    <li><?php _e('Once approved, we\'ll create a detailed proposal for your project', 'arsol-pfw'); ?></li>
    <li><?php _e('You can review and approve the proposal in your customer portal', 'arsol-pfw'); ?></li>
    </ol>
    
    <div style="text-align: center; margin: 25px 0;">
    <a class="link" href="<?php echo esc_url($portal_url); ?>" style="background-color: #96588a; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
        <?php _e('View Request in Portal', 'arsol-pfw'); ?>
        </a>
    </div>
    
<p><?php _e('You can track the progress of your request and view all communications in your customer portal at any time.', 'arsol-pfw'); ?></p>

<p><?php _e('If you have any questions, please don\'t hesitate to contact us.', 'arsol-pfw'); ?></p>
    
<p>
    <?php _e('Best regards,', 'arsol-pfw'); ?><br>
    <?php printf(__('The %s Team', 'arsol-pfw'), esc_html(get_bloginfo('name'))); ?>
</p>
    
<?php do_action('woocommerce_email_footer', $email);
