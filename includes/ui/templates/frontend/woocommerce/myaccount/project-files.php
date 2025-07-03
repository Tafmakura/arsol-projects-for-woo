<?php
/**
 * Project Files endpoint template
 * 
 * Handles /my-account/project-files/{project_id}/ endpoint
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from the endpoint class:
// $project (WP_Post object), $project_id, $current_tab, $statuses, $current_status, $wrapper_data

// Include unified project header
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/partials/frontend/project/project-header.php';

// --- Render Page Content ---
?>

<div class="project-files-wrapper">
    <div class="project-files-content">
        <h3><?php esc_html_e('Project Files', 'arsol-pfw'); ?></h3>
        
        <!-- Placeholder HTML tag as requested -->
        <div class="arsol-pfw-project-files-placeholder">
            <?php
            // Project files list shortcode
            echo do_shortcode('[arsol_pfw_project_files_list id="' . $project_id . '"]');
            ?>
        </div>
    </div>
</div> 