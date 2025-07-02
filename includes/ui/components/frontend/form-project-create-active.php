<?php
/**
 * Project Form: Create/Edit Active Project
 *
 * This template handles both project creation and editing based on the $is_edit and $post variables.
 * 
 * Usage Examples:
 * - Create: [arsol_pfw_project_form]
 * - Edit: [arsol_pfw_project_form is_edit="true" post_id="123"]
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_edit = isset($is_edit) && $is_edit;

// If editing, populate fields from the post object
if ($is_edit && isset($post) && $post) {
    $title = $post->post_title;
    $content = $post->post_content;
    $budget_data = get_post_meta($post->ID, '_arsol_pfw_project_budget', true);
    $budget = !empty($budget_data['amount']) ? $budget_data['amount'] : '';
    $start_date = get_post_meta($post->ID, '_arsol_pfw_project_start_date', true);
    $delivery_date = get_post_meta($post->ID, '_arsol_pfw_project_delivery_date', true);
} else {
    $title = '';
    $content = '';
    $budget = '';
    $start_date = '';
    $delivery_date = '';
}

// Check if user can create/edit projects
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

// Determine form title and button text
$form_title = $is_edit ? __('Edit Project', 'arsol-pfw') : __('Create New Project', 'arsol-pfw');
$button_text = $is_edit ? __('Update Project', 'arsol-pfw') : __('Create Project', 'arsol-pfw');
$form_action = $is_edit ? 'arsol_edit_project' : 'create_project';
$nonce_action = $is_edit ? 'edit_project' : 'create_project';
?>

<div class="arsol-pfw-project-create">
    <form method="post" class="arsol-pfw-project-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <h4><?php echo esc_html($form_title); ?></h4>
        
        <input type="hidden" name="action" value="<?php echo esc_attr($form_action); ?>">
        <?php wp_nonce_field($nonce_action, $nonce_action . '_nonce'); ?>
        <?php if ($is_edit && isset($post) && $post) : ?>
            <input type="hidden" name="project_id" value="<?php echo esc_attr($post->ID); ?>">
        <?php endif; ?>
        
        <div class="form-row">
            <label for="project_title"><?php _e('Project Title', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <input type="text" 
                   id="project_title" 
                   name="project_title" 
                   value="<?php echo esc_attr($title); ?>"
                   required>
        </div>
        
        <!-- Three column row for Budget, Start Date, and Delivery Date -->
        <div class="form-row form-row-wide arsol-pfw-project-meta-row">
            <div class="arsol-pfw-project-field-col arsol-budget-col">
                <label for="project_budget"><?php echo sprintf(__('Budget (%s)', 'arsol-pfw'), $currency_code); ?> <span class="required">*</span></label>
                <input type="text" 
                       id="project_budget" 
                       name="project_budget" 
                       value="<?php echo esc_attr($budget); ?>"
                       class="arsol-budget-input arsol-money-input" 
                       inputmode="decimal"
                       required>
            </div>
            
            <div class="arsol-pfw-project-field-col arsol-date-col">
                <label for="project_start_date"><?php _e('Start Date', 'arsol-pfw'); ?></label>
                <input type="date" 
                       id="project_start_date" 
                       name="project_start_date"
                       value="<?php echo esc_attr($start_date); ?>"
                       class="arsol-date-input">
            </div>
            
            <div class="arsol-pfw-project-field-col arsol-date-col">
                <label for="project_delivery_date"><?php _e('Delivery Date', 'arsol-pfw'); ?></label>
                <input type="date" 
                       id="project_delivery_date" 
                       name="project_delivery_date"
                       value="<?php echo esc_attr($delivery_date); ?>"
                       class="arsol-date-input">
            </div>
        </div>
        
        <div class="form-row">
            <label for="project_description"><?php _e('Project Description', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <textarea id="project_description" 
                      name="project_description" 
                      rows="5" 
                      required><?php echo esc_textarea($content); ?></textarea>
        </div>
        
        <div class="form-row">
            <button type="submit" class="button submit-button"><?php echo esc_html($button_text); ?></button>
        </div>
    </form>
</div> 