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
        <h4><?php echo $is_edit ? esc_html__('Edit Your Request', 'arsol-pfw') : esc_html__('Submit a Project Request', 'arsol-pfw'); ?></h4>
        
        <input type="hidden" name="action" value="<?php echo esc_attr($form_action); ?>">
        <?php wp_nonce_field($form_action, 'arsol_request_nonce'); ?>
        <?php if ($is_edit) : ?>
            <input type="hidden" name="request_id" value="<?php echo esc_attr($post->ID); ?>">
        <?php endif; ?>

        <p class="form-row">
            <label for="request_title"><?php _e('Request Title', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <input type="text" id="request_title" name="request_title" value="<?php echo esc_attr($title); ?>" required>
        </p>
        
        <p class="form-row">
            <label for="request_description"><?php _e('Project Description', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <textarea id="request_description" name="request_description" rows="5" required><?php echo esc_textarea($content); ?></textarea>
        </p>
        
        <p class="form-row">
            <label for="request_budget"><?php _e('Budget', 'arsol-pfw'); ?></label>
            <input type="number" id="request_budget" name="request_budget" value="<?php echo esc_attr($budget); ?>" step="0.01" min="0" placeholder="<?php esc_attr_e('Enter your budget amount', 'arsol-pfw'); ?>">
        </p>
        
        <p class="form-row">
            <label for="request_start_date"><?php _e('Required Start Date', 'arsol-pfw'); ?></label>
            <input type="date" id="request_start_date" name="request_start_date" value="<?php echo esc_attr($start_date); ?>">
        </p>
        
        <p class="form-row">
            <label for="request_delivery_date"><?php _e('Required Delivery Date', 'arsol-pfw'); ?></label>
            <input type="date" id="request_delivery_date" name="request_delivery_date" value="<?php echo esc_attr($delivery_date); ?>">
        </p>
        
        <?php if (!$is_edit) : ?>
            <p class="form-row">
                <button type="submit" class="button"><?php echo esc_html($button_text); ?></button>
            </p>
        <?php endif; ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    console.log('Form handler initialized');
    
    $('#arsol-request-edit-form').on('submit', function(e) {
        console.log('Form submitted');
        e.preventDefault();
        
        var $form = $(this);
        var $submitButton = $form.find('button[type="submit"]');
        
        // Disable submit button
        $submitButton.prop('disabled', true);
        
        // Get form data
        var formData = new FormData(this);
        formData.append('action', 'arsol_handle_request_submission');
        
        console.log('Sending AJAX request to:', arsol_pfw_ajax.ajax_url);
        console.log('Form data:', Object.fromEntries(formData));
        
        // Send AJAX request
        $.ajax({
            url: arsol_pfw_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('AJAX response:', response);
                if (response.success) {
                    // Show success message
                    if (response.data.notice) {
                        wc_add_to_cart_params.i18n_view_cart = response.data.notice;
                        $(document.body).trigger('added_to_cart');
                    }
                    
                    // If editing, reload the page to show updated content
                    if ($form.find('input[name="request_id"]').length) {
                        window.location.reload();
                    } else {
                        // If creating new, redirect to the new request
                        window.location.href = response.data.redirect_url;
                    }
                } else {
                    // Show error message
                    if (response.data.notice) {
                        wc_add_to_cart_params.i18n_view_cart = response.data.notice;
                        $(document.body).trigger('added_to_cart');
                    }
                    $submitButton.prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', {xhr: xhr, status: status, error: error});
                // Show generic error message
                wc_add_to_cart_params.i18n_view_cart = '<?php esc_html_e('An error occurred. Please try again.', 'arsol-pfw'); ?>';
                $(document.body).trigger('added_to_cart');
                $submitButton.prop('disabled', false);
            }
        });
    });
});
</script> 