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
            $proposal_data = Woocommerce_Biller_Helper::get_proposal_data($proposal_id);
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
            $order = Woocommerce_Biller_Helper::create_parent_order(
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
            $proposal_data = Woocommerce_Biller_Helper::get_proposal_data($proposal_id);
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
            if (!Woocommerce_Biller_Helper::has_recurring_items($line_items)) {
                return new \WP_Error('no_recurring_items', 
                    __('No recurring items found for subscription creation', 'arsol-pfw'));
            }
            
            $order = null;
            $subscription = null;
            
            try {
                // Create parent order with one-time items
                $order = Woocommerce_Biller_Helper::create_parent_order(
                    $line_items, 
                    $customer_id, 
                    $log_source
                );
                
                // Create subscription with recurring items
                $subscription = Woocommerce_Biller_Helper::create_subscription(
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
        
        // Check for recurring fees
        if (!empty($line_items['recurring_fees']) && is_array($line_items['recurring_fees'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get order by proposal ID
     * 
     * @param int $proposal_id The proposal ID
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
     * @param int $proposal_id The proposal ID
     * @return bool True if proposal has been billed
     */
    public static function is_proposal_billed($proposal_id) {
        $order = self::get_order_by_proposal($proposal_id);
        return !is_null($order);
    }
}
