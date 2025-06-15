<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Biller Class
 * 
 * Handles creation of WooCommerce orders from proposals
 * Following WordPress coding standards and WooCommerce best practices
 */
class Woocommerce_Biller {
    
    /**
     * Create order from proposal
     * 
     * @param int $proposal_id The proposal ID
     * @return array|WP_Error Result array with order details or error
     */
    public static function create_order($proposal_id) {
        $log_source = 'order_creation';
        
        try {
            Woocommerce_Logs::log($log_source, 'info', 
                sprintf('Starting order creation for proposal #%d', $proposal_id));
            
            // Get and validate proposal data
            $proposal_data = self::get_proposal_data($proposal_id);
            if (is_wp_error($proposal_data)) {
                return $proposal_data;
            }
            
            $line_items = $proposal_data['line_items'];
            $customer_id = $proposal_data['customer_id'];
            
            // Validate that we have items to process
            if (!self::has_billable_items($line_items)) {
                return new \WP_Error('no_billable_items', 
                    __('No billable items found in proposal', 'arsol-pfw'));
            }
            
            // Create parent order with one-time items
            $order = self::create_parent_order(
                $line_items, 
                $customer_id, 
                $log_source
            );
            
            $result = array(
                'success' => true,
                'order_id' => $order->get_id(),
                'order_total' => $order->get_total(),
                'message' => sprintf('Order #%d created successfully', $order->get_id())
            );
            
            // Add proposal reference to order
            $order->add_meta_data('_arsol_proposal_id', $proposal_id);
            $order->save();
            
            Woocommerce_Logs::log($log_source, 'success', 
                sprintf('Order creation completed for proposal #%d. Order #%d created with total $%.2f', 
                    $proposal_id, $order->get_id(), $order->get_total()));
            
            return $result;
            
        } catch (Exception $e) {
            $error_message = sprintf('Order creation failed for proposal #%d: %s', 
                $proposal_id, $e->getMessage());
            
            Woocommerce_Logs::log($log_source, 'error', $error_message);
            
            return new \WP_Error('order_creation_failed', $error_message);
        }
    }
    
    /**
     * Create order and subscription from proposal
     * 
     * @param int $proposal_id The proposal ID
     * @return array|WP_Error Result array with order and subscription details or error
     */
    public static function create_order_with_subscription($proposal_id) {
        $log_source = 'order_subscription_creation';
        
        try {
            Woocommerce_Logs::log($log_source, 'info', 
                sprintf('Starting order and subscription creation for proposal #%d', $proposal_id));
            
            // Get and validate proposal data
            $proposal_data = self::get_proposal_data($proposal_id);
            if (is_wp_error($proposal_data)) {
                return $proposal_data;
            }
            
            $line_items = $proposal_data['line_items'];
            $customer_id = $proposal_data['customer_id'];
            
            // Validate that we have items to process
            if (!self::has_billable_items($line_items)) {
                return new \WP_Error('no_billable_items', 
                    __('No billable items found in proposal', 'arsol-pfw'));
            }
            
            // Check if we have recurring items
            if (!self::has_recurring_items($line_items)) {
                return new \WP_Error('no_recurring_items', 
                    __('No recurring items found for subscription creation', 'arsol-pfw'));
            }
            
            $order = null;
            $subscription = null;
            
            try {
                // Create parent order with one-time items
                $order = self::create_parent_order(
                    $line_items, 
                    $customer_id, 
                    $log_source
                );
                
                // Create subscription with recurring items
                $subscription = self::create_subscription(
                    $line_items, 
                    $customer_id, 
                    $order->get_id(), 
                    $log_source
                );
                
                // Add proposal reference to both order and subscription
                $order->add_meta_data('_arsol_proposal_id', $proposal_id);
                $order->save();
                
                $subscription->add_meta_data('_arsol_proposal_id', $proposal_id);
                $subscription->save();
                
                $result = array(
                    'success' => true,
                    'order_id' => $order->get_id(),
                    'order_total' => $order->get_total(),
                    'subscription_id' => $subscription->get_id(),
                    'subscription_total' => $subscription->get_total(),
                    'message' => sprintf('Order #%d and Subscription #%d created successfully', 
                        $order->get_id(), $subscription->get_id())
                );
                
                Woocommerce_Logs::log($log_source, 'success', 
                    sprintf('Order and subscription creation completed for proposal #%d. Order #%d ($%.2f) and Subscription #%d ($%.2f) created', 
                        $proposal_id, $order->get_id(), $order->get_total(), 
                        $subscription->get_id(), $subscription->get_total()));
                
                return $result;
                
            } catch (Exception $e) {
                // Rollback on failure
                if ($subscription && $subscription->get_id()) {
                    wp_delete_post($subscription->get_id(), true);
                    Woocommerce_Logs::log($log_source, 'info', 
                        sprintf('Rolled back subscription #%d due to error', $subscription->get_id()));
                }
                
                if ($order && $order->get_id()) {
                    wp_delete_post($order->get_id(), true);
                    Woocommerce_Logs::log($log_source, 'info', 
                        sprintf('Rolled back order #%d due to error', $order->get_id()));
                }
                
                throw $e;
            }
            
        } catch (Exception $e) {
            $error_message = sprintf('Order and subscription creation failed for proposal #%d: %s', 
                $proposal_id, $e->getMessage());
            
            Woocommerce_Logs::log($log_source, 'error', $error_message);
            
            return new \WP_Error('order_subscription_creation_failed', $error_message);
        }
    }
    
