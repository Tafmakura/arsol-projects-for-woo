<?php
if (!defined('ABSPATH')) {
    exit;
}

// Check if user can create project proposals
$user_id = get_current_user_id();
$can_create = \Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_project_proposals($user_id);

if (!$can_create) {
    wc_add_notice(__('You do not have permission to create project proposals. Please contact the administrator if you believe this is an error.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_proposal_nonce']) && wp_verify_nonce($_POST['create_proposal_nonce'], 'create_proposal')) {
    $title = sanitize_text_field($_POST['proposal_title']);
    $description = wp_kses_post($_POST['proposal_description']);
    $budget = isset($_POST['proposal_budget']) ? floatval($_POST['proposal_budget']) : '';
    $start_date = isset($_POST['proposal_start_date']) ? sanitize_text_field($_POST['proposal_start_date']) : '';
    $delivery_date = isset($_POST['proposal_delivery_date']) ? sanitize_text_field($_POST['proposal_delivery_date']) : '';
    
    // Create project proposal post
    $proposal_data = array(
        'post_title'    => $title,
        'post_content'  => $description,
        'post_status'   => 'publish',
        'post_type'     => 'arsol-pfw-proposal',
        'post_author'   => $user_id
    );
    
    $proposal_id = wp_insert_post($proposal_data);
    
    if (!is_wp_error($proposal_id)) {
        // Set default proposal status
        wp_set_object_terms($proposal_id, 'pending', 'arsol-proposal-status');
        
        // Save additional proposal meta
        if (!empty($budget)) {
            update_post_meta($proposal_id, '_proposal_budget', $budget);
        }
        if (!empty($start_date)) {
            update_post_meta($proposal_id, '_proposal_start_date', $start_date);
        }
        if (!empty($delivery_date)) {
            update_post_meta($proposal_id, '_proposal_delivery_date', $delivery_date);
        }
        
        // Redirect to project proposal view page
        $redirect_url = wc_get_account_endpoint_url('project-view-proposal/' . $proposal_id);
        wp_safe_redirect($redirect_url);
        exit;
    } else {
        // Add error notice if proposal creation failed
        wc_add_notice(__('Failed to submit project proposal. Please try again.', 'arsol-pfw'), 'error');
    }
}
?>

<div class="arsol-project-proposal">
    <form method="post" class="arsol-proposal-form">
        <h4><?php _e('Submit a Project Proposal', 'arsol-pfw'); ?></h4>
        <?php wp_nonce_field('create_proposal', 'create_proposal_nonce'); ?>
        
        <p class="form-row">
            <label for="proposal_title"><?php _e('Proposal Title', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <input type="text" id="proposal_title" name="proposal_title" required>
        </p>
        
        <p class="form-row">
            <label for="proposal_description"><?php _e('Project Description', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <textarea id="proposal_description" name="proposal_description" rows="5" required></textarea>
            <span class="description"><?php _e('Please provide a detailed description of your proposed project.', 'arsol-pfw'); ?></span>
        </p>
        
        <p class="form-row">
            <label for="proposal_start_date"><?php _e('Proposed Start Date', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <input type="date" 
                   id="proposal_start_date" 
                   name="proposal_start_date" 
                   required>
            <span class="description"><?php _e('When do you propose to start the project?', 'arsol-pfw'); ?></span>
        </p>
        
        <p class="form-row">
            <label for="proposal_delivery_date"><?php _e('Proposed Delivery Date', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <input type="date" 
                   id="proposal_delivery_date" 
                   name="proposal_delivery_date" 
                   required>
            <span class="description"><?php _e('When do you propose to complete the project?', 'arsol-pfw'); ?></span>
        </p>
        
        <p class="form-row">
            <label for="proposal_budget"><?php _e('Proposed Budget', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <input type="number" 
                   id="proposal_budget" 
                   name="proposal_budget" 
                   step="0.01"
                   min="0"
                   required>
            <span class="description"><?php _e('What is your proposed budget for this project?', 'arsol-pfw'); ?></span>
        </p>
        
        <p class="form-row">
            <button type="submit" class="button"><?php _e('Submit Proposal', 'arsol-pfw'); ?></button>
        </p>
    </form>
</div> 