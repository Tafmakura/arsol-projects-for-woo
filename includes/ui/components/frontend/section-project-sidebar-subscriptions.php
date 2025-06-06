<?php
/**
 * Project Subscriptions Sidebar
 *
 * This template displays the sidebar on the project subscriptions page,
 * showing a summary of project-related subscriptions.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!isset($project_id)) {
    return;
}

// Fetch and calculate subscription-related stats
$subscription_count = 0;
$total_recurring = 0;

if (class_exists('WC_Subscriptions')) {
    $args = array(
        'post_type'      => 'shop_subscription',
        'post_status'    => 'any',
        'meta_key'       => '_arsol_project_id', // Assuming this meta key links subscriptions to projects
        'meta_value'     => $project_id,
        'posts_per_page' => -1,
    );
    $subscriptions = get_posts($args);
    $subscription_count = count($subscriptions);

    foreach ($subscriptions as $sub_post) {
        $subscription = wcs_get_subscription($sub_post->ID);
        if ($subscription) {
            $total_recurring += $subscription->get_total();
        }
    }
}
?>

<div class="arsol-project-sidebar">
    <h4><?php _e('Subscription Summary', 'arsol-pfw'); ?></h4>
    <div class="project-details">
        <?php if (class_exists('WC_Subscriptions')): ?>
            <p>
                <strong><?php _e('Total Subscriptions:', 'arsol-pfw'); ?></strong>
                <span><?php echo esc_html($subscription_count); ?></span>
            </p>
            <p>
                <strong><?php _e('Total Recurring:', 'arsol-pfw'); ?></strong>
                <span><?php echo wp_kses_post(wc_price($total_recurring)); ?> / month</span>
            </p>
        <?php else: ?>
            <p><?php _e('WooCommerce Subscriptions is not active.', 'arsol-pfw'); ?></p>
        <?php endif; ?>
    </div>
</div>