    /**
     * Get proposal data with validation
     * 
     * @param int $proposal_id The proposal ID
     * @return array|WP_Error Proposal data or error
     */
    public static function get_proposal_data($proposal_id) {
        $proposal = get_post($proposal_id);
        if (!$proposal) {
            return new \WP_Error('invalid_proposal', __('Invalid proposal ID', 'arsol-pfw'));
        }
        
        $line_items = get_post_meta($proposal_id, 'line_items', true);
        if (empty($line_items)) {
            return new \WP_Error('no_line_items', __('No line items found in proposal', 'arsol-pfw'));
        }
        
        return array(
            'proposal' => $proposal,
            'line_items' => $line_items,
            'customer_id' => self::get_customer_id($proposal_id, $proposal)
        );
    }
    
    /**
     * Get customer ID from proposal
     * 
     * @param int $proposal_id The proposal ID
     * @param WP_Post $proposal The proposal post object
     * @return int Customer ID
     */
    public static function get_customer_id($proposal_id, $proposal) {
        $customer_id = get_post_meta($proposal_id, '_arsol_customer_id', true);
        
        if (empty($customer_id)) {
            $customer_id = $proposal->post_author;
        }
        
        return intval($customer_id);
    }
    
    /**
     * Add fees to order using WooCommerce native add_fee method
     * 
     * @param WC_Order $order The order object
     * @param array $fees Array of fees
     * @param string $fee_type Type of fee for logging
     * @param string $log_source Log source
     * @return int Number of fees added
     */
    public static function add_fees_to_order($order, $fees, $fee_type = 'fee', $log_source = 'order_creation') {
        if (empty($fees) || !is_array($fees)) {
            return 0;
        }
        
        $added_count = 0;
        
        foreach ($fees as $fee_id => $fee_data) {
            try {
                // Validate fee data
                if (!self::validate_fee_data($fee_data)) {
                    Woocommerce_Logs::log($log_source, 'warning', 
                        sprintf('Invalid fee data for ID %s: %s', $fee_id, wp_json_encode($fee_data)));
                    continue;
                }
                
                // Prepare fee details
                $fee_name = self::get_fee_name($fee_data, $fee_type);
                $fee_amount = floatval($fee_data['amount']);
                list($is_taxable, $tax_class) = self::determine_tax_settings($fee_data);
                
                // Add fee using WooCommerce native method
                $order->add_fee($fee_name, $fee_amount, $is_taxable, $tax_class);
                $added_count++;
                
                Woocommerce_Logs::log($log_source, 'info', 
                    sprintf('Added %s fee: %s = $%.2f (taxable: %s)', 
                        $fee_type, $fee_name, $fee_amount, $is_taxable ? 'yes' : 'no'));
                
            } catch (Exception $e) {
                Woocommerce_Logs::log($log_source, 'error', 
                    sprintf('Failed to add fee %s: %s', $fee_id, $e->getMessage()));
                throw new \Exception(sprintf('Failed to add fee %s: %s', $fee_id, $e->getMessage()));
            }
        }
        
        return $added_count;
    }
    
