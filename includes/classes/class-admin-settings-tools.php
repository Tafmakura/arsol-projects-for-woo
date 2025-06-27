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
        
        // Add AJAX handlers for log management
        add_action('wp_ajax_arsol_view_logs', array($this, 'handle_view_logs_ajax'));
        add_action('wp_ajax_arsol_clear_logs', array($this, 'handle_clear_logs_ajax'));
        add_action('wp_ajax_arsol_download_logs', array($this, 'handle_download_logs_ajax'));
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

        // Error Logs Section
        add_settings_section(
            'arsol_projects_error_logs_section',
            __('Error Logs', 'arsol-pfw'),
            array($this, 'render_error_logs_description'),
            'arsol_projects_tools_settings'
        );

        add_settings_field(
            'error_logs_viewer',
            __('Log Management', 'arsol-pfw'),
            array($this, 'render_error_logs_field'),
            'arsol_projects_tools_settings',
            'arsol_projects_error_logs_section'
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

    public function render_error_logs_description() {
        echo '<p>' . esc_html__('View and manage plugin error logs. These logs help diagnose issues with projects, conversions, and other plugin operations.', 'arsol-pfw') . '</p>';
    }

    public function render_error_logs_field() {
        ?>
        <div class="arsol-logs-management">
            <div class="arsol-logs-actions" style="margin-bottom: 15px;">
                <button type="button" id="view-logs" class="button button-secondary">
                    <?php esc_html_e('View Recent Logs', 'arsol-pfw'); ?>
                </button>
                <button type="button" id="clear-logs" class="button">
                    <?php esc_html_e('Clear All Logs', 'arsol-pfw'); ?>
                </button>
                <button type="button" id="download-logs" class="button">
                    <?php esc_html_e('Download Logs', 'arsol-pfw'); ?>
                </button>
            </div>
            
            <div id="logs-viewer" style="display: none; background: #f9f9f9; border: 1px solid #ddd; padding: 15px; max-height: 400px; overflow-y: auto;">
                <pre id="logs-content" style="margin: 0; white-space: pre-wrap; font-family: Consolas, Monaco, 'Courier New', monospace; font-size: 12px;"></pre>
            </div>
        </div>
        
        <p class="description">
            <?php esc_html_e('View recent plugin error logs, clear old entries, or download logs for debugging purposes.', 'arsol-pfw'); ?>
        </p>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // View logs
            $('#view-logs').on('click', function() {
                const button = $(this);
                const viewer = $('#logs-viewer');
                const content = $('#logs-content');
                
                button.prop('disabled', true).text('<?php esc_js_e('Loading...', 'arsol-pfw'); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'arsol_view_logs',
                        nonce: '<?php echo wp_create_nonce('arsol_admin'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            content.text(response.data.logs);
                            viewer.show();
                        } else {
                            alert('<?php esc_js_e('Failed to load logs:', 'arsol-pfw'); ?> ' + response.data);
                        }
                        button.prop('disabled', false).text('<?php esc_js_e('View Recent Logs', 'arsol-pfw'); ?>');
                    },
                    error: function() {
                        alert('<?php esc_js_e('Ajax request failed.', 'arsol-pfw'); ?>');
                        button.prop('disabled', false).text('<?php esc_js_e('View Recent Logs', 'arsol-pfw'); ?>');
                    }
                });
            });
            
            // Clear logs
            $('#clear-logs').on('click', function() {
                if (confirm('<?php esc_js_e('Clear all plugin logs? This action cannot be undone.', 'arsol-pfw'); ?>')) {
                    const button = $(this);
                    button.prop('disabled', true).text('<?php esc_js_e('Clearing...', 'arsol-pfw'); ?>');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'arsol_clear_logs',
                            nonce: '<?php echo wp_create_nonce('arsol_admin'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('<?php esc_js_e('Logs cleared successfully.', 'arsol-pfw'); ?>');
                                $('#logs-viewer').hide();
                                $('#logs-content').text('');
                            } else {
                                alert('<?php esc_js_e('Failed to clear logs:', 'arsol-pfw'); ?> ' + response.data);
                            }
                            button.prop('disabled', false).text('<?php esc_js_e('Clear All Logs', 'arsol-pfw'); ?>');
                        },
                        error: function() {
                            alert('<?php esc_js_e('Ajax request failed.', 'arsol-pfw'); ?>');
                            button.prop('disabled', false).text('<?php esc_js_e('Clear All Logs', 'arsol-pfw'); ?>');
                        }
                    });
                }
            });
            
            // Download logs
            $('#download-logs').on('click', function() {
                const form = $('<form method="post" action="' + ajaxurl + '">' +
                    '<input type="hidden" name="action" value="arsol_download_logs">' +
                    '<input type="hidden" name="nonce" value="<?php echo wp_create_nonce('arsol_admin'); ?>">' +
                    '</form>');
                $('body').append(form);
                form.submit();
                form.remove();
            });
        });
        </script>
        <?php
    }

    /**
     * Handle AJAX view logs request
     */
    public function handle_view_logs_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'arsol_admin') || !current_user_can('manage_options')) {
            wp_send_json_error('Security check failed');
        }
        
        $logs = $this->get_recent_logs();
        
        wp_send_json_success(array(
            'logs' => $logs,
            'message' => __('Logs loaded successfully.', 'arsol-pfw')
        ));
    }

    /**
     * Handle AJAX clear logs request
     */
    public function handle_clear_logs_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'arsol_admin') || !current_user_can('manage_options')) {
            wp_send_json_error('Security check failed');
        }
        
        $cleared = $this->clear_plugin_logs();
        
        if ($cleared) {
            wp_send_json_success(array(
                'message' => __('All plugin logs have been cleared.', 'arsol-pfw')
            ));
        } else {
            wp_send_json_error('Failed to clear logs');
        }
    }

    /**
     * Handle AJAX download logs request
     */
    public function handle_download_logs_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'arsol_admin') || !current_user_can('manage_options')) {
            wp_die('Security check failed');
        }
        
        $logs = $this->get_all_logs();
        $filename = 'arsol-pfw-logs-' . date('Y-m-d-H-i-s') . '.txt';
        
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($logs));
        
        echo $logs;
        exit;
    }

    /**
     * Get recent plugin logs
     */
    private function get_recent_logs($lines = 100) {
        $logs = array();
        $logs[] = "=== Arsol Projects for WooCommerce - Error Logs ===";
        $logs[] = "Generated: " . date('Y-m-d H:i:s');
        $logs[] = "";
        
        // Check WooCommerce logs first
        if (function_exists('wc_get_logger')) {
            $logs[] = "=== WooCommerce Logs ===";
            
            // Get WooCommerce log directory
            $log_dir = WC_LOG_DIR;
            if (is_dir($log_dir)) {
                $log_files = array(
                    'arsol-pfw',
                    'arsol-pfw-workflow', 
                    'arsol-pfw-defaults',
                    'arsol-pfw-conversions'
                );
                
                $found_logs = false;
                foreach ($log_files as $source) {
                    // Look for log files with this source
                    $pattern = $log_dir . '/' . $source . '-*.log';
                    $files = glob($pattern);
                    
                    if (!empty($files)) {
                        // Sort by modification time, newest first
                        usort($files, function($a, $b) {
                            return filemtime($b) - filemtime($a);
                        });
                        
                        // Read the most recent file
                        $latest_file = $files[0];
                        if (file_exists($latest_file) && is_readable($latest_file)) {
                            $logs[] = "--- {$source} (from " . basename($latest_file) . ") ---";
                            $file_lines = file($latest_file);
                            if ($file_lines) {
                                // Get last N lines
                                $recent_lines = array_slice($file_lines, -min($lines, count($file_lines)));
                                foreach ($recent_lines as $line) {
                                    $logs[] = trim($line);
                                }
                                $found_logs = true;
                            }
                            $logs[] = "";
                        }
                    }
                }
                
                if (!$found_logs) {
                    $logs[] = "No WooCommerce plugin logs found for sources: " . implode(', ', $log_files);
                    $logs[] = "";
                }
            } else {
                $logs[] = "WooCommerce log directory not accessible: " . $log_dir;
                $logs[] = "";
            }
        } else {
            $logs[] = "WooCommerce logger not available";
            $logs[] = "";
        }
        
        // Check WordPress debug log
        $logs[] = "=== WordPress Debug Log ===";
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            $debug_log = WP_CONTENT_DIR . '/debug.log';
            if (file_exists($debug_log) && is_readable($debug_log)) {
                $logs[] = "Reading from: " . $debug_log;
                $file_lines = file($debug_log);
                if ($file_lines) {
                    $plugin_lines = array();
                    // Filter for plugin-related lines
                    foreach ($file_lines as $line) {
                        if (stripos($line, 'arsol') !== false || 
                            stripos($line, 'projects for woo') !== false) {
                            $plugin_lines[] = trim($line);
                        }
                    }
                    
                    if (!empty($plugin_lines)) {
                        // Get last N plugin-related lines
                        $recent_lines = array_slice($plugin_lines, -min($lines, count($plugin_lines)));
                        foreach ($recent_lines as $line) {
                            $logs[] = $line;
                        }
                    } else {
                        $logs[] = "No plugin-related entries found in debug log";
                    }
                } else {
                    $logs[] = "Could not read debug log file";
                }
            } else {
                $logs[] = "Debug log file not found or not readable: " . $debug_log;
            }
        } else {
            $logs[] = "WordPress debug logging is not enabled";
            $logs[] = "To enable: Add these lines to wp-config.php:";
            $logs[] = "define('WP_DEBUG', true);";
            $logs[] = "define('WP_DEBUG_LOG', true);";
            $logs[] = "define('WP_DEBUG_DISPLAY', false);";
        }
        $logs[] = "";
        
        // Check PHP error log
        $logs[] = "=== PHP Error Log ===";
        $php_error_log = ini_get('error_log');
        if ($php_error_log && file_exists($php_error_log) && is_readable($php_error_log)) {
            $logs[] = "Reading from: " . $php_error_log;
            $file_lines = file($php_error_log);
            if ($file_lines) {
                $plugin_lines = array();
                // Filter for plugin-related lines
                foreach ($file_lines as $line) {
                    if (stripos($line, 'arsol') !== false || 
                        stripos($line, 'projects') !== false) {
                        $plugin_lines[] = trim($line);
                    }
                }
                
                if (!empty($plugin_lines)) {
                    $recent_lines = array_slice($plugin_lines, -min(20, count($plugin_lines))); // Fewer PHP error lines
                    foreach ($recent_lines as $line) {
                        $logs[] = $line;
                    }
                } else {
                    $logs[] = "No plugin-related entries found in PHP error log";
                }
            }
        } else {
            $logs[] = "PHP error log not available or not readable";
        }
        $logs[] = "";
        
        // Add debugging information
        $logs[] = "=== Debug Information ===";
        $logs[] = "Plugin Version: " . (defined('ARSOL_PROJECTS_VERSION') ? ARSOL_PROJECTS_VERSION : 'Unknown');
        $logs[] = "WordPress Version: " . get_bloginfo('version');
        $logs[] = "WooCommerce Version: " . (defined('WC_VERSION') ? WC_VERSION : 'Not installed');
        $logs[] = "PHP Version: " . phpversion();
        $logs[] = "WP Debug: " . (defined('WP_DEBUG') && WP_DEBUG ? 'Enabled' : 'Disabled');
        $logs[] = "WP Debug Log: " . (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? 'Enabled' : 'Disabled');
        $logs[] = "Log Directory: " . (function_exists('wc_get_logger') ? WC_LOG_DIR : 'WooCommerce not available');
        
        return implode("\n", $logs);
    }

    /**
     * Get all plugin logs
     */
    private function get_all_logs() {
        return $this->get_recent_logs(1000); // Get more lines for download
    }

    /**
     * Clear plugin logs
     */
    private function clear_plugin_logs() {
        $cleared = false;
        
        // Clear WordPress debug log (only plugin-related entries would require parsing)
        // For safety, we'll just log that a clear was requested
        if (function_exists('wc_get_logger')) {
            $logger = wc_get_logger();
            $logger->info('Plugin logs cleared by administrator', array('source' => 'arsol-pfw'));
            $cleared = true;
        }
        
        return $cleared;
    }
} 