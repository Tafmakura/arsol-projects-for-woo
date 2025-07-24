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
$customer = $project->get_customer();

// Use entity methods for stage and data
$project_stage = $project->get_stage();
$budget = $project->get_budget();
$due_date = $project->get_due_date();
$start_date = $project->get_start_date();
$project_lead = $project->get_project_lead();
$customer_notice = $project->get_customer_notice();

// Get available stages using entity method
$available_stages = $project->get_available_stages();

// Check if has request data using entity methods
$has_request_data = false;
$request_id = $project->get_meta('_arsol_pfw_project_request_id');
$request_title = $project->get_meta('_arsol_pfw_project_request_title');
$request_content = $project->get_meta('_arsol_pfw_project_request_content');
$requested_budget = $project->get_requested_budget();
$requested_start_date = $project->get_requested_start_date();
$requested_due_date = $project->get_requested_due_date();

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
                    $template_path = ARSOL_PFW_PLUGIN_DIR . 'ui/components/admin/section-edit-project-header-column-1.php';
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
                    $template_path = ARSOL_PFW_PLUGIN_DIR . 'ui/components/admin/section-edit-project-header-column-2.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>
                <div class="project_data_column column_3">
                    <h3><?php _e('Project Details', 'arsol-pfw'); ?></h3>
                    <?php
                    // Load the status & actions template
                    $template_path = ARSOL_PFW_PLUGIN_DIR . 'ui/components/admin/section-edit-project-header-column-3.php';
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
                    $template_path = ARSOL_PFW_PLUGIN_DIR . 'ui/components/admin/section-edit-project-header-column-3.php';
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
