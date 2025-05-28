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

<?php // Navigation included in includes/classes/class-endpoints.php  ?>

<div class="project-content project-subscriptions"> 
    <?php do_action('arsol_projects_subscriptions_before_table', $project_id); ?>
    
    <div class="arsol-project-table-wrapper">
        <p><?php printf(
            /* translators: %s: subscriptions */
            esc_html__('This table gives you a live view of all %s. Monitor status, payment schedules, and manage your recurring transactions in one place.', 'arsol-projects-for-woo'),
            '<span class="woocommerce-subscriptions">' . esc_html__('subscriptions', 'woocommerce-subscriptions') . '</span>'
        ); ?></p>
        <?php 
        // Use the project_subscriptions shortcode to render the table
        echo do_shortcode('[arsol_project_subscriptions project_id="' . esc_attr($project_id) . '"]');
        ?>
    </div>
    
    <?php do_action('arsol_projects_subscriptions_after_table', $project_id); ?>
</div>

<?php do_action('arsol_projects_after_project_subscriptions', $project_id); ?>