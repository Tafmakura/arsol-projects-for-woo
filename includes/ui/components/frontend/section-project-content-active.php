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
$stage_terms = wp_get_post_terms($post->ID, 'arsol-pfw-project-stage', array('fields' => 'names'));
$project_stage = (!empty($stage_terms) && !is_wp_error($stage_terms)) ? $stage_terms[0] : 'Not Started';
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
        
        <?php
        // Add Customer Notice section using new three-layer helper function
        $customer_notice = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_customer_notice($post->ID, 'project');
        if (!empty($customer_notice)) : ?>
            <div class="arsol-pfw-notice arsol-pfw-customer-notice">
                <div class="arsol-pfw-notice-header">
                    <h4><?php _e('Important Notice', 'arsol-pfw'); ?></h4>
                </div>
                <div class="arsol-pfw-notice-content">
                    <?php echo wp_kses_post(wpautop($customer_notice)); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div> 