<?php
/**
 * Admin Template: Edit Project Header Container
 *
 * This container appears below the title and above the WYSIWYG editor.
 * Inspired by WooCommerce order data panel structure.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the current post
global $post;

    if (!$post || $post->post_type !== 'arsol-pfw-project') {
    return;
}

// Get project data
$project_id = $post->ID;
$customer_id = $post->post_author;
$customer = get_userdata($customer_id);
        $project_stage_terms = wp_get_object_terms($project_id, 'arsol-pfw-project-stage', array('fields' => 'slugs'));
$project_stage = !empty($project_stage_terms) ? $project_stage_terms[0] : 'not-started';

// Create project instance to use getter methods
        $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Arsol_PFW_CPT_Project($post->ID);
        $budget = $project->get_budget();
        $due_date = $project->get_due_date();
        $start_date = $project->get_start_date();
        $project_lead = $project->get_project_lead();
        $customer_notice = $project->get_customer_notice();

// Get all project statuses
$all_statuses = get_terms(array(
                'taxonomy' => 'arsol-pfw-project-stage',
    'hide_empty' => false,
));

// Check if has original proposal data
$has_proposal_data = false;
$column_2_title = __('Project Details', 'arsol-pfw');
$original_proposal_id = get_post_meta($project_id, '_arsol_pfw_project_proposal_id', true);

// Check for proposal data first (priority) - must have actual displayable data
if ($original_proposal_id) {
    // Check if there's any actual proposal data to show (excluding expiration date)
    $budget_data = $project->get_proposal_budget_onetime_amount();
    $recurring_budget_data = $project->get_proposal_budget_recurring_amount();
    $proposed_start_date = get_post_meta($project_id, '_arsol_pfw_proposal_start_date', true);
    $proposed_delivery_date = get_post_meta($project_id, '_arsol_pfw_project_due_date', true);
    
    if ($budget_data || $recurring_budget_data || $proposed_start_date || $proposed_delivery_date) {
        $has_proposal_data = true;
        $column_2_title = __('Proposal Details', 'arsol-pfw');
    }
} else {
    // Check for original request data as fallback - must have actual displayable data
    $original_request_id = get_post_meta($project_id, '_arsol_pfw_project_request_id', true);
    $original_request_title = get_post_meta($project_id, '_arsol_pfw_proposal_request_title', true);
    $original_request_content = get_post_meta($project_id, '_arsol_pfw_proposal_request_details', true);
    $original_request_budget = get_post_meta($project_id, '_arsol_pfw_proposal_request_budget', true);
    $original_request_start_date = get_post_meta($project_id, '_arsol_pfw_proposal_request_start_date', true);
    $original_request_delivery_date = get_post_meta($project_id, '_arsol_pfw_proposal_request_delivery_date', true);
    
    if ($original_request_id || $original_request_title || $original_request_content || $original_request_budget || $original_request_start_date || $original_request_delivery_date) {
    $has_proposal_data = true;
        $column_2_title = __('Original Request Details', 'arsol-pfw');
    }
}

// Determine layout classes - only add has-col-2 if there's actually original data
$container_class = 'arsol-header-grid';
if ($has_proposal_data) {
    $container_class .= ' has-col-2';
}
?>

<div id="arsol-pfw-project-project-data" class="arsol-pfw-project postbox ">
    <div id="proposal_metabox" class="panel-wrap woocommerce">
        <div id="order_data" class="panel woocommerce">
            <h2>
                <?php printf(__('Project #%d details', 'arsol-pfw'), $project_id); ?>
            </h2>

            <div class="project_data_column_container <?php echo esc_attr($container_class); ?>">
                <div class="project_data_column column_1">
                    <h3><?php _e('General Settings', 'arsol-pfw'); ?></h3>

                        <?php
                    // Load the general settings template
                    $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-project-header-column-1.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>

                <?php if ($has_proposal_data): ?>
                <div class="project_data_column column_2">
                    <h3><?php echo esc_html($column_2_title); ?></h3>
                    
                    <?php
                    // Load the project details template
                    $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-project-header-column-2.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>
                <div class="project_data_column column_3">
                    <h3><?php _e('Project Details', 'arsol-pfw'); ?></h3>
                    <?php
                    // Load the status & actions template
                    $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-project-header-column-3.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>
                <?php else: ?>
                <div class="project_data_column column_3">
                    <h3><?php _e('Project Details', 'arsol-pfw'); ?></h3>
                    <?php 
                    // Load the status & actions template
                    $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-project-header-column-3.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="clear"></div>
        </div>
    </div>
</div>
