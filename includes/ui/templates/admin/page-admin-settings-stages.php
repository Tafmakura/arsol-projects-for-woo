<?php
/**
 * Admin Settings Page: Stages
 * 
 * Template for the stages settings tab.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="wrap">
    
    <!-- Stage Management Section -->
    <div class="arsol-pfw-stage-management-section" style="margin-bottom: 30px;">
        <h2><?php _e('Stage Management', 'arsol-pfw'); ?></h2>
        <p class="description"><?php _e('Manage the different stage taxonomies used throughout the plugin.', 'arsol-pfw'); ?></p>
        
        <div class="arsol-pfw-stage-buttons" style="display: flex; gap: 15px; margin-top: 15px;">
            <a href="<?php echo admin_url('edit-tags.php?taxonomy=arsol-pfw-request-stage&post_type=arsol-pfw-request'); ?>" 
               class="button button-primary">
                <span class="dashicons dashicons-admin-tools" style="vertical-align: middle; margin-right: 5px;"></span>
                <?php _e('Manage Request Stages', 'arsol-pfw'); ?>
            </a>
            
            <a href="<?php echo admin_url('edit-tags.php?taxonomy=arsol-pfw-proposal-stage&post_type=arsol-pfw-proposal'); ?>" 
               class="button button-primary">
                <span class="dashicons dashicons-admin-tools" style="vertical-align: middle; margin-right: 5px;"></span>
                <?php _e('Manage Proposal Stages', 'arsol-pfw'); ?>
            </a>
            
            <a href="<?php echo admin_url('edit-tags.php?taxonomy=arsol-pfw-project-stage&post_type=arsol-pfw-project'); ?>" 
               class="button button-primary">
                <span class="dashicons dashicons-admin-tools" style="vertical-align: middle; margin-right: 5px;"></span>
                <?php _e('Manage Project Stages', 'arsol-pfw'); ?>
            </a>
        </div>
        
        <hr style="margin: 25px 0;">
    </div>

    <form method="post" action="options.php">
        <?php
        settings_fields('arsol_stages_settings');
        do_settings_sections('arsol_stages_settings');
        submit_button();
        ?>
    </form>
</div> 