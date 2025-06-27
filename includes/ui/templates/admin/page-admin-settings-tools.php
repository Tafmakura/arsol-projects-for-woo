<?php
/**
 * Admin Tools Settings Page
 *
 * @package Arsol_Projects_For_Woo
 */

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="wrap">
    <h1><?php esc_html_e('Tools', 'arsol-pfw'); ?></h1>
    <form action="options.php" method="post">
        <?php
        settings_fields('arsol_projects_tools_settings');
        do_settings_sections('arsol_projects_tools_settings');
        // Note: No submit button needed for tools - they use AJAX actions
        ?>
    </form>
</div> 