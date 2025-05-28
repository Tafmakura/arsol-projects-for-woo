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

// Set up the post data to ensure proper context for both Bricks and content
global $post, $wp_query;
$post = get_post($project_id);
setup_postdata($post);

// Store original query
$original_query = $wp_query;

// Create a new query to simulate project context
$wp_query = new \WP_Query(array(
    'post_type' => 'project',
    'p' => $project_id,
    'post_status' => 'publish'
));

// Store the current post ID for Bricks context
$bricks_post_id = $post->ID;
?>

<?php // Navigation included in includes/classes/class-endpoints.php  ?>

<!-- Debug: Current Post ID -->
<div style="background: #f0f0f0; padding: 5px; margin: 5px 0; font-size: 12px; color: #666;">
    Debug - Post ID: <?php echo esc_html($post->ID); ?> | Project ID: <?php echo esc_html($project_id); ?> | Bricks Post ID: <?php echo esc_html($bricks_post_id); ?>
</div>

<!-- Debug: WordPress Template Tags -->
<div style="background: #f0f0f0; padding: 5px; margin: 5px 0; font-size: 12px; color: #666;">
    Debug - WordPress Context:<br>
    the_title(): <?php the_title(); ?><br>
    get_the_title(): <?php echo esc_html(get_the_title()); ?><br>
    get_the_ID(): <?php echo esc_html(get_the_ID()); ?><br>
    get_post_type(): <?php echo esc_html(get_post_type()); ?><br>
    is_singular('project'): <?php echo is_singular('project') ? 'true' : 'false'; ?><br>
    $post->post_title: <?php echo esc_html($post->post_title); ?><br>
    $post->post_type: <?php echo esc_html($post->post_type); ?>
</div>

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
            <?php 
            // Display the content with proper context
            the_content();
            ?>
        </div>
        
        <!-- Comments Section -->
        <div class="project-comments">
            <?php
            // If comments are open or we have at least one comment, load up the comment template.
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;
            ?>
        </div>
        
        <?php do_action('arsol_projects_overview_after_content', $project_id); ?>
    </div>

    <!-- Sidebar Area -->
    <div class="project-sidebar">
        <?php 
        // Add a placeholder title or structure for the sidebar
        ?>
        <h4><?php esc_html_e('Project Details', 'arsol-projects-for-woo'); ?></h4>

        <div class="project-meta">
            <?php if (!empty($project_meta['_project_start_date'][0])) : ?>
                <p><strong><?php esc_html_e('Start Date:', 'arsol-projects-for-woo'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($project_meta['_project_start_date'][0]))); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($project_meta['_project_end_date'][0])) : ?>
                <p><strong><?php esc_html_e('End Date:', 'arsol-projects-for-woo'); ?></strong> <?php esc_html(date_i18n(get_option('date_format'), strtotime($project_meta['_project_end_date'][0]))); ?></p>
            <?php endif; ?>
        </div>
    </div>

</div> <?php // end .project-overview-wrapper ?>

<?php 
// Remove our filter
if (function_exists('bricks_set_post_id')) {
    remove_all_filters('bricks_dynamic_data_post_id');
}

// Restore original query
$wp_query = $original_query;
wp_reset_postdata();

do_action('arsol_projects_after_project_overview', $project_id); 
?>