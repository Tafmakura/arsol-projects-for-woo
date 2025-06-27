<?php
/**
 * Project Create Content
 * 
 * Main content area for project creation page.
 * Variables: $form_type, $edit_mode (optional), $project_id (optional)
 */

if (!defined('ABSPATH')) exit;

$form_type = $form_type ?? 'project';
$edit_mode = $edit_mode ?? false;

?>

<div class="arsol-project-create-content">
    <?php
    // Include appropriate form based on type
    switch ($form_type) {
        case 'request':
            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/form-project-create-request.php';
            break;
        case 'project':
        default:
            include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/form-project-create-active.php';
            break;
    }
    ?>
</div>
