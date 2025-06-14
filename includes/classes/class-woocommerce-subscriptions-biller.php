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
        // Enhanced logging for debugging
        Woocommerce_Logs::log_subscription_creation('info', 
            sprintf('Starting subscription creation handler for project #%d from proposal #%d', 
                $project_id, $proposal_id));
        
        // Check if this proposal should create orders
        $cost_proposal_type = get_post_meta($proposal_id, '_cost_proposal_type', true);
        
        Woocommerce_Logs::log_subscription_creation('info', 
            sprintf('Proposal #%d cost type: %s', $proposal_id, $cost_proposal_type));
        
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
                    
                    Woocommerce_Logs::log_subscription_creation('info', 
                        sprintf('Successfully created subscription #%d from proposal #%d', 
                            $subscription_result, $proposal_id));
                } else {
                    Woocommerce_Logs::log_subscription_creation('error', 
                        sprintf('Failed to retrieve subscription #%d after creation', $subscription_result));
                }
            } else {
                // Only log error if there were actually recurring items to process
                $line_items = get_post_meta($proposal_id, '_arsol_proposal_line_items', true);
                $has_recurring = $this->has_recurring_items($line_items);
                
                Woocommerce_Logs::log_subscription_creation('info', 
                    sprintf('Proposal #%d has recurring items: %s', 
                        $proposal_id, $has_recurring ? 'yes' : 'no'));
                
                if ($has_recurring) {
                    // Add error note to project
                    $error_note = sprintf(
                        __('Failed to create WooCommerce subscription from proposal: %s', 'arsol-pfw'),
                        $subscription_result->get_error_message()
                    );
                    update_post_meta($project_id, '_project_subscription_creation_error', $error_note);
                    
                    Woocommerce_Logs::log_subscription_creation('error', 
                        sprintf('Failed to create subscription from proposal #%d: %s', 
                            $proposal_id, $subscription_result->get_error_message()));
                } else {
                    Woocommerce_Logs::log_subscription_creation('info', 
                        sprintf('No recurring items in proposal #%d, skipping subscription creation', 
                            $proposal_id));
                }
            }
        } else {
            Woocommerce_Logs::log_subscription_creation('info', 
                sprintf('Proposal #%d is not invoice_line_items type, skipping subscription creation', 
                    $proposal_id));
        }
    }
    
    /**
     * Validate billing interval (simple safety net)
     * 
     * @param mixed $interval The billing interval to validate
     * @return int Valid billing interval (1-6)
     */
    private function validate_billing_interval($interval) {
        $interval = intval($interval);
        return ($interval >= 1 && $interval <= 6) ? $interval : 1;
    }
    
    /**
     * Validate billing period (simple safety net)
     * 
     * @param string $period The billing period to validate
     * @return string Valid billing period (day, week, month, or year)
     */
    private function validate_billing_period($period) {
        $valid_periods = array('day', 'week', 'month', 'year');
        return in_array($period, $valid_periods) ? $period : 'month';
    }
    
    /**
     * Create subscription from proposal
     * 
     * @param int $proposal_id The proposal ID
     * @return int|WP_Error Subscription ID on success, WP_Error on failure
     */
    public function create_subscription_from_proposal($proposal_id) {
        try {
            // Check if WooCommerce Subscriptions is active
            if (!class_exists('WC_Subscriptions') || !function_exists('wcs_create_subscription')) {
                throw new \Exception(__('WooCommerce Subscriptions is not active or not properly loaded', 'arsol-pfw'));
            }
            
            // Get proposal data
            $proposal = get_post($proposal_id);
            if (!$proposal) {
                throw new \Exception(__('Proposal not found', 'arsol-pfw'));
            }
            
            // Get proposal line items
            $line_items = get_post_meta($proposal_id, '_arsol_proposal_line_items', true);
            if (empty($line_items)) {
                throw new \Exception(__('No line items found in proposal', 'arsol-pfw'));
            }
            
            // Check if proposal has recurring items
            if (!$this->has_recurring_items($line_items)) {
                throw new \Exception(__('Proposal contains no recurring items', 'arsol-pfw'));
            }
            
            // Get customer ID from proposal
            $customer_id = get_post_meta($proposal_id, '_arsol_customer_id', true);
            
            // If no customer ID in meta, use the proposal author
            if (empty($customer_id)) {
                $proposal_post = get_post($proposal_id);
                if ($proposal_post && $proposal_post->post_author) {
                    $customer_id = $proposal_post->post_author;
                } else {
                    throw new \Exception(__('No customer associated with proposal', 'arsol-pfw'));
                }
            }
            
            // Validate that the customer exists and is a valid WooCommerce customer
            $customer = new \WC_Customer($customer_id);
            if (!$customer || !$customer->get_id()) {
                throw new \Exception(__('Invalid customer associated with proposal', 'arsol-pfw'));
            }
            
            // Log customer assignment for debugging
            if (function_exists('wc_get_logger')) {
                $logger = wc_get_logger();
                $logger->info(
                    sprintf('Creating subscription from proposal #%d for customer #%d (%s)', 
                        $proposal_id, $customer_id, $customer->get_billing_email()),
                    array('source' => 'arsol-pfw-subscriptions')
                );
            }
            
            // Create subscription
            $subscription = wcs_create_subscription(array(
                'order_id' => 0,
                'status' => 'pending',
                'billing_period' => 'month',
                'billing_interval' => 1,
                'customer_id' => $customer_id
            ));
            
            if (is_wp_error($subscription)) {
                throw new \Exception($subscription->get_error_message());
            }
            
            // Set billing and shipping addresses from proposal or customer
            $billing_address = get_post_meta($proposal_id, '_arsol_billing_address', true);
            $shipping_address = get_post_meta($proposal_id, '_arsol_shipping_address', true);
            
            if (!empty($billing_address) && is_array($billing_address)) {
                $subscription->set_address($billing_address, 'billing');
            } else {
                // Fall back to customer's billing address
                $customer_billing = array(
                    'first_name' => $customer->get_billing_first_name(),
                    'last_name'  => $customer->get_billing_last_name(),
                    'company'    => $customer->get_billing_company(),
                    'address_1'  => $customer->get_billing_address_1(),
                    'address_2'  => $customer->get_billing_address_2(),
                    'city'       => $customer->get_billing_city(),
                    'state'      => $customer->get_billing_state(),
                    'postcode'   => $customer->get_billing_postcode(),
                    'country'    => $customer->get_billing_country(),
                    'email'      => $customer->get_billing_email(),
                    'phone'      => $customer->get_billing_phone(),
                );
                $subscription->set_address($customer_billing, 'billing');
            }
            
            if (!empty($shipping_address) && is_array($shipping_address)) {
                $subscription->set_address($shipping_address, 'shipping');
            } else {
                // Fall back to customer's shipping address
                $customer_shipping = array(
                    'first_name' => $customer->get_shipping_first_name(),
                    'last_name'  => $customer->get_shipping_last_name(),
                    'company'    => $customer->get_shipping_company(),
                    'address_1'  => $customer->get_shipping_address_1(),
                    'address_2'  => $customer->get_shipping_address_2(),
                    'city'       => $customer->get_shipping_city(),
                    'state'      => $customer->get_shipping_state(),
                    'postcode'   => $customer->get_shipping_postcode(),
                    'country'    => $customer->get_shipping_country(),
                );
                $subscription->set_address($customer_shipping, 'shipping');
            }
            
            // Track billing schedule
            $primary_billing_interval = 1;
            $primary_billing_period = 'month';
            $has_subscription_products = false;
            
            // Add subscription products
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
                                $raw_interval = $product->get_meta('_subscription_period_interval') ?: 1;
                                $primary_billing_interval = $this->validate_billing_interval($raw_interval);
                                $raw_period = $product->get_meta('_subscription_period') ?: 'month';
                                $primary_billing_period = $this->validate_billing_period($raw_period);
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
                            $raw_interval = isset($fee['interval']) ? $fee['interval'] : 1;
                            $primary_billing_interval = $this->validate_billing_interval($raw_interval);
                            $raw_period = isset($fee['period']) ? $fee['period'] : 'month';
                            $primary_billing_period = $this->validate_billing_period($raw_period);
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
            
            // Set billing schedule with validated period
            $subscription->set_billing_period($primary_billing_period);
            $subscription->set_billing_interval($primary_billing_interval);
            
            // Log billing schedule for debugging
            if (function_exists('wc_get_logger')) {
                $logger = wc_get_logger();
                $logger->info(
                    sprintf('Creating subscription from proposal #%d with billing: %d %s', 
                        $proposal_id, $primary_billing_interval, $primary_billing_period),
                    array('source' => 'arsol-pfw-subscriptions')
                );
            }
            
            // Calculate totals
            $subscription->calculate_totals();
            
            // Add order note
            $subscription->add_order_note(
                sprintf(__('Subscription created from proposal #%d', 'arsol-pfw'), $proposal_id)
            );
            
            // Link subscription to proposal
            $subscription->update_meta_data('_arsol_proposal_id', $proposal_id);
            $subscription->save();
            
            Woocommerce_Logs::log_subscription_creation('info', 
                sprintf('Successfully created subscription #%d from proposal #%d', 
                    $subscription->get_id(), $proposal_id));
            
            // Create pending parent order using WooCommerce Subscriptions built-in functionality
            $parent_order_result = $this->create_pending_parent_order($subscription);
            
            if (!is_wp_error($parent_order_result)) {
                Woocommerce_Logs::log_subscription_creation('info', 
                    sprintf('Successfully created pending parent order #%d for subscription #%d', 
                        $parent_order_result, $subscription->get_id()));
            } else {
                Woocommerce_Logs::log_subscription_creation('warning', 
                    sprintf('Failed to create pending parent order for subscription #%d: %s', 
                        $subscription->get_id(), $parent_order_result->get_error_message()));
            }
            
            return $subscription->get_id();
            
        } catch (\Exception $e) {
            return new \WP_Error('subscription_creation_failed', $e->getMessage());
        }
    }
    
    /**
     * Create pending parent order for subscription using WooCommerce Subscriptions built-in functionality
     * This is the same function that powers the "Create pending parent order" button in admin
     * 
     * @param WC_Subscription $subscription The subscription object
     * @return int|WP_Error Parent order ID on success, WP_Error on failure
     */
    private function create_pending_parent_order($subscription) {
        try {
            // Ensure we have a valid subscription
            if (!$subscription || !is_a($subscription, 'WC_Subscription')) {
                throw new \Exception(__('Invalid subscription object', 'arsol-pfw'));
            }
            
            // Check if subscription already has a parent order
            $existing_parent_id = $subscription->get_parent_id();
            if ($existing_parent_id && $existing_parent_id > 0) {
                Woocommerce_Logs::log_subscription_creation('info', 
                    sprintf('Subscription #%d already has parent order #%d', 
                        $subscription->get_id(), $existing_parent_id));
                return $existing_parent_id;
            }
            
            // Use WooCommerce Subscriptions built-in function to create parent order
            // This is the same function used by the admin "Create pending parent order" action
            if (function_exists('wcs_create_order_from_subscription')) {
                $parent_order = wcs_create_order_from_subscription($subscription, 'parent');
                
                if (is_wp_error($parent_order)) {
                    throw new \Exception($parent_order->get_error_message());
                }
                
                if (!$parent_order || !is_a($parent_order, 'WC_Order')) {
                    throw new \Exception(__('Failed to create parent order - invalid order object returned', 'arsol-pfw'));
                }
                
                // Set order status to pending
                $parent_order->set_status('pending');
                $parent_order->save();
                
                Woocommerce_Logs::log_subscription_creation('info', 
                    sprintf('Created pending parent order #%d for subscription #%d using wcs_create_order_from_subscription', 
                        $parent_order->get_id(), $subscription->get_id()));
                
                return $parent_order->get_id();
                
            } else {
                // Fallback: Manual parent order creation if the function doesn't exist
                Woocommerce_Logs::log_subscription_creation('warning', 
                    'wcs_create_order_from_subscription function not available, using manual creation');
                
                return $this->create_parent_order_manually($subscription);
            }
            
        } catch (\Exception $e) {
            return new \WP_Error('parent_order_creation_failed', $e->getMessage());
        }
    }
    
    /**
     * Manually create parent order for subscription (fallback method)
     * 
     * @param WC_Subscription $subscription The subscription object
     * @return int|WP_Error Parent order ID on success, WP_Error on failure
     */
    private function create_parent_order_manually($subscription) {
        try {
            // Create a new order with the same customer and details as subscription
            $parent_order = wc_create_order(array(
                'customer_id' => $subscription->get_customer_id(),
                'status' => 'pending'
            ));
            
            if (is_wp_error($parent_order)) {
                throw new \Exception($parent_order->get_error_message());
            }
            
            // Copy billing and shipping addresses from subscription
            $parent_order->set_address($subscription->get_address('billing'), 'billing');
            $parent_order->set_address($subscription->get_address('shipping'), 'shipping');
            
            // Copy line items from subscription to parent order
            foreach ($subscription->get_items() as $item) {
                $parent_order->add_item($item);
            }
            
            // Copy fees from subscription to parent order
            foreach ($subscription->get_fees() as $fee) {
                $parent_order->add_item($fee);
            }
            
            // Copy shipping from subscription to parent order
            foreach ($subscription->get_shipping_methods() as $shipping) {
                $parent_order->add_item($shipping);
            }
            
            // Copy taxes from subscription to parent order
            foreach ($subscription->get_taxes() as $tax) {
                $parent_order->add_item($tax);
            }
            
            // Calculate totals
            $parent_order->calculate_totals();
            
            // Link the parent order to the subscription
            $subscription->set_parent_id($parent_order->get_id());
            $subscription->save();
            
            // Add order note
            $parent_order->add_order_note(
                sprintf(__('Parent order created for subscription #%d', 'arsol-pfw'), $subscription->get_id())
            );
            
            $parent_order->save();
            
            Woocommerce_Logs::log_subscription_creation('info', 
                sprintf('Manually created parent order #%d for subscription #%d', 
                    $parent_order->get_id(), $subscription->get_id()));
            
            return $parent_order->get_id();
            
        } catch (\Exception $e) {
            return new \WP_Error('manual_parent_order_creation_failed', $e->getMessage());
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
                        $raw_interval = $product->get_meta('_subscription_period_interval') ?: 1;
                        $raw_period = $product->get_meta('_subscription_period') ?: 'month';
                        return array(
                            'interval' => $this->validate_billing_interval($raw_interval),
                            'period' => $this->validate_billing_period($raw_period)
                        );
                    }
                }
            }
        }
        
        // Fall back to recurring fees
        if (!empty($line_items['recurring_fees'])) {
            $first_fee = reset($line_items['recurring_fees']);
            if (!empty($first_fee)) {
                $raw_interval = isset($first_fee['interval']) ? $first_fee['interval'] : 1;
                $raw_period = isset($first_fee['period']) ? $first_fee['period'] : 'month';
                return array(
                    'interval' => $this->validate_billing_interval($raw_interval),
                    'period' => $this->validate_billing_period($raw_period)
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
