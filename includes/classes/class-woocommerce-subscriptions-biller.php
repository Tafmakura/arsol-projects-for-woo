<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Subscriptions Biller Class
 * 
 * Handles creation of WooCommerce subscriptions from proposals
 * Following WordPress coding standards and WooCommerce best practices
 */
class Woocommerce_Subscriptions_Biller {
    
    /**
     * Create subscription from proposal
     * 
     * @param int $proposal_id The proposal ID
     * @return array|WP_Error Result array with subscription details or error
     */
    public static function create_subscription($proposal_id) {
        $log_source = 'subscription_creation';
        
        try {
            Woocommerce_Logs::log($log_source, 'info', 
                sprintf('Starting subscription creation for proposal #%d', $proposal_id));
            
            // Check if WooCommerce Subscriptions is active
            if (!self::is_subscriptions_active()) {
                return new \WP_Error('subscriptions_not_active', 
                    __('WooCommerce Subscriptions is not active', 'arsol-pfw'));
            }
            
            // Get and validate proposal data
            $proposal_data = Woocommerce_Biller_Helper::get_proposal_data($proposal_id);
            if (is_wp_error($proposal_data)) {
                return $proposal_data;
            }
            
            $line_items = $proposal_data['line_items'];
            $customer_id = $proposal_data['customer_id'];
            
            // Check if we have recurring items
            if (!Woocommerce_Biller_Helper::has_recurring_items($line_items)) {
                return new \WP_Error('no_recurring_items', 
                    __('No recurring items found for subscription creation', 'arsol-pfw'));
            }
            
            // Create subscription with recurring items
            $subscription = Woocommerce_Biller_Helper::create_subscription(
                $line_items, 
                $customer_id, 
                null, // No parent order
                $log_source
            );
            
            // Add proposal reference to subscription
            $subscription->add_meta_data('_arsol_proposal_id', $proposal_id);
            $subscription->save();
            
            $result = array(
                'success' => true,
                'subscription_id' => $subscription->get_id(),
                'subscription_total' => $subscription->get_total(),
                'billing_period' => $subscription->get_billing_period(),
                'billing_interval' => $subscription->get_billing_interval(),
                'message' => sprintf('Subscription #%d created successfully', $subscription->get_id())
            );
            
            Woocommerce_Logs::log($log_source, 'success', 
                sprintf('Subscription creation completed for proposal #%d. Subscription #%d created with total $%.2f', 
                    $proposal_id, $subscription->get_id(), $subscription->get_total()));
            
            return $result;
            
        } catch (Exception $e) {
            $error_message = sprintf('Subscription creation failed for proposal #%d: %s', 
                $proposal_id, $e->getMessage());
            
            Woocommerce_Logs::log($log_source, 'error', $error_message);
            
            return new \WP_Error('subscription_creation_failed', $error_message);
        }
    }
    
