<?php

namespace Arsol_Projects_For_Woo\Workflow;

use Exception;
use Arsol_Projects_For_Woo\Core\Conversion_Handler;
use Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Request_Conversion;
use Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Proposal_Conversion;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Workflow Handler Class
 * Handles all workflow operations, conversions, and creates default stages on activation
 */
class Workflow_Handler {

    /**
     * Active workflow implementation instance
     * @var \Arsol_Projects_For_Woo\Workflows\Workflow_Interface
     */
    private $workflow;

    // Default stage definitions - only created on plugin activation
    protected static $default_stage_definitions = array(
        'request' => array(
            'pending-review' => 'Pending Review',
            'under-review'   => 'Under Review', 
            'on-hold'        => 'On Hold',
            'approved'       => 'Approved',
            'rejected'       => 'Rejected',
        ),
        'proposal' => array(
            'processing' => 'Processing',
            'approved'   => 'Approved',
            'rejected'   => 'Rejected',
            'expired'    => 'Expired',
        ),
        'project' => array(
            'not-started' => 'Not Started',
            'in-progress' => 'In Progress',
            'on-hold'     => 'On Hold',
            'completed'   => 'Completed',
            'cancelled'   => 'Cancelled',
        ),
    );

    public function __construct() {
        // Load active workflow implementation (hard-coded to "standard" for now)
        $workflow_slug = 'standard';
        $workflow_file = ARSOL_PROJECTS_PLUGIN_DIR . "includes/workflows/{$workflow_slug}/class-arsol-pfw-workflow-{$workflow_slug}.php";
        if (file_exists($workflow_file)) {
            require_once $workflow_file;
            $class = '\\Arsol_Projects_For_Woo\\Workflows\\Standard\\Workflow_Standard';
            if (class_exists($class)) {
                $this->workflow = new $class();
                // Allow the workflow to add its own hooks
                if (method_exists($this->workflow, 'register_hooks')) {
                    $this->workflow->register_hooks();
                }
            }
        }
        
        // Hook into post status transitions
        add_action('transition_post_status', array($this, 'set_proposal_review_status'), 10, 3);
        
        // Hook for admin notices
        add_action('admin_notices', array($this, 'display_conversion_notices'));
        
        // Hook for post edit screens to display conversion notices
        add_action('admin_notices', array($this, 'display_post_edit_notices'));
        
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

        // Hook into plugin activation to create default stages
        // Hook admin actions for conversions
        add_action('admin_post_arsol_convert_to_proposal', array($this, 'convert_request_to_proposal'));
        add_action('admin_post_arsol_convert_to_project', array($this, 'convert_proposal_to_project'));        add_action('arsol_pfw_plugin_activated', array($this, 'create_default_stages_on_activation'));
    }

    /**
     * Create default stages on plugin activation only
     * Only creates stages if none exist
     */
    public function create_default_stages_on_activation() {
        $taxonomies = array(
            'request'  => 'arsol-pfw-request-stage',
            'proposal' => 'arsol-pfw-proposal-stage',
            'project'  => 'arsol-pfw-project-stage',
        );

        foreach ($taxonomies as $entity_type => $taxonomy) {
            // Check if taxonomy exists
            if (!taxonomy_exists($taxonomy)) {
                continue;
            }

            // Check if any terms exist
            $existing_terms = get_terms(array(
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
                'number'     => 1,
            ));

            // Only create defaults if no terms exist
            if (empty($existing_terms) || is_wp_error($existing_terms)) {
                $this->create_default_stages($entity_type, $taxonomy);
            }
        }
    }

    /**
     * Create default stages for entity type
     *
     * @param string $entity_type Entity type
     * @param string $taxonomy Taxonomy name
     */
    protected function create_default_stages($entity_type, $taxonomy) {
        if (!isset(self::$default_stage_definitions[$entity_type])) {
            return;
        }

        $stages = self::$default_stage_definitions[$entity_type];
        
        foreach ($stages as $slug => $name) {
            // Check if term already exists
            if (!term_exists($slug, $taxonomy)) {
                $result = wp_insert_term($name, $taxonomy, array(
                    'slug' => $slug,
                ));

                if (is_wp_error($result)) {
                    error_log("Failed to create default stage '{$slug}' for {$entity_type}: " . $result->get_error_message());
                } else {
                    // Log success
                    error_log("Created default stage '{$slug}' for {$entity_type}");
                }
            }
        }

        // Clear any cached stage data
        if (class_exists('\Arsol_Projects_For_Woo\Core\Stage_Handler')) {
            \Arsol_Projects_For_Woo\Core\Stage_Handler::clear_stages_cache($entity_type);
        }
    }

