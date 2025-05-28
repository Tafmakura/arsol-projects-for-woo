<?php
/**
 * Admin Settings Page Template
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Get saved settings
$settings = get_option('arsol_projects_settings', array());
?>

<div class="wrap">
    <h1>Arsol Projects for Woo</h1>
    
    <form action="options.php" method="post">
        <?php
        settings_fields('arsol_projects_settings');
        do_settings_sections('arsol_projects_settings');
        submit_button();
        ?>
    </form>
</div>
