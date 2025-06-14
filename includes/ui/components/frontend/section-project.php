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

// Map of valid types to their template files
$templates = array(
    'overview' => array(
        'content' => 'section-project-content-active.php',
        'sidebar' => 'section-project-sidebar-active.php'
    ),
    'proposal' => array(
        'content' => 'section-project-content-proposal.php',
        'sidebar' => 'section-project-sidebar-proposal.php'
    ),
    'request' => array(
        'content' => 'section-project-content-request.php',
        'sidebar' => 'section-project-sidebar-request.php'
    )
);

// Default to overview if type is not valid
if (!isset($templates[$type])) {
    $type = 'overview';
}

// Get template paths
$content_template = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/' . $templates[$type]['content'];
$sidebar_template = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/' . $templates[$type]['sidebar'];

// Set sidebar type for backward compatibility
$sidebar_type = $type;

// Get project ID from the project data
$project_id = isset($project['id']) ? $project['id'] : 0;

// Determine project type based on the template being loaded
$project_type = 'active'; // default
if (strpos($content_template, 'proposal') !== false) {
    $project_type = 'proposal';
} elseif (strpos($content_template, 'request') !== false) {
    $project_type = 'request';
}

// Prepare comprehensive data for efficient hook usage
$wrapper_data = compact('project_id', 'project_type', 'type', 'content_template', 'sidebar_template');
?>

<?php
// Check if there's a project overview override for this project type
if (\Arsol_Projects_For_Woo\Frontend_Template_Overrides::has_project_overview_override($project_type)) {
    echo \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_project_overview_override($project_type);
} else {
    // Use default template with preserved structure
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
            if (file_exists($content_template)) {
                include $content_template;
            } else {
                echo '<p>' . esc_html__('Content template not found.', 'arsol-pfw') . '</p>';
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
                    if (file_exists($sidebar_template)) {
                        include $sidebar_template;
                    } else {
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
    <?php
}
?> 