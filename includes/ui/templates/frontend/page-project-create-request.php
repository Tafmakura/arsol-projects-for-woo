<?php
if (!defined('ABSPATH')) {
    exit;
}


// Check if user can create project requests
$user_id = get_current_user_id();
$can_create = \Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_project_requests($user_id);

if (!$can_create) {
    wc_add_notice(__('You do not have permission to create project requests. Please contact the administrator if you believe this is an error.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_request_nonce']) && wp_verify_nonce($_POST['create_request_nonce'], 'create_request')) {
    $title = sanitize_text_field($_POST['request_title']);
    $description = wp_kses_post($_POST['request_description']);
    $budget = isset($_POST['request_budget']) ? sanitize_text_field($_POST['request_budget']) : '';
    $start_date = isset($_POST['request_start_date']) ? sanitize_text_field($_POST['request_start_date']) : '';
    $delivery_date = isset($_POST['request_delivery_date']) ? sanitize_text_field($_POST['request_delivery_date']) : '';
    
    // Create project request post
    $request_data = array(
        'post_title'    => $title,
        'post_content'  => $description,
        'post_status'   => 'publish',
        'post_type'     => 'arsol-pfw-request',
        'post_author'   => $user_id
    );
    
    $request_id = wp_insert_post($request_data);
    
    if (!is_wp_error($request_id)) {
        // Set default request status
        wp_set_object_terms($request_id, 'pending', 'arsol-request-status');
        
        // Save additional request meta
        if (!empty($budget)) {
            update_post_meta($request_id, '_request_budget', $budget);
        }
        if (!empty($start_date)) {
            update_post_meta($request_id, '_request_start_date', $start_date);
        }
        if (!empty($delivery_date)) {
            update_post_meta($request_id, '_request_delivery_date', $delivery_date);
        }
        
        // Add success notice
        wc_add_notice(__('Project request submitted successfully!', 'arsol-pfw'), 'success');
        
        // Redirect to project request view page
        $redirect_url = wc_get_account_endpoint_url('project-view-request/' . $request_id);
        wp_safe_redirect($redirect_url);
        exit;
    } else {
        // Add error notice if request creation failed
        wc_add_notice(__('Failed to submit project request. Please try again.', 'arsol-pfw'), 'error');
    }
}
?>

<div class="arsol-project-request">
    <form method="post" class="arsol-request-form">
        <h4><?php _e('Submit a Project Request', 'arsol-pfw'); ?></h4>
        <?php wp_nonce_field('create_request', 'create_request_nonce'); ?>
        
        <p class="form-row">
            <label for="request_title"><?php _e('Request Title', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <input type="text" 
                   id="request_title" 
                   name="request_title" 
                   required>
        </p>
        
        <p class="form-row">
            <label for="request_description"><?php _e('Project Description', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <textarea id="request_description" 
                      name="request_description" 
                      rows="5" 
                      required></textarea>
            <span class="description"><?php _e('Please provide a detailed description of your project requirements.', 'arsol-pfw'); ?></span>
        </p>
        
        <p class="form-row">
            <label for="request_budget"><?php _e('Budget', 'arsol-pfw'); ?></label>
            <input type="number" 
                   id="request_budget" 
                   name="request_budget" 
                   step="0.01"
                   min="0"
                   placeholder="<?php esc_attr_e('Enter your budget amount', 'arsol-pfw'); ?>">
            <span class="description"><?php _e('Optional: Enter your budget for this project.', 'arsol-pfw'); ?></span>
        </p>
        
        <p class="form-row">
            <label for="request_start_date"><?php _e('Required Start Date', 'arsol-pfw'); ?></label>
            <input type="date" 
                   id="request_start_date" 
                   name="request_start_date">
            <span class="description"><?php _e('Optional: When would you like to start this project?', 'arsol-pfw'); ?></span>
        </p>
        
        <p class="form-row">
            <label for="request_delivery_date"><?php _e('Required Delivery Date', 'arsol-pfw'); ?></label>
            <input type="date" 
                   id="request_delivery_date" 
                   name="request_delivery_date">
            <span class="description"><?php _e('Optional: When do you need this project completed?', 'arsol-pfw'); ?></span>
        </p>
        
        <p class="form-row">
            <button type="submit" class="button"><?php _e('Submit Request', 'arsol-pfw'); ?></button>
        </p>
    </form>
</div>
