<?php
/**
 * Project Orders Content
 * 
 * Main content area for project orders page.
 * Variables: $project_id, $orders (optional)
 */

if (!defined('ABSPATH')) exit;

?>

<div class="arsol-project-orders-content">
    <?php
    // Include the existing orders listing section
    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/sections/frontend/section-project-listing-orders.php';
    ?>
</div>
