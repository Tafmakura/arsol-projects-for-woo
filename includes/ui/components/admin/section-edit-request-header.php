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
$request_status_terms = wp_get_object_terms($request_id, 'arsol-request-status', array('fields' => 'slugs'));
$request_status = !empty($request_status_terms) ? $request_status_terms[0] : 'pending';
$budget_data = get_post_meta($request_id, '_request_budget', true);
$start_date = get_post_meta($request_id, '_request_start_date', true);
$delivery_date = get_post_meta($request_id, '_request_delivery_date', true);

// Get all request statuses
$all_statuses = get_terms(array(
    'taxonomy' => 'arsol-request-status',
    'hide_empty' => false,
));

// Requests always show both columns
$container_class = 'arsol-header-grid';
?>

<div id="arsol-pfw-project-data" class="postbox ">
    <div id="request_metabox" class="panel-wrap woocommerce">
        <div id="order_data" class="panel woocommerce">
            <h2>
                <?php printf(__('Project Request #%d details', 'arsol-pfw'), $request_id); ?>
            </h2>
            <p class="order_number">
                <?php _e('Title:', 'arsol-pfw'); ?> <?php echo esc_html($post->post_title); ?>
            </p>

            <div class="order_data_column_container <?php echo esc_attr($container_class); ?>">
                <div class="order_data_column">
                    <h3><?php _e('General Settings', 'arsol-pfw'); ?></h3>

                    <?php
                    // Load the general settings template
                    $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-request-header-column-1.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>

                <div class="order_data_column extended_column">
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
