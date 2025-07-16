<?php
/**
 * Project Request View endpoint template
 *
 * Handles /my-account/view-request/{request_id}/ endpoint
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from the endpoint class:
// $request (Arsol_PFW_Request object), $request_id, $current_tab, $current_stage, $wrapper_data

// Set variables for the header component
$request_title = $request->get_title() ?? '';

// Basic validation
if (!$request) {
    echo '<p>' . esc_html__('Request not found.', 'arsol-pfw') . '</p>';
    return;
}

// Set project type for hook compatibility
$project_type = 'request';

// === PURE STAGE-BASED DISPLAY LOGIC ===
$current_stage_id = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_current_stage_id($request_id);

// Check what forms are allowed for this stage
$should_show_edit_form = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_form($request_id, $current_stage_id, 'edit_request_form');
$should_show_create_form = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_form($request_id, $current_stage_id, 'request_form');

// Determine display mode based on stage settings
if ($should_show_edit_form) {
    $form_type = 'edit_request_form';
    $display_mode = 'form';
    $is_edit_mode = true;
} elseif ($should_show_create_form) {
    $form_type = 'request_form';
    $display_mode = 'form';
    $is_edit_mode = false;
} else {
    // No forms allowed, check if content should be shown
    $form_type = '';
    $is_edit_mode = false;
    $display_mode = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_content($request_id, $current_stage_id) ? 'content' : 'empty';
}

// Check sidebar visibility (create forms: hidden, edit forms: follow settings, content: follow settings)
$show_sidebar = ($display_mode === 'form' && !$is_edit_mode) 
    ? false 
    : \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_sidebar($request_id, $current_stage_id);

// === DEBUG LOGGING ===
error_log("=== ARSOL DEBUG: Request Display Mode Detection ===");
error_log("Request ID: " . $request_id);
error_log("Current Stage ID: " . $current_stage_id);
error_log("Is Edit Mode: " . ($is_edit_mode ? 'YES' : 'NO'));
error_log("Form Type: " . $form_type);
error_log("Display Mode: " . $display_mode);

// Let's also check what the form display settings contain
$form_settings = get_option('arsol_pfw_display_forms_settings', array());
error_log("Form Settings: " . print_r($form_settings, true));

// Check specifically for edit_request_form settings
$edit_form_visibility = isset($form_settings['edit_request_form_visibility']) ? $form_settings['edit_request_form_visibility'] : 'not set';
$edit_form_stages = isset($form_settings['edit_request_form_stages']) ? $form_settings['edit_request_form_stages'] : array();
error_log("Edit Request Form Visibility: " . $edit_form_visibility);
error_log("Edit Request Form Stages: " . print_r($edit_form_stages, true));

// Check the should_show_form result for both form types
$should_show_edit_form = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_form($request_id, $current_stage_id, 'edit_request_form');
$should_show_create_form = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_form($request_id, $current_stage_id, 'request_form');
error_log("Should Show Edit Form: " . ($should_show_edit_form ? 'YES' : 'NO'));
error_log("Should Show Create Form: " . ($should_show_create_form ? 'YES' : 'NO'));
error_log("=== END DEBUG ===");

// === NEW DISPLAY CONTROL LOGIC ===
$current_stage_id = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_current_stage_id($request_id);

// Determine form type based on edit mode
$is_edit_mode = !empty($_GET['edit']) && !empty($_GET['post_id']);
$form_type = $is_edit_mode ? 'edit_request_form' : 'request_form';

// Get display mode (form, content, or empty)
$display_mode = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_display_mode($request_id, $current_stage_id, $form_type);

// Check sidebar visibility (create forms: hidden, edit forms: follow settings, content: follow settings)
$show_sidebar = ($display_mode === 'form' && !$is_edit_mode) 
    ? false 
    : \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_sidebar($request_id, $current_stage_id);

// --- Render Project Request Content ---
?>

<?php
/**
 * Hook: arsol_pfw_project_wrapper_before
 * 
 * @param string $project_type Project type: 'request'
 * @param array $data Wrapper data
 */
do_action('arsol_pfw_project_wrapper_before', $project_type, $wrapper_data);
?>