    /**
     * Update subscription from proposal
     * 
     * @param int $subscription_id The subscription ID
     * @param int $proposal_id The proposal ID
     * @return array|WP_Error Result array with subscription details or error
     */
    public static function update_subscription($subscription_id, $proposal_id) {
        $log_source = 'subscription_update';
        
        try {
            Woocommerce_Logs::log($log_source, 'info', 
                sprintf('Starting subscription update for subscription #%d from proposal #%d', 
                    $subscription_id, $proposal_id));
            
            // Check if WooCommerce Subscriptions is active
            if (!self::is_subscriptions_active()) {
                return new \WP_Error('subscriptions_not_active', 
                    __('WooCommerce Subscriptions is not active', 'arsol-pfw'));
            }
            
            // Get subscription
            $subscription = wcs_get_subscription($subscription_id);
            if (!$subscription) {
                return new \WP_Error('invalid_subscription', 
                    __('Invalid subscription ID', 'arsol-pfw'));
            }
            
            // Get and validate proposal data
            $proposal_data = Woocommerce_Biller_Helper::get_proposal_data($proposal_id);
            if (is_wp_error($proposal_data)) {
                return $proposal_data;
            }
            
            $line_items = $proposal_data['line_items'];
            
            // Check if we have recurring items
            if (!Woocommerce_Biller_Helper::has_recurring_items($line_items)) {
                return new \WP_Error('no_recurring_items', 
                    __('No recurring items found for subscription update', 'arsol-pfw'));
            }
            
            // Remove existing fees
            self::remove_subscription_fees($subscription);
            
            // Add new recurring fees
            if (!empty($line_items['recurring_fees'])) {
                Woocommerce_Biller_Helper::add_fees_to_order(
                    $subscription, 
                    $line_items['recurring_fees'], 
                    'recurring', 
                    $log_source
                );
            }
            
            // Update billing schedule if needed
            $billing_schedule = Woocommerce_Biller_Helper::get_billing_schedule($line_items['recurring_fees']);
            $subscription->set_billing_period($billing_schedule['period']);
            $subscription->set_billing_interval($billing_schedule['interval']);
            
            // Calculate totals and save
            $subscription->calculate_totals();
            $subscription->save();
            
            $result = array(
                'success' => true,
                'subscription_id' => $subscription->get_id(),
                'subscription_total' => $subscription->get_total(),
                'billing_period' => $subscription->get_billing_period(),
                'billing_interval' => $subscription->get_billing_interval(),
                'message' => sprintf('Subscription #%d updated successfully', $subscription->get_id())
            );
            
            Woocommerce_Logs::log($log_source, 'success', 
                sprintf('Subscription update completed for subscription #%d from proposal #%d. New total $%.2f', 
                    $subscription_id, $proposal_id, $subscription->get_total()));
            
            return $result;
            
        } catch (Exception $e) {
            $error_message = sprintf('Subscription update failed for subscription #%d from proposal #%d: %s', 
                $subscription_id, $proposal_id, $e->getMessage());
            
            Woocommerce_Logs::log($log_source, 'error', $error_message);
            
            return new \WP_Error('subscription_update_failed', $error_message);
        }
    }
    
    /**
     * Get subscription by proposal ID
     * 
     * @param int $proposal_id The proposal ID
     * @return WC_Subscription|null Subscription object or null if not found
     */
    public static function get_subscription_by_proposal($proposal_id) {
        if (!self::is_subscriptions_active()) {
            return null;
        }
        
        $subscriptions = wcs_get_subscriptions(array(
            'meta_key' => '_arsol_proposal_id',
            'meta_value' => $proposal_id,
            'subscriptions_per_page' => 1
        ));
        
        return !empty($subscriptions) ? array_values($subscriptions)[0] : null;
    }
    
    /**
     * Check if proposal has a subscription
     * 
     * @param int $proposal_id The proposal ID
     * @return bool True if proposal has a subscription
     */
    public static function has_subscription($proposal_id) {
        $subscription = self::get_subscription_by_proposal($proposal_id);
        return !is_null($subscription);
    }
    
    /**
     * Cancel subscription by proposal ID
     * 
     * @param int $proposal_id The proposal ID
     * @return array|WP_Error Result array or error
     */
    public static function cancel_subscription_by_proposal($proposal_id) {
        $log_source = 'subscription_cancellation';
        
        try {
            $subscription = self::get_subscription_by_proposal($proposal_id);
            if (!$subscription) {
                return new \WP_Error('subscription_not_found', 
                    __('No subscription found for this proposal', 'arsol-pfw'));
            }
            
            $subscription->update_status('cancelled', 
                sprintf('Cancelled via proposal #%d', $proposal_id));
            
            Woocommerce_Logs::log($log_source, 'info', 
                sprintf('Subscription #%d cancelled for proposal #%d', 
                    $subscription->get_id(), $proposal_id));
            
            return array(
                'success' => true,
                'subscription_id' => $subscription->get_id(),
                'message' => sprintf('Subscription #%d cancelled successfully', $subscription->get_id())
            );
            
        } catch (Exception $e) {
            $error_message = sprintf('Subscription cancellation failed for proposal #%d: %s', 
                $proposal_id, $e->getMessage());
            
            Woocommerce_Logs::log($log_source, 'error', $error_message);
            
            return new \WP_Error('subscription_cancellation_failed', $error_message);
        }
    }
    
    /**
     * Check if WooCommerce Subscriptions is active
     * 
     * @return bool True if active
     */
    public static function is_subscriptions_active() {
        return class_exists('WC_Subscriptions') && function_exists('wcs_create_subscription');
    }
    
    /**
     * Remove existing fees from subscription
     * 
     * @param WC_Subscription $subscription The subscription object
     * @return void
     */
    private static function remove_subscription_fees($subscription) {
        $items = $subscription->get_items('fee');
        
        foreach ($items as $item_id => $item) {
            $subscription->remove_item($item_id);
        }
    }
    

}
