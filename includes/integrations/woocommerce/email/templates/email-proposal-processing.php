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

<p><?php printf(__('Hello %s,', 'arsol-pfw'), esc_html($customer->first_name ?: $customer->display_name)); ?></p>
    
<p><?php _e('Great news! We\'ve started working on your proposal and wanted to keep you informed of our progress.', 'arsol-pfw'); ?></p>
    
<h2><?php _e('Proposal Details', 'arsol-pfw'); ?></h2>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
    <tbody>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Proposal ID:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;">#<?php echo esc_html($proposal->ID); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Title:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html($proposal->post_title); ?></td>
        </tr>
        <?php if ($project_manager): ?>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Project Manager:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;"><?php echo esc_html($project_manager->first_name . ' ' . $project_manager->last_name); ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php _e('Status:', 'arsol-pfw'); ?></th>
            <td class="td" style="text-align:left;"><strong><?php _e('In Progress', 'arsol-pfw'); ?></strong></td>
        </tr>
    </tbody>
</table>

<h3><?php _e('What we\'re working on:', 'arsol-pfw'); ?></h3>
    <ul>
    <li><?php _e('Detailed project scope and requirements analysis', 'arsol-pfw'); ?></li>
    <li><?php _e('Resource allocation and timeline planning', 'arsol-pfw'); ?></li>
    <li><?php _e('Accurate pricing and cost estimation', 'arsol-pfw'); ?></li>
    <li><?php _e('Technical specifications and deliverables', 'arsol-pfw'); ?></li>
    </ul>
    
<h3><?php _e('Timeline:', 'arsol-pfw'); ?></h3>
<p><?php _e('We typically complete proposals within 3-5 business days. You\'ll receive a notification as soon as your proposal is ready for review.', 'arsol-pfw'); ?></p>
    
    <?php if ($project_manager): ?>
<h3><?php _e('Your Project Lead', 'arsol-pfw'); ?></h3>
<p><strong><?php echo esc_html($project_manager->first_name . ' ' . $project_manager->last_name); ?></strong> <?php _e('has been assigned to your project and will be your primary point of contact throughout the proposal process.', 'arsol-pfw'); ?></p>
    <?php endif; ?>
    
    <div style="text-align: center; margin: 25px 0;">
    <a class="link" href="<?php echo esc_url($portal_url); ?>" style="background-color: #96588a; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
        <?php _e('Track Progress in Portal', 'arsol-pfw'); ?>
        </a>
    </div>
    
<p><?php _e('You can monitor the progress and view all updates in your customer portal. We\'ll keep you informed of any major milestones.', 'arsol-pfw'); ?></p>

<p><?php _e('If you have any questions or additional requirements, please don\'t hesitate to reach out.', 'arsol-pfw'); ?></p>
    
<p>
    <?php _e('Best regards,', 'arsol-pfw'); ?><br>
    <?php printf(__('The %s Team', 'arsol-pfw'), esc_html(get_bloginfo('name'))); ?>
</p>
    
<?php do_action('woocommerce_email_footer', $email);
