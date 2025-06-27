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

// Get the actual current post instead of relying on passed project data
global $post;
$current_post = get_post();

if (!$current_post) {
    echo '<p>' . esc_html__('Post not found.', 'arsol-pfw') . '</p>';
    return;
}

$project_id = $current_post->ID;
$current_post_type = get_post_type($project_id);

// Initialize variables
$status_terms = array();
$current_status = '';

// Determine project type and get actual status from taxonomy
if ($current_post_type === 'arsol-project') {
    $project_type = 'active';
    $status_terms = wp_get_object_terms($project_id, 'arsol-project-status', array('fields' => 'slugs'));
    $current_status = !empty($status_terms) ? $status_terms[0] : '';
    error_log("ARSOL DEBUG: Active project - Status: '$current_status', Terms: " . print_r($status_terms, true));
} elseif ($current_post_type === 'arsol-pfw-proposal') {
    $project_type = 'proposal';
    $status_terms = wp_get_object_terms($project_id, 'arsol-proposal-status', array('fields' => 'slugs'));
    $current_status = !empty($status_terms) ? $status_terms[0] : '';
} elseif ($current_post_type === 'arsol-pfw-request') {
    $project_type = 'request';
    $status_terms = wp_get_object_terms($project_id, 'arsol-request-status', array('fields' => 'slugs'));
    $current_status = !empty($status_terms) ? $status_terms[0] : '';
} else {
    // Fallback for unknown post types
    $project_type = 'active';
    $current_status = '';
}

// Prepare comprehensive data for efficient hook usage
$wrapper_data = compact('project_id', 'project_type', 'current_post_type', 'current_status');

// Debug: uncomment to see what's detected
error_log("Project template - ID: $project_id, CPT: '$current_post_type', Type: $project_type, Status: '$current_status'");
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
                    // Get status label for display
                    $status_label = '';
                    if (!empty($current_status)) {
                        $taxonomy_name = '';
                        switch ($current_post_type) {
                            case 'arsol-project':
                                $taxonomy_name = 'arsol-project-status';
                                break;
                            case 'arsol-pfw-proposal':
                                $taxonomy_name = 'arsol-proposal-status';
                                break;
                            case 'arsol-pfw-request':
                                $taxonomy_name = 'arsol-request-status';
                                break;
                        }
                        
                        if ($taxonomy_name) {
                            $status_term = get_term_by('slug', $current_status, $taxonomy_name);
                            $status_label = $status_term ? $status_term->name : ucfirst(str_replace('-', ' ', $current_status));
                        }
                    }
                    
                    // Use atomic ProjectSidebar organism
                    arsol_load_organism('ProjectSidebar.php', [
                        'post_id' => $project_id,
                        'post_type' => $current_post_type,
                        'status' => $current_status,
                        'status_label' => $status_label,
                        'css_class' => 'project-sidebar-' . $project_type
                    ]);
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
