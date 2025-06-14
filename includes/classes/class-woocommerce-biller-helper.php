<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Biller Helper Class
 * 
 * Helper methods for WooCommerce order and subscription creation
 * Following WordPress coding standards and WooCommerce best practices
 */
class Woocommerce_Biller_Helper {
    
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
            // Rollback on failure
            if (isset($order) && $order->get_id()) {
                wp_delete_post($order->get_id(), true);
                Woocommerce_Logs::log($log_source, 'info', 
                    sprintf('Rolled back order #%d due to error', $order->get_id()));
            }
            throw new \Exception('Failed to create parent order: ' . $e->getMessage());
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
        try {
            // Check if WooCommerce Subscriptions is active
            if (!class_exists('WC_Subscriptions') || !function_exists('wcs_create_subscription')) {
                throw new \Exception('WooCommerce Subscriptions is not active');
            }
            
            // Get billing schedule from recurring fees
            $billing_schedule = self::get_billing_schedule($line_items['recurring_fees']);
            
            Woocommerce_Logs::log($log_source, 'info', 
                sprintf('Creating subscription for customer #%d with billing: every %d %s(s)', 
                    $customer_id, $billing_schedule['interval'], $billing_schedule['period']));
            
            $subscription = wcs_create_subscription(array(
                'order_id' => $parent_order_id,
                'customer_id' => $customer_id,
                'billing_period' => $billing_schedule['period'],
                'billing_interval' => $billing_schedule['interval'],
                'status' => 'pending'
            ));
            
            if (is_wp_error($subscription)) {
                throw new \Exception($subscription->get_error_message());
            }
            
            // Add recurring fees
            if (!empty($line_items['recurring_fees'])) {
                self::add_fees_to_order($subscription, $line_items['recurring_fees'], 'recurring', $log_source);
            }
            
            // Calculate totals and save
            $subscription->calculate_totals();
            $subscription->set_status('active');
            $subscription->save();
            
            Woocommerce_Logs::log($log_source, 'success', 
                sprintf('Created subscription #%d with recurring total $%.2f', 
                    $subscription->get_id(), $subscription->get_total()));
            
            return $subscription;
            
        } catch (Exception $e) {
            // Rollback on failure
            if (isset($subscription) && $subscription->get_id()) {
                wp_delete_post($subscription->get_id(), true);
                Woocommerce_Logs::log($log_source, 'info', 
                    sprintf('Rolled back subscription #%d due to error', $subscription->get_id()));
            }
            throw new \Exception('Failed to create subscription: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate fee data
     * 
     * @param array $fee_data Fee data
     * @return bool Valid status
     */
    public static function validate_fee_data($fee_data) {
        if (!is_array($fee_data)) {
            return false;
        }
        
        // Amount is required and must be numeric and positive
        if (!isset($fee_data['amount']) || !is_numeric($fee_data['amount']) || floatval($fee_data['amount']) <= 0) {
            return false;
        }
        
        // Name or description is required
        if (empty($fee_data['name']) && empty($fee_data['description'])) {
            return false;
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
            $name = sanitize_text_field($fee_data['name']);
        } elseif (!empty($fee_data['description'])) {
            $name = sanitize_text_field($fee_data['description']);
        } else {
            $name = ucfirst($fee_type) . ' Fee';
        }
        
        // Add suffix for shipping fees
        if ($fee_type === 'shipping') {
            $name .= ' (Shipping)';
        }
        
        return $name;
    }
    
    /**
     * Determine tax settings from fee data
     * 
     * @param array $fee_data Fee data
     * @return array [is_taxable, tax_class]
     */
    public static function determine_tax_settings($fee_data) {
        $tax_class = isset($fee_data['tax_class']) ? strtolower($fee_data['tax_class']) : '';
        
        // No tax if tax_class is empty, 'no-tax', or 'none'
        if (empty($tax_class) || $tax_class === 'no-tax' || $tax_class === 'none') {
            return array(false, '');
        }
        
        // Convert 'standard' to empty string (WooCommerce standard)
        if ($tax_class === 'standard') {
            $tax_class = '';
        }
        
        return array(true, $tax_class);
    }
    
    /**
     * Get billing schedule from recurring fees
     * 
     * @param array $recurring_fees Recurring fees
     * @return array Billing schedule
     */
    public static function get_billing_schedule($recurring_fees) {
        // Default schedule
        $schedule = array(
            'interval' => 1,
            'period' => 'month'
        );
        
        if (!empty($recurring_fees) && is_array($recurring_fees)) {
            foreach ($recurring_fees as $fee) {
                if (!empty($fee['interval']) && !empty($fee['period'])) {
                    $interval = intval($fee['interval']);
                    $period = sanitize_text_field($fee['period']);
                    
                    // Validate interval and period
                    if (self::validate_billing_interval($interval) && self::validate_billing_period($period)) {
                        $schedule['interval'] = $interval;
                        $schedule['period'] = $period;
                        break; // Use first valid schedule found
                    }
                }
            }
        }
        
        return $schedule;
    }
    
    /**
     * Check if line items contain recurring items
     * 
     * @param array $line_items Line items array
     * @return bool True if has recurring items
     */
    public static function has_recurring_items($line_items) {
        if (empty($line_items) || !is_array($line_items)) {
            return false;
        }
        
        return !empty($line_items['recurring_fees']);
    }
    
    /**
     * Validate billing interval
     * 
     * @param int $interval Billing interval
     * @return bool Valid interval
     */
    public static function validate_billing_interval($interval) {
        $valid_intervals = array(1, 2, 3, 4, 5, 6);
        return in_array(intval($interval), $valid_intervals);
    }
    
    /**
     * Validate billing period
     * 
     * @param string $period Billing period
     * @return bool Valid period
     */
    public static function validate_billing_period($period) {
        $valid_periods = array('day', 'week', 'month', 'year');
        return in_array($period, $valid_periods);
    }
}
