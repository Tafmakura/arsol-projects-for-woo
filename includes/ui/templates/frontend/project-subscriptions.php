<?php
/**
 * Project Subscriptions
 *
 * Shows subscriptions associated with a project.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

do_action('arsol_projects_before_project_subscriptions', $project_id);
?>

<!-- Navigation included in includes/classes/class-endpoints.php -->

<div class="woocommerce">
    <div class="project-content project-subscriptions">
        <div class="woocommerce">
            <?php 
            // Use the project_subscriptions shortcode to render the table
            echo do_shortcode('[project_subscriptions project_id="' . esc_attr($project_id) . '"]');
            ?>
        </div>
    </div>

<?php do_action('arsol_projects_after_project_subscriptions', $project_id); ?>