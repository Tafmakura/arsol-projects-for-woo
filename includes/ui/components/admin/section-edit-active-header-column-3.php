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
$project_lead = get_post_meta($project_id, '_project_lead', true);
$project_status_terms = wp_get_object_terms($project_id, 'arsol-project-status', array('fields' => 'slugs'));
$project_status = !empty($project_status_terms) ? $project_status_terms[0] : 'not-started';

$all_statuses = get_terms(array(
    'taxonomy' => 'arsol-project-status',
    'hide_empty' => false,
));
?>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Project Status:', 'arsol-pfw'); ?></strong></label>
    <select id="project_status_col3" name="project_status" class="wc-enhanced-select">
        <?php foreach ($all_statuses as $status) : ?>
            <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($project_status, $status->slug); ?>>
                <?php echo esc_html($status->name); ?>
            </option>
        <?php endforeach; ?>
    </select>
</p>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Project Lead:', 'arsol-pfw'); ?></strong></label>
    <?php
    $lead_dropdown = wp_dropdown_users(array(
        'name' => 'project_lead',
        'selected' => $project_lead,
        'include_selected' => true,
        'echo' => false,
        'class' => 'wc-enhanced-select',
        'show_option_none' => __('Select Project Lead', 'arsol-pfw'),
        'option_none_value' => '',
        'capability' => 'edit_posts'
    ));
    echo $lead_dropdown;
    ?>
</p>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Project Status:', 'arsol-pfw'); ?></strong></label>
    <select id="project_status_col3" name="project_status" class="wc-enhanced-select">
        <?php foreach ($all_statuses as $status) : ?>
            <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($project_status, $status->slug); ?>>
                <?php echo esc_html($status->name); ?>
            </option>
        <?php endforeach; ?>
    </select>
</p>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Project Lead:', 'arsol-pfw'); ?></strong></label>
    <?php
    $lead_dropdown = wp_dropdown_users(array(
        'name' => 'project_lead',
        'selected' => $project_lead,
        'include_selected' => true,
        'echo' => false,
        'class' => 'wc-enhanced-select',
        'show_option_none' => __('Select Project Lead', 'arsol-pfw'),
        'option_none_value' => '',
        'capability' => 'edit_posts'
    ));
    echo $lead_dropdown;
    ?>
</p>

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

<p class="form-field form-field-wide">
    <label><strong><?php _e('Actions:', 'arsol-pfw'); ?></strong></label>
    <a href="<?php echo admin_url('edit.php?post_type=shop_order&project_id=' . $project_id); ?>" class="button">
        <?php _e('View Orders', 'arsol-pfw'); ?>
    </a>
    <?php if (class_exists('WC_Subscriptions')): ?>
    <a href="<?php echo admin_url('edit.php?post_type=shop_subscription&project_id=' . $project_id); ?>" class="button">
        <?php _e('View Subscriptions', 'arsol-pfw'); ?>
    </a>
    <?php endif; ?>
</p> 