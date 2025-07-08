<?php
/**
 * Customer email template for proposal ready for review
 * 
 * @var WP_Post $proposal The proposal object
 * @var WP_User $customer The customer user object
 * @var string $portal_url Portal URL for proposal review
 * @var string $email_heading Email heading
 * @var string $status_icon Status icon
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_email_header', $email_heading, $email);
?>

<p><?php echo esc_html($status_icon); ?> <?php printf(__('Hello %s!', 'arsol-pfw'), esc_html($customer->display_name)); ?></p>

<p><?php printf(__('Great news! Your project proposal "%s" has been completed and is now ready for your review.', 'arsol-pfw'), '<strong>' . esc_html($proposal->post_title) . '</strong>'); ?></p>

<h2><?php printf(__('Proposal #%d Ready for Review', 'arsol-pfw'), $proposal->ID); ?></h2>

<p><?php _e('Our team has carefully crafted a detailed proposal based on your requirements. Please review the proposal and let us know your decision.', 'arsol-pfw'); ?></p>

<h3><?php _e('What\'s Next?', 'arsol-pfw'); ?></h3>
<ul>
    <li><strong><?php _e('Review:', 'arsol-pfw'); ?></strong> <?php _e('Check all the details, timeline, and pricing', 'arsol-pfw'); ?></li>
    <li><strong><?php _e('Questions:', 'arsol-pfw'); ?></strong> <?php _e('Contact us if you need any clarification', 'arsol-pfw'); ?></li>
    <li><strong><?php _e('Decision:', 'arsol-pfw'); ?></strong> <?php _e('Approve or request modifications', 'arsol-pfw'); ?></li>
    <li><strong><?php _e('Start:', 'arsol-pfw'); ?></strong> <?php _e('Once approved, we\'ll begin your project immediately', 'arsol-pfw'); ?></li>
</ul>

<?php
// Get proposal details
$estimated_cost = get_post_meta($proposal->ID, '_arsol_pfw_proposal_estimated_cost', true);
$estimated_timeline = get_post_meta($proposal->ID, '_arsol_pfw_proposal_timeline', true);

if ($estimated_cost || $estimated_timeline):
?>
<h3><?php _e('Quick Overview', 'arsol-pfw'); ?></h3>
<ul>
    <?php if ($estimated_cost): ?>
    <li><strong><?php _e('Estimated Investment:', 'arsol-pfw'); ?></strong> <?php echo esc_html($estimated_cost); ?></li>
    <?php endif; ?>
    <?php if ($estimated_timeline): ?>
    <li><strong><?php _e('Estimated Timeline:', 'arsol-pfw'); ?></strong> <?php echo esc_html($estimated_timeline); ?></li>
    <?php endif; ?>
</ul>
<?php endif; ?>

<p>
    <a class="link" href="<?php echo esc_url($portal_url); ?>"><?php _e('Review Your Proposal', 'arsol-pfw'); ?></a>
</p>

<blockquote>
    <strong><?php _e('Action Required:', 'arsol-pfw'); ?></strong><br>
    <?php _e('Please review your proposal at your earliest convenience. If you have any questions or need modifications, don\'t hesitate to reach out to us. We\'re here to ensure your project exceeds your expectations!', 'arsol-pfw'); ?>
</blockquote>

<?php
do_action('woocommerce_email_footer', $email);
?> 