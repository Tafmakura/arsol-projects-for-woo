<?php
/**
 * Project Request Content
 * 
 * Main content area for project request page.
 * Variables: None required
 */

if (!defined('ABSPATH')) exit;

?>

<div class="arsol-project-request-content">
    <?php
    // Include the project request form
    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/form-project-create-request.php';
    ?>
</div>
