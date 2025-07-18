<?php
/**
 * Admin Template: Edit Request Header Container
 *
 * This container appears below the title and above the WYSIWYG editor.
 * Inspired by WooCommerce order data panel structure.
 *
 * Variables passed from controller:
 * $request (Arsol_PFW_Request object) - The request entity instance
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-request') {
    return;
}

// Ensure we have the request entity instance
if (!isset($request) || !is_object($request)) {
    return;
}

$request_id = $request->get_id();
$customer_id = $request->get_customer_id();
$customer = $request->get_customer();

// Use entity methods for stage and data
$request_stage = $request->get_stage();
$budget = $request->get_requested_project_budget();
$delivery_date = $request->get_requested_project_due_date();
$start_date = $request->get_requested_project_start_date();

// Get available stages using entity method
$available_stages = $request->get_available_stages();

// Check for parent project using entity method
$parent_project_id = $request->get_parent_project_id();
$parent_project_data = null;
if ($parent_project_id) {
    $parent_project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($parent_project_id);
    if ($parent_project && $parent_project->exists()) {
        $parent_project_data = array(
            'id' => $parent_project_id,
            'title' => $parent_project->get_title()
        );
    }
}

// Requests always show both columns
$container_class = 'arsol-header-grid';
?>

<div id="arsol-pfw-project-request-data" class="arsol-pfw-project postbox ">
    <div id="request_metabox" class="panel-wrap woocommerce">
        <div id="order_data" class="panel woocommerce">
            <h2>
                <?php printf(__('Project Request #%d details', 'arsol-pfw'), $request_id); ?>
            </h2>
            
            <?php
            // Show parent project if this is a project-tied request
            if ($parent_project_data) {
                echo '<p class="order_number">';
                printf(__('Parent Project: %s', 'arsol-pfw'), esc_html($parent_project_data['title']));
                echo '</p>';
            }
            ?>

            <div class="project_data_column_container <?php echo esc_attr($container_class); ?>">
                <div class="project_data_column">
                    <h3><?php _e('General Settings', 'arsol-pfw'); ?></h3>

                        <?php
                    // Load the general settings template
                    $template_path = ARSOL_PFW_PLUGIN_DIR . 'ui/components/admin/section-edit-request-header-column-1.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>

                <div class="project_data_column">
                    <h3><?php _e('Request Details', 'arsol-pfw'); ?></h3>
                    
                    <?php
                    // Load the request details template
                    $template_path = ARSOL_PFW_PLUGIN_DIR . 'ui/components/admin/section-edit-request-header-column-2.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>
            </div>

            <div class="clear"></div>
        </div>
    </div>
</div>
