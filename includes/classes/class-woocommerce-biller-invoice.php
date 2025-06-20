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
        // Note: Legacy hook removed in favor of direct exception-throwing calls
        // The conversion is now handled directly in the workflow with proper try-catch
    }
    
    /**
     * Declare HPOS compatibility
     */
    private function declare_hpos_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', ARSOL_PROJECTS_PLUGIN_FILE, true);
        }
    }
    
    // Note: handle_proposal_conversion method removed - now using direct exception-throwing calls
    
    /**
     * Convert proposal to WooCommerce order and subscription
     * 
     * @param int $proposal_id The proposal ID to convert
     * @param int $project_id The associated project ID (optional)
     * @return array Result array with success status and message
     * @throws Exception When conversion fails
     */
    public function convert_proposal_to_order($proposal_id, $project_id = null) {
        // Validate proposal
        if (!$this->validate_proposal($proposal_id)) {
            $cost_proposal_type = get_post_meta($proposal_id, '_cost_proposal_type', true) ?: 'none';
            $error_messages = array(
                'none' => __('Proposal validation failed. Please ensure a customer is assigned.', 'arsol-pfw'),
                'budget' => __('Proposal validation failed. Please ensure a customer is assigned.', 'arsol-pfw'),
                'quotation' => __('Proposal validation failed. Please ensure it has valid quotation line items (with description and amount) and a customer assigned.', 'arsol-pfw')
            );
            $error_message = isset($error_messages[$cost_proposal_type]) ? $error_messages[$cost_proposal_type] : $error_messages['none'];
            
            throw new Exception($error_message);
        }
        
        // Get proposal data
        $proposal_data = $this->get_proposal_data($proposal_id);
        if (!$proposal_data) {
            throw new Exception(__('Failed to retrieve proposal data.', 'arsol-pfw'));
        }
        
        // Step 1: Always create parent order with ALL line items
        $order_result = $this->create_parent_order($proposal_data, $project_id);
        if (!$order_result['success']) {
            throw new Exception($order_result['message']);
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
                // Subscription creation failed - throw exception to trigger rollback
                throw new Exception(sprintf(__('Subscription creation failed: %s', 'arsol-pfw'), $subscription_result['message']));
            }
        }
        
        return array(
            'success' => true,
            'message' => $result_message,
            'order_id' => $order_id,
            'subscription_id' => $subscription_id
        );
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
                // Save project using both methods to ensure compatibility
                \Arsol_Projects_For_Woo\Woocommerce::save_project_to_order($order, $project_id);
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
                // Save project using both methods to ensure compatibility
                \Arsol_Projects_For_Woo\Woocommerce::save_project_to_order($subscription, $project_id);
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
     * Validate proposal for conversion based on proposal type
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
        
        // Check if proposal has a customer (post_author)
        $customer_id = $proposal->post_author;
        if (empty($customer_id)) {
            return false;
        }
        
        // Check if customer exists
        $customer = get_userdata($customer_id);
        if (!$customer) {
            return false;
        }
        
        // Get proposal type
        $cost_proposal_type = get_post_meta($proposal_id, '_cost_proposal_type', true) ?: 'none';
        
        // Validate based on proposal type
        switch ($cost_proposal_type) {
            case 'none':
                // No requirements for 'none' type (no orders created)
                return true;
                
            case 'budget':
                // Budget type requires at least one-time budget with amount > 0
                $budget_data = get_post_meta($proposal_id, '_proposal_budget', true);
                if (empty($budget_data) || !is_array($budget_data)) {
                    return false;
                }
                
                $budget_amount = !empty($budget_data['amount']) ? floatval($budget_data['amount']) : 0;
                if ($budget_amount <= 0) {
                    return false;
                }
                
                // If amount is provided, description is required
                $budget_details = get_post_meta($proposal_id, '_proposal_budget_details', true);
                if (empty($budget_details)) {
            return false;
        }
        
        return true;
                
            case 'quotation':
                // Quotation type requires at least one quotation line item with description and amount
                $quotation_line_items = get_post_meta($proposal_id, '_arsol_proposal_quotation_line_items', true);
                if (empty($quotation_line_items) || !is_array($quotation_line_items)) {
                    return false;
                }
                
                // Check that at least one valid line item exists across all item types
                $has_valid_item = false;
                
                // Check products
                if (!empty($quotation_line_items['products'])) {
                    foreach ($quotation_line_items['products'] as $item) {
                        if (!empty($item['description']) && isset($item['regular_price']) && floatval($item['regular_price']) > 0) {
                            $has_valid_item = true;
                            break;
                        }
                    }
                }
                
                // Check one-time fees
                if (!$has_valid_item && !empty($quotation_line_items['one_time_fees'])) {
                    foreach ($quotation_line_items['one_time_fees'] as $item) {
                        if (!empty($item['description']) && !empty($item['amount']) && floatval($item['amount']) > 0) {
                            $has_valid_item = true;
                            break;
                        }
                    }
                }
                
                // Check recurring fees
                if (!$has_valid_item && !empty($quotation_line_items['recurring_fees'])) {
                    foreach ($quotation_line_items['recurring_fees'] as $item) {
                        if (!empty($item['description']) && !empty($item['amount']) && floatval($item['amount']) > 0) {
                            $has_valid_item = true;
                            break;
                        }
                    }
                }
                
                // Check shipping fees
                if (!$has_valid_item && !empty($quotation_line_items['shipping_fees'])) {
                    foreach ($quotation_line_items['shipping_fees'] as $item) {
                        if (!empty($item['description']) && !empty($item['amount']) && floatval($item['amount']) > 0) {
                        $has_valid_item = true;
                        break;
                        }
                    }
                }
                
                return $has_valid_item;
                
            default:
                // Unknown proposal type
                return false;
        }
    }
    
    /**
     * Get proposal data for conversion based on proposal type
     * 
     * @param int $proposal_id
     * @return array|false
     */
    private function get_proposal_data($proposal_id) {
        $proposal = get_post($proposal_id);
        if (!$proposal) {
            return false;
        }
        
        $cost_proposal_type = get_post_meta($proposal_id, '_cost_proposal_type', true) ?: 'none';
        $currency = get_post_meta($proposal_id, '_arsol_proposal_currency', true) ?: get_woocommerce_currency();
        $line_items = array();
        
        // Get line items based on proposal type
        switch ($cost_proposal_type) {
            case 'quotation':
                // Get quotation line items
                $quotation_line_items = get_post_meta($proposal_id, '_arsol_proposal_quotation_line_items', true);
                if (!empty($quotation_line_items) && is_array($quotation_line_items)) {
                    $line_items = $quotation_line_items;
                }
                break;
                
            case 'budget':
            case 'none':
            default:
                // No line items for 'budget' and 'none' types (no orders created)
                $line_items = array();
                break;
        }
        
        return array(
            'proposal_id' => $proposal_id,
            'customer_id' => $proposal->post_author,
            'line_items' => $line_items,
            'currency' => $currency,
            'proposal_type' => $cost_proposal_type
        );
    }
}