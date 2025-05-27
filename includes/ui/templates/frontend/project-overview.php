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

<div class="project-bricks-template">
    <?php 
    // Set up Bricks context
    if (function_exists('bricks_set_post_id')) {
        bricks_set_post_id($bricks_post_id);
    }
    echo do_shortcode('[bricks_template id="1491"]'); 
    ?>
</div>

<div class="project-content">
    <?php do_action('arsol_projects_overview_before_content', $project_id); ?>
    
    <div class="project-description">
        <?php 
        // Display the content with proper context
        the_content();
        ?>
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

<?php 
// Restore original query
$wp_query = $original_query;
wp_reset_postdata();

do_action('arsol_projects_after_project_overview', $project_id); 
?>