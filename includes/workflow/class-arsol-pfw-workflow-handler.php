<?php

namespace Arsol_Projects_For_Woo\Workflow;

use Exception;
use Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Request_Conversion;
use Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Proposal_Conversion;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Workflow Handler Class
 * Handles all workflow operations for conversions and customer actions
 */
class Workflow_Handler {
    public function __construct() {
        // Hook into post status transitions
        add_action('transition_post_status', array($this, 'set_proposal_review_status'), 10, 3);
        
        // Hook for admin notices
        add_action('admin_notices', array($this, 'display_conversion_notices'));
        
        // Hook into woocommerce notices (for frontend)
        add_action('woocommerce_before_single_product_summary', array($this, 'display_conversion_notices'), 5);
        add_action('woocommerce_before_shop_loop', array($this, 'display_conversion_notices'), 5);
        add_action('woocommerce_before_checkout_form', array($this, 'display_conversion_notices'), 5);
        
        // Hook into specific WooCommerce my account pages
        add_action('woocommerce_before_account_orders', array($this, 'display_conversion_notices'), 5);
        add_action('woocommerce_before_account_downloads', array($this, 'display_conversion_notices'), 5);
        add_action('woocommerce_before_account_payment_methods', array($this, 'display_conversion_notices'), 5);
        add_action('woocommerce_before_account_edit', array($this, 'display_conversion_notices'), 5);
        
        // Hook into custom my account pages
        add_action('woocommerce_account_projects_endpoint', array($this, 'display_conversion_notices'), 5);
        add_action('woocommerce_account_view-project_endpoint', array($this, 'display_conversion_notices'), 5);
        add_action('woocommerce_account_view-proposal_endpoint', array($this, 'display_conversion_notices'), 5);
        add_action('woocommerce_account_view-request_endpoint', array($this, 'display_conversion_notices'), 5);
        add_action('woocommerce_account_create-request_endpoint', array($this, 'display_conversion_notices'), 5);
        
        // Hook into general frontend pages
        add_action('wp_head', array($this, 'display_conversion_notices'), 1);
    }

    /**
     * Check if a user can view a specific post
     * This method is used for security validation in conversions
     */
    public static function user_can_view_post($user_id, $post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }
    
        // The user can view their own posts
        if ($post->post_author == $user_id) {
            return true;
        }
    
        // Admins can view everything
        if (user_can($user_id, 'manage_options')) {
            return true;
        }
        
