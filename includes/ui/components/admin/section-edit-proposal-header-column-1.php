<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
    return;
}

$proposal_id = $post->ID;
$customer_id = $post->post_author;
$customer = get_userdata($customer_id);
$proposal_status = get_post_status($post);
$start_date = get_post_meta($proposal_id, '_proposal_start_date', true);
$delivery_date = get_post_meta($proposal_id, '_proposal_delivery_date', true);
$expiration_date = get_post_meta($proposal_id, '_proposal_expiration_date', true);
$cost_proposal_type = get_post_meta($proposal_id, '_cost_proposal_type', true);
?>

<p class="form-field form-field-wide">
    <label for="proposal_start_date"><?php _e('Proposed Start Date:', 'arsol-pfw'); ?></label>
    <input type="date" id="proposal_start_date" name="proposal_start_date" value="<?php echo esc_attr($start_date); ?>" class="widefat">
</p>
<p class="form-field form-field-wide">
    <label for="proposal_delivery_date"><?php _e('Proposed Delivery Date:', 'arsol-pfw'); ?></label>
    <input type="date" id="proposal_delivery_date" name="proposal_delivery_date" value="<?php echo esc_attr($delivery_date); ?>" class="widefat">
</p>
<p class="form-field form-field-wide wc-customer-user">
    <label for="post_author_override">
        <?php _e('Customer:', 'arsol-pfw'); ?>
        <?php if ($customer): ?>
            <a href="<?php echo admin_url('edit.php?post_status=all&post_type=arsol-pfw-proposal&author=' . $customer_id); ?>">
                <?php _e('View other proposals →', 'arsol-pfw'); ?>
            </a>
            <a href="<?php echo admin_url('user-edit.php?user_id=' . $customer_id); ?>">
                <?php _e('Profile →', 'arsol-pfw'); ?>
            </a>
        <?php endif; ?>
    </label>
    <?php
    $author_dropdown = wp_dropdown_users(array(
        'name' => 'post_author_override',
        'selected' => $post->post_author,
        'include_selected' => true,
        'echo' => false,
        'class' => 'wc-customer-search'
    ));
    echo $author_dropdown;
    ?>
</p>
<p class="form-field form-field-wide">
    <label for="cost_proposal_type"><?php _e('Cost Proposal Type:', 'arsol-pfw'); ?></label>
    <select id="cost_proposal_type" name="cost_proposal_type" class="wc-enhanced-select">
        <option value="none" <?php selected($cost_proposal_type, 'none'); ?>><?php _e('None', 'arsol-pfw'); ?></option>
        <option value="budget_estimates" <?php selected($cost_proposal_type, 'budget_estimates'); ?>><?php _e('Budget Estimates', 'arsol-pfw'); ?></option>
        <option value="invoice_line_items" <?php selected($cost_proposal_type, 'invoice_line_items'); ?>><?php _e('Invoice Line Items', 'arsol-pfw'); ?></option>
    </select>
</p>
<p class="form-field form-field-wide">
    <label for="proposal_expiration_date"><?php _e('Proposal Expiration Date:', 'arsol-pfw'); ?></label>
    <input type="date" id="proposal_expiration_date" name="proposal_expiration_date" value="<?php echo esc_attr($expiration_date); ?>" class="widefat">
</p> 