<?php
/**
 * Project Proposal View endpoint template
 *
 * Handles /my-account/project-view-proposal/{project_id}/ endpoint
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from the endpoint class:
// $proposal (WP_Post object), $proposal_id, $current_tab, $statuses, $current_status, $wrapper_data

// Set variables for the header component
$proposal_title = $proposal->post_title ?? '';

// Basic validation
if (!$proposal) {
    echo '<p>' . esc_html__('Proposal not found.', 'arsol-pfw') . '</p>';
    return;
}

// Set project type for hook compatibility
$project_type = 'proposal';

// === NEW DISPLAY CONTROL LOGIC ===
$current_stage_id = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_current_stage_id($proposal_id);

// Get display mode (always content for proposals - no forms)
$display_mode = 'content';

// Check sidebar visibility
$show_sidebar = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_sidebar($proposal_id, $current_stage_id);
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

<div class="arsol-pfw-proposal">
    <div class="arsol-pfw-header">
        <h3 class="arsol-pfw-title"><?php echo esc_html($proposal->post_title); ?></h3>
    </div>
    
    <div class="arsol-pfw-content-wrapper" id="proposal-wrapper">
        <?php
        /**
         * Hook: arsol_pfw_project_wrapper_start
         * 
         * @param string $project_type Project type: 'proposal'
         * @param array $data Wrapper data
         */
        do_action('arsol_pfw_project_wrapper_start', $project_type, $wrapper_data);
        ?>
        
        <div class="arsol-pfw-content">
            <?php
            /**
             * Hook: arsol_pfw_project_content_before
             * 
             * @param string $project_type Project type: 'proposal'
             * @param array $data Wrapper data
             */
            do_action('arsol_pfw_project_content_before', $project_type, $wrapper_data);
            ?>
            
            <div class="arsol-pfw-post-content">
                <?php
                // Check for shortcode override using the new system
                $override = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_shortcode_override('[arsol_pfw_proposal_overview]');
                if ($override) {
                    echo do_shortcode($override . ' proposal_id="' . $proposal_id . '"');
                } else {
                    echo do_shortcode('[arsol_pfw_proposal_overview proposal_id="' . $proposal_id . '"]');
                }
                ?>
            </div>
            
            <?php
            /**
             * Hook: arsol_pfw_project_content_after
             * 
             * @param string $project_type Project type: 'proposal'
             * @param array $data Wrapper data
             */
            do_action('arsol_pfw_project_content_after', $project_type, $wrapper_data);
            ?>
            
            <?php
            // Files section - show proposal files if enabled for this stage
            $current_stage_id = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_current_stage_id($proposal_id);
            $show_files = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_files($proposal_id, $current_stage_id, 'proposal_file_display');
            if ($show_files): ?>
                <div class="files">
                    <?php
                    /**
                     * Hook: arsol_pfw_proposal_files_before
                     * 
                     * @param string $project_type Project type
                     * @param int $proposal_id Proposal ID
                     * @param int $current_stage_id Current stage ID
                     */
                    do_action('arsol_pfw_proposal_files_before', $project_type, $proposal_id, $current_stage_id);
                    ?>
                    
                    <div class="arsol-pfw-files-section">
                        <h4><?php esc_html_e('Proposal Files', 'arsol-pfw'); ?></h4>
                        <div class="arsol-pfw-files-content">
                            <?php echo do_shortcode('[arsol_pfw_proposal_files id="' . $proposal_id . '"]'); ?>
                        </div>
                    </div>
                    
                    <?php
                    /**
                     * Hook: arsol_pfw_proposal_files_after
                     * 
                     * @param string $project_type Project type
                     * @param int $proposal_id Proposal ID
                     * @param int $current_stage_id Current stage ID
                     */
                    do_action('arsol_pfw_proposal_files_after', $project_type, $proposal_id, $current_stage_id);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php
            // Comments section - dual-layer permission check
            $show_comments = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_comments($proposal_id, $current_stage_id);
            if ($show_comments): ?>
                <div class="comments">
                    <?php 
                    // Set the variable that the comments template expects
                    $project_id = $proposal_id;
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/woocommerce/partials/project-overview/comments.php'; 
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($show_sidebar): ?>
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
                        /**
                         * Hook: arsol_pfw_project_sidebar_start
                         * 
                         * @param string $project_type Project type: 'proposal'
                         * @param array $data Wrapper data
                         */
                        do_action('arsol_pfw_project_sidebar_start', $project_type, $wrapper_data);
                        ?>
                        
                        <?php
                        // Include proposal sidebar template directly
                        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/sections/frontend/section-sidebar-proposal.php';
                        ?>
                        
                        <?php
                        /**
                         * Hook: arsol_pfw_project_sidebar_end
                         * 
                         * @param string $project_type Project type: 'proposal'
                         * @param array $data Wrapper data
                         */
                        do_action('arsol_pfw_project_sidebar_end', $project_type, $wrapper_data);
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
        <?php endif; ?>
        
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
