<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Biller Invoice Class
 * 
 * Handles conversion of project proposals to WooCommerce orders and subscriptions
 * using HPOS-compatible methods and modern WooCommerce CRUD patterns.
 * 
 * Automatically triggered during proposal-to-project conversion workflow.
 */
class Woocommerce_Biller {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->declare_hpos_compatibility();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Hook into the proposal-to-project conversion workflow
        add_action('arsol_proposal_converted_to_project', array($this, 'handle_proposal_conversion'), 10, 2);
    }
    
    /**
     * Declare HPOS compatibility
     */
    private function declare_hpos_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', ARSOL_PROJECTS_PLUGIN_FILE, true);
        }
    }
    
    /**
     * Handle proposal conversion to project
     * 
     * This method is automatically called when a proposal is converted to a project.
     * It creates WooCommerce orders and subscriptions based on the proposal's line items.
     * 
     * @param int $project_id The newly created project ID
     * @param int $proposal_id The original proposal ID
     */
    public function handle_proposal_conversion($project_id, $proposal_id) {
        try {
            // Log the conversion attempt
            if (function_exists('wc_get_logger')) {
                \Arsol_Projects_For_Woo\Woocommerce_Logs::log_conversion('info',
                    sprintf('Starting order creation for project #%d from proposal #%d', $project_id, $proposal_id));
            }
            
            // Convert proposal to order
            $result = $this->convert_proposal_to_order($proposal_id, $project_id);
            
            if ($result['success']) {
                // Store success information on the project
                update_post_meta($project_id, '_project_order_creation_note', $result['message']);
                
                if (!empty($result['order_id'])) {
                    update_post_meta($project_id, '_project_woocommerce_order_id', $result['order_id']);
                }
                
                if (!empty($result['subscription_id'])) {
                    update_post_meta($project_id, '_project_woocommerce_subscription_id', $result['subscription_id']);
                }
                
                \Arsol_Projects_For_Woo\Woocommerce_Logs::log_conversion('info',
                    sprintf('Successfully created orders for project #%d: %s', $project_id, $result['message']));
                    
            } else {
                // Store error information on the project for rollback handling
                update_post_meta($project_id, '_project_order_creation_error', $result['message']);
                
                \Arsol_Projects_For_Woo\Woocommerce_Logs::log_conversion('error',
                    sprintf('Failed to create orders for project #%d: %s', $project_id, $result['message']));
            }
            
        } catch (Exception $e) {
            $error_message = sprintf(__('Order creation failed with exception: %s', 'arsol-pfw'), $e->getMessage());
            update_post_meta($project_id, '_project_order_creation_error', $error_message);
            
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_conversion('error',
                sprintf('Exception during order creation for project #%d: %s', $project_id, $e->getMessage()));
        }
    }
    
    /**
     * Convert proposal to WooCommerce order and subscription
     * 
     * @param int $proposal_id The proposal ID to convert
     * @param int $project_id The associated project ID (optional)
     * @return array Result array with success status and message
     */
    public function convert_proposal_to_order($proposal_id, $project_id = null) {
        // Validate proposal
        if (!$this->validate_proposal($proposal_id)) {
            return array(
                'success' => false,
                'message' => __('Proposal validation failed. Please ensure it has line items and a customer assigned.', 'arsol-pfw')
            );
        }
        
        // Get proposal data
        $proposal_data = $this->get_proposal_data($proposal_id);
        if (!$proposal_data) {
            return array(
                'success' => false,
                'message' => __('Failed to retrieve proposal data.', 'arsol-pfw')
            );
        }
        
        try {
            // Step 1: Always create parent order with ALL line items
            $order_result = $this->create_parent_order($proposal_data, $project_id);
            if (!$order_result['success']) {
                return $order_result;
            }
            
            $order_id = $order_result['order_id'];
            $result_message = sprintf(__('Order #%s created successfully.', 'arsol-pfw'), $order_id);
            
            // Step 2: Check for recurring items and create subscription if needed
            $has_recurring = $this->has_recurring_items($proposal_data['line_items']);
            $subscription_id = null;
            
            if ($has_recurring) {
                $subscription_result = $this->create_subscription($proposal_data, $order_id, $project_id);
                if ($subscription_result['success']) {
                    $subscription_id = $subscription_result['subscription_id'];
                    $result_message .= sprintf(__(' Subscription #%s created successfully.', 'arsol-pfw'), $subscription_id);
                } else {
                    // Subscription creation failed - should we rollback the order?
                    // For now, we'll keep the order and just log the subscription error
                    $result_message .= sprintf(__(' Warning: Subscription creation failed: %s', 'arsol-pfw'), $subscription_result['message']);
                }
            }
            
            return array(
                'success' => true,
                'message' => $result_message,
                'order_id' => $order_id,
                'subscription_id' => $subscription_id
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Order creation failed: %s', 'arsol-pfw'), $e->getMessage())
            );
        }
    }
    
    /**
     * Create parent order with all line items
     * 
     * @param array $proposal_data
     * @param int $project_id
     * @return array
     */
    private function create_parent_order($proposal_data, $project_id = null) {
        try {
            // Create order using modern HPOS-compatible method
            $order = wc_create_order(array(
                'status' => 'pending',
                'customer_id' => $proposal_data['customer_id'],
                'created_via' => 'proposal_conversion'
            ));
            
            if (is_wp_error($order)) {
                throw new Exception($order->get_error_message());
            }
            
            // Set billing and shipping addresses
            $this->set_order_addresses($order, $proposal_data['customer_id']);
            
            // Add all line items to the order (Debug: logging line item types)
            $this->add_line_items_to_order($order, $proposal_data['line_items']);
            
            // Set order meta
            $order->add_meta_data('_arsol_proposal_id', $proposal_data['proposal_id']);
            if ($project_id) {
                $order->add_meta_data('_arsol_project_id', $project_id);
            }
            $order->add_meta_data('_arsol_conversion_date', current_time('mysql'));
            
            // Set currency if specified
            if (!empty($proposal_data['currency'])) {
                $order->set_currency($proposal_data['currency']);
            }
            
            // Calculate totals and save
            $order->calculate_totals();
            $order->save();
            
            return array(
                'success' => true,
                'order_id' => $order->get_id(),
                'message' => sprintf(__('Order #%s created successfully.', 'arsol-pfw'), $order->get_id())
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Failed to create order: %s', 'arsol-pfw'), $e->getMessage())
            );
        }
    }
    
    /**
     * Create subscription for recurring items
     * 
     * @param array $proposal_data
     * @param int $parent_order_id
     * @param int $project_id
     * @return array
     */
    private function create_subscription($proposal_data, $parent_order_id, $project_id = null) {
        // Check if WooCommerce Subscriptions is active
        if (!class_exists('WC_Subscriptions') || !function_exists('wcs_create_subscription')) {
            return array(
                'success' => false,
                'message' => __('WooCommerce Subscriptions plugin is required for recurring items.', 'arsol-pfw')
            );
        }
        
        try {
            // Get parent order
            $parent_order = wc_get_order($parent_order_id);
            if (!$parent_order) {
                throw new Exception(__('Parent order not found.', 'arsol-pfw'));
            }
            
            // Create subscription
            $subscription = wcs_create_subscription(array(
                'order_id' => $parent_order_id,
                'status' => 'pending',
                'billing_period' => 'month', // Default to monthly, can be customized
                'billing_interval' => 1,
                'customer_id' => $proposal_data['customer_id']
            ));
            
            if (is_wp_error($subscription)) {
                throw new Exception($subscription->get_error_message());
            }
            
            // Copy addresses from parent order
            $subscription->set_address($parent_order->get_address('billing'), 'billing');
            $subscription->set_address($parent_order->get_address('shipping'), 'shipping');
            
            // Add only recurring items to subscription
            $this->add_recurring_items_to_subscription($subscription, $proposal_data['line_items']);
            
            // Set subscription meta
            $subscription->add_meta_data('_arsol_proposal_id', $proposal_data['proposal_id']);
            if ($project_id) {
                $subscription->add_meta_data('_arsol_project_id', $project_id);
            }
            $subscription->add_meta_data('_arsol_conversion_date', current_time('mysql'));
            
            // Set currency if specified
            if (!empty($proposal_data['currency'])) {
                $subscription->set_currency($proposal_data['currency']);
            }
            
            // Calculate totals and save
            $subscription->calculate_totals();
            $subscription->save();
            
            return array(
                'success' => true,
                'subscription_id' => $subscription->get_id(),
                'message' => sprintf(__('Subscription #%s created successfully.', 'arsol-pfw'), $subscription->get_id())
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Failed to create subscription: %s', 'arsol-pfw'), $e->getMessage())
            );
        }
    }
    
    /**
     * Set order addresses from customer data
     * 
     * @param WC_Order $order
     * @param int $customer_id
     */
    private function set_order_addresses($order, $customer_id) {
        $customer = new \WC_Customer($customer_id);
        
        // Set billing address
        $billing_address = array(
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
        
        // Set shipping address
        $shipping_address = array(
            'first_name' => $customer->get_shipping_first_name() ?: $customer->get_billing_first_name(),
            'last_name'  => $customer->get_shipping_last_name() ?: $customer->get_billing_last_name(),
            'company'    => $customer->get_shipping_company() ?: $customer->get_billing_company(),
            'address_1'  => $customer->get_shipping_address_1() ?: $customer->get_billing_address_1(),
            'address_2'  => $customer->get_shipping_address_2() ?: $customer->get_billing_address_2(),
            'city'       => $customer->get_shipping_city() ?: $customer->get_billing_city(),
            'state'      => $customer->get_shipping_state() ?: $customer->get_billing_state(),
            'postcode'   => $customer->get_shipping_postcode() ?: $customer->get_billing_postcode(),
            'country'    => $customer->get_shipping_country() ?: $customer->get_billing_country(),
        );
        
        $order->set_address($billing_address, 'billing');
        $order->set_address($shipping_address, 'shipping');
    }
    
    /**
     * Add line items to order
     * 
     * @param WC_Order $order
     * @param array $line_items
     */
    private function add_line_items_to_order($order, $line_items) {
        // Add products
        if (!empty($line_items['products'])) {
            foreach ($line_items['products'] as $item) {
                $product = wc_get_product($item['product_id']);
                if ($product) {
                    $item_id = $order->add_product($product, $item['quantity']); $order_item = $item_id ? $order->get_item($item_id) : null;
                    
                    // Set custom price if specified (only if order item was created successfully)
                    if ($order_item && isset($item['price']) && $item['price'] !== '') {
                        $order_item->set_subtotal($item['price'] * $item['quantity']);
                        $order_item->set_total($item['price'] * $item['quantity']);
                    }
                    
                    // Add start date for subscription products
                    if ($order_item && isset($item['start_date']) && !empty($item['start_date'])) {
                        $order_item->add_meta_data('_subscription_start_date', $item['start_date']);
                    }
                }
            }
        }
        
        // Add recurring fees
        if (!empty($line_items['recurring_fees'])) {
            foreach ($line_items['recurring_fees'] as $fee) {
                $fee_item = new \WC_Order_Item_Fee();
                $fee_item->set_name($fee['description']);
                $fee_item->set_amount($fee['amount']);
                $fee_item->set_total($fee['amount']);
                
                // Add start date if specified
                if (isset($fee['start_date']) && !empty($fee['start_date'])) {
                    $fee_item->add_meta_data('_subscription_start_date', $fee['start_date']);
                }
                
                $order->add_item($fee_item);
            }
        }
        
        // Add one-time fees
        if (!empty($line_items['one_time_fees'])) {
            foreach ($line_items['one_time_fees'] as $fee) {
                $fee_item = new \WC_Order_Item_Fee();
                $fee_item->set_name($fee['description']);
                $fee_item->set_amount($fee['amount']);
                $fee_item->set_total($fee['amount']);
                $order->add_item($fee_item);
            }
        }
        
        // Add shipping fees
        if (!empty($line_items['shipping_fees'])) {
            foreach ($line_items['shipping_fees'] as $shipping) {
                $shipping_item = new \WC_Order_Item_Shipping();
                $shipping_item->set_method_title($shipping['description']);
                $shipping_item->set_total($shipping['amount']);
                $order->add_item($shipping_item);
            }
        }
    }
    
    /**
     * Add only recurring items to subscription
     * 
     * @param WC_Subscription $subscription
     * @param array $line_items
     */
    private function add_recurring_items_to_subscription($subscription, $line_items) {
        // Add subscription products only
        if (!empty($line_items['products'])) {
            foreach ($line_items['products'] as $item) {
                $product = wc_get_product($item['product_id']);
                if ($product) {
                    // Debug logging for product types
                    $actual_product_type = $product->get_type();
                    $stored_product_type = isset($item['product_type']) ? $item['product_type'] : 'not_set';
                    
                    \Arsol_Projects_For_Woo\Woocommerce_Logs::log_conversion('info', 
                        sprintf('Processing product #%d for subscription: Actual type: %s, Stored type: %s', 
                            $item['product_id'], $actual_product_type, $stored_product_type));
                    
                    // Use actual product type as fallback if stored type is missing
                    $product_type_to_check = !empty($item['product_type']) ? $item['product_type'] : $actual_product_type;
                    
                    if (in_array($product_type_to_check, ['subscription', 'subscription_variation'])) {
                        $item_id = $subscription->add_product($product, $item['quantity']); 
                        $subscription_item = $item_id ? $subscription->get_item($item_id) : null;
                        
                        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_conversion('info', 
                            sprintf('Added subscription product #%d to subscription (item_id: %s)', 
                                $item['product_id'], $item_id ? $item_id : 'failed'));
                        
                        // Set custom price if specified (only if order item was created successfully)
                        if ($subscription_item && isset($item['price']) && $item['price'] !== '') {
                            $subscription_item->set_subtotal($item['price'] * $item['quantity']);
                            $subscription_item->set_total($item['price'] * $item['quantity']);
                        }
                        
                        // Add start date
                        if ($subscription_item && isset($item['start_date']) && !empty($item['start_date'])) {
                            $subscription_item->add_meta_data('_subscription_start_date', $item['start_date']);
                        }
                    } else {
                        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_conversion('info', 
                            sprintf('Skipped product #%d for subscription (type: %s)', 
                                $item['product_id'], $product_type_to_check));
                    }
                }
            }
        }
        
        // Add recurring fees only
        if (!empty($line_items['recurring_fees'])) {
            foreach ($line_items['recurring_fees'] as $fee) {
                $fee_item = new \WC_Order_Item_Fee();
                $fee_item->set_name($fee['description']);
                $fee_item->set_amount($fee['amount']);
                $fee_item->set_total($fee['amount']);
                
                // Add start date if specified
                if (isset($fee['start_date']) && !empty($fee['start_date'])) {
                    $fee_item->add_meta_data('_subscription_start_date', $fee['start_date']);
                }
                
                $subscription->add_item($fee_item);
                
                \Arsol_Projects_For_Woo\Woocommerce_Logs::log_conversion('info', 
                    sprintf('Added recurring fee "%s" to subscription', $fee['description']));
            }
        }
    }
    
    /**
     * Check if line items contain recurring items
     * 
     * @param array $line_items
     * @return bool
     */
    private function has_recurring_items($line_items) {
        // Check for subscription products
        if (!empty($line_items['products'])) {
            foreach ($line_items['products'] as $item) {
                // Use actual product type as fallback if stored type is missing
                $product_type_to_check = !empty($item['product_type']) ? $item['product_type'] : '';
                
                // If stored type is not available, check the actual product
                if (empty($product_type_to_check) && !empty($item['product_id'])) {
                    $product = wc_get_product($item['product_id']);
                    if ($product) {
                        $product_type_to_check = $product->get_type();
                    }
                }
                
                if (in_array($product_type_to_check, ['subscription', 'subscription_variation'])) {
                    return true;
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
     * Validate proposal for conversion
     * 
     * @param int $proposal_id
     * @return bool
     */
    private function validate_proposal($proposal_id) {
        // Check if proposal exists
        $proposal = get_post($proposal_id);
        if (!$proposal || $proposal->post_type !== 'arsol-pfw-proposal') {
            return false;
        }
        
        // Check if proposal has line items
        $line_items = get_post_meta($proposal_id, '_arsol_proposal_line_items', true);
        if (empty($line_items)) {
            return false;
        }
        
        // Check if proposal has a customer
        $customer_id = $proposal->post_author;
        if (empty($customer_id)) {
            return false;
        }
        
        // Check if customer exists
        $customer = get_userdata($customer_id);
        if (!$customer) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get proposal data for conversion
     * 
     * @param int $proposal_id
     * @return array|false
     */
    private function get_proposal_data($proposal_id) {
        $proposal = get_post($proposal_id);
        if (!$proposal) {
            return false;
        }
        
        $line_items = get_post_meta($proposal_id, '_arsol_proposal_line_items', true);
        $currency = get_post_meta($proposal_id, '_arsol_proposal_currency', true);
        
        return array(
            'proposal_id' => $proposal_id,
            'customer_id' => $proposal->post_author,
            'line_items' => $line_items ?: array(),
            'currency' => $currency ?: get_woocommerce_currency()
        );
    }
}