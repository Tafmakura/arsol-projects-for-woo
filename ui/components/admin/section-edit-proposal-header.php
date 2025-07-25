<?php
/**
 * Admin Template: Edit Proposal Header Container
 *
 * This container appears below the title and above the WYSIWYG editor.
 * Inspired by WooCommerce order data panel structure.
 *
 * Variables passed from controller:
 * $proposal (Arsol_PFW_Proposal object) - The proposal entity instance
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

// Ensure we have the proposal entity instance
if (!isset($proposal) || !is_object($proposal)) {
    return;
}

$proposal_id = $proposal->get_id();
$budget_data = $proposal->get_budget();
$delivery_date = $proposal->get_due_date();
$expiration_date = $proposal->get_expiration_date();
$start_date = $proposal->get_start_date();
$project_manager = $proposal->get_project_manager();
$costing_type = $proposal->get_costing_type();
$budget_notes = $proposal->get_budget_notes();
$quotation_notes = $proposal->get_quotation_notes();
$quotation_data = $proposal->get_quotation();

// Check for project-tied proposal - URL parameter first, then meta data
$is_project_tied = false;
$parent_project_data = false;

// ALWAYS check URL parameter first (for new proposals)
if (isset($_GET['parent_project']) && !empty($_GET['parent_project'])) {
    $parent_project_id = intval($_GET['parent_project']);
    $parent_project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($parent_project_id);
    
    if ($parent_project && $parent_project->exists()) {
        $is_project_tied = true;
        $parent_project_data = array(
            'id' => $parent_project_id,
            'title' => $parent_project->get_title()
        );
    }
} 
// Fallback to getter method check (for existing proposals)
elseif ($proposal_id > 0) {
    $parent_project_id = $proposal->get_parent_project_id();
    if (!empty($parent_project_id)) {
        $parent_project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($parent_project_id);
        if ($parent_project && $parent_project->exists()) {
            $is_project_tied = true;
            $parent_project_data = array(
                'id' => $parent_project_id,
                'title' => $parent_project->get_title()
            );
        }
    }
}

// Check if has request data
$has_request_data = false;
$request_id = $proposal->get_request_id();
$requested_budget = $proposal->get_requested_project_budget();
$requested_start_date = $proposal->get_requested_project_start_date();
$requested_due_date = $proposal->get_requested_project_due_date();

if ($request_id || $requested_budget || $requested_start_date || $requested_due_date) {
    $has_request_data = true;
}

// Determine layout classes
$container_class = 'arsol-header-grid';
if ($has_request_data) {
    $container_class .= ' has-col-2';
}
?>

<div id="arsol-pfw-project-proposal-data" class="arsol-pfw-project postbox ">
    <div id="proposal_metabox" class="panel-wrap woocommerce">
        <div id="order_data" class="panel woocommerce">
            <h2>
                <?php printf(__('Proposal #%d details', 'arsol-pfw'), $proposal_id); ?>
            </h2>

            <?php
            // Display project relationship if this is a project-tied proposal
            if ($is_project_tied && $parent_project_data) {
                echo '<p class="order_number">';
                printf(__('Parent Project: %s', 'arsol-pfw'), esc_html($parent_project_data['title']));
                echo '</p>';
            }
            ?>

            <div class="arsol-pfw-project-data-column-container <?php echo esc_attr($container_class); ?>">
                <div class="arsol-pfw-project-data-column column-1">
                    <h3><?php _e('General Settings', 'arsol-pfw'); ?></h3>

                                        <?php
                    // Load the general settings template
                    $template_path = ARSOL_PFW_PLUGIN_DIR . 'ui/components/admin/section-edit-proposal-header-column-1.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>

                <?php if ($has_request_data): ?>
                <div class="arsol-pfw-project-data-column column-2">
                    <h3><?php _e('Project Request Details', 'arsol-pfw'); ?></h3>
                    
                    <?php
                    // Load the original request details template
                    $template_path = ARSOL_PFW_PLUGIN_DIR . 'ui/components/admin/section-edit-proposal-header-column-2.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>
                <div class="arsol-pfw-project-data-column column-3">
                    <h3><?php _e('Project Proposal Summary', 'arsol-pfw'); ?></h3>
                    <?php
                    // Load the review status & actions template
                    $template_path = ARSOL_PFW_PLUGIN_DIR . 'ui/components/admin/section-edit-proposal-header-column-3.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>
                <?php else: ?>
                <div class="arsol-pfw-project-data-column column-3">
                    <h3><?php _e('Project Proposal Summary', 'arsol-pfw'); ?></h3>
                        <?php
                    // Load the review status & actions template
                    $template_path = ARSOL_PFW_PLUGIN_DIR . 'ui/components/admin/section-edit-proposal-header-column-3.php';
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

