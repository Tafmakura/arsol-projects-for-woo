<?php
/**
 * Admin Debugging Settings Page
 *
 * @package Arsol_Projects_For_Woo
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the settings class instance
$settings_debugging = new \Arsol_Projects_For_Woo\Admin\Settings_Debugging();
?>
<div class="wrap">
    <form action="options.php" method="post">
        <?php settings_fields('arsol_pfw_debug_options'); ?>
        
        <table class="form-table" role="presentation">
            <tbody>
                <?php $settings_debugging->render_woocommerce_logs_row(); ?>
            </tbody>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>
