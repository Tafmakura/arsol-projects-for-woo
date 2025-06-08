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
    <form action="options.php" method="post">
        <table class="form-table">
            <?php
            settings_fields('arsol_projects_settings');
            do_settings_sections('arsol_projects_settings');
            ?>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
