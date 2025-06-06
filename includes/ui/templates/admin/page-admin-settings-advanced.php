<?php
/**
 * Admin Advanced Settings Page
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
        settings_fields('arsol_projects_advanced_settings');
        do_settings_sections('arsol_projects_advanced_settings');
        submit_button();
        ?>
    </form>
</div> 