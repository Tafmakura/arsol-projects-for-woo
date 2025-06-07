<?php
/**
 * Project Overview Content
 *
 * Shows overview information about a project.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

// The $project variable is passed from the render_project_page function
if (!isset($project)) {
    echo '<p>' . esc_html__('Project not found.', 'arsol-projects-for-woo') . '</p>';
    return;
}

global $post;
$post_obj = get_post($project['id']);
if (!$post_obj) {
    return;
}
$post = $post_obj;
setup_postdata($post);

// Get project details
$status_terms = wp_get_post_terms($project['id'], 'arsol-project-status', array('fields' => 'names'));
$status = !empty($status_terms) ? $status_terms[0] : 'N/A';
$start_date = get_post_meta($project['id'], '_project_start_date', true);
$due_date = get_post_meta($project['id'], '_project_due_date', true);
?>

<div class="project-overview-wrapper">
    <div class="project-content">
        <?php 
        // Check if there's a project overview override for active projects
        if (\Arsol_Projects_For_Woo\Frontend_Template_Overrides::has_project_overview_override('active')) {
            echo \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_project_overview_override('active');
        } else {
            // Use default template content
            ?>
            <div class="project-description">
                <?php if (empty(get_the_content())) : ?>
                    <?php include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-overview-empty.php'; ?>
                <?php else : ?>
                    <?php the_content(); ?>
                <?php endif; ?>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<?php 
wp_reset_postdata();
?> 