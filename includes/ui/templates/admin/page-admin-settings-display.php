<?php
/**
 * Admin Settings Page: Display
 * 
 * Template for the display settings tab (customer notices and display controls).
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="wrap">
    <form method="post" action="options.php">
        <?php
        // Register all settings groups for the display page
        settings_fields('arsol_content_display_settings');
        settings_fields('arsol_sidebar_display_settings');
        settings_fields('arsol_comment_display_settings');
        settings_fields('arsol_files_display_settings');
        settings_fields('arsol_form_display_settings');
        
        // Output all settings sections
        do_settings_sections('arsol_phases_settings');
        
        submit_button();
        ?>
    </form>
</div>
