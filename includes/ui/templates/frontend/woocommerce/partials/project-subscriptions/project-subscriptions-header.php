<?php
/**
 * Project Subscriptions Header
 * 
 * Header section for project subscriptions page.
 * Variables: $project_id, $project_title (optional)
 */

if (!defined('ABSPATH')) exit;

$project_title = $project_title ?? get_the_title($project_id);

?>

<div class="arsol-project-subscriptions-header">
    <div class="subscriptions-header-content">
        <div class="project-breadcrumb">
            <a href="<?php echo esc_url(wc_get_account_endpoint_url('projects')); ?>">
                <?php _e('← Back to Projects', 'arsol-pfw'); ?>
            </a>
            <?php if (!empty($project_id)): ?>
                <span class="breadcrumb-separator">/</span>
                <a href="<?php echo esc_url(wc_get_account_endpoint_url('project-overview') . '/' . $project_id); ?>">
                    <?php echo esc_html($project_title); ?>
                </a>
            <?php endif; ?>
        </div>
        
        <h1 class="subscriptions-title"><?php _e('Project Subscriptions', 'arsol-pfw'); ?></h1>
        <?php if (!empty($project_title)): ?>
            <p class="subscriptions-subtitle"><?php printf(__('Subscriptions related to: %s', 'arsol-pfw'), esc_html($project_title)); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="subscriptions-header-actions">
        <?php if (!empty($project_id)): ?>
            <a href="<?php echo esc_url(wc_get_account_endpoint_url('project-overview') . '/' . $project_id); ?>" class="button button-secondary">
                <?php _e('View Project', 'arsol-pfw'); ?>
            </a>
        <?php endif; ?>
    </div>
</div>
