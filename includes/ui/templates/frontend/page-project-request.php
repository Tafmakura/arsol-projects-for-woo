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
    $timeline = isset($_POST['request_timeline']) ? sanitize_text_field($_POST['request_timeline']) : '';
    
    // Create project request post
    $request_data = array(
        'post_title'    => $title,
        'post_content'  => $description,
        'post_status'   => 'publish',
        'post_type'     => 'arsol-project-request',
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
        if (!empty($timeline)) {
            update_post_meta($request_id, '_request_timeline', $timeline);
        }
        
        // Redirect to project request view page
        $redirect_url = wc_get_account_endpoint_url('project-view-request');
        $redirect_url = add_query_arg('id', $request_id, $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    } else {
        // Add error notice if request creation failed
        wc_add_notice(__('Failed to submit project request. Please try again.', 'arsol-pfw'), 'error');
    }
}
?>

<div class="arsol-project-request">
    <h2><?php _e('Submit Project Request', 'arsol-pfw'); ?></h2>
    
    <form method="post" class="arsol-request-form" action="<?php echo esc_url(wc_get_account_endpoint_url('project-request')); ?>">
        <?php wp_nonce_field('create_request', 'create_request_nonce'); ?>
        
        <p class="form-row">
            <label for="request_title"><?php _e('Request Title', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <input type="text" id="request_title" name="request_title" required>
        </p>
        
        <p class="form-row">
            <label for="request_description"><?php _e('Project Description', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <textarea id="request_description" name="request_description" rows="5" required></textarea>
            <span class="description"><?php _e('Please provide a detailed description of your project requirements.', 'arsol-pfw'); ?></span>
        </p>
        
        <p class="form-row">
            <label for="request_budget"><?php _e('Budget Range', 'arsol-pfw'); ?></label>
            <input type="text" id="request_budget" name="request_budget" placeholder="<?php esc_attr_e('e.g., $1000 - $2000', 'arsol-pfw'); ?>">
            <span class="description"><?php _e('Optional: Provide your budget range for this project.', 'arsol-pfw'); ?></span>
        </p>
        
        <p class="form-row">
            <label for="request_timeline"><?php _e('Timeline', 'arsol-pfw'); ?></label>
            <input type="text" id="request_timeline" name="request_timeline" placeholder="<?php esc_attr_e('e.g., 2-3 weeks', 'arsol-pfw'); ?>">
            <span class="description"><?php _e('Optional: When do you need this project completed?', 'arsol-pfw'); ?></span>
        </p>
        
        <p class="form-row">
            <button type="submit" class="button"><?php _e('Submit Request', 'arsol-pfw'); ?></button>
        </p>
    </form>
</div>

<style>
.arsol-project-request {
    max-width: 800px;
    margin: 2em auto;
    padding: 20px;
}

.arsol-request-form .form-row {
    margin-bottom: 1.5em;
}

.arsol-request-form label {
    display: block;
    margin-bottom: 0.5em;
    font-weight: bold;
}

.arsol-request-form input[type="text"],
.arsol-request-form textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.arsol-request-form .required {
    color: #e2401c;
}

.arsol-request-form .description {
    display: block;
    margin-top: 0.5em;
    font-size: 0.9em;
    color: #666;
}

.arsol-request-form button {
    background-color: #2271b1;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.arsol-request-form button:hover {
    background-color: #135e96;
}
</style> 