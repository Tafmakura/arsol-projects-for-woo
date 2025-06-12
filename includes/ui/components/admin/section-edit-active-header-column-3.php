<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-project') {
    return;
}

$project_id = $post->ID;
$project_orders = get_post_meta($project_id, '_project_orders', true);
$project_subscriptions = get_post_meta($project_id, '_project_subscriptions', true);
?>

<?php if (!empty($project_orders) && is_array($project_orders)): ?>
<p class="form-field form-field-wide">
    <label><strong><?php _e('Connected Orders:', 'arsol-pfw'); ?></strong></label>
    <?php foreach ($project_orders as $order_id): ?>
        <a href="<?php echo admin_url('post.php?post=' . $order_id . '&action=edit'); ?>" target="_blank">
            <?php printf(__('Order #%d', 'arsol-pfw'), $order_id); ?>
        </a><br>
    <?php endforeach; ?>
</p>
<?php endif; ?>

<?php if (!empty($project_subscriptions) && is_array($project_subscriptions) && class_exists('WC_Subscriptions')): ?>
<p class="form-field form-field-wide">
    <label><strong><?php _e('Connected Subscriptions:', 'arsol-pfw'); ?></strong></label>
    <?php foreach ($project_subscriptions as $subscription_id): ?>
        <a href="<?php echo admin_url('post.php?post=' . $subscription_id . '&action=edit'); ?>" target="_blank">
            <?php printf(__('Subscription #%d', 'arsol-pfw'), $subscription_id); ?>
        </a><br>
    <?php endforeach; ?>
</p>
<?php endif; ?> 