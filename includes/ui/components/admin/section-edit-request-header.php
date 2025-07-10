<?php
/**
 * Admin Template: Edit Project Request Header Container
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

if (!$post || $post->post_type !== 'arsol-pfw-request') {
    return;
}

// Get request data
$request_id = $post->ID;
$customer_id = $post->post_author;
$customer = get_userdata($customer_id);

// Get request stage (with proper error handling)
$request_stage_terms = wp_get_object_terms($request_id, 'arsol-pfw-request-stage', array('fields' => 'slugs'));
$request_stage = 'pending'; // Default value
if (!is_wp_error($request_stage_terms) && !empty($request_stage_terms)) {
    $request_stage = $request_stage_terms[0];
}

$budget_data = get_post_meta($request_id, '_arsol_pfw_request_budget', true);
$start_date = get_post_meta($request_id, '_arsol_pfw_request_start_date', true);
$delivery_date = get_post_meta($request_id, '_arsol_pfw_request_delivery_date', true);

// Get all request stages (with proper error handling)
$stages = get_terms(array(
    'taxonomy' => 'arsol-pfw-request-stage',
    'hide_empty' => false,
));

// Handle WP_Error from get_terms
if (is_wp_error($stages)) {
    $stages = array(); // Fallback to empty array
}

// Check for parent project
$parent_project_id = get_post_meta($request_id, '_arsol_pfw_parent_project_id', true);
$parent_project_data = null;
if ($parent_project_id) {
    $parent_project = get_post($parent_project_id);
    if ($parent_project && $parent_project->post_type === 'arsol-pfw-project') {
        $parent_project_data = array(
            'id' => $parent_project_id,
            'title' => $parent_project->post_title
        );
    }
}

// Requests always show both columns
$container_class = 'arsol-header-grid';
?>

<div id="arsol-pfw-project-request-data" class="arsol-pfw-project postbox ">
    <?php wp_nonce_field('arsol-pfw-request-actions-metabox', 'arsol_pfw_request_actions_metabox_nonce'); ?>
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
                    $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-request-header-column-1.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>

                <div class="project_data_column">
                    <h3><?php _e('Request Details', 'arsol-pfw'); ?></h3>
                    
                    <?php
                    // Load the request details template
                    $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-request-header-column-2.php';
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
