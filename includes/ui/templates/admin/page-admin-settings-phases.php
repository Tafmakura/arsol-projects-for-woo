<?php
/**
 * Admin Settings Page: Phases
 * 
 * Template for the phases settings tab.
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
        // Register both settings groups
        settings_fields('arsol_phases_settings');
        settings_fields('arsol_content_display_settings');
        
        // Output all settings sections
        do_settings_sections('arsol_phases_settings');
        
        submit_button();
        ?>
    </form>
</div>
