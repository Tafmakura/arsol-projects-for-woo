<?php
/**
 * WooCommerce Biller Helper Class - Version 2
 * 
 * Reimplemented from scratch following strict WooCommerce documentation
 * Based on official WooCommerce fee handling patterns
 * 
 * @package Arsol_Projects_For_Woo
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Woocommerce_Biller_Helper_V2 {
    
    /**
     * Add fees to WooCommerce order using official WooCommerce methods
     * 
     * @param WC_Order $order The order object
     * @param array $fee_data Fee data array
     * @param string $fee_type Type of fee (one_time, shipping, recurring)
     * @param string $log_source Log source for debugging
     * @return bool Success status
     */
    public static function add_order_fee($order, $fee_data, $fee_type = 'one_time', $log_source = 'order_creation') {
        if (!$order || !is_array($fee_data) || empty($fee_data)) {
            Woocommerce_Logs::log($log_source, 'error', 'Invalid order or fee data provided');
            return false;
        }
        
        $fees_added = 0;
        
        foreach ($fee_data as $fee_id => $fee) {
            if (!self::validate_fee_data($fee)) {
                Woocommerce_Logs::log($log_source, 'warning', 
                    sprintf('Invalid fee data for fee ID %s: %s', $fee_id, json_encode($fee)));
                continue;
            }
            
            // Prepare fee data according to WooCommerce standards
            $fee_name = self::prepare_fee_name($fee, $fee_type);
            $fee_amount = floatval($fee['amount']);
            $is_taxable = self::determine_taxable_status($fee);
            $tax_class = self::get_tax_class($fee);
            
            Woocommerce_Logs::log($log_source, 'info', 
                sprintf('Adding %s fee: %s = $%.2f (taxable: %s, tax_class: %s)', 
                    $fee_type, $fee_name, $fee_amount, $is_taxable ? 'yes' : 'no', $tax_class));
            
            try {
                // Use WooCommerce's official add_fee method with individual parameters
                $order->add_fee($fee_name, $fee_amount, $is_taxable, $tax_class);
                $fees_added++;
                
                Woocommerce_Logs::log($log_source, 'success', 
                    sprintf('Successfully added fee: %s = $%.2f', $fee_name, $fee_amount));
                
            } catch (Exception $e) {
                Woocommerce_Logs::log($log_source, 'error', 
                    sprintf('Failed to add fee %s: %s', $fee_name, $e->getMessage()));
            }
        }
        
        if ($fees_added > 0) {
            // Save the order after adding fees (HPOS compatible)
            $order->save();
            Woocommerce_Logs::log($log_source, 'info', 
                sprintf('Order #%d saved after adding %d fees', $order->get_id(), $fees_added));
        }
        
        return $fees_added > 0;
    }
    
    /**
     * Add fees to WooCommerce cart using official cart methods
     * 
     * @param WC_Cart $cart The cart object
     * @param array $fee_data Fee data array
     * @param string $fee_type Type of fee
     * @return bool Success status
     */
    public static function add_cart_fee($cart, $fee_data, $fee_type = 'one_time') {
        if (!$cart || !is_array($fee_data) || empty($fee_data)) {
            return false;
        }
        
        foreach ($fee_data as $fee_id => $fee) {
            if (!self::validate_fee_data($fee)) {
                continue;
            }
            
            $fee_name = self::prepare_fee_name($fee, $fee_type);
            $fee_amount = floatval($fee['amount']);
            $is_taxable = self::determine_taxable_status($fee);
            $tax_class = self::get_tax_class($fee);
            
            // Use WooCommerce cart's add_fee method
            $cart->add_fee($fee_name, $fee_amount, $is_taxable, $tax_class);
        }
        
        return true;
    }
    
    /**
     * Validate fee data structure
     * 
     * @param array $fee Fee data
     * @return bool Valid status
     */
    private static function validate_fee_data($fee) {
        if (!is_array($fee)) {
            return false;
        }
        
        // Required fields
        if (!isset($fee['amount']) || !is_numeric($fee['amount'])) {
            return false;
        }
        
        if (floatval($fee['amount']) <= 0) {
            return false;
        }
        
        // Name is required for one-time and recurring fees
        if (!isset($fee['name']) && !isset($fee['description'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Prepare fee name according to WooCommerce standards
     * 
     * @param array $fee Fee data
     * @param string $fee_type Fee type
     * @return string Fee name
     */
    private static function prepare_fee_name($fee, $fee_type) {
        if (isset($fee['name']) && !empty($fee['name'])) {
            $name = sanitize_text_field($fee['name']);
        } elseif (isset($fee['description']) && !empty($fee['description'])) {
            $name = sanitize_text_field($fee['description']);
        } else {
            $name = ucfirst($fee_type) . ' Fee';
        }
        
        // Add suffix for shipping fees to distinguish them
        if ($fee_type === 'shipping') {
            $name .= ' (Shipping)';
        }
        
        return $name;
    }
    
    /**
     * Determine if fee should be taxable
     * 
     * @param array $fee Fee data
     * @return bool Taxable status
     */
    private static function determine_taxable_status($fee) {
        if (!isset($fee['tax_class'])) {
            return false;
        }
        
        $tax_class = strtolower($fee['tax_class']);
        
        // WooCommerce standard: 'no-tax' means not taxable
        if ($tax_class === 'no-tax' || $tax_class === 'none' || empty($tax_class)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get tax class for fee
     * 
     * @param array $fee Fee data
     * @return string Tax class
     */
    private static function get_tax_class($fee) {
        if (!isset($fee['tax_class'])) {
            return '';
        }
        
        $tax_class = strtolower($fee['tax_class']);
        
        // Convert 'no-tax' to empty string (WooCommerce standard)
        if ($tax_class === 'no-tax' || $tax_class === 'none') {
            return '';
        }
        
        return sanitize_text_field($fee['tax_class']);
    }
    
    /**
     * Create a properly linked parent order for subscriptions
     * Following WooCommerce Subscriptions documentation
     * 
     * @param array $proposal_data Proposal data
     * @param WC_Subscription $subscription The subscription object
     * @param string $log_source Log source
     * @return WC_Order|false Parent order or false on failure
     */
    public static function create_linked_parent_order($proposal_data, $subscription, $log_source = 'subscription_creation') {
        try {
            // Create parent order using WooCommerce function
            $parent_order = wc_create_order();
            
            if (is_wp_error($parent_order)) {
                Woocommerce_Logs::log($log_source, 'error', 
                    'Failed to create parent order: ' . $parent_order->get_error_message());
                return false;
            }
            
            Woocommerce_Logs::log($log_source, 'info', 
                sprintf('Created parent order #%d for subscription #%d', 
                    $parent_order->get_id(), $subscription->get_id()));
            
            // Add customer information
            if (isset($proposal_data['customer_id'])) {
                $parent_order->set_customer_id($proposal_data['customer_id']);
            }
            
            // Add products to parent order
            if (isset($proposal_data['products']) && is_array($proposal_data['products'])) {
                foreach ($proposal_data['products'] as $product_data) {
                    if (isset($product_data['product_id'])) {
                        $product = wc_get_product($product_data['product_id']);
                        if ($product) {
                            $parent_order->add_product(
                                $product,
                                isset($product_data['quantity']) ? intval($product_data['quantity']) : 1,
                                array(
                                    'subtotal' => isset($product_data['price']) ? floatval($product_data['price']) : $product->get_price(),
                                    'total' => isset($product_data['sale_price']) ? floatval($product_data['sale_price']) : $product->get_price()
                                )
                            );
                        }
                    }
                }
            }
            
            // Save after adding products
            $parent_order->save();
            
            // Add one-time fees to parent order
            if (isset($proposal_data['one_time_fees'])) {
                self::add_order_fee($parent_order, $proposal_data['one_time_fees'], 'one_time', $log_source);
            }
            
            // Add shipping fees to parent order
            if (isset($proposal_data['shipping_fees'])) {
                self::add_order_fee($parent_order, $proposal_data['shipping_fees'], 'shipping', $log_source);
            }
            
            // Link parent order to subscription
            $subscription->set_parent_id($parent_order->get_id());
            $subscription->save();
            
            // Final save of parent order
            $parent_order->save();
            
            Woocommerce_Logs::log($log_source, 'success', 
                sprintf('Successfully created and linked parent order #%d to subscription #%d', 
                    $parent_order->get_id(), $subscription->get_id()));
            
            return $parent_order;
            
        } catch (Exception $e) {
            Woocommerce_Logs::log($log_source, 'error', 
                'Exception creating parent order: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get and validate proposal data
     * 
     * @param int $proposal_id Proposal ID
     * @return array|false Proposal data or false on failure
     */
    public static function get_proposal_data($proposal_id) {
        if (empty($proposal_id)) {
            return false;
        }
        
        $proposal_data = get_post_meta($proposal_id, 'line_items', true);
        
        if (empty($proposal_data) || !is_array($proposal_data)) {
            return false;
        }
        
        return $proposal_data;
    }
    
    /**
     * Validate billing interval and period
     * 
     * @param string $interval Billing interval
     * @param string $period Billing period
     * @return bool Valid status
     */
    public static function validate_billing_schedule($interval, $period) {
        $valid_intervals = array('1', '2', '3', '4', '5', '6');
        $valid_periods = array('day', 'week', 'month', 'year');
        
        return in_array($interval, $valid_intervals) && in_array($period, $valid_periods);
    }
} 