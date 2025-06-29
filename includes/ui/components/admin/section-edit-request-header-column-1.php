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
// Get request stage (with proper error handling)
$request_stage_terms = wp_get_object_terms($request_id, 'arsol-pfw-request-stage', array('fields' => 'slugs'));
$request_stage = 'pending-review'; // Default value
if (!is_wp_error($request_stage_terms) && !empty($request_stage_terms)) {
    $request_stage = $request_stage_terms[0];
}

$budget_data = get_post_meta($request_id, '_arsol_pfw_request_budget', true);
$start_date = get_post_meta($request_id, '_arsol_pfw_request_start_date', true);
$delivery_date = get_post_meta($request_id, '_arsol_pfw_request_delivery_date', true);

// Get all request stages (with proper error handling)
$stages = get_terms(array(
    'taxonomy' => 'arsol-pfw-request-stage',
    'hide_empty' => false,
));

// Handle WP_Error from get_terms
if (is_wp_error($stages)) {
    $stages = array(); // Fallback to empty array
}
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
        <select class="arsol-disabled-select" name="post_author_override" disabled>
            <?php if ($post->post_author): ?>
        <?php
                $customer_user = get_userdata($post->post_author);
                if ($customer_user) {
                    $customer_display = \Arsol_Projects_For_Woo\Woocommerce::format_customer_admin_display($customer_user);
                    
                    printf(
                        '<option value="%s" selected="selected">%s</option>',
                        esc_attr($customer_user->ID),
                        esc_html($customer_display)
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
        <label for="request_stage"><?php _e('Stage:', 'arsol-pfw'); ?></label>
        <select id="request_stage" name="request_stage" class="wc-enhanced-select">
            <?php if (!empty($stages) && !is_wp_error($stages)): ?>
                <?php foreach ($stages as $stage): ?>
                    <option value="<?php echo esc_attr($stage->slug); ?>" <?php selected($request_stage, $stage->slug); ?>>
                        <?php echo esc_html($stage->name); ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value=""><?php _e('No stages available', 'arsol-pfw'); ?></option>
            <?php endif; ?>
        </select>
    </p>
</div>

 