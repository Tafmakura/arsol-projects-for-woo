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
    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/sections/frontend/section-project-listing-subscriptions.php';
    ?>
</div>
