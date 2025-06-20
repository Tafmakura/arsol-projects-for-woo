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
    <h2><?php esc_html_e('Error Logs', 'arsol-pfw'); ?></h2>
    
    <p class="description">
        <?php esc_html_e('Enable debug logging for different components to help troubleshoot issues.', 'arsol-pfw'); ?>
        <?php esc_html_e('Logs can be found', 'arsol-pfw'); ?> 
        <a href="<?php echo esc_url(admin_url('admin.php?page=wc-status&tab=logs')); ?>" target="_blank">
            <?php esc_html_e('here', 'arsol-pfw'); ?>
        </a>.
        <strong><?php esc_html_e('Note:', 'arsol-pfw'); ?></strong> 
        <?php esc_html_e('Only enable logging when needed as it can generate large log files over time.', 'arsol-pfw'); ?>
    </p>
    
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
