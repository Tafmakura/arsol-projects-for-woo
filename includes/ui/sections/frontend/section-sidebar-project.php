<?php
/**
 * Project Sidebar Template: Active Status
 * Shows project metadata and action buttons for active projects
 * 
 * Variables passed from endpoint class:
 * $project_id, $project (WP_Post object), $current_tab, $statuses, $current_status, $wrapper_data
 */

if (!defined('ABSPATH')) {
    exit;
}

error_log("ARSOL DEBUG: Active project sidebar - ID: {$project->ID}, Title: '{$project->post_title}', Type: {$project->post_type}, Status: '$current_status'");
?>

<div class="arsol-pfw-project-sidebar">
<?php
/**
     * Hook: arsol_pfw_project_sidebar_before
     * 
     * @param string $current_status The current status
     * @param int $project_id The project ID
     */
    do_action('arsol_pfw_project_sidebar_before', $current_status, $project_id);
    ?>

    <div class="arsol-pfw-sidebar-section arsol-pfw-sidebar-meta">
        <?php
        /**
         * Hook: arsol_pfw_project_sidebar_meta
         * 
         * Display sidebar metadata (status, dates, budget, etc.)
         * 
         * @param string $current_status The current status
         * @param int $project_id The project ID
         */
        do_action('arsol_pfw_project_sidebar_meta', $current_status, $project_id);
        ?>
    </div>

    <div class="arsol-pfw-sidebar-section arsol-pfw-sidebar-buttons">
        <?php
        /**
         * Hook: arsol_pfw_project_sidebar_buttons
         * 
         * Display sidebar action buttons
         * 
         * @param string $current_status The current status
         * @param int $project_id The project ID
     */
        do_action('arsol_pfw_project_sidebar_buttons', $current_status, $project_id);
        ?>
    </div>

    <?php
    /**
     * Hook: arsol_pfw_project_sidebar_after
     * 
     * @param string $current_status The current status
     * @param int $project_id The project ID
     */
    do_action('arsol_pfw_project_sidebar_after', $current_status, $project_id);
    ?>
</div>
