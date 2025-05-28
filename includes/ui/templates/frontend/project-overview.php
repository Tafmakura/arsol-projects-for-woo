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

// Set up the post data
global $post;
$post = get_post($project_id);
setup_postdata($post);

// Store original query
$original_query = $wp_query;

// Create a new query for the project
$wp_query = new \WP_Query(array(
    'post_type' => 'project',
    'p' => $project_id,
    'post_status' => 'publish',
    'suppress_filters' => false
));

// Store the current post ID for Bricks context
$bricks_post_id = $post->ID;
?>

<?php // Navigation included in includes/classes/class-endpoints.php ?>

<div class="project-bricks-template">
    <?php 
    // echo do_shortcode('[bricks_template id="1491"]'); 
    ?>
</div>

<!-- Main content and Sidebar Wrapper -->
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
            if ($wp_query->have_posts()) :
                while ($wp_query->have_posts()) : $wp_query->the_post();
                    if (comments_open() || get_comments_number()) :
                        comments_template();
                    endif;
                endwhile;
            endif;
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
// Restore original query
$wp_query = $original_query;
wp_reset_postdata();

do_action('arsol_projects_after_project_overview', $project_id); 
?>

