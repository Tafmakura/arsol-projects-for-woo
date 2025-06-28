<?php
/**
 * Project Sidebar Template: Active Status
 * Shows project metadata, forms, and action buttons for active projects
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get project data from parent template variables
// Variables available: $project_id, $project_type, $current_post_type, $current_status, $wrapper_data
$post_id = isset($project_id) ? $project_id : get_queried_object_id();
$post_type = isset($current_post_type) ? $current_post_type : get_post_type($post_id);
$status = isset($current_status) ? $current_status : '';
$cpt = 'project'; // Internal CPT identifier

error_log("ARSOL DEBUG: Active project sidebar - Post ID: $post_id, Post Type: $post_type, Status: '$status'");
error_log("ARSOL DEBUG: Active project sidebar - Using variables from parent: project_id=" . (isset($project_id) ? $project_id : 'not set') . ", current_status=" . (isset($current_status) ? "'$current_status'" : 'not set'));

// If no status from parent, try to get it directly
if (empty($status)) {
    $status_terms = wp_get_post_terms($post_id, 'arsol-project-status');
    $status = !empty($status_terms) && !is_wp_error($status_terms) ? $status_terms[0]->slug : '';
    error_log("ARSOL DEBUG: Active project sidebar - Fallback status detection: '$status'");
}
?>

<div class="arsol-pfw-project-sidebar">
<?php
/**
     * Hook: arsol_pfw_project_sidebar_before
     * 
     * @param string $status The current status
     * @param int $post_id The post ID
     */
    do_action('arsol_pfw_project_sidebar_before', $status, $post_id);
    ?>

    <div class="arsol-pfw-sidebar-section arsol-pfw-sidebar-meta">
        <?php
        /**
         * Hook: arsol_pfw_project_sidebar_meta
         * 
         * Display sidebar metadata (status, dates, budget, etc.)
         * 
         * @param string $status The current status
         * @param int $post_id The post ID
         */
        do_action('arsol_pfw_project_sidebar_meta', $status, $post_id);
        ?>
    </div>

    <div class="arsol-pfw-sidebar-section arsol-pfw-sidebar-form">
        <?php
        /**
         * Hook: arsol_pfw_project_sidebar_form
         * 
         * Display sidebar forms (if any)
         * 
         * @param string $status The current status
         * @param int $post_id The post ID
     */
        do_action('arsol_pfw_project_sidebar_form', $status, $post_id);
        ?>
    </div>

    <div class="arsol-pfw-sidebar-section arsol-pfw-sidebar-buttons">
        <?php
        /**
         * Hook: arsol_pfw_project_sidebar_buttons
         * 
         * Display sidebar action buttons
         * 
         * @param string $status The current status
         * @param int $post_id The post ID
     */
        do_action('arsol_pfw_project_sidebar_buttons', $status, $post_id);
        ?>
    </div>

    <?php
    /**
     * Hook: arsol_pfw_project_sidebar_after
     * 
     * @param string $status The current status
     * @param int $post_id The post ID
     */
    do_action('arsol_pfw_project_sidebar_after', $status, $post_id);
    ?>
</div>
