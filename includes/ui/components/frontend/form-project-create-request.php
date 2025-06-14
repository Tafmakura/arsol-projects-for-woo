<?php
/**
 * Request Project Form: Project Request
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_edit = isset($is_edit) && $is_edit;

// If editing, populate fields from the post object
if ($is_edit) {
    $title = $post->post_title;
    $content = $post->post_content;
    $budget_data = get_post_meta($post->ID, '_request_budget', true);
    $budget = !empty($budget_data['amount']) ? $budget_data['amount'] : '';
    $start_date = get_post_meta($post->ID, '_request_start_date', true);
    $delivery_date = get_post_meta($post->ID, '_request_delivery_date', true);
} else {
    $title = '';
    $content = '';
    $budget = '';
    $start_date = '';
    $delivery_date = '';
}

// Get currency information
$currency_code = get_woocommerce_currency();
$currency_symbol = get_woocommerce_currency_symbol($currency_code);

$button_text = $is_edit ? __('Update Request', 'arsol-pfw') : __('Submit Request', 'arsol-pfw');
$form_action = $is_edit ? 'arsol_edit_request' : 'arsol_create_request';

// Check if user can create project requests
if (!$is_edit) {
    $user_id = get_current_user_id();
    $can_create = \Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_project_requests($user_id);

    if (!$can_create) {
        wc_add_notice(__('You do not have permission to create project requests. Please contact the administrator if you believe this is an error.', 'arsol-pfw'), 'error');
        wp_safe_redirect(wc_get_account_endpoint_url('projects'));
        exit;
    }
}
?>

<div class="arsol-project-request">
    <form method="post" id="arsol-request-edit-form" class="arsol-request-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <h4><?php echo $is_edit ? esc_html__('Edit Your Project Request', 'arsol-pfw') : esc_html__('Submit a Project Request', 'arsol-pfw'); ?></h4>
        
        <input type="hidden" name="action" value="<?php echo esc_attr($form_action); ?>">
        <?php wp_nonce_field($form_action, 'arsol_request_nonce'); ?>
        <?php if ($is_edit) : ?>
            <input type="hidden" name="request_id" value="<?php echo esc_attr($post->ID); ?>">
        <?php endif; ?>

        <div class="form-row">
            <label for="request_title"><?php _e('Request Title', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <input type="text" id="request_title" name="request_title" value="<?php echo esc_attr($title); ?>" required>
        </div>
        
        <!-- Three column row for Budget, Start Date, and Delivery Date -->
        <div class="form-row form-row-wide arsol-project-meta-row">
            <div class="arsol-project-field-col arsol-budget-col">
                <label for="request_budget"><?php echo sprintf(__('Budget (%s)', 'arsol-pfw'), $currency_code); ?> <span class="required">*</span></label>
                <input type="text" id="request_budget" name="request_budget" value="<?php echo esc_attr($budget); ?>" class="arsol-budget-input arsol-money-input" inputmode="decimal" required>
            </div>
            
            <div class="arsol-project-field-col arsol-date-col">
                <label for="request_start_date"><?php _e('Required Start Date', 'arsol-pfw'); ?></label>
                <input type="date" id="request_start_date" name="request_start_date" value="<?php echo esc_attr($start_date); ?>" class="arsol-date-input">
            </div>
            
            <div class="arsol-project-field-col arsol-date-col">
                <label for="request_delivery_date"><?php _e('Required Delivery Date', 'arsol-pfw'); ?></label>
                <input type="date" id="request_delivery_date" name="request_delivery_date" value="<?php echo esc_attr($delivery_date); ?>" class="arsol-date-input">
            </div>
        </div>
        
        <div class="form-row">
            <label for="request_description"><?php _e('Project Description', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <textarea id="request_description" name="request_description" rows="5" required><?php echo esc_textarea($content); ?></textarea>
        </div>
        
        <?php if (!$is_edit) : ?>
            <div class="form-row">
                <button type="submit" class="button" style="width: 100%; margin-top: 8px;"><?php echo esc_html($button_text); ?></button>
            </div>
        <?php endif; ?>
    </form>
</div> 