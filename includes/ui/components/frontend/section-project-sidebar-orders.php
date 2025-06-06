<?php
/**
 * Project Orders Sidebar
 *
 * This template displays the sidebar on the project orders page,
 * showing a summary of project-related orders.
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

// Fetch and calculate order-related stats
$args = array(
    'post_type'      => 'shop_order',
    'post_status'    => array_keys(wc_get_order_statuses()),
    'meta_key'       => '_arsol_project_id', // Assuming this meta key links orders to projects
    'meta_value'     => $project_id,
    'posts_per_page' => -1,
);
$orders = get_posts($args);
$order_count = count($orders);
$total_spent = 0;

foreach ($orders as $order_post) {
    $order = wc_get_order($order_post->ID);
    if ($order) {
        $total_spent += $order->get_total();
    }
}
?>

<div class="arsol-project-sidebar">
    <h4><?php _e('Order Summary', 'arsol-pfw'); ?></h4>
    <div class="project-details">
        <p>
            <strong><?php _e('Total Orders:', 'arsol-pfw'); ?></strong>
            <span><?php echo esc_html($order_count); ?></span>
        </p>
        <p>
            <strong><?php _e('Total Spent:', 'arsol-pfw'); ?></strong>
            <span><?php echo wp_kses_post(wc_price($total_spent)); ?></span>
        </p>
    </div>
</div>
