<?php
/**
 * User Projects Template
 *
 * Shows a list of projects associated with the current user.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

do_action('arsol_projects_before_user_projects', $has_projects);
?>

<div class="woocommerce">
    <?php do_action('arsol_projects_before_projects_list', $has_projects); ?>
    
    <?php include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-projects-create-or-request.php'; ?>
    
    <?php 
    // Use the existing shortcode to display user projects
    echo do_shortcode('[arsol_user_projects per_page="' . esc_attr($posts_per_page) . '" paged="' . esc_attr($paged) . '"]'); 
    ?>
    
    <?php do_action('arsol_projects_after_projects_list', $has_projects); ?>
</div>

<?php do_action('arsol_projects_after_user_projects', $has_projects); ?>