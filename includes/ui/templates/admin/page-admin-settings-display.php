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
        // Use the main display settings group - this will handle all display settings
        settings_fields('arsol_pfw_display_settings');
        
        // Output all settings sections
        do_settings_sections('arsol_pfw_display_settings');
        
        submit_button();
        ?>
    </form>
</div>
