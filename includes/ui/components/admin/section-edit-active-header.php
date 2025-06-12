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

if (!$post || $post->post_type !== 'arsol-project') {
    return;
}

// Get project data
$project_id = $post->ID;
$customer_id = $post->post_author;
$customer = get_userdata($customer_id);
$project_status_terms = wp_get_object_terms($project_id, 'arsol-project-status', array('fields' => 'slugs'));
$project_status = !empty($project_status_terms) ? $project_status_terms[0] : 'not-started';
$project_lead = get_post_meta($project_id, '_project_lead', true);
$start_date = get_post_meta($project_id, '_project_start_date', true);
$due_date = get_post_meta($project_id, '_project_due_date', true);

// Get all project statuses
$all_statuses = get_terms(array(
    'taxonomy' => 'arsol-project-status',
    'hide_empty' => false,
));

// Check if has original proposal data
$has_proposal_data = false;
$original_proposal_id = get_post_meta($project_id, '_original_proposal_id', true);
if ($original_proposal_id || 
    get_post_meta($project_id, '_original_proposal_budget', true) ||
    get_post_meta($project_id, '_original_proposal_start_date', true) ||
    get_post_meta($project_id, '_original_proposal_delivery_date', true)) {
    $has_proposal_data = true;
}

// Determine layout classes
$container_class = 'arsol-header-grid';
if ($has_proposal_data) {
    $container_class .= ' has-col-2';
}
?>

<div id="arsol-pfw-project-data" class="postbox ">
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
                    $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-active-header-column-1.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>

                <?php if ($has_proposal_data): ?>
                <div class="arsol-col-2-3-container">
                    <div class="project_data_column column_2">
                        <h3><?php _e('Proposal Details', 'arsol-pfw'); ?></h3>
                        
                        <?php
                        // Load the project details template
                        $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-active-header-column-2.php';
                        if (file_exists($template_path)) {
                            include $template_path;
                        }
                        ?>
                    </div>
                    <div class="project_data_column column_3">
                        <h3><?php _e('Status & Actions', 'arsol-pfw'); ?></h3>
                        <?php 
                        // Load the status & actions template
                        $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-active-header-column-3.php';
                        if (file_exists($template_path)) {
                            include $template_path;
                        }
                        ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="project_data_column column_3">
                    <h3><?php _e('Status & Actions', 'arsol-pfw'); ?></h3>
                    <?php 
                    // Load the status & actions template
                    $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-active-header-column-3.php';
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
