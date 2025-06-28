<?php
/**
 * Project View Request endpoint template
 *
 * Handles /my-account/project-view-request/{request_id}/ endpoint
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the request ID from query vars
global $wp;
$request_id = absint($wp->query_vars['project-view-request']);

// Validate request ID
if (!$request_id) {
    wc_add_notice(__('Invalid request ID.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
}

// Get and validate request
$request_post = get_post($request_id);
if (!$request_post || $request_post->post_type !== 'arsol-pfw-request') {
    wc_add_notice(__('Request not found.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
}

// Set up project data consistently as WP_Post object (like other templates)
$project = $request_post;
$project_id = $project->ID;
$project_title = $project->post_title;
$current_tab = 'request';


// --- Get Project Data ---
$current_post = get_post($project_id);
if (!$current_post) {
    echo '<p>' . esc_html__('Request not found.', 'arsol-pfw') . '</p>';
    return;
}

$current_post_type = get_post_type($project_id);

// Initialize variables
$status_terms = array();
$current_status = '';

// Set project type for hook compatibility - use actual CPT slug
$project_type = $request_post->post_type; // 'arsol-pfw-request'
$status_terms = wp_get_object_terms($project_id, 'arsol-pfw-request-status', array('fields' => 'slugs'));
$current_status = !empty($status_terms) ? $status_terms[0] : '';

// Prepare comprehensive data for efficient hook usage
$wrapper_data = compact('project_id', 'project_type', 'current_post_type', 'current_status');

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

<div class="project-content-wrapper" id="project-request-wrapper">
    <?php
    /**
     * Hook: arsol_pfw_project_wrapper_start
     * 
     * @param string $project_type Project type: 'request'
     * @param array $data Wrapper data
     */
    do_action('arsol_pfw_project_wrapper_start', $project_type, $wrapper_data);
    ?>
    
    <div class="project-content">
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
            echo do_shortcode($override . ' project_id="' . $project_id . '"');
        } else {
            echo do_shortcode('[arsol_pfw_request_overview project_id="' . $project_id . '"]');
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
    </div>
    
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
                // Include request sidebar template
                include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/sections/frontend/section-sidebar-request.php';
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

<?php
/**
 * Hook: arsol_pfw_project_wrapper_after
 * 
 * @param string $project_type Project type: 'request'
 * @param array $data Wrapper data
 */
do_action('arsol_pfw_project_wrapper_after', $project_type, $wrapper_data);
?> 