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


// Check if user can create project requests
$user_id = get_current_user_id();
$can_create = \Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_project_requests($user_id);

if (!$can_create) {
    wc_add_notice(__('You do not have permission to create project requests. Please contact the administrator if you believe this is an error.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
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
        </p>
        
        <p class="form-row">
            <label for="request_budget"><?php _e('Budget', 'arsol-pfw'); ?></label>
            <input type="number" 
                   id="request_budget" 
                   name="request_budget" 
                   step="0.01"
                   min="0"
                   placeholder="<?php esc_attr_e('Enter your budget amount', 'arsol-pfw'); ?>">
        </p>
        
        <p class="form-row">
            <label for="request_start_date"><?php _e('Required Start Date', 'arsol-pfw'); ?></label>
            <input type="date" 
                   id="request_start_date" 
                   name="request_start_date">
        </p>
        
        <p class="form-row">
            <label for="request_delivery_date"><?php _e('Required Delivery Date', 'arsol-pfw'); ?></label>
            <input type="date" 
                   id="request_delivery_date" 
                   name="request_delivery_date">
        </p>
        
        <p class="form-row">
            <button type="submit" class="button"><?php _e('Submit Request', 'arsol-pfw'); ?></button>
        </p>
    </form>
</div> 