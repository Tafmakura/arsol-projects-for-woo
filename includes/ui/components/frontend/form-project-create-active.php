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

// Get currency information
$currency_code = get_woocommerce_currency();
$currency_symbol = get_woocommerce_currency_symbol($currency_code);
?>

<div class="arsol-project-create">
    <form method="post" class="arsol-project-form">
        <h4><?php _e('Create New Project', 'arsol-pfw'); ?></h4>
        <?php wp_nonce_field('create_project', 'create_project_nonce'); ?>
        
        <div class="form-row">
            <label for="project_title"><?php _e('Project Title', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <input type="text" 
                   id="project_title" 
                   name="project_title" 
                   required>
        </div>
        
        <!-- Three column row for Budget, Start Date, and Delivery Date -->
        <div class="form-row form-row-wide arsol-project-meta-row">
            <div class="arsol-project-field-col arsol-budget-col">
                <label for="project_budget"><?php echo sprintf(__('Budget (%s)', 'arsol-pfw'), $currency_code); ?> <span class="required">*</span></label>
                <input type="text" 
                       id="project_budget" 
                       name="project_budget" 
                       class="arsol-budget-input arsol-money-input" 
                       inputmode="decimal"
                       required>
            </div>
            
            <div class="arsol-project-field-col arsol-date-col">
                <label for="project_start_date"><?php _e('Start Date', 'arsol-pfw'); ?></label>
                <input type="date" 
                       id="project_start_date" 
                       name="project_start_date"
                       class="arsol-date-input">
            </div>
            
            <div class="arsol-project-field-col arsol-date-col">
                <label for="project_delivery_date"><?php _e('Delivery Date', 'arsol-pfw'); ?></label>
                <input type="date" 
                       id="project_delivery_date" 
                       name="project_delivery_date"
                       class="arsol-date-input">
            </div>
        </div>
        
        <div class="form-row">
            <label for="project_description"><?php _e('Project Description', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <textarea id="project_description" 
                      name="project_description" 
                      rows="5" 
                      required></textarea>
        </div>
        
        <div class="form-row">
            <button type="submit" class="button" style="width: 100%; margin-top: 8px;"><?php _e('Create Project', 'arsol-pfw'); ?></button>
        </div>
    </form>
</div> 