        return false;
    }

    /**
     * Legacy method to manage review status - currently not used
     */
    public function set_proposal_review_status($new_status, $old_status, $post) {
        // Currently not implementing automatic review status changes
        // This is reserved for future review workflow improvements
        
        // Removed automatic review status setting - using proposal status only
        // if ($post->post_type === 'arsol-pfw-proposal' && $new_status === 'publish' && $old_status !== 'publish') {
        // Set proposal to pending-approval status when published');
        // }
    }

    public function convert_request_to_proposal() {
        // Use the dedicated conversion class
        $converter = new Request_Conversion();
        $converter->convert_request_to_proposal();
    }

    public function convert_proposal_to_project($proposal_id = 0, $is_internal_call = false) {
        // Use the dedicated conversion class
        $converter = new Proposal_Conversion();
        $converter->convert_proposal_to_project($proposal_id, $is_internal_call);
    }

    public function customer_cancel_request() {
        $request_frontend = new \Arsol_Projects_For_Woo\Frontend\Request_Frontend();
        $request_frontend->customer_cancel_request();
    }

    public function customer_approve_proposal() {
        $proposal_frontend = new \Arsol_Projects_For_Woo\Frontend\Proposal_Frontend();
        $proposal_frontend->customer_approve_proposal();
    }

    public function customer_reject_proposal() {
        $proposal_frontend = new \Arsol_Projects_For_Woo\Frontend\Proposal_Frontend();
        $proposal_frontend->customer_reject_proposal();
    }

    public function handle_create_request() {
        $request_frontend = new \Arsol_Projects_For_Woo\Frontend\Request_Frontend();
        $request_frontend->customer_create_request();
    }

    public function handle_edit_request() {
        $request_frontend = new \Arsol_Projects_For_Woo\Frontend\Request_Frontend();
        $request_frontend->customer_edit_request();
    }

    private function update_request_meta($post_id, $data) {
        // Save parent project if provided
        if (isset($data['parent_project_id']) && !empty($data['parent_project_id'])) {
            $parent_project_id = absint($data['parent_project_id']);
            if ($parent_project_id) {
                // Validate parent project exists
                $parent_project = get_post($parent_project_id);
                if ($parent_project && $parent_project->post_type === 'arsol-pfw-project') {
                    // Save parent project ID
                    update_post_meta($post_id, '_arsol_pfw_parent_project_id', $parent_project_id);
                    
                    // Mark as project-tied request
                    update_post_meta($post_id, '_arsol_pfw_is_project_tied_request', 1);
                }
            }
        }
        
        if (isset($data['request_budget'])) {
            $amount = wc_clean(wp_unslash($data['request_budget']));
            // Remove commas and other non-numeric characters except decimal point
            $amount = \Arsol_Projects_For_Woo\Woocommerce::clean_amount_input($amount);
            // Convert to proper decimal format
            $amount = wc_format_decimal($amount);
            $currency = get_woocommerce_currency();
            update_post_meta($post_id, '_arsol_pfw_request_budget', ['amount' => $amount, 'currency' => $currency]);
        }
        if (isset($data['request_start_date'])) {
            update_post_meta($post_id, '_arsol_pfw_request_start_date', sanitize_text_field($data['request_start_date']));
        }
        if (isset($data['request_delivery_date'])) {
            update_post_meta($post_id, '_arsol_pfw_request_delivery_date', sanitize_text_field($data['request_delivery_date']));
        }
    }

    /**
     * Safe redirect that handles "headers already sent" issues
     */
    private function safe_redirect($url) {
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
     * Debug function to test proposal conversion
     * Call this function manually to test conversion logic
     * 
     * @param int $proposal_id The proposal ID to test
     * @return array Debug information
     */
    public static function debug_proposal_conversion($proposal_id) {
        $debug_info = array();
        
        // Check proposal exists
        $proposal = get_post($proposal_id);
        $debug_info['proposal_exists'] = !empty($proposal);
        $debug_info['proposal_type'] = $proposal ? $proposal->post_type : 'N/A';
        $debug_info['proposal_stage'] = $proposal ? $proposal->post_status : 'N/A';
        $debug_info['proposal_author'] = $proposal ? $proposal->post_author : 'N/A';
        
        // Check cost proposal type
        $cost_proposal_type = get_post_meta($proposal_id, '_arsol_pfw_proposal_costing_type', true) ?: 'none';
        
        $debug_info['cost_proposal_type'] = $cost_proposal_type;
        $debug_info['should_create_orders'] = ($cost_proposal_type === 'quotation');
        
        // Check line items
        $line_items = get_post_meta($proposal_id, '_arsol_pfw_proposal_quotation_line_items', true);
        $debug_info['has_line_items'] = !empty($line_items);
        $debug_info['line_items_structure'] = !empty($line_items) ? array_keys($line_items) : array();
        
        if (!empty($line_items)) {
            $debug_info['products_count'] = !empty($line_items['products']) ? count($line_items['products']) : 0;
            $debug_info['one_time_fees_count'] = !empty($line_items['one_time_fees']) ? count($line_items['one_time_fees']) : 0;
            $debug_info['recurring_fees_count'] = !empty($line_items['recurring_fees']) ? count($line_items['recurring_fees']) : 0;
            $debug_info['shipping_fees_count'] = !empty($line_items['shipping_fees']) ? count($line_items['shipping_fees']) : 0;
        }
        
        // Check if customer exists
        if ($proposal) {
            $customer = new \WC_Customer($proposal->post_author);
            $debug_info['customer_exists'] = $customer && $customer->get_id();
            $debug_info['customer_email'] = $customer ? $customer->get_billing_email() : 'N/A';
        }
        
        // Check WooCommerce Subscriptions
        $debug_info['wc_subscriptions_active'] = class_exists('WC_Subscriptions') && function_exists('wcs_create_subscription');
        
        return $debug_info;
    }

    // ==========================================
    // TRANSACTION SYSTEM METHODS
    // ==========================================

    /**
     * Cleanup stuck workflows (static method for cron)
     */
    public static function cleanup_stuck_workflows($max_age_minutes = 30) {
        global $wpdb;
        
        $cutoff_time = date('Y-m-d H:i:s', strtotime("-{$max_age_minutes} minutes"));
        
        // Find stuck conversions
        $stuck_posts = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, pm1.meta_value as conversion_type, pm2.meta_value as started_time
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_arsol_workflow_status'
            INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_arsol_workflow_started'
            WHERE pm1.meta_value = 'in_progress'
            AND pm2.meta_value < %s
        ", $cutoff_time));
        
        $cleaned = 0;
        foreach ($stuck_posts as $post) {
            // Use appropriate converter to force rollback stuck conversion
            $conversion_type = get_post_meta($post->ID, '_arsol_conversion_type', true);
            
            switch ($conversion_type) {
                case 'request_to_proposal':
                    $converter = new Request_Conversion();
                    $converter->force_clear_stuck_workflow($post->ID);
                    break;
                case 'proposal_to_project':
                    $converter = new Proposal_Conversion();
                    $converter->force_clear_stuck_workflow($post->ID);
                    break;
                default:
                    // Fallback for unknown conversion types - just clear the metadata
                    delete_post_meta($post->ID, '_arsol_workflow_status');
                    delete_post_meta($post->ID, '_arsol_workflow_started');
                    delete_post_meta($post->ID, '_arsol_conversion_type');
                    delete_post_meta($post->ID, '_arsol_conversion_created_ids');
                    delete_post_meta($post->ID, '_arsol_conversion_step');
                    break;
            }
            
            $cleaned++;
            
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('warning', 
                "Cleaned up stuck conversion: Post #{$post->ID}, Type: {$conversion_type}");
        }
        
        return $cleaned;
    }

    /**
     * Force clear stuck workflow for a specific post (public method for manual cleanup)
     */
    public function force_clear_stuck_workflow($post_id) {
        $conversion_type = get_post_meta($post_id, '_arsol_conversion_type', true);
        
        switch ($conversion_type) {
            case 'request_to_proposal':
                $converter = new Request_Conversion();
                return $converter->force_clear_stuck_workflow($post_id);
            case 'proposal_to_project':
                $converter = new Proposal_Conversion();
                return $converter->force_clear_stuck_workflow($post_id);
            default:
                // Fallback for unknown conversion types
                $status = get_post_meta($post_id, '_arsol_workflow_status', true);
        if ($status === 'in_progress') {
                    delete_post_meta($post_id, '_arsol_workflow_status');
                    delete_post_meta($post_id, '_arsol_workflow_started');
                    delete_post_meta($post_id, '_arsol_conversion_type');
                    delete_post_meta($post_id, '_arsol_conversion_created_ids');
                    delete_post_meta($post_id, '_arsol_conversion_step');
            
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                        "Manually cleared stuck workflow metadata for post #{$post_id}");
            
            return true;
        }
        return false;
        }
    }

    /**
     * Clear all stuck workflows regardless of age (emergency cleanup)
     */
    public static function emergency_cleanup_all_stuck_workflows() {
        global $wpdb;
        
        // Find all stuck conversions regardless of age
        $stuck_posts = $wpdb->get_results("
            SELECT p.ID, pm1.meta_value as conversion_type
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_arsol_workflow_status'
            WHERE pm1.meta_value = 'in_progress'
        ");
        
        $cleaned = 0;
        foreach ($stuck_posts as $post) {
            // Use appropriate converter to force rollback stuck conversion
            $conversion_type = get_post_meta($post->ID, '_arsol_conversion_type', true);
            
            switch ($conversion_type) {
                case 'request_to_proposal':
                    $converter = new Request_Conversion();
                    $converter->force_clear_stuck_workflow($post->ID);
                    break;
                case 'proposal_to_project':
                    $converter = new Proposal_Conversion();
                    $converter->force_clear_stuck_workflow($post->ID);
                    break;
                default:
                    // Fallback for unknown conversion types - just clear the metadata
                    delete_post_meta($post->ID, '_arsol_workflow_status');
                    delete_post_meta($post->ID, '_arsol_workflow_started');
                    delete_post_meta($post->ID, '_arsol_conversion_type');
                    delete_post_meta($post->ID, '_arsol_conversion_created_ids');
                    delete_post_meta($post->ID, '_arsol_conversion_step');
                    break;
            }
            
            $cleaned++;
            
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('warning', 
                "Emergency cleanup: Post #{$post->ID}, Type: {$conversion_type}");
        }
        
        return $cleaned;
    }

    // ==========================================
    // NOTICE SYSTEM METHODS
    // ==========================================

    /**
     * Set admin notice for display after redirect
     */
    private function set_admin_notice($type, $message, $details = array()) {
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
    private function set_conversion_success_notice($from_type, $to_type, $from_id, $to_id, $title) {
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
    private function set_conversion_failure_notice($from_type, $to_type, $from_id, $title, $error) {
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
     * Display conversion notices
     */
    public function display_conversion_notices() {
        $user_id = get_current_user_id();
        $notice_data = get_transient('arsol_notice_' . $user_id);
        
        if ($notice_data && is_array($notice_data)) {
            $class = 'notice notice-' . $notice_data['type'] . ' is-dismissible';
            echo '<div class="' . esc_attr($class) . '">';
            echo '<p>' . wp_kses_post($notice_data['message']) . '</p>';
            echo '</div>';
            
            // Clear the transient after displaying
            delete_transient('arsol_notice_' . $user_id);
        }
    }
}