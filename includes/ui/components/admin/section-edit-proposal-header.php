<?php
/**
 * Admin Template: Edit Proposal Header Container
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

if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
    return;
}

// Get proposal data
$proposal_id = $post->ID;
$customer_id = $post->post_author;
$customer = get_userdata($customer_id);
$proposal_status = get_post_status($post);
$start_date = get_post_meta($proposal_id, '_proposal_start_date', true);
$delivery_date = get_post_meta($proposal_id, '_proposal_delivery_date', true);
$expiration_date = get_post_meta($proposal_id, '_proposal_expiration_date', true);
$cost_proposal_type = get_post_meta($proposal_id, '_cost_proposal_type', true);

// Check if has original request data
$has_request_data = false;
$original_request_id = get_post_meta($proposal_id, '_original_request_id', true);
if ($original_request_id || 
    get_post_meta($proposal_id, '_original_request_budget', true) ||
    get_post_meta($proposal_id, '_original_request_start_date', true) ||
    get_post_meta($proposal_id, '_original_request_delivery_date', true)) {
    $has_request_data = true;
}

// Determine layout classes
$container_class = 'arsol-header-grid';
if ($has_request_data) {
    $container_class .= ' has-col-2';
}
?>

<div id="arsol-pfw-project-data" class="postbox ">
    <div id="proposal_metabox" class="panel-wrap woocommerce">
        <div id="order_data" class="panel woocommerce">
            <h2>
                <?php printf(__('Proposal #%d details', 'arsol-pfw'), $proposal_id); ?>
            </h2>

            <div class="order_data_column_container <?php echo esc_attr($container_class); ?>">
                <div class="order_data_column">
                    <h3><?php _e('General Settings', 'arsol-pfw'); ?></h3>

                    <?php
                    // Load the general settings template
                    $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-proposal-header-column-1.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>

                <?php if ($has_request_data): ?>
                <div class="arsol-col-2-3-container">
                    <div class="order_data_column">
                        <h3><?php _e('Original Request Details', 'arsol-pfw'); ?></h3>
                        
                        <?php
                        // Load the original request details template
                        $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-proposal-header-column-2.php';
                        if (file_exists($template_path)) {
                            include $template_path;
                        }
                        ?>
                    </div>
                    <div class="order_data_column">
                        <h3><?php _e('Review Status & Actions', 'arsol-pfw'); ?></h3>
                        <?php
                        // Load the review status & actions template
                        $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-proposal-header-column-3.php';
                        if (file_exists($template_path)) {
                            include $template_path;
                        }
                        ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="order_data_column">
                    <h3><?php _e('Review Status & Actions', 'arsol-pfw'); ?></h3>
                    <?php
                    // Load the review status & actions template
                    $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-proposal-header-column-3.php';
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

