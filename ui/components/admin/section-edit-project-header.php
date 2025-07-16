<?php
/**
 * Admin Template: Edit Project Header Container
 *
 * This container appears below the title and above the WYSIWYG editor.
 * Inspired by WooCommerce order data panel structure.
 *
 * Variables passed from controller:
 * $project (Arsol_PFW_Project object) - The project entity instance
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-project') {
    return;
}

// Ensure we have the project entity instance
if (!isset($project) || !is_object($project)) {
    return;
}

$project_id = $project->get_id();
$customer_id = $project->get_customer_id();
$customer = get_userdata($customer_id);
$project_stage_terms = wp_get_object_terms($project_id, 'arsol-pfw-project-stage', array('fields' => 'slugs'));
$project_stage = !empty($project_stage_terms) ? $project_stage_terms[0] : 'not-started';

// Use existing project instance to get data via getter methods
$budget = $project->get_project_budget();
$due_date = $project->get_project_due_date();
$start_date = $project->get_project_start_date();
$project_lead = $project->get_project_lead();
$customer_notice = $project->get_customer_notice();

// Get all project statuses
$all_statuses = get_terms(array(
    'taxonomy' => 'arsol-pfw-project-stage',
    'hide_empty' => false,
));

// Check if has request data
$has_request_data = false;
$request_id = get_post_meta($project_id, '_arsol_pfw_project_request_id', true);
$request_title = get_post_meta($project_id, '_arsol_pfw_project_request_title', true);
$request_content = get_post_meta($project_id, '_arsol_pfw_project_request_content', true);
$requested_budget = get_post_meta($project_id, '_arsol_pfw_requested_project_budget', true);
$requested_start_date = get_post_meta($project_id, '_arsol_pfw_requested_project_start_date', true);
$requested_due_date = get_post_meta($project_id, '_arsol_pfw_requested_project_due_date', true);

if ($request_id || $request_title || $request_content || $requested_budget || $requested_start_date || $requested_due_date) {
    $has_request_data = true;
}

// Determine layout classes - only add has-col-2 if there's actually original data
$container_class = 'arsol-header-grid';
if ($has_request_data) {
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
                    $template_path = ARSOL_PFW_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-project-header-column-1.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>

                <?php if ($has_request_data): ?>
                <div class="project_data_column column_2">
                    <h3><?php _e('Project Details', 'arsol-pfw'); ?></h3>
                    
                    <?php
                    // Load the project details template
                    $template_path = ARSOL_PFW_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-project-header-column-2.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>
                <div class="project_data_column column_3">
                    <h3><?php _e('Project Details', 'arsol-pfw'); ?></h3>
                    <?php
                    // Load the status & actions template
                    $template_path = ARSOL_PFW_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-project-header-column-3.php';
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
                    $template_path = ARSOL_PFW_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-project-header-column-3.php';
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
