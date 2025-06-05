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

$project = get_post($project_id);
if (!$project) {
    echo '<p>' . esc_html__('Project not found.', 'arsol-projects-for-woo') . '</p>';
    return;
}

global $post;
$post = $project;
setup_postdata($post);

// Get project details
$status_terms = wp_get_post_terms($project_id, 'arsol-project-status', array('fields' => 'names'));
$status = !empty($status_terms) ? $status_terms[0] : 'N/A';
$start_date = get_post_meta($project_id, '_project_start_date', true);
$due_date = get_post_meta($project_id, '_project_due_date', true);
?>

<div class="project-overview-wrapper">
    <div class="project-content">
        <h3 class="project-title"><?php the_title(); ?></h3>
        <div class="project-description">
            <?php if (empty(get_the_content())) : ?>
                <?php include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-overview-empty.php'; ?>
            <?php else : ?>
                <?php the_content(); ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="project-sidebar">
        <div class="project-sidebar-wrapper">
            <div class="project-sidebar-card card">
                <div class="project-details">
                    <h4><?php esc_html_e('Project Details', 'arsol-projects-for-woo'); ?></h4>
                    <div class="project-meta">
                        <p><strong><?php esc_html_e('Status:', 'arsol-projects-for-woo'); ?></strong> <?php echo esc_html($status); ?></p>
                        <?php if ($start_date) : ?>
                            <p><strong><?php esc_html_e('Start Date:', 'arsol-projects-for-woo'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($start_date))); ?></p>
                        <?php endif; ?>
                        <?php if ($due_date) : ?>
                            <p><strong><?php esc_html_e('Due Date:', 'arsol-projects-for-woo'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($due_date))); ?></p>
                        <?php endif; ?>
                        <p><strong><?php esc_html_e('Date Created:', 'arsol-projects-for-woo'); ?></strong> <?php echo get_the_date(); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
wp_reset_postdata();
?>

