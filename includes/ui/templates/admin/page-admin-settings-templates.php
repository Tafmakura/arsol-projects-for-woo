<?php
/**
 * Admin Template Settings Page
 *
 * Template for the template overrides tab.
 *
 * @package Arsol_Projects_For_Woo
 */

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="wrap">
    <form action="options.php" method="post">
        <?php
        // Register settings group for the templates page
        settings_fields('arsol_projects_templates_settings');
        
        // Output all settings sections
        do_settings_sections('arsol_projects_templates_settings');
        
        submit_button();
        ?>
    </form>
</div>
