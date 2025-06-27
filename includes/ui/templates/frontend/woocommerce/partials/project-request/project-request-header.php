<?php
/**
 * Project Request Header
 * 
 * Header section for project request page.
 * Variables: None required
 */

if (!defined('ABSPATH')) exit;

?>

<div class="arsol-project-request-header">
    <div class="request-header-content">
        <div class="project-breadcrumb">
            <a href="<?php echo esc_url(wc_get_account_endpoint_url('projects')); ?>">
                <?php _e('← Back to Projects', 'arsol-pfw'); ?>
            </a>
        </div>
        
        <h1 class="request-title"><?php _e('Request a Project', 'arsol-pfw'); ?></h1>
        <p class="request-subtitle"><?php _e('Submit a request for a new project to be created', 'arsol-pfw'); ?></p>
    </div>
    
    <div class="request-header-actions">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('project-create')); ?>" class="button button-secondary">
            <?php _e('Create Project Instead', 'arsol-pfw'); ?>
        </a>
    </div>
</div>
