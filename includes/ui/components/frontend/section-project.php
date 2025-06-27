<?php
/**
 * Project Template Controller
 *
 * This template loads the correct content and sidebar based on the context.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the type from GET parameters or default to 'overview'
$type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'overview';

// Default to overview if type is not valid
$valid_types = array('overview', 'proposal', 'request');
if (!in_array($type, $valid_types)) {
    $type = 'overview';
}

// Get project ID from the project data
$project_id = isset($project['id']) ? $project['id'] : 0;

// Determine project type based on the type parameter
$project_type = $type === 'proposal' ? 'proposal' : ($type === 'request' ? 'request' : 'active');

// Prepare comprehensive data for efficient hook usage
$wrapper_data = compact('project_id', 'project_type', 'type');
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

<div class="project-overview-wrapper">
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
            switch ($project_type) {
                case 'active':
                    echo do_shortcode('[arsol_pfw_project_content_active project_id="' . $project_id . '"]');
                    break;
                case 'proposal':
                    echo do_shortcode('[arsol_pfw_project_content_proposal project_id="' . $project_id . '"]');
                    break;
                case 'request':
                    echo do_shortcode('[arsol_pfw_project_content_request project_id="' . $project_id . '"]');
                    break;
                default:
                    echo do_shortcode('[arsol_pfw_project_content_active project_id="' . $project_id . '"]');
                    break;
            }
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
                // Determine current status and post type for conditional sidebar display
                global $post;
                $current_post_type = get_post_type($project_id);
                $current_status = '';
                
                // Get status based on post type
                if ($current_post_type === 'arsol-project') {
                    $status_terms = wp_get_post_terms($project_id, 'arsol-project-status', array('fields' => 'slugs'));
                    $current_status = !empty($status_terms) ? $status_terms[0] : 'active';
                    $sidebar_type = 'active';
                } elseif ($current_post_type === 'arsol-pfw-proposal') {
                    $status_terms = wp_get_post_terms($project_id, 'arsol-proposal-status', array('fields' => 'slugs'));
                    $current_status = !empty($status_terms) ? $status_terms[0] : 'processing';
                    $sidebar_type = 'proposal';
                } elseif ($current_post_type === 'arsol-pfw-request') {
                    $status_terms = wp_get_post_terms($project_id, 'arsol-request-status', array('fields' => 'slugs'));
                    $current_status = !empty($status_terms) ? $status_terms[0] : 'pending-review';
                    $sidebar_type = 'request';
                } else {
                    // Fallback to active project
                    $sidebar_type = 'active';
                    $current_status = 'active';
                }
                
                // Include appropriate sidebar template based on type and status
                if ($sidebar_type === 'active') {
                    // Always use the main active sidebar for projects regardless of status
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-sidebar-active.php';
                    
                } elseif ($sidebar_type === 'proposal') {
                    // Use proposal sidebar
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-sidebar-proposal.php';
                    
                } elseif ($sidebar_type === 'request') {
                    // Use main request sidebar
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-sidebar-request.php';
                    
                    // Include status-specific request sidebar additions (these contain conditional logic already)
                    // Note: The main request sidebar already includes these conditionally, but keeping for clarity
                    
                } else {
                    // Fallback
                    echo '<p>' . esc_html__('Sidebar template not found.', 'arsol-pfw') . '</p>';
                }
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
?> 