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

do_action('arsol_projects_before_project_wrapper', $project_id);
?>

<?php // Navigation included in includes/classes/class-endpoints.php ?>

<div class="project-overview-wrapper">
    <?php do_action('arsol_projects_before_project_content', $project_id); ?>
    
    <!-- Main Content Area -->
    <div class="project-content">
        <?php do_action('arsol_projects_overview_before_content', $project_id); ?>
        
        <?php do_action('arsol_projects_before_description', $project_id); ?>
        
        <div class="project-description">
            <?php if (empty(get_the_content())) : ?>
                <?php include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-overview-empty.php'; ?>
            <?php else : ?>
                <?php the_content(); ?>
            <?php endif; ?>
        </div>
        
        <?php do_action('arsol_projects_after_description', $project_id); ?>
        
        <?php do_action('arsol_projects_overview_after_content', $project_id); ?>
    </div>

    <?php do_action('arsol_projects_before_sidebar', $project_id); ?>

    <!-- Sidebar Area -->
    <div class="project-sidebar">
        <?php do_action('arsol_projects_before_sidebar_content', $project_id); ?>
        
        <div class="project-sidebar-wrapper">
            <div class="project-sidebar-card card">
                <!-- Project Excerpt -->
            

                <div class="project-details">
                    <h4><?php esc_html_e('Project Details', 'arsol-projects-for-woo'); ?></h4>

                    <div class="project-excerpt">
                        <?php do_action('arsol_projects_before_excerpt', $project_id); ?>
                        <?php the_excerpt(); ?>
                        <?php do_action('arsol_projects_after_excerpt', $project_id); ?>
                    </div>

                    <div class="project-meta">
                        <?php do_action('arsol_projects_before_meta', $project_id); ?>
                        
                        <?php if (!empty($project_meta['_project_start_date'][0])) : ?>
                            <p><strong><?php esc_html_e('Start Date:', 'arsol-projects-for-woo'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($project_meta['_project_start_date'][0]))); ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($project_meta['_project_end_date'][0])) : ?>
                            <p><strong><?php esc_html_e('End Date:', 'arsol-projects-for-woo'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($project_meta['_project_end_date'][0]))); ?></p>
                        <?php endif; ?>
                        
                        <?php do_action('arsol_projects_after_meta', $project_id); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php do_action('arsol_projects_after_sidebar_content', $project_id); ?>
    </div>

    <?php do_action('arsol_projects_after_sidebar', $project_id); ?>
</div>

<?php 
do_action('arsol_projects_after_project_wrapper', $project_id);

// Reset post data
wp_reset_postdata();

do_action('arsol_projects_after_project_overview', $project_id); 
?>

