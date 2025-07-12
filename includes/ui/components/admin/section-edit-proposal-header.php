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
$budget = $proposal->get_budget();
$delivery_date = $proposal->get_due_date();
$expiration_date = $proposal->get_expiration_date();
$start_date = $proposal->get_start_date();
$project_lead = $proposal->get_project_lead();
$costing_type = $proposal->get_costing_type();
$budget_notes = $proposal->get_budget_notes();
$quotation_notes = $proposal->get_quotation_notes();
$quotation = $proposal->get_quotation();

// Check for project-tied proposal - URL parameter first, then meta data
$is_project_tied = false;
$parent_project_data = false;

// ALWAYS check URL parameter first (for new proposals)
if (isset($_GET['parent_project']) && !empty($_GET['parent_project'])) {
    $parent_project_id = intval($_GET['parent_project']);
    $parent_project = get_post($parent_project_id);
    
    if ($parent_project && $parent_project->post_type === 'arsol-pfw-project') {
        $is_project_tied = true;
        $parent_project_data = array(
            'id' => $parent_project_id,
            'title' => $parent_project->post_title
        );
    }
} 
// Fallback to meta data check (for existing proposals)
elseif ($proposal_id > 0) {
    $parent_project_id = get_post_meta($proposal_id, '_arsol_pfw_parent_project_id', true);
    if (!empty($parent_project_id)) {
        $parent_project = get_post($parent_project_id);
        if ($parent_project && $parent_project->post_type === 'arsol-pfw-project') {
            $is_project_tied = true;
            $parent_project_data = array(
                'id' => $parent_project_id,
                'title' => $parent_project->post_title
            );
        }
    }
}

// Check if has original request data
$has_request_data = false;
$original_request_id = get_post_meta($proposal_id, '_arsol_pfw_proposal_request_id', true);
if ($original_request_id || 
    get_post_meta($proposal_id, '_arsol_pfw_proposal_request_budget', true) ||
    get_post_meta($proposal_id, '_arsol_pfw_proposal_request_start_date', true) ||
    get_post_meta($proposal_id, '_arsol_pfw_proposal_request_delivery_date', true)) {
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
            // Check for project-tied proposal - URL parameter first, then meta data
            $is_project_tied = false;
            $parent_project_data = false;
            
            // ALWAYS check URL parameter first (for new proposals)
            if (isset($_GET['parent_project']) && !empty($_GET['parent_project'])) {
                $parent_project_id = intval($_GET['parent_project']);
                $parent_project = get_post($parent_project_id);
                
                if ($parent_project && $parent_project->post_type === 'arsol-pfw-project') {
                    $is_project_tied = true;
                    $parent_project_data = array(
                        'id' => $parent_project_id,
                        'title' => $parent_project->post_title
                    );
                }
            } 
            // Fallback to meta data check (for existing proposals)
            elseif ($proposal_id > 0) {
                $parent_project_id = get_post_meta($proposal_id, '_arsol_pfw_parent_project_id', true);
                if (!empty($parent_project_id)) {
                    $parent_project = get_post($parent_project_id);
                    if ($parent_project && $parent_project->post_type === 'arsol-pfw-project') {
                        $is_project_tied = true;
                        $parent_project_data = array(
                            'id' => $parent_project_id,
                            'title' => $parent_project->post_title
                        );
                    }
                }
            }
            
            // Display project relationship if this is a project-tied proposal
            if ($is_project_tied && $parent_project_data) {
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
                    $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-proposal-header-column-1.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>

                <?php if ($has_request_data): ?>
                <div class="project_data_column">
                    <h3><?php _e('Project Request Details', 'arsol-pfw'); ?></h3>
                    
                    <?php
                    // Load the original request details template
                    $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-proposal-header-column-2.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>
                <div class="project_data_column">
                    <h3><?php _e('Project Proposal Summary', 'arsol-pfw'); ?></h3>
                    <?php
                    // Load the review status & actions template
                    $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-proposal-header-column-3.php';
                    if (file_exists($template_path)) {
                        include $template_path;
                    }
                    ?>
                </div>
                <?php else: ?>
                <div class="project_data_column">
                    <h3><?php _e('Project Proposal Summary', 'arsol-pfw'); ?></h3>
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