<div class="arsol-pfw-request">
    <div class="arsol-pfw-header">
        <h3 class="arsol-pfw-title"><?php echo esc_html($request->get_title()); ?></h3>
    </div>
    
    <div class="arsol-pfw-content-wrapper" id="request-wrapper">
    <?php
    /**
     * Hook: arsol_pfw_project_wrapper_start
     * 
     * @param string $project_type Project type: 'request'
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
                if ($form_type === 'edit_request_form') {
                    // Check for shortcode override for edit form
                    $override = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_shortcode_override('[arsol_pfw_edit_request_form]');
                    if ($override) {
                        echo do_shortcode($override . ' request_id="' . $request_id . '"');
                    } else {
                        echo do_shortcode('[arsol_pfw_edit_request_form request_id="' . $request_id . '"]');
                    }
                } else {
                    // Check for shortcode override for create form
                    $override = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_shortcode_override('[arsol_pfw_request_form]');
                    if ($override) {
                        echo do_shortcode($override . ' request_id="' . $request_id . '"');
                    } else {
                        echo do_shortcode('[arsol_pfw_request_form request_id="' . $request_id . '"]');
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
         * @param string $project_type Project type: 'request'
         * @param array $data Wrapper data
         */
        do_action('arsol_pfw_project_content_before', $project_type, $wrapper_data);
        ?>
        
        <?php
        // Check for shortcode override using the new system
        $override = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_shortcode_override('[arsol_pfw_request_overview]');
        if ($override) {
                                echo do_shortcode($override . ' request_id="' . $request_id . '"');
        } else {
                                echo do_shortcode('[arsol_pfw_request_overview request_id="' . $request_id . '"]');
        }
        ?>
        
        <?php
        /**
         * Hook: arsol_pfw_project_content_after
         * 
         * @param string $project_type Project type: 'request'
         * @param array $data Wrapper data
         */
        do_action('arsol_pfw_project_content_after', $project_type, $wrapper_data);
        ?>
        
        <?php
                // Files section - show request file upload if enabled for this stage
                $show_files = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_files($request_id, $current_stage_id, 'request_file_upload');
                if ($show_files): ?>
                    <div class="files">
                        <?php
                        /**
                         * Hook: arsol_pfw_request_files_before
                         * 
                         * @param string $project_type Project type
                         * @param int $request_id Request ID
                         * @param int $current_stage_id Current stage ID
                         */
                        do_action('arsol_pfw_request_files_before', $project_type, $request_id, $current_stage_id);
                        ?>
                        
                        <div class="arsol-pfw-files-section">
                            <h4><?php esc_html_e('Request Files', 'arsol-pfw'); ?></h4>
                            <div class="arsol-pfw-files-content">
                                <?php echo do_shortcode('[arsol_pfw_request_file_upload id="' . $request_id . '"]'); ?>
                            </div>
                        </div>
                        
                <?php 
                        /**
                         * Hook: arsol_pfw_request_files_after
                         * 
                         * @param string $project_type Project type
                         * @param int $request_id Request ID
                         * @param int $current_stage_id Current stage ID
                         */
                        do_action('arsol_pfw_request_files_after', $project_type, $request_id, $current_stage_id);
                ?>
            </div>
        <?php endif; ?>
        
        <?php
        // Comments section - dual-layer permission check
                $show_comments = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::should_show_comments($request_id, $current_stage_id);
        if ($show_comments): ?>
            <div class="comments">
                <?php 
                        // Set the variable that the comments template expects
                        $project_id = $request_id;
                include ARSOL_PFW_PLUGIN_DIR . 'ui/templates/frontend/woocommerce/partials/project-overview/comments.php'; 
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
         * @param string $project_type Project type: 'request'
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
                             * @param string $project_type Project type: 'request'
                             * @param array $data Wrapper data
                             */
                            do_action('arsol_pfw_project_sidebar_start', $project_type, $wrapper_data);
                            ?>
                            
                            <?php
                            // Include request sidebar template directly
                            include ARSOL_PFW_PLUGIN_DIR . 'ui/sections/frontend/section-sidebar-request.php';
                ?>
                            
                            <?php
                            /**
                             * Hook: arsol_pfw_project_sidebar_end
                             * 
                             * @param string $project_type Project type: 'request'
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
         * @param string $project_type Project type: 'request'
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
                     * @param string $project_type Project type: 'request'
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
     * @param string $project_type Project type: 'request'
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
 * @param string $project_type Project type: 'request'
 * @param array $data Wrapper data
 */
do_action('arsol_pfw_project_wrapper_after', $project_type, $wrapper_data);
?> 