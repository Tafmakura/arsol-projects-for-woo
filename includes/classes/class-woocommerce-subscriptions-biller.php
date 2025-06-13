<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Subscriptions Biller Class
 * 
 * Handles subscription creation functionality for proposals and projects
 */
class Woocommerce_Subscriptions_Biller {
    
    public function __construct() {
        // Only initialize if WooCommerce Subscriptions is active
        if (class_exists('WC_Subscriptions')) {
            // Hook into project creation from proposal
            add_action('arsol_proposal_converted_to_project', array($this, 'handle_subscription_creation'), 10, 2);
        }
    }
    
    /**
     * Handle subscription creation when project is created from proposal
     * 
     * @param int $project_id The new project ID
     * @param int $proposal_id The original proposal ID
     */
    public function handle_subscription_creation($project_id, $proposal_id) {
        // Check if this proposal should create orders
        $cost_proposal_type = get_post_meta($proposal_id, '_cost_proposal_type', true);
        
        if ($cost_proposal_type === 'invoice_line_items') {
            // Create subscription from recurring items
            $subscription_result = $this->create_subscription_from_proposal($proposal_id);
            
            if (!is_wp_error($subscription_result)) {
                // Link the subscription to the new project
                $subscription = wc_get_order($subscription_result);
                if ($subscription) {
                    $subscription->update_meta_data(Woocommerce::PROJECT_META_KEY, $project_id);
                    $subscription->save();
                    
                    // Add success note to project
                    $success_note = sprintf(
                        __('WooCommerce subscription #%d created from proposal recurring items.', 'arsol-pfw'),
                        $subscription->get_order_number()
                    );
                    update_post_meta($project_id, '_project_subscription_creation_note', $success_note);
                }
            } else {
                // Only log error if there were actually recurring items to process
                $line_items = get_post_meta($proposal_id, '_arsol_proposal_line_items', true);
                $has_recurring = $this->has_recurring_items($line_items);
                
                if ($has_recurring) {
                    error_log('Arsol Projects: Failed to create subscription from proposal ' . $proposal_id . ': ' . $subscription_result->get_error_message());
                    
                    // Add error note to project
                    $error_note = sprintf(
                        __('Failed to create WooCommerce subscription from proposal: %s', 'arsol-pfw'),
                        $subscription_result->get_error_message()
                    );
                    update_post_meta($project_id, '_project_subscription_creation_error', $error_note);
                }
            }
        }
    }
    
    /**
     * Create subscription order from proposal recurring items
     * 
     * @param int $proposal_id The proposal ID
     * @return int|WP_Error Subscription ID on success, WP_Error on failure
     */
    public function create_subscription_from_proposal($proposal_id) {
        // Check if WooCommerce Subscriptions is active
        if (!class_exists('WC_Subscriptions')) {
            return new \WP_Error('subscriptions_not_active', __('WooCommerce Subscriptions is not active', 'arsol-pfw'));
        }
        
        // Get proposal data
        $proposal = get_post($proposal_id);
        if (!$proposal) {
            return new \WP_Error('invalid_proposal', __('Invalid proposal ID', 'arsol-pfw'));
        }
        
        // Get line items
        $line_items = get_post_meta($proposal_id, '_arsol_proposal_line_items', true);
        if (empty($line_items)) {
            return new \WP_Error('no_line_items', __('No line items found in proposal', 'arsol-pfw'));
        }
        
        // Check if there are any recurring items
        if (!$this->has_recurring_items($line_items)) {
            return new \WP_Error('no_recurring_items', __('No recurring items found in proposal', 'arsol-pfw'));
        }
        
        try {
            // Create subscription order
            $subscription = wcs_create_subscription(array(
                'customer_id' => $proposal->post_author,
                'status' => 'pending'
            ));
            
            if (is_wp_error($subscription)) {
                return $subscription;
            }
            
            $has_subscription_products = false;
            $primary_billing_interval = 1;
            $primary_billing_period = 'month';
            
            // Add subscription products to subscription
            if (!empty($line_items['products'])) {
                foreach ($line_items['products'] as $item) {
                    if (!empty($item['product_id'])) {
                        $product = wc_get_product($item['product_id']);
                        if ($product && $product->is_type(array('subscription', 'subscription_variation'))) {
                            $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
                            $price = isset($item['sale_price']) && !empty($item['sale_price']) 
                                ? floatval($item['sale_price']) 
                                : floatval($item['price']);
                            
                            // Add product to subscription
                            $subscription->add_product($product, $quantity, array(
                                'subtotal' => $price * $quantity,
                                'total' => $price * $quantity
                            ));
                            
                            // Set subscription billing schedule from first subscription product
                            if (!$has_subscription_products) {
                                $primary_billing_interval = $product->get_meta('_subscription_period_interval') ?: 1;
                                $primary_billing_period = $product->get_meta('_subscription_period') ?: 'month';
                                $has_subscription_products = true;
                            }
                            
                            // Handle start date if specified
                            if (!empty($item['start_date'])) {
                                $start_date = strtotime($item['start_date']);
                                if ($start_date) {
                                    $subscription->update_dates(array('start' => date('Y-m-d H:i:s', $start_date)));
                                }
                            }
                        }
                    }
                }
            }
            
            // Add recurring fees as subscription fees
            if (!empty($line_items['recurring_fees'])) {
                foreach ($line_items['recurring_fees'] as $fee) {
                    if (!empty($fee['name']) && !empty($fee['amount'])) {
                        $subscription->add_fee(array(
                            'name' => $fee['name'],
                            'amount' => floatval($fee['amount']),
                            'taxable' => !empty($fee['tax_class']) && $fee['tax_class'] !== 'no-tax',
                            'tax_class' => !empty($fee['tax_class']) ? $fee['tax_class'] : ''
                        ));
                        
                        // Use recurring fee billing cycle if no subscription products
                        if (!$has_subscription_products) {
                            $primary_billing_interval = isset($fee['interval']) ? intval($fee['interval']) : 1;
                            $primary_billing_period = isset($fee['period']) ? $fee['period'] : 'month';
                        }
                        
                        // Handle start date for recurring fees
                        if (!empty($fee['start_date'])) {
                            $start_date = strtotime($fee['start_date']);
                            if ($start_date) {
                                $subscription->update_dates(array('start' => date('Y-m-d H:i:s', $start_date)));
                            }
                        }
                    }
                }
            }
            
            // Set billing schedule
            $subscription->set_billing_period($primary_billing_period);
            $subscription->set_billing_interval($primary_billing_interval);
            
            // Calculate totals
            $subscription->calculate_totals();
            
            // Add order note
            $subscription->add_order_note(
                sprintf(__('Subscription created from proposal #%d', 'arsol-pfw'), $proposal_id)
            );
            
            // Link subscription to proposal
            $subscription->update_meta_data('_arsol_proposal_id', $proposal_id);
            $subscription->save();
            
            return $subscription->get_id();
            
        } catch (\Exception $e) {
            return new \WP_Error('subscription_creation_failed', $e->getMessage());
        }
    }
    
