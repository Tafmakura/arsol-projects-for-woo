<?php
/**
 * Create Project Form: Active Project
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if user can create projects
$user_id = get_current_user_id();
$can_create = \Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_projects($user_id);

if (!$can_create) {
    wc_add_notice(__('You do not have permission to create projects. Please contact the administrator if you believe this is an error.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
}
?>

<div class="arsol-project-create">
    <form method="post" class="arsol-project-form">
        <h4><?php _e('Create New Project', 'arsol-pfw'); ?></h4>
        <?php wp_nonce_field('create_project', 'create_project_nonce'); ?>
        
        <p class="form-row">
            <label for="project_title"><?php _e('Project Title', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <input type="text" 
                   id="project_title" 
                   name="project_title" 
                   required>
        </p>
        
        <p class="form-row">
            <label for="project_description"><?php _e('Project Description', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <textarea id="project_description" 
                      name="project_description" 
                      rows="5" 
                      required></textarea>
        </p>
        
        <p class="form-row">
            <label for="project_budget"><?php _e('Budget', 'arsol-pfw'); ?></label>
            <input type="number" 
                   id="project_budget" 
                   name="project_budget" 
                   step="0.01"
                   min="0"
                   placeholder="<?php esc_attr_e('Enter project budget', 'arsol-pfw'); ?>">
        </p>
        
        <p class="form-row">
            <label for="project_start_date"><?php _e('Start Date', 'arsol-pfw'); ?></label>
            <input type="date" 
                   id="project_start_date" 
                   name="project_start_date">
        </p>
        
        <p class="form-row">
            <label for="project_delivery_date"><?php _e('Delivery Date', 'arsol-pfw'); ?></label>
            <input type="date" 
                   id="project_delivery_date" 
                   name="project_delivery_date">
        </p>
        
        <p class="form-row">
            <button type="submit" class="button"><?php _e('Create Project', 'arsol-pfw'); ?></button>
        </p>
    </form>
</div> 