<?php
/**
 * Admin Settings Page: Permissions
 * 
 * Template for the permissions settings tab.
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
        settings_fields('arsol_pfw_permissions_settings');
        do_settings_sections('arsol_pfw_permissions_settings');
        submit_button();
        ?>
    </form>
</div> 