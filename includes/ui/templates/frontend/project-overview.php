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
?>

<?php // Navigation included in includes/classes/class-endpoints.php  ?>

<div class="project-content">
    <?php do_action('arsol_projects_overview_before_content', $project_id); ?>
    
    <div class="project-description">
        <?php echo wp_kses_post($project_content); ?>
    </div>
    
    <div class="project-meta">
        <?php if (!empty($project_meta['_project_start_date'][0])) : ?>
            <p><strong><?php esc_html_e('Start Date:', 'arsol-projects-for-woo'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($project_meta['_project_start_date'][0]))); ?></p>
        <?php endif; ?>
        
        <?php if (!empty($project_meta['_project_end_date'][0])) : ?>
            <p><strong><?php esc_html_e('End Date:', 'arsol-projects-for-woo'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($project_meta['_project_end_date'][0]))); ?></p>
        <?php endif; ?>
    </div>
    
    <?php do_action('arsol_projects_overview_after_content', $project_id); ?>
</div>

<?php do_action('arsol_projects_after_project_overview', $project_id); ?>