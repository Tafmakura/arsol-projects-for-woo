<?php
/**
 * WooCommerce Subscriptions Biller Class - Version 2
 * 
 * Reimplemented from scratch following strict WooCommerce Subscriptions documentation
 * Handles subscription creation with proper fee handling and parent order linking
 * 
 * @package Arsol_Projects_For_Woo
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Woocommerce_Subscriptions_Biller_V2 {
    
    private $log_source = 'subscription_creation';
    
    /**
     * Create WooCommerce subscription from proposal
     * Following official WooCommerce Subscriptions patterns
     * 
     * @param int $proposal_id Proposal ID
     * @return array Result array with success status and subscription ID
     */
    public function create_subscription_from_proposal($proposal_id) {
        Woocommerce_Logs::log($this->log_source, 'info', 
            sprintf('Starting subscription creation from proposal #%d', $proposal_id));
        
        // Check if WooCommerce Subscriptions is active
        if (!class_exists('WC_Subscriptions')) {
            Woocommerce_Logs::log($this->log_source, 'error', 'WooCommerce Subscriptions not active');
            return array('success' => false, 'message' => 'WooCommerce Subscriptions not active');
        }
        
        // Get proposal data
        $proposal_data = Woocommerce_Biller_Helper_V2::get_proposal_data($proposal_id);
        if (!$proposal_data) {
            Woocommerce_Logs::log($this->log_source, 'error', 
                sprintf('Failed to get proposal data for proposal #%d', $proposal_id));
            return array('success' => false, 'message' => 'Invalid proposal data');
        }
        
        Woocommerce_Logs::log($this->log_source, 'info', 
            'Proposal data retrieved: ' . json_encode($proposal_data));
        
        try {
            // Create subscription using WooCommerce Subscriptions function
            $subscription = wcs_create_subscription();
            
            if (is_wp_error($subscription)) {
                Woocommerce_Logs::log($this->log_source, 'error', 
                    'Failed to create subscription: ' . $subscription->get_error_message());
                return array('success' => false, 'message' => 'Failed to create subscription');
            }
            
            Woocommerce_Logs::log($this->log_source, 'info', 
                sprintf('Created subscription #%d', $subscription->get_id()));
            
            // Add customer information if available
            if (isset($proposal_data['customer_id'])) {
                $subscription->set_customer_id($proposal_data['customer_id']);
                Woocommerce_Logs::log($this->log_source, 'info', 
                    sprintf('Set customer ID: %d', $proposal_data['customer_id']));
            }
            
            // Add products to subscription
            $this->add_products_to_subscription($subscription, $proposal_data);
            
            // Set billing schedule
            $this->set_billing_schedule($subscription, $proposal_data);
            
            // Add recurring fees to subscription
            $this->add_recurring_fees_to_subscription($subscription, $proposal_data);
            
            // Save subscription
            $subscription->save();
            Woocommerce_Logs::log($this->log_source, 'info', 
                sprintf('Subscription #%d saved', $subscription->get_id()));
            
            // Create and link parent order for one-time fees
            $parent_order = $this->create_parent_order($proposal_data, $subscription);
            
            // Set subscription status
            $subscription->set_status('active');
            $subscription->save();
            
            // Link proposal to subscription
            $subscription->update_meta_data('_proposal_id', $proposal_id);
            $subscription->save_meta_data();
            
            Woocommerce_Logs::log($this->log_source, 'success', 
                sprintf('Successfully created subscription #%d from proposal #%d. Recurring total: $%.2f', 
                    $subscription->get_id(), $proposal_id, $subscription->get_total()));
            
            return array(
                'success' => true, 
                'subscription_id' => $subscription->get_id(),
                'parent_order_id' => $parent_order ? $parent_order->get_id() : null,
                'subscription_total' => $subscription->get_total()
            );
            
        } catch (Exception $e) {
            Woocommerce_Logs::log($this->log_source, 'error', 
                'Exception during subscription creation: ' . $e->getMessage());
            return array('success' => false, 'message' => $e->getMessage());
        }
    }
    
    /**
     * Add products to subscription
     * 
     * @param WC_Subscription $subscription Subscription object
     * @param array $proposal_data Proposal data
     */
    private function add_products_to_subscription($subscription, $proposal_data) {
        if (!isset($proposal_data['products']) || !is_array($proposal_data['products'])) {
            Woocommerce_Logs::log($this->log_source, 'warning', 'No products found in proposal data');
            return;
        }
        
        foreach ($proposal_data['products'] as $product_id => $product_data) {
            if (!isset($product_data['product_id'])) {
                continue;
            }
            
            $product = wc_get_product($product_data['product_id']);
            if (!$product) {
                Woocommerce_Logs::log($this->log_source, 'warning', 
                    sprintf('Product #%d not found', $product_data['product_id']));
                continue;
            }
            
            $quantity = isset($product_data['quantity']) ? intval($product_data['quantity']) : 1;
            $price = isset($product_data['price']) ? floatval($product_data['price']) : $product->get_price();
            $sale_price = isset($product_data['sale_price']) ? floatval($product_data['sale_price']) : $price;
            
            $subscription->add_product($product, $quantity, array(
                'subtotal' => $price * $quantity,
                'total' => $sale_price * $quantity
            ));
            
            Woocommerce_Logs::log($this->log_source, 'info', 
                sprintf('Added product #%d (%s) x%d to subscription', 
                    $product_data['product_id'], $product->get_name(), $quantity));
        }
    }
    
    /**
     * Set billing schedule for subscription
     * 
     * @param WC_Subscription $subscription Subscription object
     * @param array $proposal_data Proposal data
     */
    private function set_billing_schedule($subscription, $proposal_data) {
        // Default billing schedule
        $interval = '1';
        $period = 'month';
        
        // Check if recurring fees have billing schedule
        if (isset($proposal_data['recurring_fees']) && is_array($proposal_data['recurring_fees'])) {
            foreach ($proposal_data['recurring_fees'] as $fee) {
                if (isset($fee['interval']) && isset($fee['period'])) {
                    if (Woocommerce_Biller_Helper_V2::validate_billing_schedule($fee['interval'], $fee['period'])) {
                        $interval = $fee['interval'];
                        $period = $fee['period'];
                        break; // Use first valid schedule found
                    }
                }
            }
        }
        
        // Set billing schedule
        $subscription->set_billing_interval($interval);
        $subscription->set_billing_period($period);
        
        Woocommerce_Logs::log($this->log_source, 'info', 
            sprintf('Set billing schedule: every %s %s(s)', $interval, $period));
    }
    
    /**
     * Add recurring fees to subscription
     * 
     * @param WC_Subscription $subscription Subscription object
     * @param array $proposal_data Proposal data
     */
    private function add_recurring_fees_to_subscription($subscription, $proposal_data) {
        if (!isset($proposal_data['recurring_fees']) || !is_array($proposal_data['recurring_fees'])) {
            Woocommerce_Logs::log($this->log_source, 'info', 'No recurring fees found in proposal data');
            return;
        }
        
        Woocommerce_Logs::log($this->log_source, 'info', 
            sprintf('Processing %d recurring fees', count($proposal_data['recurring_fees'])));
        
        Woocommerce_Biller_Helper_V2::add_order_fee($subscription, $proposal_data['recurring_fees'], 'recurring', $this->log_source);
    }
    
    /**
     * Create parent order for one-time fees
     * 
     * @param array $proposal_data Proposal data
     * @param WC_Subscription $subscription Subscription object
     * @return WC_Order|false Parent order or false on failure
     */
    private function create_parent_order($proposal_data, $subscription) {
        // Check if we have one-time or shipping fees
        $has_one_time_fees = isset($proposal_data['one_time_fees']) && !empty($proposal_data['one_time_fees']);
        $has_shipping_fees = isset($proposal_data['shipping_fees']) && !empty($proposal_data['shipping_fees']);
        
        if (!$has_one_time_fees && !$has_shipping_fees) {
            Woocommerce_Logs::log($this->log_source, 'info', 'No one-time or shipping fees found, skipping parent order creation');
            return false;
        }
        
        Woocommerce_Logs::log($this->log_source, 'info', 'Creating parent order for one-time and shipping fees');
        
        return Woocommerce_Biller_Helper_V2::create_linked_parent_order($proposal_data, $subscription, $this->log_source);
    }
} 