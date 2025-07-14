<?php
/**
 * Admin email template for new project requests
 * 
 * @var WP_Post $request The request object
 * @var WP_User $customer The customer user object
 * @var string $admin_url Admin edit URL
 * @var string $email_heading Email heading
 * @var string $status_icon Status icon
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_email_header', $email_heading, $email);
?>

<p><?php echo esc_html($status_icon); ?> <?php printf(__('A new project request has been submitted by %s and requires your attention.', 'arsol-pfw'), esc_html($customer->display_name)); ?></p>

<h2><?php printf(__('Request #%d: %s', 'arsol-pfw'), $request->ID, esc_html($request->post_title)); ?></h2>

<h3><?php _e('Customer Information', 'arsol-pfw'); ?></h3>
<ul>
    <li><strong><?php _e('Name:', 'arsol-pfw'); ?></strong> <?php echo esc_html($customer->display_name); ?></li>
    <li><strong><?php _e('Email:', 'arsol-pfw'); ?></strong> <?php echo esc_html($customer->user_email); ?></li>
    <li><strong><?php _e('Submitted:', 'arsol-pfw'); ?></strong> <?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($request->post_date))); ?></li>
    <li><strong><?php _e('Status:', 'arsol-pfw'); ?></strong> <?php echo esc_html(ucfirst(str_replace('-', ' ', $request->post_status))); ?></li>
</ul>

<?php if (!empty($request->post_content)): ?>
<h3><?php _e('Request Description', 'arsol-pfw'); ?></h3>
<blockquote>
    <?php echo wp_kses_post(wpautop($request->post_content)); ?>
</blockquote>
<?php endif; ?>

<?php
// Get custom fields
$budget = get_post_meta($request->ID, '_arsol_pfw_requested_project_budget', true);
?>

<?php if ($budget): ?>
<h3><?php _e('Additional Details', 'arsol-pfw'); ?></h3>
<ul>
    <li><strong><?php _e('Budget:', 'arsol-pfw'); ?></strong> <?php echo esc_html($budget); ?></li>
</ul>
<?php endif; ?>

<p>
    <a class="link" href="<?php echo esc_url($admin_url); ?>"><?php _e('Review & Manage Request', 'arsol-pfw'); ?></a>
</p>

<h3><?php _e('Next Steps', 'arsol-pfw'); ?></h3>
<ul>
    <li><?php _e('Review the request details', 'arsol-pfw'); ?></li>
    <li><?php _e('Contact the customer if clarification is needed', 'arsol-pfw'); ?></li>
    <li><?php _e('Convert to proposal when ready', 'arsol-pfw'); ?></li>
    <li><?php _e('Update the request status to keep the customer informed', 'arsol-pfw'); ?></li>
</ul>

<?php
do_action('woocommerce_email_footer', $email);
?> 