<?php
/**
 * Project Overview Content
 *
 * Shows overview information about a project.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// The global $post is set up by the shortcode (same pattern as proposal and request templates)
if (!isset($post) || !$post) {
    echo '<p>' . esc_html__('Project not found.', 'arsol-pfw') . '</p>';
    return;
}

// Use factory function to get project object
$project = arsol_pfw_get_project($post->ID);
if (!$project) {
    echo '<p>' . esc_html__('Project not found.', 'arsol-pfw') . '</p>';
    return;
}

// Get project details using CRUD methods
$project_budget = $project->get_budget();
$project_timeline = $project->get_prop('timeline');
$project_stage = $project->get_stage();
$project_progress = $project->get_progress();
$project_deadline = $project->get_deadline();
$project_lead = $project->get_project_lead();
$related_request_id = $project->get_prop('related_request_id');

$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';
?>

<?php
// 1. Customer Notice (first)
$customer_notice = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_customer_notice($project->get_id(), 'project');
if (!empty($customer_notice)) : ?>
    <div class="arsol-pfw-customer-notice">
        <div class="arsol-pfw-notice-content">
            <?php echo wp_kses_post(wpautop($customer_notice)); ?>
        </div>
    </div>
<?php endif; ?>

<?php
// 2. Project Status and Details
?>
<div class="arsol-pfw-project-status">
    <?php if ($project_stage) : ?>
        <p><strong><?php _e('Status:', 'arsol-pfw'); ?></strong> <?php echo esc_html(\Arsol_Projects_For_Woo\Core\Stage_Manager::get_stage_label('project', $project_stage)); ?></p>
    <?php endif; ?>
    
    <?php if ($project_progress !== null) : ?>
        <p><strong><?php _e('Progress:', 'arsol-pfw'); ?></strong> <?php echo esc_html($project_progress); ?>%</p>
    <?php endif; ?>
    
    <?php if ($project_deadline) : ?>
        <p><strong><?php _e('Deadline:', 'arsol-pfw'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($project_deadline))); ?></p>
    <?php endif; ?>
    
    <?php if ($project_budget) : ?>
        <p><strong><?php _e('Budget:', 'arsol-pfw'); ?></strong> <?php echo wp_kses_post(wc_price($project_budget)); ?></p>
    <?php endif; ?>
    
    <?php if ($project_lead) : ?>
        <?php $lead_user = get_userdata($project_lead); ?>
        <?php if ($lead_user) : ?>
            <p><strong><?php _e('Project Lead:', 'arsol-pfw'); ?></strong> <?php echo esc_html($lead_user->display_name); ?></p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
// 3. Post Content (project description)
if (!empty($project->get_name()) && !empty($post->post_content)) : ?>
    <div class="arsol-pfw-post-content">
        <h4><?php _e('Project Details', 'arsol-pfw'); ?></h4>
        <?php echo wp_kses_post($post->post_content); ?>
    </div>
<?php endif; ?> 