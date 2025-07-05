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
// $project (WP_Post object), $project_id, $current_tab, $statuses, $current_status, $wrapper_data

// Include unified project header
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/partials/frontend/project/project-header.php';

// Basic validation
if (!$project) {
    echo '<p>' . esc_html__('Project not found.', 'arsol-pfw') . '</p>';
    return;
}

// Set project type for hook compatibility - use actual CPT slug
$project_type = $project->post_type; // 'arsol-pfw-project'

// === PURE STAGE-BASED DISPLAY LOGIC ===
$current_stage_id = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_current_stage_id($project_id);

// Check what forms are allowed for this stage
$should_show_edit_form = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_form($project_id, $current_stage_id, 'edit_project_form');
$should_show_create_form = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_form($project_id, $current_stage_id, 'project_form');

// Determine display mode based on stage settings
if ($should_show_edit_form) {
    $form_type = 'edit_project_form';
    $display_mode = 'form';
    $is_edit_mode = true;
} elseif ($should_show_create_form) {
    $form_type = 'project_form';
    $display_mode = 'form';
    $is_edit_mode = false;
} else {
    // No forms allowed, check if content should be shown
    $form_type = '';
    $is_edit_mode = false;
    $display_mode = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_content($project_id, $current_stage_id) ? 'content' : 'empty';
}

// Check sidebar visibility (create forms: hidden, edit forms: follow settings, content: follow settings)
$show_sidebar = ($display_mode === 'form' && !$is_edit_mode) 
    ? false 
    : \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_sidebar($project_id, $current_stage_id);

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

<div class="arsol-pfw-project">
    <div class="arsol-pfw-header">
        <h3 class="arsol-pfw-title"><?php echo esc_html($project->post_title); ?></h3>
    </div>
    
    <div class="arsol-pfw-content-wrapper" id="project-overview-wrapper">
    <?php
    /**
     * Hook: arsol_pfw_project_wrapper_start
     * 
     * @param string $project_type Project type: 'active', 'proposal', 'request'
     * @param array $data Wrapper data
     */
    do_action('arsol_pfw_project_wrapper_start', $project_type, $wrapper_data);
    ?>
    
    <?php if ($display_mode === 'form'): ?>
        <!-- FORM MODE: Form overrides content and sidebar -->
        <div class="form-content">
            <?php
            /**
             * Hook: arsol_pfw_project_form_before
             * 
             * @param string $project_type Project type
             * @param string $form_type Form type
             * @param array $data Wrapper data
             */
            do_action('arsol_pfw_project_form_before', $project_type, $form_type, $wrapper_data);
            ?>
            
            <?php
            // Render appropriate form based on form type
            if ($form_type === 'edit_project_form') {
                // Check for shortcode override for edit form
                $override = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_shortcode_override('[arsol_pfw_edit_project_form]');
                if ($override) {
                    echo do_shortcode($override . ' project_id="' . $project_id . '"');
                } else {
                    echo do_shortcode('[arsol_pfw_edit_project_form project_id="' . $project_id . '"]');
                }
            } else {
                // Check for shortcode override for create form
                $override = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_shortcode_override('[arsol_pfw_project_form]');
                if ($override) {
                    echo do_shortcode($override . ' project_id="' . $project_id . '"');
                } else {
                    echo do_shortcode('[arsol_pfw_project_form project_id="' . $project_id . '"]');
                }
            }
            ?>
            
            <?php
            /**
             * Hook: arsol_pfw_project_form_after
             * 
             * @param string $project_type Project type
             * @param string $form_type Form type
             * @param array $data Wrapper data
             */
            do_action('arsol_pfw_project_form_after', $project_type, $form_type, $wrapper_data);
            ?>
        </div>
        
    <?php elseif ($display_mode === 'content'): ?>
        <!-- CONTENT MODE: Show content and optionally sidebar -->
            <div class="arsol-pfw-content">
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
            // Check for shortcode override using the new system
            $override = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_shortcode_override('[arsol_pfw_project_overview]');
            if ($override) {
                echo do_shortcode($override . ' project_id="' . $project_id . '"');
            } else {
                echo do_shortcode('[arsol_pfw_project_overview project_id="' . $project_id . '"]');
            }
                    ?>

                <?php
            /**
             * Hook: arsol_pfw_project_content_after
             * 
             * @param string $project_type Project type: 'active', 'proposal', 'request'
             * @param array $data Wrapper data
             */
            do_action('arsol_pfw_project_content_after', $project_type, $wrapper_data);
            ?>
                
                <?php
                // Project files section - show project files if enabled for this stage
                $show_project_files = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_files($project_id, $current_stage_id, 'project_files_list');
                if ($show_project_files): ?>
                    <div class="files">
                        <?php
                        /**
                         * Hook: arsol_pfw_project_files_before
                         * 
                         * @param string $project_type Project type
                         * @param int $project_id Project ID
                         * @param int $current_stage_id Current stage ID
                         */
                        do_action('arsol_pfw_project_files_before', $project_type, $project_id, $current_stage_id);
                        ?>
                        
                        <div class="arsol-pfw-files-section">
                            <h4><?php esc_html_e('Project Files', 'arsol-pfw'); ?></h4>
                            <div class="arsol-pfw-files-content">
                                <?php echo do_shortcode('[arsol_pfw_project_files_list id="' . $project_id . '"]'); ?>
                            </div>
                        </div>
                        
                        <?php
                        /**
                         * Hook: arsol_pfw_project_files_after
                         * 
                         * @param string $project_type Project type
                         * @param int $project_id Project ID
                         * @param int $current_stage_id Current stage ID
                         */
                        do_action('arsol_pfw_project_files_after', $project_type, $project_id, $current_stage_id);
                        ?>
                    </div>
                <?php endif; ?>
            
            <?php
            // Comments section - dual-layer permission check
            $show_comments = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_comments($project_id, $current_stage_id);
            if ($show_comments): ?>
                <div class="comments">
                    <?php include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/woocommerce/partials/project-overview/comments.php'; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($show_sidebar): ?>
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
                            /**
                             * Hook: arsol_pfw_project_sidebar_start
                             * 
                             * @param string $project_type Project type: 'active', 'proposal', 'request'
                             * @param array $data Wrapper data
                             */
                            do_action('arsol_pfw_project_sidebar_start', $project_type, $wrapper_data);
                            ?>
                            
                            <?php
                            // Include project sidebar template directly
                            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/sections/frontend/section-sidebar-project.php';
                            ?>
                            
                            <?php
                            /**
                             * Hook: arsol_pfw_project_sidebar_end
                             * 
                             * @param string $project_type Project type: 'active', 'proposal', 'request'
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
                 * @param string $project_type Project type: 'active', 'proposal', 'request'
                 * @param array $data Wrapper data
                 */
                do_action('arsol_pfw_project_sidebar_wrapper_after', $project_type, $wrapper_data);
                ?>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
            <!-- EMPTY MODE: Project exists but no display mode active -->
            <div class="project-empty-state">
                <div class="empty-state-message">
                    <?php
                    /**
                     * Hook: arsol_pfw_project_empty_state_message
                     * 
                     * @param string $project_type Project type: 'active', 'proposal', 'request'
                     * @param array $data Wrapper data
                     */
                    do_action('arsol_pfw_project_empty_state_message', $project_type, $wrapper_data);
                    ?>
                </div>
        </div>
        <?php endif; ?>
    
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
 