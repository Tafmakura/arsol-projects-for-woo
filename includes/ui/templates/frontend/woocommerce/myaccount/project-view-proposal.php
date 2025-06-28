<?php
/**
 * Project View Proposal endpoint template
 *
 * Handles /my-account/project-view-proposal/{proposal_id}/ endpoint
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from the endpoint class:
// $project_proposal (WP_Post object), $project_proposal_id, $current_tab, $statuses, $current_status, $wrapper_data

// Include unified project header
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/partials/frontend/project/project-header.php';

// Basic validation
if (!$project_proposal) {
    echo '<p>' . esc_html__('Proposal not found.', 'arsol-pfw') . '</p>';
    return;
}

// Set project type for hook compatibility - use actual CPT slug
$project_type = $project_proposal->post_type; // 'arsol-pfw-proposal'

// --- Render Project Proposal Content ---
?>

<?php
/**
 * Hook: arsol_pfw_project_wrapper_before
 * 
 * @param string $project_type Project type: 'proposal'
 * @param array $data Wrapper data
 */
do_action('arsol_pfw_project_wrapper_before', $project_type, $wrapper_data);
?>

<div class="project-content-wrapper" id="project-proposal-wrapper">
    <?php
    /**
     * Hook: arsol_pfw_project_wrapper_start
     * 
     * @param string $project_type Project type: 'proposal'
     * @param array $data Wrapper data
     */
    do_action('arsol_pfw_project_wrapper_start', $project_type, $wrapper_data);
    ?>
    
    <div class="project-content">
        <?php
        /**
         * Hook: arsol_pfw_project_content_before
         * 
         * @param string $project_type Project type: 'proposal'
         * @param array $data Wrapper data
         */
        do_action('arsol_pfw_project_content_before', $project_type, $wrapper_data);
        ?>
        
        <?php
        // Check for shortcode override using the new system
        $override = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_shortcode_override('[arsol_pfw_proposal_overview]');
        if ($override) {
            echo do_shortcode($override . ' project_id="' . $project_proposal_id . '"');
        } else {
            echo do_shortcode('[arsol_pfw_proposal_overview project_id="' . $project_proposal_id . '"]');
        }
        ?>
        
        <?php
        /**
         * Hook: arsol_pfw_project_content_after
         * 
         * @param string $project_type Project type: 'proposal'
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
         * @param string $project_type Project type: 'proposal'
         * @param array $data Wrapper data
         */
        do_action('arsol_pfw_project_sidebar_wrapper_before', $project_type, $wrapper_data);
        ?>
        
        <div class="project-sidebar-wrapper">
            <div class="project-sidebar-card card">
                <?php
                // Include proposal sidebar template
                include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/sections/frontend/section-sidebar-proposal.php';
                ?>
            </div>
        </div>
        
        <?php
        /**
         * Hook: arsol_pfw_project_sidebar_wrapper_after
         * 
         * @param string $project_type Project type: 'proposal'
         * @param array $data Wrapper data
         */
        do_action('arsol_pfw_project_sidebar_wrapper_after', $project_type, $wrapper_data);
        ?>
    </div>
    
    <?php
    /**
     * Hook: arsol_pfw_project_wrapper_end
     * 
     * @param string $project_type Project type: 'proposal'
     * @param array $data Wrapper data
     */
    do_action('arsol_pfw_project_wrapper_end', $project_type, $wrapper_data);
    ?>
</div>

<?php
/**
 * Hook: arsol_pfw_project_wrapper_after
 * 
 * @param string $project_type Project type: 'proposal'
 * @param array $data Wrapper data
 */
do_action('arsol_pfw_project_wrapper_after', $project_type, $wrapper_data);
?>
