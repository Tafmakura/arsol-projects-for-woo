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

// The global $post is set up by the shortcode (same pattern as proposal and request templates)
if (!isset($post) || !$post) {
    echo '<p>' . esc_html__('Project not found.', 'arsol-pfw') . '</p>';
    return;
}

// Get project details using global $post (consistent with other templates)
$status_terms = wp_get_post_terms($post->ID, 'arsol-pfw-project-status', array('fields' => 'names'));
$status = !empty($status_terms) ? $status_terms[0] : 'N/A';
$start_date = get_post_meta($post->ID, '_arsol_pfw_project_start_date', true);
$due_date = get_post_meta($post->ID, '_arsol_pfw_project_due_date', true);
?>

<div class="project-content-wrapper">
    <div class="project-content">
        <div class="project-description">
            <?php if (empty(get_the_content())) : ?>
                <?php include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-overview-empty.php'; ?>
            <?php else : ?>
                <?php the_content(); ?>
            <?php endif; ?>
        </div>
    </div>
</div> 