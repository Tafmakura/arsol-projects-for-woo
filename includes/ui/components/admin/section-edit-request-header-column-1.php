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
        <select class="arsol-disabled-select" name="post_author_override" disabled style="width: 100%;">
            <?php if ($post->post_author): ?>
        <?php
                $customer_user = get_userdata($post->post_author);
                if ($customer_user) {
                    // Format customer display like WooCommerce: "First Last (#ID – email)" or fallback to "Display Name (#ID – email)"
                    $customer_name = trim($customer_user->first_name . ' ' . $customer_user->last_name);
                    if (empty($customer_name)) {
                        $customer_name = $customer_user->display_name;
                    }
                    
                    printf(
                        '<option value="%s" selected="selected">%s (#%s &ndash; %s)</option>',
                        esc_attr($customer_user->ID),
                        esc_html($customer_name),
                        esc_html($customer_user->ID),
                        esc_html($customer_user->user_email)
                    );
                } else {
                    echo '<option value="">' . esc_html__('Customer not found', 'arsol-pfw') . '</option>';
                }
                ?>
            <?php else: ?>
                <option value=""><?php esc_html_e('No customer assigned', 'arsol-pfw'); ?></option>
            <?php endif; ?>
        </select>
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

 