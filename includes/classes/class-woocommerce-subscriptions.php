<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Subscriptions Integration Class
 * 
 * This class centralizes all WooCommerce Subscriptions functionality including:
 * - Subscription product detection and handling
 * - Project-subscription associations
 * - Billing calculations and cost projections
 * - Subscription lifecycle management
 * - Frontend subscription displays
 * 
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */
class Woocommerce_Subscriptions {

    /**
     * @var Woocommerce_Subscriptions
     */
    private static $instance = null;

    /**
     * @var array Cached form options to avoid repeated expensive calls
     */
    private static $cached_form_options = null;

    /**
     * Meta key used for storing project data on orders/subscriptions
     */
    const PROJECT_META_KEY = '_wc_other/arsol-projects-for-woo/arsol-project';

    /**
     * Constructor
     */
    public function __construct() {
        if (self::is_plugin_active()) {
            $this->init_hooks();
        }
    }

    /**
     * Get singleton instance
     *
     * @return Woocommerce_Subscriptions
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize hooks and filters
     */
    private function init_hooks() {
        // Admin hooks
        add_action('wp_ajax_arsol_proposal_invoice_ajax_get_subscription_product_details', array($this, 'ajax_get_subscription_product_details'));
        
        // Frontend hooks
        add_action('woocommerce_subscription_details_after_subscription_table', array($this, 'display_project_details'));
        
        // Subscription lifecycle hooks
        add_action('woocommerce_subscription_status_updated', array($this, 'handle_subscription_status_change'), 10, 3);
        add_action('wcs_renewal_order_created', array($this, 'handle_renewal_order_creation'), 10, 2);
        add_action('wcs_switch_order_created', array($this, 'handle_switch_order_creation'), 10, 2);
        
        // Project association hooks
        add_action('woocommerce_checkout_update_order_meta', array($this, 'handle_subscription_project_association'), 20, 2);
    }

    /**
     * Check if WooCommerce Subscriptions plugin is active
     *
     * @return bool
     */
    public static function is_plugin_active() {
        return class_exists('WC_Subscriptions');
    }

    /**
     * Check if WooCommerce Subscriptions is available and show error if not
     *
     * @return bool
     */
    public static function ensure_plugin_active() {
        if (!self::is_plugin_active()) {
            wc_add_notice(__('WooCommerce Subscriptions plugin is required for this feature.', 'arsol-pfw'), 'error');
            return false;
        }
        return true;
    }

    // =====================================================
    // PRODUCT DETECTION & HANDLING METHODS
    // =====================================================

    /**
     * Check if a product is a subscription product
     *
     * @param \WC_Product|int $product Product object or ID
     * @return bool
     */
    public static function is_subscription_product($product) {
        if (!self::is_plugin_active()) {
            return false;
        }

        if (is_numeric($product)) {
            $product = wc_get_product($product);
        }

        if (!$product) {
            return false;
        }

        return $product->is_type(array('subscription', 'subscription_variation', 'variable-subscription'));
    }

    /**
     * Get subscription product details
     *
     * @param \WC_Product $product The product object
     * @return array Subscription details
     */
    public static function get_subscription_product_details($product) {
        if (!self::is_subscription_product($product)) {
            return array();
        }

        $regular_price = $product->get_regular_price();
        $active_price = $product->get_price();
        $sale_price = '';
        
        if (is_numeric($active_price) && is_numeric($regular_price) && $active_price < $regular_price) {
            $sale_price = $active_price;
        }

        return array(
            'regular_price' => wc_format_decimal($regular_price ?: 0, wc_get_price_decimals()),
            'sale_price' => $sale_price ? wc_format_decimal($sale_price, wc_get_price_decimals()) : '',
            'is_subscription' => true,
            'sign_up_fee' => wc_format_decimal($product->get_meta('_subscription_sign_up_fee') ?: 0, wc_get_price_decimals()),
            'billing_interval' => $product->get_meta('_subscription_period_interval'),
            'billing_period' => $product->get_meta('_subscription_period'),
            'trial_length' => $product->get_meta('_subscription_trial_length'),
            'trial_period' => $product->get_meta('_subscription_trial_period'),
        );
    }

