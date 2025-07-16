<?php
/**
 * Project Subscriptions Content
 * 
 * Main content area for project subscriptions page.
 * Variables: $project_id, $subscriptions (optional)
 */

if (!defined('ABSPATH')) exit;

?>

<div class="arsol-pfw-project-subscriptions-content">
    <?php
    // Include the existing subscriptions listing section
    include ARSOL_PFW_PLUGIN_DIR . 'includes/ui/components/frontend/endpoint-view-project-subscriptions.php';
    ?>
</div>
