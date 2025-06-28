<?php
/**
 * Project Orders Content
 * 
 * Main content area for project orders page.
 * Variables: $project_id, $orders (optional)
 */

if (!defined('ABSPATH')) exit;

?>

<div class="arsol-pfw-project-orders-content">
    <?php
    // Include the existing orders listing section
    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-listing-orders.php';
    ?>
</div>
