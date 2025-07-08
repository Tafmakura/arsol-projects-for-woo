<?php
/**
 * Customer email template for project order ready
 * 
 * @var WP_Post $project The project object
 * @var int $order_id The WooCommerce order ID
 * @var WP_User $customer The customer user object
 * @var string $portal_url Portal URL for project view
 * @var string $email_heading Email heading
 * @var string $status_icon Status icon
 */

if (!defined('ABSPATH')) {
    exit;
}

$order = wc_get_order($order_id ?? 0);

do_action('woocommerce_email_header', $email_heading, $email);
?>

<p><?php echo esc_html($status_icon); ?> <?php printf(__('Congratulations %s!', 'arsol-pfw'), esc_html($customer->display_name)); ?></p>

<p><?php printf(__('Fantastic news! Your project "%s" has been approved and converted into an order. We\'re ready to begin work as soon as you complete your payment.', 'arsol-pfw'), '<strong>' . esc_html($project->post_title) . '</strong>'); ?></p>

<?php if ($order): ?>
<h2><?php printf(__('Order #%s Ready', 'arsol-pfw'), esc_html($order->get_order_number())); ?></h2>

<h3><?php _e('Order Summary', 'arsol-pfw'); ?></h3>
<ul>
    <li><strong><?php _e('Project:', 'arsol-pfw'); ?></strong> <?php echo esc_html($project->post_title); ?></li>
    <li><strong><?php _e('Order Date:', 'arsol-pfw'); ?></strong> <?php echo esc_html($order->get_date_created()->format(get_option('date_format'))); ?></li>
    <li><strong><?php _e('Order Number:', 'arsol-pfw'); ?></strong> #<?php echo esc_html($order->get_order_number()); ?></li>
    <li><strong><?php _e('Total:', 'arsol-pfw'); ?></strong> <?php echo wp_kses_post($order->get_formatted_order_total()); ?></li>
</ul>

<p>
    <a class="link" href="<?php echo esc_url($order->get_checkout_payment_url()); ?>"><?php _e('Complete Payment & Start Project', 'arsol-pfw'); ?></a>
</p>
<?php else: ?>
<p>
    <a class="link" href="<?php echo esc_url($portal_url); ?>"><?php _e('View Your Project', 'arsol-pfw'); ?></a>
</p>
<?php endif; ?>

<h3><?php _e('What Happens Next?', 'arsol-pfw'); ?></h3>
<ul>
    <li><?php _e('Complete your secure payment using the button above', 'arsol-pfw'); ?></li>
    <li><?php _e('You\'ll receive instant confirmation', 'arsol-pfw'); ?></li>
    <li><?php _e('Our team will begin work on your project immediately', 'arsol-pfw'); ?></li>
    <li><?php _e('You\'ll receive regular updates on progress', 'arsol-pfw'); ?></li>
    <li><?php _e('Track everything in your customer portal', 'arsol-pfw'); ?></li>
</ul>

<blockquote>
    <strong><?php _e('Ready to Start:', 'arsol-pfw'); ?></strong><br>
    <?php _e('We have everything prepared and our team is standing by. Once payment is processed, work begins within 24 hours!', 'arsol-pfw'); ?>
</blockquote>

<?php
do_action('woocommerce_email_footer', $email);
?> 