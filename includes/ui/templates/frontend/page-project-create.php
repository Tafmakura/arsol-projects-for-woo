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
        if (isset($_POST['project_due_date'])) {
            update_post_meta($project_id, '_project_due_date', sanitize_text_field($_POST['project_due_date']));
        }
        
        // Add success notice
        wc_add_notice(__('Project created successfully!', 'arsol-pfw'), 'success');
        
        // Redirect to project overview
        wp_redirect(add_query_arg('project', $project_id, wc_get_account_endpoint_url('project-overview')));
        exit;
    } else {
        // Add error notice if project creation failed
        wc_add_notice(__('Failed to create project. Please try again.', 'arsol-pfw'), 'error');
    }
}
?>

<div class="arsol-project-create">
    <h2><?php _e('Create New Project', 'arsol-pfw'); ?></h2>
    
    <form method="post" class="arsol-project-form">
        <?php wp_nonce_field('create_project', 'create_project_nonce'); ?>
        
        <p class="form-row">
            <label for="project_title"><?php _e('Project Title', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <input type="text" id="project_title" name="project_title" required>
        </p>
        
        <p class="form-row">
            <label for="project_description"><?php _e('Project Description', 'arsol-pfw'); ?></label>
            <textarea id="project_description" name="project_description" rows="5"></textarea>
        </p>
        
        <p class="form-row">
            <label for="project_due_date"><?php _e('Due Date', 'arsol-pfw'); ?></label>
            <input type="date" id="project_due_date" name="project_due_date">
        </p>
        
        <p class="form-row">
            <button type="submit" class="button"><?php _e('Create Project', 'arsol-pfw'); ?></button>
        </p>
    </form>
</div>
