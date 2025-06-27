<?php
/**
 * Admin Settings Tools Class
 *
 * @package Arsol_Projects_For_Woo\Admin
 * @version 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Settings_Tools {

    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add AJAX handler for cleanup
        add_action('wp_ajax_arsol_cleanup_conversions', array($this, 'handle_cleanup_ajax'));
    }

    public function register_settings() {
        register_setting(
            'arsol_projects_tools_settings', 
            'arsol_projects_tools_settings',
            array(
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'default' => array()
            )
        );

        // Conversion Management Section
        add_settings_section(
            'arsol_projects_conversion_management_section',
            __('Conversion Management', 'arsol-pfw'),
            array($this, 'render_conversion_management_description'),
            'arsol_projects_tools_settings'
        );

        add_settings_field(
            'conversion_cleanup',
            __('Maintenance', 'arsol-pfw'),
            array($this, 'render_conversion_cleanup_field'),
            'arsol_projects_tools_settings',
            'arsol_projects_conversion_management_section'
        );
    }

    /**
     * Sanitize settings before saving
     *
     * @param array $input Raw input data
     * @return array Sanitized data
     */
    public function sanitize_settings($input) {
        // For tools settings, we typically don't store persistent data
        // This is mainly for form processing of tool actions
        return array();
    }

    public function render_conversion_management_description() {
        echo '<p>' . esc_html__('Manage the conversion system that handles transforming requests to proposals and proposals to projects. The system includes automatic rollback capabilities and cleanup functionality.', 'arsol-pfw') . '</p>';
    }

    public function render_conversion_cleanup_field() {
        ?>
        <button type="button" id="cleanup-conversions" class="button">
            <?php esc_html_e('Clean Up Stuck Conversions', 'arsol-pfw'); ?>
        </button>
        <p class="description">
            <?php esc_html_e('Remove conversion data for processes stuck for more than 30 minutes.', 'arsol-pfw'); ?>
        </p>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#cleanup-conversions').on('click', function() {
                if (confirm('<?php esc_js_e('Clean up stuck conversions?', 'arsol-pfw'); ?>')) {
                    const button = $(this);
                    button.prop('disabled', true).text('<?php esc_js_e('Cleaning...', 'arsol-pfw'); ?>');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'arsol_cleanup_conversions',
                            nonce: '<?php echo wp_create_nonce('arsol_admin'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('<?php esc_js_e('Cleanup completed:', 'arsol-pfw'); ?> ' + response.data.cleaned + ' <?php esc_js_e('conversions cleaned', 'arsol-pfw'); ?>');
                            } else {
                                alert('<?php esc_js_e('Cleanup failed:', 'arsol-pfw'); ?> ' + response.data);
                            }
                            button.prop('disabled', false).text('<?php esc_js_e('Clean Up Stuck Conversions', 'arsol-pfw'); ?>');
                            location.reload();
                        },
                        error: function() {
                            alert('<?php esc_js_e('Ajax request failed.', 'arsol-pfw'); ?>');
                            button.prop('disabled', false).text('<?php esc_js_e('Clean Up Stuck Conversions', 'arsol-pfw'); ?>');
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Handle AJAX cleanup request
     */
    public function handle_cleanup_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'arsol_admin') || !current_user_can('manage_options')) {
            wp_send_json_error('Security check failed');
        }
        
        $cleaned = \Arsol_Projects_For_Woo\Workflow\Workflow_Handler::cleanup_stuck_workflows(30);
        
        wp_send_json_success(array(
            'cleaned' => $cleaned,
            'message' => sprintf(__('%d stuck conversions cleaned up.', 'arsol-pfw'), $cleaned)
        ));
    }
} 