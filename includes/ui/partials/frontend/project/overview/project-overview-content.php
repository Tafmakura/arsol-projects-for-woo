<?php
/**
 * Project Overview Content
 * 
 * Main content area for individual project pages.
 * Variables: $post_id, $post_type, $project_type
 */

if (!defined('ABSPATH')) exit;

if (empty($post_id) || empty($post_type)) {
    return;
}

?>

<div class="arsol-project-overview-content">
    <?php
    // Include appropriate content based on project type
    switch ($project_type) {
        case 'active':
            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-content-active.php';
            break;
        case 'proposal':
            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-content-proposal.php';
            break;
        case 'request':
            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-content-request.php';
            break;
        default:
            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-content-active.php';
            break;
    }
    ?>
</div>
