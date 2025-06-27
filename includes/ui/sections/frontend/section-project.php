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

// Prepare data for partials
$post_id = $project_id;
$post_type = $current_post_type;
$status = $current_status;

?>

<div class="project-overview-wrapper">

<?php
    // Include project overview header
    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/woocommerce/partials/project-overview/project-overview-header.php';
    ?>
    
    <div class="project-overview-body">
        <div class="project-content">
            <?php
            // Include project overview content
            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/woocommerce/partials/project-overview/project-overview-content.php';
            ?>
        </div>
        
        <div class="project-sidebar">
            <div class="project-sidebar-wrapper">
                <div class="project-sidebar-card card">
                    <?php
                    // Include project overview sidebar
                    $css_class = 'project-sidebar-' . $project_type;
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/woocommerce/partials/project-overview/project-overview-sidebar.php';
                    ?>
                </div>
            </div>
        </div>
    </div>
    
</div>