    /**
     * Add products to order
     * 
     * @param WC_Order $order The order object
     * @param array $products Array of products
     * @param string $log_source Log source
     * @return int Number of products added
     */
    public static function add_products_to_order($order, $products, $log_source = 'order_creation') {
        if (empty($products) || !is_array($products)) {
            return 0;
        }
        
        $added_count = 0;
        
        foreach ($products as $product_id => $product_data) {
            try {
                if (empty($product_data['product_id'])) {
                    continue;
                }
                
                $product = wc_get_product($product_data['product_id']);
                if (!$product) {
                    Woocommerce_Logs::log($log_source, 'warning', 
                        sprintf('Product #%d not found', $product_data['product_id']));
                    continue;
                }
                
                $quantity = isset($product_data['quantity']) ? intval($product_data['quantity']) : 1;
                $price = isset($product_data['price']) ? floatval($product_data['price']) : $product->get_price();
                $sale_price = isset($product_data['sale_price']) ? floatval($product_data['sale_price']) : $price;
                
                $order->add_product($product, $quantity, array(
                    'subtotal' => $price * $quantity,
                    'total' => $sale_price * $quantity
                ));
                
                $added_count++;
                
                Woocommerce_Logs::log($log_source, 'info', 
                    sprintf('Added product #%d (%s) x%d to order', 
                        $product_data['product_id'], $product->get_name(), $quantity));
                
            } catch (Exception $e) {
                Woocommerce_Logs::log($log_source, 'error', 
                    sprintf('Failed to add product %s: %s', $product_id, $e->getMessage()));
                throw new \Exception(sprintf('Failed to add product %s: %s', $product_id, $e->getMessage()));
            }
        }
        
        return $added_count;
    }
    
    /**
     * Create parent order with one-time items
     * 
     * @param array $line_items Line items from proposal
     * @param int $customer_id Customer ID
     * @param string $log_source Log source
     * @return WC_Order Created order
     */
    public static function create_parent_order($line_items, $customer_id, $log_source = 'order_creation') {
        try {
            // Create order using modern HPOS approach
            $order = new \WC_Order();
            $order->set_customer_id($customer_id);
            $order->set_created_via('proposal_conversion');
            
            Woocommerce_Logs::log($log_source, 'info', 
                sprintf('Creating parent order for customer #%d', $customer_id));
            
            // Add products
            if (!empty($line_items['products'])) {
                self::add_products_to_order($order, $line_items['products'], $log_source);
            }
            
            // Add one-time fees
            if (!empty($line_items['one_time_fees'])) {
                self::add_fees_to_order($order, $line_items['one_time_fees'], 'one_time', $log_source);
            }
            
            // Add shipping fees
            if (!empty($line_items['shipping_fees'])) {
                self::add_fees_to_order($order, $line_items['shipping_fees'], 'shipping', $log_source);
            }
            
            // Calculate totals and save
            $order->calculate_totals();
            $order->set_status('pending');
            $order->save();
            
            Woocommerce_Logs::log($log_source, 'success', 
                sprintf('Created parent order #%d with total $%.2f', 
                    $order->get_id(), $order->get_total()));
            
            return $order;
            
        } catch (Exception $e) {
            Woocommerce_Logs::log($log_source, 'error', 
                sprintf('Failed to create parent order: %s', $e->getMessage()));
            throw new \Exception(sprintf('Failed to create parent order: %s', $e->getMessage()));
        }
    }
    
    /**
     * Create subscription with recurring items
     * 
     * @param array $line_items Line items from proposal
     * @param int $customer_id Customer ID
     * @param int $parent_order_id Parent order ID
     * @param string $log_source Log source
     * @return WC_Subscription Created subscription
     */
    public static function create_subscription($line_items, $customer_id, $parent_order_id = null, $log_source = 'subscription_creation') {
        if (!class_exists('WC_Subscriptions')) {
            throw new \Exception('WooCommerce Subscriptions is not active');
        }
        
        try {
            // Create subscription using modern approach
            $subscription = new \WC_Subscription();
            $subscription->set_customer_id($customer_id);
            $subscription->set_created_via('proposal_conversion');
            
            if ($parent_order_id) {
                $subscription->set_parent_id($parent_order_id);
            }
            
            // Get billing schedule from recurring fees
            $billing_schedule = self::get_billing_schedule($line_items['recurring_fees'] ?? array());
            $subscription->set_billing_period($billing_schedule['period']);
            $subscription->set_billing_interval($billing_schedule['interval']);
            
            Woocommerce_Logs::log($log_source, 'info', 
                sprintf('Creating subscription for customer #%d with billing period %s every %d %s(s)', 
                    $customer_id, $billing_schedule['period'], $billing_schedule['interval'], $billing_schedule['period']));
            
            // Add recurring products
            if (!empty($line_items['recurring_products'])) {
                self::add_products_to_order($subscription, $line_items['recurring_products'], $log_source);
            }
            
            // Add recurring fees
            if (!empty($line_items['recurring_fees'])) {
                self::add_fees_to_order($subscription, $line_items['recurring_fees'], 'recurring', $log_source);
            }
            
            // Calculate totals and save
            $subscription->calculate_totals();
            $subscription->set_status('pending');
            $subscription->save();
            
            Woocommerce_Logs::log($log_source, 'success', 
                sprintf('Created subscription #%d with total $%.2f', 
                    $subscription->get_id(), $subscription->get_total()));
            
            return $subscription;
            
        } catch (Exception $e) {
            Woocommerce_Logs::log($log_source, 'error', 
                sprintf('Failed to create subscription: %s', $e->getMessage()));
            throw new \Exception(sprintf('Failed to create subscription: %s', $e->getMessage()));
        }
    }
    
