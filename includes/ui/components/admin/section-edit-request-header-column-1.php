<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-request') {
    return;
}

$request_id = $post->ID;
$customer_id = $post->post_author;
$customer = get_userdata($customer_id);
$request_status_terms = wp_get_object_terms($request_id, 'arsol-request-status', array('fields' => 'slugs'));
$request_status = !empty($request_status_terms) ? $request_status_terms[0] : 'pending';
$budget_data = get_post_meta($request_id, '_request_budget', true);
$start_date = get_post_meta($request_id, '_request_start_date', true);
$delivery_date = get_post_meta($request_id, '_request_delivery_date', true);

$all_statuses = get_terms(array(
    'taxonomy' => 'arsol-request-status',
    'hide_empty' => false,
));
?>

<div class="form-field-row">
    <p class="form-field form-field-wide wc-customer-user">
        <label for="post_author_override">
            <?php _e('Customer:', 'arsol-pfw'); ?>
            <?php if ($customer): ?>
                <a href="<?php echo admin_url('edit.php?post_status=all&post_type=arsol-pfw-request&author=' . $customer_id); ?>">
                    <?php _e('View other requests →', 'arsol-pfw'); ?>
                </a>
                <a href="<?php echo admin_url('user-edit.php?user_id=' . $customer_id); ?>">
                    <?php _e('Profile →', 'arsol-pfw'); ?>
                </a>
            <?php endif; ?>
        </label>
        <?php
        $author_dropdown = wp_dropdown_users(array(
            'name' => '', // Remove name to make it non-saveable
            'selected' => $post->post_author,
            'include_selected' => true,
            'echo' => false,
            'class' => 'wc-customer-search'
        ));
        // Add disabled attribute to make it non-editable
        $author_dropdown = str_replace('<select', '<select disabled', $author_dropdown);
        echo $author_dropdown;
        ?>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="request_status"><?php _e('Status:', 'arsol-pfw'); ?></label>
        <select id="request_status" name="request_status" class="wc-enhanced-select">
            <?php foreach ($all_statuses as $status) : ?>
                <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($request_status, $status->slug); ?>>
                    <?php echo esc_html($status->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
</div>

 