<?php
/**
 * Admin Settings Debugging Class
 *
 * @package Arsol_Projects_For_Woo\Admin
 * @version 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin;

use Arsol_Projects_For_Woo\Woocommerce_Logs;

if (!defined('ABSPATH')) {
    exit;
}

class Settings_Debugging {

    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'show_admin_notices'));
    }

    public function register_settings() {
        register_setting('arsol_pfw_debug_options', 'arsol_pfw_debug_options');
        // Note: We'll handle the rendering manually in the template
    }

    /**
     * Render the WooCommerce Logs settings row
     */
    public function render_woocommerce_logs_row() {
        $debug_options = get_option('arsol_pfw_debug_options', array());
        $available_options = array();
        
        if (class_exists('Arsol_Projects_For_Woo\Woocommerce_Logs')) {
            $available_options = \Arsol_Projects_For_Woo\Woocommerce_Logs::get_available_debug_options();
        }
        
        ?>
        <tr class="arsol-pfw-woocommerce-logs">
            <th scope="row"><?php esc_html_e('WooCommerce Logs', 'arsol-pfw'); ?></th>
            <td>
                <div class="arsol-pfw-setting-field arsol-pfw-woocommerce-logs">
                    <p class="description">
                        <?php esc_html_e('Enable debug logging for different components to help troubleshoot issues.', 'arsol-pfw'); ?>
                        <?php esc_html_e('Logs can be found', 'arsol-pfw'); ?> 
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wc-status&tab=logs')); ?>" target="_blank">
                            <?php esc_html_e('here', 'arsol-pfw'); ?>
                        </a>.
                        <strong><?php esc_html_e('Note:', 'arsol-pfw'); ?></strong> 
                        <?php esc_html_e('Only enable logging when needed as it can generate large log files over time.', 'arsol-pfw'); ?>
                    </p>
                    
                    <?php foreach ($available_options as $option_key => $option_label) : ?>
                        <?php $checked = !empty($debug_options[$option_key]); ?>
                        <label>
                            <input type="checkbox" 
                                   name="arsol_pfw_debug_options[<?php echo esc_attr($option_key); ?>]" 
                                   value="1" 
                                   <?php checked($checked); ?> />
                            <?php echo esc_html($option_label); ?>
                        </label><br>
                    <?php endforeach; ?>
                </div>
            </td>
        </tr>
        <?php
    }

    public function render_debugging_description() {
        // This method is kept for compatibility but not used with custom structure
    }

    public function render_woocommerce_logs_description() {
        // This method is kept for compatibility but not used with custom structure  
    }

    public function show_admin_notices() {
        // Only show notices on the debugging tab
        if (!isset($_GET['tab']) || $_GET['tab'] !== 'debugging') {
            return;
        }
        
        if (isset($_GET['logs_cleared']) && $_GET['logs_cleared'] == '1') {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . esc_html__('Plugin logs have been cleared successfully.', 'arsol-pfw') . '</p>';
            echo '</div>';
        }
        
        if (isset($_GET['error']) && $_GET['error'] == '1') {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . esc_html__('Error clearing logs. Logging class not available.', 'arsol-pfw') . '</p>';
            echo '</div>';
        }
    }
}
