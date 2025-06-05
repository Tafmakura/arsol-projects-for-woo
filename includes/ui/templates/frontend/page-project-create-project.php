<?php
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_project_nonce']) && wp_verify_nonce($_POST['create_project_nonce'], 'create_project')) {
    $title = sanitize_text_field($_POST['project_title']);
    $description = wp_kses_post($_POST['project_description']);
    $budget = isset($_POST['project_budget']) ? sanitize_text_field($_POST['project_budget']) : '';
    $start_date = isset($_POST['project_start_date']) ? sanitize_text_field($_POST['project_start_date']) : '';
    $delivery_date = isset($_POST['project_delivery_date']) ? sanitize_text_field($_POST['project_delivery_date']) : '';
    
    // Create project post
    $project_data = array(
        'post_title'    => $title,
        'post_content'  => $description,
        'post_status'   => 'publish',
        'post_type'     => 'arsol-project',
        'post_author'   => $user_id
    );
    
    $project_id = wp_insert_post($project_data);
    
    if (!is_wp_error($project_id)) {
        // Set default project status
        wp_set_object_terms($project_id, 'not-started', 'arsol-project-status');
        
        // Save additional project meta
        if (!empty($budget)) {
            update_post_meta($project_id, '_project_budget', $budget);
        }
        if (!empty($start_date)) {
            update_post_meta($project_id, '_project_start_date', $start_date);
        }
        if (!empty($delivery_date)) {
            update_post_meta($project_id, '_project_delivery_date', $delivery_date);
        }
        
        // Redirect to project overview
        $redirect_url = wc_get_account_endpoint_url('project-overview/' . $project_id);
        wp_safe_redirect($redirect_url);
        exit;
    } else {
        // Add error notice if project creation failed
        wc_add_notice(__('Failed to create project. Please try again.', 'arsol-pfw'), 'error');
    }
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
