<?php
/**
 * Project Overview endpoint template
 *
 * Handles /my-account/project-overview/{project_id}/ endpoint
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from the endpoint class:
// $project, $project_id, $project_title, $current_tab, $current_post, $current_post_type,
// $project_type, $status_terms, $current_status, $wrapper_data

// Include unified project header
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/partials/frontend/project/project-header.php';

// Basic validation
if (!$current_post) {
    echo '<p>' . esc_html__('Project not found.', 'arsol-pfw') . '</p>';
    return;
}

// --- Render Project Overview Content ---
?>

<?php
/**
 * Hook: arsol_pfw_project_wrapper_before
 * 
 * @param string $project_type Project type: 'active', 'proposal', 'request'
 * @param array $data Wrapper data
 */
do_action('arsol_pfw_project_wrapper_before', $project_type, $wrapper_data);
?>

<div class="project-content-wrapper" id="project-overview-wrapper">
    <?php
    /**
     * Hook: arsol_pfw_project_wrapper_start
     * 
     * @param string $project_type Project type: 'active', 'proposal', 'request'
     * @param array $data Wrapper data
     */
    do_action('arsol_pfw_project_wrapper_start', $project_type, $wrapper_data);
    ?>
    
    <div class="project-content">
        <?php
        /**
         * Hook: arsol_pfw_project_content_before
         * 
         * @param string $project_type Project type: 'active', 'proposal', 'request'
         * @param array $data Wrapper data
         */
        do_action('arsol_pfw_project_content_before', $project_type, $wrapper_data);
        ?>
        
        <?php
        // Check if there's a project overview override for this project type (CONTENT ONLY)
        if (\Arsol_Projects_For_Woo\Frontend_Template_Overrides::has_project_overview_override($project_type)) {
            // Use the override shortcode for content only
            echo \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_project_overview_override($project_type);
        } else {
            // Use default shortcodes for content rendering
            echo do_shortcode('[arsol_pfw_project_content_active project_id="' . $project_id . '"]');           
        }

        /**
         * Hook: arsol_pfw_project_content_after
         * 
         * @param string $project_type Project type: 'active', 'proposal', 'request'
         * @param array $data Wrapper data
         */
        do_action('arsol_pfw_project_content_after', $project_type, $wrapper_data);
        ?>
    </div>
    
    <div class="project-sidebar">
        <?php
        /**
         * Hook: arsol_pfw_project_sidebar_wrapper_before
         * 
         * @param string $project_type Project type: 'active', 'proposal', 'request'
         * @param array $data Wrapper data
         */
        do_action('arsol_pfw_project_sidebar_wrapper_before', $project_type, $wrapper_data);
        ?>
        
        <div class="project-sidebar-wrapper">
            <div class="project-sidebar-card card">
                <?php
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/sections/frontend/section-sidebar-project.php';
                ?>
            </div>
        </div>
        
        <?php
        /**
         * Hook: arsol_pfw_project_sidebar_wrapper_after
         * 
         * @param string $project_type Project type: 'active', 'proposal', 'request'
         * @param array $data Wrapper data
         */
        do_action('arsol_pfw_project_sidebar_wrapper_after', $project_type, $wrapper_data);
        ?>
    </div>
    
    <?php
    /**
     * Hook: arsol_pfw_project_wrapper_end
     * 
     * @param string $project_type Project type: 'active', 'proposal', 'request'
     * @param array $data Wrapper data
     */
    do_action('arsol_pfw_project_wrapper_end', $project_type, $wrapper_data);
    ?>
</div>

<?php
/**
 * Hook: arsol_pfw_project_wrapper_after
 * 
 * @param string $project_type Project type: 'active', 'proposal', 'request'
 * @param array $data Wrapper data
 */
do_action('arsol_pfw_project_wrapper_after', $project_type, $wrapper_data);
?>
