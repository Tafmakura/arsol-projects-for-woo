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

        // Debugging Section
        add_settings_section(
            'arsol_projects_debugging_section',
            __('Debug Logging Options', 'arsol-pfw'),
            array($this, 'render_debugging_description'),
            'arsol_projects_debugging_settings'
        );

        // Add debug option checkboxes
        if (class_exists('Arsol_Projects_For_Woo\Woocommerce_Logs')) {
            $debug_options = Woocommerce_Logs::get_available_debug_options();
            foreach ($debug_options as $option_key => $option_label) {
                add_settings_field(
                    $option_key,
                    $option_label,
                    array($this, 'render_debug_checkbox'),
                    'arsol_projects_debugging_settings',
                    'arsol_projects_debugging_section',
                    [
                        'option_key' => $option_key,
                        'label' => $option_label
                    ]
                );
            }
        }
    }

    public function render_debugging_description() {
        echo '<p>' . esc_html__('Enable debug logging for different components to help troubleshoot issues. Logs can be found', 'arsol-pfw') . ' <a href="' . esc_url(admin_url('admin.php?page=wc-status&tab=logs')) . '" target="_blank">' . esc_html__('here', 'arsol-pfw') . '</a>.</p>';
        echo '<p><strong>' . esc_html__('Note:', 'arsol-pfw') . '</strong> ' . esc_html__('Only enable logging when needed as it can generate large log files over time.', 'arsol-pfw') . '</p>';
    }

    public function render_debug_checkbox($args) {
        $debug_options = get_option('arsol_pfw_debug_options', array());
        $checked = !empty($debug_options[$args['option_key']]);
        ?>
        <label>
            <input type="checkbox" 
                   name="arsol_pfw_debug_options[<?php echo esc_attr($args['option_key']); ?>]" 
                   value="1" 
                   <?php checked($checked); ?> />
            <?php echo esc_html__('Enable logging for this component', 'arsol-pfw'); ?>
        </label>
        <?php
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
