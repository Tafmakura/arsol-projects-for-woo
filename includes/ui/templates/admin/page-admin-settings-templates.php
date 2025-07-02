<?php
/**
 * Admin Display Settings Page
 *
 * Template for the display settings tab.
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
        // Register all settings groups for the display page
        settings_fields('arsol_content_display_settings');
        settings_fields('arsol_sidebar_display_settings');
        settings_fields('arsol_comment_display_settings');
        settings_fields('arsol_form_display_settings');
        settings_fields('arsol_projects_templates_settings');
        
        // Output all settings sections
        do_settings_sections('arsol_projects_templates_settings');
        
        submit_button();
        ?>
    </form>
</div> 