    /**
     * Get default stage definitions (for reference only)
     *
     * @param string $entity_type Optional entity type
     * @return array
     */
    public static function get_default_stage_definitions($entity_type = null) {
        if ($entity_type) {
            return isset(self::$default_stage_definitions[$entity_type]) 
                ? self::$default_stage_definitions[$entity_type] 
                : array();
        }
        
        return self::$default_stage_definitions;
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
        $request_id = intval($_GET['request_id']);
        $converter = new \Arsol_Projects_For_Woo\Core\Conversion_Handler();
        $converter->convert_request_to_proposal($request_id);
        }

    public function convert_proposal_to_project($proposal_id = 0, $is_internal_call = false) {
        if (empty($proposal_id)) {
            $proposal_id = intval($_GET['proposal_id']);
        }
        $converter = new \Arsol_Projects_For_Woo\Core\Conversion_Handler();
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
        // Get request object to use setter methods
        $request = new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Arsol_PFW_Request($post_id);
        
        if (isset($data['request_budget'])) {
            $request->set_budget(sanitize_text_field($data['request_budget']));
        }
        
        if (isset($data['request_start_date'])) {
            $request->set_start_date(sanitize_text_field($data['request_start_date']));
        }
        
        if (isset($data['request_due_date'])) {
            $request->set_due_date(sanitize_text_field($data['request_due_date']));
        }
        
        if (isset($data['request_project_lead'])) {
            $request->set_project_lead(sanitize_text_field($data['request_project_lead']));
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
        $line_items = get_post_meta($proposal_id, '_arsol_pfw_proposed_project_quotation_line_items', true);
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

    /**
     * Check if proposal has valid quotation data
     * 
     * @param int $proposal_id
     * @return bool
     */
    private function has_valid_quotation_data($proposal_id) {
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Arsol_PFW_Proposal($proposal_id);
        $line_items = $proposal->get_quotation_line_items() ?: array();
        
        if (empty($line_items) || !is_array($line_items)) {
            return false;
        }
        
        // Check that at least one valid line item exists across all item types
        $has_valid_item = false;
        
        // Check products
        if (!empty($line_items['products'])) {
            foreach ($line_items['products'] as $item) {
                if (!empty($item['description']) && isset($item['regular_price']) && floatval($item['regular_price']) > 0) {
                    $has_valid_item = true;
                    break;
                }
            }
        }
        
        // Check one-time fees
        if (!$has_valid_item && !empty($line_items['one_time_fees'])) {
            foreach ($line_items['one_time_fees'] as $item) {
                if (!empty($item['description']) && !empty($item['amount']) && floatval($item['amount']) > 0) {
                    $has_valid_item = true;
                    break;
                }
            }
        }
        
        // Check recurring fees
        if (!$has_valid_item && !empty($line_items['recurring_fees'])) {
            foreach ($line_items['recurring_fees'] as $item) {
                if (!empty($item['description']) && !empty($item['amount']) && floatval($item['amount']) > 0) {
                    $has_valid_item = true;
                    break;
                }
            }
        }
        
        return $has_valid_item;
    }

    // ==========================================
    // SIMPLIFIED WORKFLOW METHODS
    // ==========================================

    /**
     * Cleanup stuck workflows (static method for cron) - Simplified version
     */
    public static function cleanup_stuck_workflows($max_age_minutes = 30) {
        // Simplified cleanup - just remove old workflow metadata
        global $wpdb;
        
        $max_age_timestamp = current_time('timestamp') - ($max_age_minutes * 60);
        $max_age_date = date('Y-m-d H:i:s', $max_age_timestamp);
        
        // Find and clean up old workflow metadata
        $cleaned_count = $wpdb->query($wpdb->prepare("
            DELETE FROM {$wpdb->postmeta}
            WHERE meta_key IN ('_arsol_pfw_workflow_started', '_arsol_pfw_workflow_type', '_arsol_pfw_conversion_type', '_arsol_pfw_conversion_step', '_arsol_pfw_conversion_created_ids', '_arsol_pfw_conversion_rollback_reason')
            AND meta_value < %s
        ", $max_age_date));
        
        if ($cleaned_count > 0) {
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                "Cleaned up {$cleaned_count} old workflow metadata entries");
        }
        
        return $cleaned_count;
    }

    /**
     * Force clear a specific stuck workflow - Simplified version
     */
    public function force_clear_stuck_workflow($post_id) {
        // Simple cleanup of workflow metadata
                    delete_post_meta($post_id, '_arsol_pfw_workflow_started');
            delete_post_meta($post_id, '_arsol_pfw_workflow_type');
                    delete_post_meta($post_id, '_arsol_pfw_conversion_type');
                    delete_post_meta($post_id, '_arsol_pfw_conversion_step');
        delete_post_meta($post_id, '_arsol_pfw_conversion_created_ids');
        delete_post_meta($post_id, '_arsol_pfw_conversion_rollback_reason');
            
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
            "Cleared workflow metadata for post #{$post_id}");
    }

    /**
     * Emergency cleanup of all stuck workflows (for admin use) - Simplified version
     */
    public static function emergency_cleanup_all_stuck_workflows() {
        global $wpdb;
        
        // Remove all workflow metadata
        $cleaned_count = $wpdb->query("
            DELETE FROM {$wpdb->postmeta}
            WHERE meta_key IN ('_arsol_pfw_workflow_started', '_arsol_pfw_workflow_type', '_arsol_pfw_conversion_type', '_arsol_pfw_conversion_step', '_arsol_pfw_conversion_created_ids', '_arsol_pfw_conversion_rollback_reason')
        ");
        
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('warning', 
            "Emergency cleanup: removed {$cleaned_count} workflow metadata entries");
        
        return $cleaned_count;
    }

    // ==========================================
    // ADMIN NOTICE METHODS
    // ==========================================

    /**
     * Set an admin notice
     */
    private function set_admin_notice($type, $message, $details = array()) {
        $notices = get_transient('arsol_pfw_admin_notices') ?: array();
        $notices[] = array(
            'type' => $type,
            'message' => $message,
            'details' => $details,
            'timestamp' => current_time('timestamp')
        );
        set_transient('arsol_pfw_admin_notices', $notices, 300); // 5 minutes
    }

    /**
     * Set a conversion success notice
     */
    private function set_conversion_success_notice($from_type, $to_type, $from_id, $to_id, $title) {
        $message = sprintf(
            __('Successfully converted %s #%d "%s" to %s #%d', 'arsol-pfw'),
            ucfirst($from_type),
            $from_id,
            $title,
            ucfirst($to_type),
            $to_id
        );
        
        $this->set_admin_notice('success', $message, array(
            'from_type' => $from_type,
            'to_type' => $to_type,
            'from_id' => $from_id,
            'to_id' => $to_id,
            'title' => $title
        ));
    }

    /**
     * Set a conversion failure notice
     */
    private function set_conversion_failure_notice($from_type, $to_type, $from_id, $title, $error) {
        $message = sprintf(
            __('Failed to convert %s #%d "%s" to %s: %s', 'arsol-pfw'),
            ucfirst($from_type),
            $from_id,
            $title,
            ucfirst($to_type),
            $error
        );
        
        $this->set_admin_notice('error', $message, array(
            'from_type' => $from_type,
            'to_type' => $to_type,
            'from_id' => $from_id,
            'title' => $title,
            'error' => $error
        ));
    }

    /**
     * Display conversion notices using session-based approach
     */
    public function display_conversion_notices() {
        // Display any conversion notices stored in transient
        if (class_exists('\Arsol_Projects_For_Woo\Core\Conversion_Handler')) {
            \Arsol_Projects_For_Woo\Core\Conversion_Handler::display_admin_notices();
        }
    }

    /**
     * Display notices on post edit screens when settings-updated parameter is present
     */
    public function display_post_edit_notices() {
        // Check if we're on a post edit screen and settings-updated parameter is present
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
            if (class_exists('\Arsol_Projects_For_Woo\Core\Conversion_Handler')) {
                \Arsol_Projects_For_Woo\Core\Conversion_Handler::display_admin_notices();
            }
        }
    }
}