    /**
     * Check if line items contain recurring items
     * 
     * @param array $line_items The line items array
     * @return bool True if has recurring items, false otherwise
     */
    private function has_recurring_items($line_items) {
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
            return true;
        }
        
        return false;
    }
    
    /**
     * Get subscription billing details from line items
     * 
     * @param array $line_items The line items array
     * @return array Array with billing interval and period
     */
    public function get_primary_billing_schedule($line_items) {
        $billing_interval = 1;
        $billing_period = 'month';
        
        // Check subscription products first (they take priority)
        if (!empty($line_items['products'])) {
            foreach ($line_items['products'] as $item) {
                if (!empty($item['product_id'])) {
                    $product = wc_get_product($item['product_id']);
                    if ($product && $product->is_type(array('subscription', 'subscription_variation'))) {
                        return array(
                            'interval' => $product->get_meta('_subscription_period_interval') ?: 1,
                            'period' => $product->get_meta('_subscription_period') ?: 'month'
                        );
                    }
                }
            }
        }
        
        // Fall back to recurring fees
        if (!empty($line_items['recurring_fees'])) {
            $first_fee = reset($line_items['recurring_fees']);
            if (!empty($first_fee)) {
                return array(
                    'interval' => isset($first_fee['interval']) ? intval($first_fee['interval']) : 1,
                    'period' => isset($first_fee['period']) ? $first_fee['period'] : 'month'
                );
            }
        }
        
        return array(
            'interval' => $billing_interval,
            'period' => $billing_period
        );
    }
    
    /**
     * Calculate subscription totals from line items
     * 
     * @param array $line_items The line items array
     * @return array Array with subscription totals
     */
    public function calculate_subscription_totals($line_items) {
        $totals = array(
            'products_total' => 0,
            'fees_total' => 0,
            'grand_total' => 0,
            'items_count' => 0
        );
        
        // Calculate subscription products total
        if (!empty($line_items['products'])) {
            foreach ($line_items['products'] as $item) {
                if (!empty($item['product_id'])) {
                    $product = wc_get_product($item['product_id']);
                    if ($product && $product->is_type(array('subscription', 'subscription_variation'))) {
                        $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
                        $price = isset($item['sale_price']) && !empty($item['sale_price']) 
                            ? floatval($item['sale_price']) 
                            : floatval($item['price']);
                        
                        $subtotal = $quantity * $price;
                        $totals['products_total'] += $subtotal;
                        $totals['items_count']++;
                    }
                }
            }
        }
        
        // Calculate recurring fees total
        if (!empty($line_items['recurring_fees'])) {
            foreach ($line_items['recurring_fees'] as $fee) {
                if (!empty($fee['name']) && !empty($fee['amount'])) {
                    $totals['fees_total'] += floatval($fee['amount']);
                    $totals['items_count']++;
                }
            }
        }
        
        $totals['grand_total'] = $totals['products_total'] + $totals['fees_total'];
        
        return $totals;
    }
}
