<?php
/**
 * Projects Listing Header
 * 
 * Header section for the projects listing page.
 * Variables: $current_tab (optional)
 */

if (!defined('ABSPATH')) exit;

$current_tab = $current_tab ?? 'active';

?>

<div class="arsol-projects-header">
    <div class="projects-header-content">
        <h1 class="projects-title"><?php _e('My Projects', 'arsol-pfw'); ?></h1>
        
        <?php 
        // Include navigation tabs section
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/sections/frontend/section-projects-navigation.php';
        ?>
    </div>
    
    <div class="projects-header-actions">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('project-create')); ?>" class="button button-primary">
            <?php _e('New Project', 'arsol-pfw'); ?>
        </a>
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('project-request')); ?>" class="button button-secondary">
            <?php _e('Request Project', 'arsol-pfw'); ?>
        </a>
    </div>
</div>