    /**
     * Validate fee data
     * 
     * @param array $fee_data Fee data array
     * @return bool True if valid
     */
    public static function validate_fee_data($fee_data) {
        if (!is_array($fee_data)) {
            return false;
        }
        
        // Required fields
        if (!isset($fee_data['amount']) || !is_numeric($fee_data['amount'])) {
            return false;
        }
        
        // Optional fields with defaults
        if (!isset($fee_data['name'])) {
            $fee_data['name'] = 'Fee';
        }
        
        return true;
    }
    
    /**
     * Get fee name from fee data
     * 
     * @param array $fee_data Fee data
     * @param string $fee_type Fee type
     * @return string Fee name
     */
    public static function get_fee_name($fee_data, $fee_type) {
        if (!empty($fee_data['name'])) {
            return sanitize_text_field($fee_data['name']);
        }
        
        if (!empty($fee_data['description'])) {
            return sanitize_text_field($fee_data['description']);
        }
        
        return ucfirst($fee_type) . ' Fee';
    }
    
    /**
     * Determine tax settings for fee
     * 
     * @param array $fee_data Fee data
     * @return array [is_taxable, tax_class]
     */
    public static function determine_tax_settings($fee_data) {
        $is_taxable = false;
        $tax_class = '';
        
        if (isset($fee_data['taxable']) && $fee_data['taxable']) {
            $is_taxable = true;
            $tax_class = isset($fee_data['tax_class']) ? $fee_data['tax_class'] : '';
        }
        
        return array($is_taxable, $tax_class);
    }
    
    /**
     * Get billing schedule from recurring fees
     * 
     * @param array $recurring_fees Recurring fees
     * @return array Billing schedule
     */
    public static function get_billing_schedule($recurring_fees) {
        // Default to monthly billing
        $schedule = array(
            'period' => 'month',
            'interval' => 1
        );
        
        // Check if any recurring fee specifies a different schedule
        foreach ($recurring_fees as $fee) {
            if (isset($fee['billing_period'])) {
                $schedule['period'] = $fee['billing_period'];
            }
            if (isset($fee['billing_interval'])) {
                $schedule['interval'] = intval($fee['billing_interval']);
            }
        }
        
        return $schedule;
    }
    
    /**
     * Check if line items have recurring items
     * 
     * @param array $line_items Line items
     * @return bool True if has recurring items
     */
    public static function has_recurring_items($line_items) {
        if (empty($line_items) || !is_array($line_items)) {
            return false;
        }
        
        return (!empty($line_items['recurring_products']) && is_array($line_items['recurring_products'])) ||
               (!empty($line_items['recurring_fees']) && is_array($line_items['recurring_fees']));
    }
    
    /**
     * Check if line items contain billable items
     * 
     * @param array $line_items Line items array
     * @return bool True if has billable items
     */
    private static function has_billable_items($line_items) {
        if (empty($line_items) || !is_array($line_items)) {
            return false;
        }
        
        // Check for products
        if (!empty($line_items['products']) && is_array($line_items['products'])) {
            return true;
        }
        
        // Check for one-time fees
        if (!empty($line_items['one_time_fees']) && is_array($line_items['one_time_fees'])) {
            return true;
        }
        
        // Check for shipping fees
        if (!empty($line_items['shipping_fees']) && is_array($line_items['shipping_fees'])) {
            return true;
        }
        
        // Check for recurring items
        if (self::has_recurring_items($line_items)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get order by proposal ID
     * 
     * @param int $proposal_id Proposal ID
     * @return WC_Order|null Order object or null if not found
     */
    public static function get_order_by_proposal($proposal_id) {
        $orders = wc_get_orders(array(
            'meta_key' => '_arsol_proposal_id',
            'meta_value' => $proposal_id,
            'limit' => 1
        ));
        
        return !empty($orders) ? $orders[0] : null;
    }
    
    /**
     * Check if proposal has been billed
     * 
     * @param int $proposal_id Proposal ID
     * @return bool True if billed
     */
    public static function is_proposal_billed($proposal_id) {
        $order = self::get_order_by_proposal($proposal_id);
        return !empty($order);
    }
}
