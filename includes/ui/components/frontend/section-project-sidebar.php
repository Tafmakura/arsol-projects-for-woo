<?php
/**
 * Project Sidebar Controller
 *
 * This template loads the correct sidebar based on the context.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// The $sidebar_type variable is passed from the parent template.
if (!isset($sidebar_type)) {
    return;
}
?>
<div class="project-sidebar card">
    <?php
    switch ($sidebar_type) {
        case 'proposal':
            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-sidebar-proposal.php';
            break;
        case 'request':
            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-sidebar-request.php';
            break;
        case 'overview':
        default:
            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-sidebar-overview.php';
            break;
    }
    ?>
</div> 