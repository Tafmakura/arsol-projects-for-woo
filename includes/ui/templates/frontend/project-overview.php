<?php
/**
 * Project Overview
 *
 * Shows overview information about a project.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

do_action('arsol_projects_before_project_overview', $project_id);

// Get the project post
$project = get_post($project_id);

if (!$project) {
    echo '<p>' . esc_html__('Project not found.', 'arsol-projects-for-woo') . '</p>';
    return;
}

// Set the global post
global $post;
$post = $project;
setup_postdata($post);

// Debug information
$comments = get_comments(array(
    'post_id' => $project_id,
    'status' => 'approve'
));

echo '<!-- Debug: Project ID: ' . esc_html($project_id) . ' -->';
echo '<!-- Debug: Comments count: ' . count($comments) . ' -->';
echo '<!-- Debug: Comments open: ' . (comments_open() ? 'yes' : 'no') . ' -->';
echo '<!-- Debug: Post type: ' . get_post_type() . ' -->';
?>

<?php // Navigation included in includes/classes/class-endpoints.php ?>

<div class="project-overview-wrapper">
    <!-- Main Content Area -->
    <div class="project-content">
        <?php do_action('arsol_projects_overview_before_content', $project_id); ?>
        
        <div class="project-description">
            <?php the_content(); ?>
        </div>
        
        <!-- Comments Section -->
        <div class="project-comments">
            <?php
            if (!empty($comments)) {
                echo '<div class="comments-list">';
                wp_list_comments(array(
                    'style' => 'div',
                    'avatar_size' => 48,
                ), $comments);
                echo '</div>';
            } else {
                echo '<p>' . esc_html__('No comments yet.', 'arsol-projects-for-woo') . '</p>';
            }

            if (comments_open()) {
                comment_form(array(), $project_id);
            }
            ?>
        </div>
        
        <?php do_action('arsol_projects_overview_after_content', $project_id); ?>
    </div>

    <!-- Sidebar Area -->
    <div class="project-sidebar">
        <h4><?php esc_html_e('Project Details', 'arsol-projects-for-woo'); ?></h4>

        <div class="project-meta">
            <?php if (!empty($project_meta['_project_start_date'][0])) : ?>
                <p><strong><?php esc_html_e('Start Date:', 'arsol-projects-for-woo'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($project_meta['_project_start_date'][0]))); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($project_meta['_project_end_date'][0])) : ?>
                <p><strong><?php esc_html_e('End Date:', 'arsol-projects-for-woo'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($project_meta['_project_end_date'][0]))); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
// Reset post data
wp_reset_postdata();

do_action('arsol_projects_after_project_overview', $project_id); 
?>