    /**
     * AJAX handler for getting subscription product details
     */
    public function ajax_get_subscription_product_details() {
        check_ajax_referer('arsol-proposal-invoice-nonce', 'nonce');
        
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        if (!$product_id) {
            wp_send_json_error('Missing product ID');
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error('Invalid product');
        }

        $data = self::get_subscription_product_details($product);
        
        if (empty($data)) {
            // Handle non-subscription products
            $data = array(
                'regular_price' => wc_format_decimal($product->get_regular_price() ?: 0, wc_get_price_decimals()),
                'sale_price' => $product->get_sale_price() ? wc_format_decimal($product->get_sale_price(), wc_get_price_decimals()) : '',
                'is_subscription' => false,
                'sign_up_fee' => 0,
                'billing_interval' => null,
                'billing_period' => null,
            );
        }

        wp_send_json_success($data);
    }

    /**
     * Get WooCommerce Subscriptions form options (cached for performance)
     *
     * @return array
     */
    public static function get_form_options() {
        // Return cached version if available
        if (self::$cached_form_options !== null) {
            return self::$cached_form_options;
        }

        if (!self::is_plugin_active()) {
            self::$cached_form_options = array(
                'intervals' => array(1 => 1),
                'periods' => array('month' => 'month')
            );
            return self::$cached_form_options;
        }

        // Cache the expensive function calls with error suppression to prevent output issues
        $intervals = array(1 => 1);
        $periods = array('month' => 'month');
        
        // Suppress any potential output from WCS functions
        ob_start();
        try {
            if (function_exists('wcs_get_subscription_period_interval_strings')) {
                $intervals = wcs_get_subscription_period_interval_strings();
            }
            if (function_exists('wcs_get_subscription_period_strings')) {
                $periods = wcs_get_subscription_period_strings();
            }
        } catch (Exception $e) {
            // Log error but don't break functionality
            error_log('Arsol Projects: WCS form options error - ' . $e->getMessage());
        }
        // Clean any unexpected output
        ob_end_clean();

        self::$cached_form_options = array(
            'intervals' => $intervals,
            'periods' => $periods
        );

        return self::$cached_form_options;
    }

    /**
     * Clear the cached form options (useful for testing or if options change)
     */
    public static function clear_form_options_cache() {
        self::$cached_form_options = null;
    }

    // =====================================================
    // PROJECT ASSOCIATION METHODS
    // =====================================================

