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
    include ARSOL_PFW_PLUGIN_DIR . 'ui/components/frontend/endpoint-view-project-orders.php';
    ?>
</div>
