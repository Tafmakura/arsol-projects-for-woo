<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Biller Helper Class
 * 
 * Consolidates common biller methods for consistency between order and subscription creation
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
        
        $line_items = get_post_meta($proposal_id, '_arsol_proposal_line_items', true);
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
     * Add one-time fees to order
     * 
     * @param WC_Order $order The order object
     * @param array $one_time_fees Array of one-time fees
     * @param string $log_source Log source for debugging
     * @return int Number of fees added
     */
    public static function add_one_time_fees($order, $one_time_fees, $log_source = 'order_creation') {
        $fees_added = 0;
        
        if (empty($one_time_fees) || !is_array($one_time_fees)) {
            return $fees_added;
        }
        
        Woocommerce_Logs::log($log_source, 'info', 
            sprintf('Processing %d one-time fees for order #%d', 
                count($one_time_fees), $order->get_id()));
        
        foreach ($one_time_fees as $fee) {
            if (!empty($fee['amount']) && floatval($fee['amount']) > 0) {
                $fee_name = !empty($fee['name']) 
                    ? $fee['name'] 
                    : __('Additional Fee', 'arsol-pfw');
                
                $fee_amount = floatval($fee['amount']);
                $tax_class = !empty($fee['tax_class']) ? $fee['tax_class'] : '';
                $is_taxable = !empty($fee['tax_class']) && $fee['tax_class'] !== 'no-tax';
                
                Woocommerce_Logs::log($log_source, 'debug', 
                    sprintf('Adding fee to order #%d: Name="%s", Amount=%s, Tax Class=%s, Taxable=%s', 
                        $order->get_id(), $fee_name, $fee_amount, $tax_class, $is_taxable ? 'yes' : 'no'));
                
                $order->add_fee(array(
                    'name' => $fee_name,
                    'amount' => $fee_amount,
                    'taxable' => $is_taxable,
                    'tax_class' => $tax_class
                ));
                
                $fees_added++;
                
                Woocommerce_Logs::log($log_source, 'info', 
                    sprintf('Added fee "%s" (%s) to order #%d', 
                        $fee_name, wc_price($fee_amount), $order->get_id()));
            } else {
                Woocommerce_Logs::log($log_source, 'warning', 
                    sprintf('Skipped fee with invalid amount: %s', 
                        wp_json_encode($fee)));
            }
        }
        
        // Save order after adding fees (HPOS compatibility)
        if ($fees_added > 0) {
            $save_result = $order->save();
            if ($save_result) {
                Woocommerce_Logs::log($log_source, 'debug', 
                    sprintf('Successfully saved order #%d after adding %d fees', 
                        $order->get_id(), $fees_added));
            } else {
                Woocommerce_Logs::log($log_source, 'warning', 
                    sprintf('Failed to save order #%d after adding fees', 
                        $order->get_id()));
            }
        }
        
        return $fees_added;
    }
    
    /**
     * Add shipping fees to order
     * 
     * @param WC_Order $order The order object
     * @param array $shipping_fees Array of shipping fees
     * @param string $log_source Log source for debugging
     * @return int Number of shipping fees added
     */
    public static function add_shipping_fees($order, $shipping_fees, $log_source = 'order_creation') {
        $shipping_added = 0;
        
        if (empty($shipping_fees) || !is_array($shipping_fees)) {
            return $shipping_added;
        }
        
        Woocommerce_Logs::log($log_source, 'info', 
            sprintf('Processing %d shipping fees for order #%d', 
                count($shipping_fees), $order->get_id()));
        
        foreach ($shipping_fees as $shipping) {
            if (!empty($shipping['amount']) && floatval($shipping['amount']) > 0) {
                $shipping_title = !empty($shipping['description']) 
                    ? $shipping['description'] 
                    : __('Shipping Fee', 'arsol-pfw');
                
                $shipping_amount = floatval($shipping['amount']);
                
                // Add shipping as fee instead of shipping item to avoid HPOS compatibility issues
                $order->add_fee(array(
                    'name' => $shipping_title . ' (Shipping)',
                    'amount' => $shipping_amount,
                    'taxable' => false,
                    'tax_class' => ''
                ));
                
                $shipping_added++;
                
                Woocommerce_Logs::log($log_source, 'info', 
                    sprintf('Added shipping "%s" (%s) as fee to order #%d', 
                        $shipping_title, wc_price($shipping_amount), $order->get_id()));
            } else {
                Woocommerce_Logs::log($log_source, 'warning', 
                    sprintf('Skipped shipping fee with invalid amount: %s', 
                        wp_json_encode($shipping)));
            }
        }
        
        // Save order after adding shipping (HPOS compatibility)
        if ($shipping_added > 0) {
            $save_result = $order->save();
            if ($save_result) {
                Woocommerce_Logs::log($log_source, 'debug', 
                    sprintf('Successfully saved order #%d after adding %d shipping fees', 
                        $order->get_id(), $shipping_added));
            } else {
                Woocommerce_Logs::log($log_source, 'warning', 
                    sprintf('Failed to save order #%d after adding shipping fees', 
                        $order->get_id()));
            }
        }
        
        return $shipping_added;
    }
    
    /**
     * Add recurring fees to subscription
     * 
     * @param WC_Subscription $subscription The subscription object
     * @param array $recurring_fees Array of recurring fees
     * @param string $log_source Log source for debugging
     * @return int Number of recurring fees added
     */
    public static function add_recurring_fees($subscription, $recurring_fees, $log_source = 'subscription_creation') {
        $fees_added = 0;
        
        if (empty($recurring_fees) || !is_array($recurring_fees)) {
            return $fees_added;
        }
        
        Woocommerce_Logs::log($log_source, 'info', 
            sprintf('Processing %d recurring fees for subscription #%d', 
                count($recurring_fees), $subscription->get_id()));
        
        foreach ($recurring_fees as $fee) {
            if (!empty($fee['amount']) && floatval($fee['amount']) > 0) {
                $fee_name = !empty($fee['name']) 
                    ? $fee['name'] 
                    : __('Recurring Fee', 'arsol-pfw');
                
                $fee_amount = floatval($fee['amount']);
                $tax_class = !empty($fee['tax_class']) ? $fee['tax_class'] : '';
                $is_taxable = !empty($fee['tax_class']) && $fee['tax_class'] !== 'no-tax';
                
                $subscription->add_fee(array(
                    'name' => $fee_name,
                    'amount' => $fee_amount,
                    'taxable' => $is_taxable,
                    'tax_class' => $tax_class
                ));
                
                $fees_added++;
                
                Woocommerce_Logs::log($log_source, 'info', 
                    sprintf('Added recurring fee "%s" (%s) to subscription #%d', 
                        $fee_name, wc_price($fee_amount), $subscription->get_id()));
            } else {
                Woocommerce_Logs::log($log_source, 'warning', 
                    sprintf('Skipped recurring fee with invalid amount: %s', 
                        wp_json_encode($fee)));
            }
        }
        
        // Save subscription after adding fees (HPOS compatibility)
        if ($fees_added > 0) {
            $save_result = $subscription->save();
            if ($save_result) {
                Woocommerce_Logs::log($log_source, 'debug', 
                    sprintf('Successfully saved subscription #%d after adding %d recurring fees', 
                        $subscription->get_id(), $fees_added));
            } else {
                Woocommerce_Logs::log($log_source, 'warning', 
                    sprintf('Failed to save subscription #%d after adding recurring fees', 
                        $subscription->get_id()));
            }
        }
        
        return $fees_added;
    }
    
    /**
     * Create and properly link parent order for subscription
     * 
     * @param WC_Subscription $subscription The subscription object
     * @param array $line_items The original proposal line items
     * @param int $proposal_id The proposal ID
     * @return int|WP_Error Parent order ID on success, WP_Error on failure
     */
    public static function create_linked_parent_order($subscription, $line_items, $proposal_id) {
        try {
            // Check if subscription already has a parent order
            $existing_parent_id = $subscription->get_parent_id();
            if ($existing_parent_id && $existing_parent_id > 0) {
                Woocommerce_Logs::log_subscription_creation('info', 
                    sprintf('Subscription #%d already has parent order #%d', 
                        $subscription->get_id(), $existing_parent_id));
                return $existing_parent_id;
            }
            
            // Create parent order manually with all items
            $parent_order = wc_create_order(array(
                'customer_id' => $subscription->get_customer_id(),
                'status' => 'pending'
            ));
            
            if (is_wp_error($parent_order)) {
                throw new \Exception($parent_order->get_error_message());
            }
            
            Woocommerce_Logs::log_subscription_creation('info', 
                sprintf('Created parent order #%d for subscription #%d', 
                    $parent_order->get_id(), $subscription->get_id()));
            
            // Copy billing and shipping addresses from subscription
            $parent_order->set_address($subscription->get_address('billing'), 'billing');
            $parent_order->set_address($subscription->get_address('shipping'), 'shipping');
            
            // Add subscription products to parent order
            foreach ($subscription->get_items() as $item) {
                $parent_order->add_item($item);
            }
            
            // Add subscription fees to parent order
            foreach ($subscription->get_fees() as $fee) {
                $parent_order->add_item($fee);
            }
            
            // Add subscription shipping to parent order
            foreach ($subscription->get_shipping_methods() as $shipping) {
                $parent_order->add_item($shipping);
            }
            
            // Save parent order after adding subscription items (HPOS compatibility)
            $save_result = $parent_order->save();
            if ($save_result) {
                Woocommerce_Logs::log_subscription_creation('debug', 
                    sprintf('Successfully saved parent order #%d after adding subscription items', 
                        $parent_order->get_id()));
            } else {
                Woocommerce_Logs::log_subscription_creation('warning', 
                    sprintf('Failed to save parent order #%d after adding subscription items', 
                        $parent_order->get_id()));
            }
            
            // Debug: Log line items structure for fee processing
            Woocommerce_Logs::log_subscription_creation('debug', 
                sprintf('Line items for parent order #%d: %s', 
                    $parent_order->get_id(), wp_json_encode($line_items)));
            
            // Debug: Check specific fee arrays
            $one_time_fees_count = !empty($line_items['one_time_fees']) ? count($line_items['one_time_fees']) : 0;
            $shipping_fees_count = !empty($line_items['shipping_fees']) ? count($line_items['shipping_fees']) : 0;
            
            Woocommerce_Logs::log_subscription_creation('debug', 
                sprintf('Fee counts for parent order #%d - One-time: %d, Shipping: %d', 
                    $parent_order->get_id(), $one_time_fees_count, $shipping_fees_count));
            
            // Add one-time fees from proposal to parent order
            if (!empty($line_items['one_time_fees'])) {
                Woocommerce_Logs::log_subscription_creation('debug', 
                    sprintf('Processing one-time fees for parent order #%d', $parent_order->get_id()));
                self::add_one_time_fees($parent_order, $line_items['one_time_fees'], 'subscription_creation');
            } else {
                Woocommerce_Logs::log_subscription_creation('debug', 
                    sprintf('No one-time fees found for parent order #%d', $parent_order->get_id()));
            }
            
            // Add shipping fees from proposal to parent order
            if (!empty($line_items['shipping_fees'])) {
                Woocommerce_Logs::log_subscription_creation('debug', 
                    sprintf('Processing shipping fees for parent order #%d', $parent_order->get_id()));
                self::add_shipping_fees($parent_order, $line_items['shipping_fees'], 'subscription_creation');
            } else {
                Woocommerce_Logs::log_subscription_creation('debug', 
                    sprintf('No shipping fees found for parent order #%d', $parent_order->get_id()));
            }
            
            // Calculate totals
            $parent_order->calculate_totals();
            
            // Link the parent order to the subscription
            $subscription->set_parent_id($parent_order->get_id());
            $subscription->save();
            
            // Add order notes
            $parent_order->add_order_note(
                sprintf(__('Parent order for subscription #%d created from proposal #%d', 'arsol-pfw'), 
                    $subscription->get_id(), $proposal_id)
            );
            
            $subscription->add_order_note(
                sprintf(__('Parent order #%d created', 'arsol-pfw'), $parent_order->get_id())
            );
            
            // Link to proposal
            $parent_order->update_meta_data('_arsol_proposal_id', $proposal_id);
            $parent_order->save();
            
            Woocommerce_Logs::log_subscription_creation('info', 
                sprintf('Created and linked parent order #%d for subscription #%d with all fees', 
                    $parent_order->get_id(), $subscription->get_id()));
            
            // Log order breakdown
            Woocommerce_Logs::log_subscription_creation('debug', 
                sprintf('Parent order #%d breakdown - Subtotal: %s, Total: %s, Fee Total: %s, Shipping Total: %s', 
                    $parent_order->get_id(), 
                    wc_price($parent_order->get_subtotal()), 
                    wc_price($parent_order->get_total()),
                    wc_price($parent_order->get_total_fees()),
                    wc_price($parent_order->get_shipping_total())));
            
            return $parent_order->get_id();
            
        } catch (\Exception $e) {
            return new \WP_Error('parent_order_creation_failed', $e->getMessage());
        }
    }
    
    /**
     * Check if line items contain recurring items
     * 
     * @param array $line_items The line items array
     * @return bool True if has recurring items, false otherwise
     */
    public static function has_recurring_items($line_items) {
        if (empty($line_items) || !is_array($line_items)) {
            return false;
        }
        
        // Check for subscription products
        if (!empty($line_items['products'])) {
            foreach ($line_items['products'] as $item) {
                if (!empty($item['product_id'])) {
                    $product = wc_get_product($item['product_id']);
                    if ($product && $product->is_type(array('subscription', 'subscription_variation'))) {
                        return true;
                    }
                }
            }
        }
        
        // Check for recurring fees
        if (!empty($line_items['recurring_fees'])) {
            foreach ($line_items['recurring_fees'] as $fee) {
                if (!empty($fee['amount']) && floatval($fee['amount']) > 0) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Log order breakdown for debugging
     * 
     * @param WC_Order $order The order object
     * @param string $log_source Log source
     * @param int $items_added Number of items added
     */
    public static function log_order_breakdown($order, $log_source, $items_added = 0) {
        Woocommerce_Logs::log($log_source, 'debug', 
            sprintf('Order #%d breakdown - Subtotal: %s, Total: %s, Fee Total: %s, Shipping Total: %s', 
                $order->get_id(), 
                wc_price($order->get_subtotal()), 
                wc_price($order->get_total()),
                wc_price($order->get_total_fees()),
                wc_price($order->get_shipping_total())));
        
        Woocommerce_Logs::log($log_source, 'info', 
            sprintf('Order #%d total calculated: %s (%d items)', 
                $order->get_id(), wc_price($order->get_total()), $items_added));
    }
    
    /**
     * Validate billing interval
     * 
     * @param mixed $interval The billing interval to validate
     * @return int Valid billing interval (1-6)
     */
    public static function validate_billing_interval($interval) {
        $interval = intval($interval);
        return ($interval >= 1 && $interval <= 6) ? $interval : 1;
    }
    
    /**
     * Validate billing period
     * 
     * @param string $period The billing period to validate
     * @return string Valid billing period (day, week, month, or year)
     */
    public static function validate_billing_period($period) {
        $valid_periods = array('day', 'week', 'month', 'year');
        return in_array($period, $valid_periods) ? $period : 'month';
    }
}
