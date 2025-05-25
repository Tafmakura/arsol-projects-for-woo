<?php
/**
 * Project Orders
 *
 * Shows orders associated with a project.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

do_action('arsol_projects_before_project_orders', $project_id);
?>

<?php // Navigation included in includes/classes/class-endpoints.php  ?>

<div class="project-content project-orders"> 
    <?php do_action('arsol_projects_orders_before_table', $project_id); ?>
    
    <div class="woocommerce">
        <?php 
        // Use the project_orders shortcode to render the table
        echo do_shortcode('[arsol_project_orders project_id="' . esc_attr($project_id) . '"]');
        ?>
    </div>
    
    <?php do_action('arsol_projects_orders_after_table', $project_id); ?>
</div>

<?php do_action('arsol_projects_after_project_orders', $project_id); ?>