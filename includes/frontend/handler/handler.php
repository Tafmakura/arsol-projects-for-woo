<?php
/**
 * Abstract Frontend Handler Class
 *
 * Base class for all frontend handlers providing shared utility methods.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Frontend;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abstract Frontend Handler
 * 
 * Provides shared functionality for all frontend handlers including:
 * - Safe redirects
 * - Notice system
 * - User permission checks
 * - Transaction utilities
 */
abstract class Frontend_Handler {

    /**
     * Safe redirect method that handles both header and JavaScript redirects
     */
    protected function safe_redirect($url) {
        if (headers_sent()) {
            // If headers are already sent, use JavaScript redirect
            echo '<script type="text/javascript">window.location.href="' . esc_url($url) . '";</script>';
            echo '<noscript><meta http-equiv="refresh" content="0;url=' . esc_url($url) . '" /></noscript>';
            exit;
        } else {
            // Use normal redirect
            wp_safe_redirect($url);
            exit;
        }
    }

    /**
     * Set admin notice for display after redirect
     */
    protected function set_admin_notice($type, $message, $details = array()) {
        $notice_data = array(
            'type' => $type, // 'success', 'error', 'warning', 'info'
            'message' => $message,
            'details' => $details,
            'timestamp' => current_time('timestamp')
        );
        
        $user_id = get_current_user_id();
        set_transient('arsol_notice_' . $user_id, $notice_data, 300); // 5 minutes
    }

    /**
     * Set conversion success notice
     */
    protected function set_conversion_success_notice($from_type, $to_type, $from_id, $to_id, $title) {
        // Map types to proper display names
        $type_names = array(
            'request' => __('Project Request', 'arsol-pfw'),
            'proposal' => __('Project Proposal', 'arsol-pfw'),
            'project' => __('Project', 'arsol-pfw')
        );
        
        $from_name = isset($type_names[$from_type]) ? $type_names[$from_type] : ucfirst(str_replace('_', ' ', $from_type));
        $to_name = isset($type_names[$to_type]) ? $type_names[$to_type] : ucfirst(str_replace('_', ' ', $to_type));
        
        $message = sprintf(
            __('%s "%s" successfully converted to %s.', 'arsol-pfw'),
            $from_name,
            $title,
            $to_name
        );
        
        $this->set_admin_notice('success', $message, array(
            'conversion_type' => $from_type . '_to_' . $to_type,
            'from_id' => $from_id,
            'to_id' => $to_id
        ));
    }

    /**
     * Set conversion failure notice
     */
    protected function set_conversion_failure_notice($from_type, $to_type, $from_id, $title, $error) {
        // Map types to proper display names
        $type_names = array(
            'request' => __('Project Request', 'arsol-pfw'),
            'proposal' => __('Project Proposal', 'arsol-pfw'),
            'project' => __('Project', 'arsol-pfw')
        );
        
        $from_name = isset($type_names[$from_type]) ? $type_names[$from_type] : ucfirst(str_replace('_', ' ', $from_type));
        $to_name = isset($type_names[$to_type]) ? $type_names[$to_type] : ucfirst(str_replace('_', ' ', $to_type));
        
        $message = sprintf(
            __('Failed to convert %s "%s" to %s. Error: %s', 'arsol-pfw'),
            $from_name,
            $title,
            $to_name,
            $error
        );
        
        $this->set_admin_notice('error', $message, array(
            'conversion_type' => $from_type . '_to_' . $to_type,
            'from_id' => $from_id,
            'error' => $error
        ));
    }

    /**
     * Add WooCommerce notice (for frontend display)
     */
    protected function add_wc_notice($message, $type = 'error') {
        if (function_exists('wc_add_notice')) {
            wc_add_notice($message, $type);
        }
    }

    /**
     * Validate nonce for security
     */
    protected function validate_nonce($nonce_field, $nonce_action) {
        \Arsol_Projects_For_Woo\Core\Access_Handler::validate_nonce_and_permissions($nonce_field, $nonce_action);
    }

    /**
     * Check if current user can perform action on post
     */
    protected function check_user_permissions($post_id, $action = 'view') {
        $user_id = get_current_user_id();
        
        if (!\Arsol_Projects_For_Woo\Core\Access_Handler::user_can_view_post($user_id, $post_id)) {
            wp_die(__('You do not have permission to perform this action.', 'arsol-pfw'));
        }
        
        return true;
    }

    /**
     * Get safe redirect URL with fallback
     */
    protected function get_safe_redirect_url($primary_url, $fallback_url = null) {
        if (empty($fallback_url)) {
            $fallback_url = wc_get_account_endpoint_url('projects');
        }
        
        return !empty($primary_url) ? $primary_url : $fallback_url;
    }

    /**
     * Handle exceptions with proper error reporting
     */
    protected function handle_exception(\Exception $e, $context = 'frontend_action') {
        // Log the error
        error_log(sprintf(
            'Frontend Handler Exception [%s]: %s in %s:%d',
            $context,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ));
        
        // Show user-friendly error
        $this->add_wc_notice(
            __('An error occurred while processing your request. Please try again.', 'arsol-pfw'),
            'error'
        );
        
        return false;
    }

    /**
     * Abstract method that must be implemented by child classes
     * This method should contain the main logic for the specific frontend handler
     */
    abstract public function init();
} 