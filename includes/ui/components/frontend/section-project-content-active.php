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
    echo '<p>' . esc_html__('Project not found.', 'arsol-pfw') . '</p>';
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
        <div class="project-description">
            <?php if (empty(get_the_content())) : ?>
                <?php include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-overview-empty.php'; ?>
            <?php else : ?>
                <?php the_content(); ?>
            <?php endif; ?>
        </div>
        
        <?php
        // Display comments if enabled for projects
        if (\Arsol_Projects_For_Woo\Admin\Settings_General::is_comments_enabled_for_post_type('arsol-project') && 
            post_type_supports('arsol-project', 'comments') && 
            (comments_open() || get_comments_number())) :
        ?>
            <div class="project-comments-section">
                <?php
                // Load WordPress native comments template
                comments_template();
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
wp_reset_postdata();
?> 