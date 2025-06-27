<?php
/**
 * Projects Listing Content
 * 
 * Main content area for projects listing page.
 * Variables: $current_tab, $projects (optional)
 */

if (!defined('ABSPATH')) exit;

$current_tab = $current_tab ?? 'active';

?>

<div class="arsol-projects-content">
    <?php
    // Include appropriate listing based on current tab
    switch ($current_tab) {
        case 'active':
            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/sections/frontend/section-projects-listing-active.php';
            break;
        case 'proposals':
            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/sections/frontend/section-projects-listing-proposals.php';
            break;
        case 'requests':
            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/sections/frontend/section-projects-listing-requests.php';
            break;
        default:
            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/sections/frontend/section-projects-listing-active.php';
            break;
    }
    ?>
</div>
