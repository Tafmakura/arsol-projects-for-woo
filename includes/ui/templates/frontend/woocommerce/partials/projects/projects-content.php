<?php
/**
 * Projects Listing Content
 * 
 * Main content area for projects listing page.
 * Variables: $current_tab, $query, $paged, $total_pages, $wp_button_class, $user_id
 */

if (!defined('ABSPATH')) exit;

// Extract variables passed from wc_get_template
$current_tab = $current_tab ?? 'active';

// Ensure all variables are available for sections
$query = $query ?? null;
$paged = $paged ?? 1;
$total_pages = $total_pages ?? 1;
$wp_button_class = $wp_button_class ?? '';
$user_id = $user_id ?? get_current_user_id();

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