    /**
     * Handle project association for subscription orders during checkout
     *
     * @param int $order_id Order ID
     * @param array $data Checkout data
     */
    public function handle_subscription_project_association($order_id, $data) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Check if this order contains subscription products
        $has_subscription = false;
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (self::is_subscription_product($product)) {
                $has_subscription = true;
                break;
            }
        }

        if (!$has_subscription) {
            return;
        }

        // Get project ID from order meta (set by checkout process)
        $project_id = $order->get_meta(self::PROJECT_META_KEY);
        if (!$project_id) {
            return;
        }

        // Associate project with any subscriptions created from this order
        $this->associate_subscriptions_with_project($order_id, $project_id);
    }

    /**
     * Associate subscriptions created from an order with a project
     *
     * @param int $order_id Parent order ID
     * @param int $project_id Project ID
     */
    private function associate_subscriptions_with_project($order_id, $project_id) {
        if (!function_exists('wcs_get_subscriptions_for_order')) {
            return;
        }

        $subscriptions = wcs_get_subscriptions_for_order($order_id);
        foreach ($subscriptions as $subscription) {
            $subscription->update_meta_data(self::PROJECT_META_KEY, $project_id);
            $subscription->save();
        }
    }

    /**
     * Get project ID from subscription or its parent order
     *
     * @param \WC_Subscription|\WC_Order $subscription_or_order
     * @return int|string Project ID or empty string
     */
    public static function get_project_from_subscription($subscription_or_order) {
        if (!self::is_plugin_active()) {
            return '';
        }

        // Try to get project from the subscription itself first
        $project_id = $subscription_or_order->get_meta(self::PROJECT_META_KEY);
        if ($project_id) {
            return $project_id;
        }

        // If not found and this is a subscription, check parent order
        if (function_exists('wcs_is_subscription') && wcs_is_subscription($subscription_or_order)) {
            $parent_order_id = $subscription_or_order->get_parent_id();
            if ($parent_order_id) {
                $parent_order = wc_get_order($parent_order_id);
                if ($parent_order) {
                    return $parent_order->get_meta(self::PROJECT_META_KEY);
                }
            }
        }

        return '';
    }

    /**
     * Check if an order/subscription is a parent order (not a renewal/switch/resubscribe)
     *
     * @param \WC_Order|\WC_Subscription $order
     * @return bool
     */
    public static function is_parent_order($order) {
        // Check for renewal, switch, or resubscribe meta
        $is_renewal = $order->get_meta('_subscription_renewal');
        $is_switch = $order->get_meta('_subscription_switch');
        $is_resubscribe = $order->get_meta('_subscription_resubscribe');
        
        if (!empty($is_renewal) || !empty($is_switch) || !empty($is_resubscribe)) {
            return false;
        }
        
        // If this is a subscription object, it's not a "parent order"
        if (function_exists('wcs_is_subscription') && wcs_is_subscription($order)) {
            return false;
        }
        
        return true;
    }

    // =====================================================
    // SUBSCRIPTION LIFECYCLE HANDLERS
    // =====================================================

    /**
     * Handle subscription status changes
     *
     * @param \WC_Subscription $subscription
     * @param string $new_status
     * @param string $old_status
     */
    public function handle_subscription_status_change($subscription, $new_status, $old_status) {
        // Placeholder for future functionality
        // Could be used for project workflow automation
        do_action('arsol_subscription_status_changed', $subscription, $new_status, $old_status);
    }

    /**
     * Handle renewal order creation
     *
     * @param \WC_Order $renewal_order
     * @param \WC_Subscription $subscription
     */
    public function handle_renewal_order_creation($renewal_order, $subscription) {
        // Ensure renewal orders inherit project association
        $project_id = self::get_project_from_subscription($subscription);
        if ($project_id) {
            $renewal_order->update_meta_data(self::PROJECT_META_KEY, $project_id);
            $renewal_order->save();
        }
    }

    /**
     * Handle switch order creation
     *
     * @param \WC_Order $switch_order
     * @param \WC_Subscription $subscription
     */
    public function handle_switch_order_creation($switch_order, $subscription) {
        // Ensure switch orders inherit project association
        $project_id = self::get_project_from_subscription($subscription);
        if ($project_id) {
            $switch_order->update_meta_data(self::PROJECT_META_KEY, $project_id);
            $switch_order->save();
        }
    }

    // =====================================================
    // SUBSCRIPTION RETRIEVAL METHODS
    // =====================================================

    /**
     * Get subscriptions associated with a project
     *
     * @param int $project_id Project ID
     * @param int $user_id User ID
     * @param int $current_page Current page number
     * @param int $per_page Subscriptions per page
     * @return object Subscriptions object with pagination data
     */
    public static function get_project_subscriptions($project_id, $user_id, $current_page = 1, $per_page = 10) {
        if (!self::is_plugin_active()) {
            return self::create_empty_subscriptions_result();
        }
        
        // Get parent orders associated with this project
        $parent_order_args = array(
            'customer_id' => $user_id,
            'meta_key' => self::PROJECT_META_KEY,
            'meta_value' => $project_id,
            'return' => 'ids',
            'limit' => -1,
        );
        $parent_order_ids = wc_get_orders($parent_order_args);
        
        // Find all subscriptions related to these parent orders
        $all_subscription_ids = array();
        
        foreach ($parent_order_ids as $parent_id) {
            $parent_order = wc_get_order($parent_id);
            if ($parent_order && function_exists('wcs_get_subscriptions_for_order')) {
                $subscriptions = wcs_get_subscriptions_for_order($parent_order);
                foreach ($subscriptions as $subscription) {
                    $all_subscription_ids[] = $subscription->get_id();
                }
            }
        }
        
        // Remove duplicates and sort
        $all_subscription_ids = array_unique($all_subscription_ids);
        rsort($all_subscription_ids);
        
        // Paginate the results
        $total = count($all_subscription_ids);
        $max_pages = ceil($total / $per_page);
        $offset = ($current_page - 1) * $per_page;
        $subscription_ids = array_slice($all_subscription_ids, $offset, $per_page);
        
        $result = new \stdClass();
        $result->subscriptions = $subscription_ids;
        $result->total = $total;
        $result->max_num_pages = $max_pages;
        
        return $result;
    }

    /**
     * Create an empty subscriptions result
     *
     * @return object
     */
    private static function create_empty_subscriptions_result() {
        $result = new \stdClass();
        $result->subscriptions = array();
        $result->total = 0;
        $result->max_num_pages = 1;
        return $result;
    }

    // =====================================================
    // FRONTEND DISPLAY METHODS
    // =====================================================

    /**
     * Display project details on subscription pages
     *
     * @param \WC_Subscription $subscription
     */
    public function display_project_details($subscription) {
        $project_id = self::get_project_from_subscription($subscription);
        
        if (!$project_id) {
            return;
        }

        $project = get_post($project_id);
        if (!$project || $project->post_type !== 'arsol-project') {
            return;
        }

        echo '<h2>' . esc_html__('Project Information', 'arsol-pfw') . '</h2>';
        echo '<table class="woocommerce-table woocommerce-table--project-details shop_table project_details">';
        echo '<tbody>';
        echo '<tr>';
        echo '<th>' . esc_html__('Project:', 'arsol-pfw') . '</th>';
        echo '<td><a href="' . esc_url(wc_get_account_endpoint_url('project-overview/' . $project_id)) . '">' . esc_html($project->post_title) . '</a></td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
    }

    // =====================================================
    // CALCULATION METHODS (EXISTING)
    // =====================================================

    /**
     * Converts a billing period string into a number of days.
     *
     * @param string $period The billing period (day, week, month, year).
     * @return float The number of days in the period.
     */
    private static function get_days_in_period($period) {
        if (!self::is_plugin_active()) {
            return 0;
        }
        
        switch (strtolower($period)) {
            case 'day':
                return 1;
            case 'week':
                return 7;
            case 'month':
                return 30.417; // Average days in a month (365 / 12)
            case 'year':
                return 365;
            default:
                return 0;
        }
    }

    /**
     * Returns an array of constants used for date calculations.
     *
     * @return array
     */
    public static function get_calculation_constants() {
        return array(
            'days_in_month' => 30.417,
            'days_in_year'  => 365
        );
    }

    /**
     * Calculates the normalized daily cost of a subscription.
     *
     * @param float $price The price of the subscription.
     * @param int   $interval The billing interval (e.g., 1, 2, 3).
     * @param string $period The billing period (day, week, month, year).
     * @return float The calculated daily cost.
     */
    public static function get_daily_cost($price, $interval, $period) {
        if (!self::is_plugin_active()) {
            return 0;
        }
        
        $price = (float) $price;
        $interval = (int) $interval;
        
        if ($interval === 0) {
            return 0;
        }

        $days_in_period = self::get_days_in_period($period);
        if ($days_in_period === 0) {
            return 0;
        }
        
        $total_days_in_cycle = $days_in_period * $interval;
        if ($total_days_in_cycle === 0) {
            return 0;
        }

        return $price / $total_days_in_cycle;
    }

    /**
     * Calculates the average monthly cost of a subscription.
     *
     * @param float $price The price of the subscription.
     * @param int   $interval The billing interval (e.g., 1, 2, 3).
     * @param string $period The billing period (day, week, month, year).
     * @return float The calculated average monthly cost.
     */
    public static function get_monthly_cost($price, $interval, $period) {
        if (!self::is_plugin_active()) {
            return 0;
        }
        
        $constants = self::get_calculation_constants();
        $daily_cost = self::get_daily_cost($price, $interval, $period);
        return $daily_cost * $constants['days_in_month'];
    }

    /**
     * Calculates the normalized annual cost of a subscription.
     *
     * @param float $price The price of the subscription.
     * @param int   $interval The billing interval (e.g., 1, 2, 3).
     * @param string $period The billing period (day, week, month, year).
     * @return float The calculated annual cost.
     */
    public static function get_annual_cost($price, $interval, $period) {
        if (!self::is_plugin_active()) {
            return 0;
        }
        
        $constants = self::get_calculation_constants();
        $daily_cost = self::get_daily_cost($price, $interval, $period);
        return $daily_cost * $constants['days_in_year'];
    }
}