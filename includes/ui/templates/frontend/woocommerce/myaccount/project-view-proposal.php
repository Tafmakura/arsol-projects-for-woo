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

// Get the proposal ID from query vars
global $wp;
$proposal_id = absint($wp->query_vars['project-view-proposal']);

// Validate proposal ID
if (!$proposal_id) {
    wc_add_notice(__('Invalid proposal ID.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
}

// Get and validate proposal
$proposal_post = get_post($proposal_id);
if (!$proposal_post || $proposal_post->post_type !== 'arsol-pfw-proposal') {
    wc_add_notice(__('Proposal not found.', 'arsol-pfw'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('projects'));
    exit;
}

// Set up project data for consistency with project-overview.php
$project = array(
    'id' => $proposal_id,
    'title' => $proposal_post->post_title
);

$project_id = $project['id'];
$project_title = $project['title'];
$current_tab = 'proposal';

// --- Get Project Data ---
$current_post = get_post($project_id);
if (!$current_post) {
    echo '<p>' . esc_html__('Proposal not found.', 'arsol-pfw') . '</p>';
    return;
}

$current_post_type = get_post_type($project_id);

// Initialize variables
$status_terms = array();
$current_status = '';

// Set project type and get actual status from taxonomy
$project_type = 'proposal';
$status_terms = wp_get_object_terms($project_id, 'arsol-proposal-status', array('fields' => 'slugs'));
$current_status = !empty($status_terms) ? $status_terms[0] : '';

// Prepare comprehensive data for efficient hook usage
$wrapper_data = compact('project_id', 'project_type', 'current_post_type', 'current_status');

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
        // Check if there's a project overview override for this project type (CONTENT ONLY)
        if (\Arsol_Projects_For_Woo\Frontend_Template_Overrides::has_project_overview_override($project_type)) {
            // Use the override shortcode for content only
            echo \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_project_overview_override($project_type);
        } else {
            // Use default shortcode for content rendering
            echo do_shortcode('[arsol_pfw_project_content_proposal project_id="' . $project_id . '"]');
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