<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Biller Class
 * 
 * Handles order creation functionality for proposals and projects
 */
class Woocommerce_Biller {
    
    public function __construct() {
        // Hook into proposal approval process
        add_action('arsol_proposal_approved', array($this, 'handle_proposal_approval'), 10, 1);
        
        // Hook into project creation from proposal
        add_action('arsol_proposal_converted_to_project', array($this, 'handle_project_creation'), 10, 2);
    }
    
    /**
     * Handle proposal approval and create orders if needed
     * 
     * @param int $proposal_id The proposal ID
     */
    public function handle_proposal_approval($proposal_id) {
        // Check if this proposal should create orders
        $cost_proposal_type = get_post_meta($proposal_id, '_cost_proposal_type', true);
        
        if ($cost_proposal_type === 'invoice_line_items') {
            $this->create_order_from_proposal($proposal_id);
        }
    }
    
    /**
     * Handle project creation and create orders if needed
     * 
     * @param int $project_id The new project ID
     * @param int $proposal_id The original proposal ID
     */
    public function handle_project_creation($project_id, $proposal_id) {
        // Check if this proposal should create orders
        $cost_proposal_type = get_post_meta($proposal_id, '_cost_proposal_type', true);
        
        if ($cost_proposal_type === 'invoice_line_items') {
            // Set project in session for checkout display
            if (WC()->session) {
                WC()->session->set('arsol_pre_assigned_project', $project_id);
            }
            
            // Create regular order from one-time items
            $order_result = $this->create_order_from_proposal($proposal_id);
            
            if (!is_wp_error($order_result)) {
                // Link the order to the new project
                $order = wc_get_order($order_result);
                if ($order) {
                    $order->update_meta_data(Woocommerce::PROJECT_META_KEY, $project_id);
                    $order->save();
                    
                    // Add note to project about order creation
                    $project_note = sprintf(
                        __('WooCommerce order #%d created from proposal invoice line items.', 'arsol-pfw'),
                        $order->get_order_number()
                    );
                    update_post_meta($project_id, '_project_order_creation_note', $project_note);
                }
            } else {
                // Log error for debugging
                error_log('Arsol Projects: Failed to create order from proposal ' . $proposal_id . ': ' . $order_result->get_error_message());
                
                // Add error note to project
                $error_note = sprintf(
                    __('Failed to create WooCommerce order from proposal: %s', 'arsol-pfw'),
                    $order_result->get_error_message()
                );
                update_post_meta($project_id, '_project_order_creation_error', $error_note);
            }
        }
        
        // Note: Budget estimates are ignored - they remain informational only
        // Note: Subscription creation is handled by Woocommerce_Subscriptions_Biller class
    }
    
    /**
     * Create WooCommerce order from proposal line items
     * 
     * @param int $proposal_id The proposal ID
     * @return int|WP_Error Order ID on success, WP_Error on failure
     */
    public function create_order_from_proposal($proposal_id) {
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
        
        // Create the order
        try {
            $order = wc_create_order(array(
                'customer_id' => $proposal->post_author,
                'status' => 'pending'
            ));
            
            if (is_wp_error($order)) {
                return $order;
            }
            
            // Add products to order (only non-subscription products)
            if (!empty($line_items['products'])) {
                foreach ($line_items['products'] as $item) {
                    if (!empty($item['product_id'])) {
                        $product = wc_get_product($item['product_id']);
                        if ($product && !$product->is_type(array('subscription', 'subscription_variation'))) {
                            $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
                            $price = isset($item['sale_price']) && !empty($item['sale_price']) 
                                ? floatval($item['sale_price']) 
                                : floatval($item['price']);
                            
                            $order->add_product($product, $quantity, array(
                                'subtotal' => $price * $quantity,
                                'total' => $price * $quantity
                            ));
                        }
                    }
                }
            }
            
            // Add fees (only one-time fees)
            if (!empty($line_items['one_time_fees'])) {
                foreach ($line_items['one_time_fees'] as $fee) {
                    if (!empty($fee['name']) && !empty($fee['amount'])) {
                        $order->add_fee(array(
                            'name' => $fee['name'],
                            'amount' => floatval($fee['amount']),
                            'taxable' => !empty($fee['tax_class']) && $fee['tax_class'] !== 'no-tax',
                            'tax_class' => !empty($fee['tax_class']) ? $fee['tax_class'] : ''
                        ));
                    }
                }
            }
            
            // Add shipping fees
            if (!empty($line_items['shipping_fees'])) {
                foreach ($line_items['shipping_fees'] as $shipping) {
                    if (!empty($shipping['description']) && !empty($shipping['amount'])) {
                        $order->add_shipping(array(
                            'method_title' => $shipping['description'],
                            'method_id' => 'arsol_proposal_shipping',
                            'total' => floatval($shipping['amount'])
                        ));
                    }
                }
            }
            
            // Calculate totals
            $order->calculate_totals();
            
            // Add order note
            $order->add_order_note(
                sprintf(__('Order created from proposal #%d', 'arsol-pfw'), $proposal_id)
            );
            
            // Link order to proposal/project
            $order->update_meta_data('_arsol_proposal_id', $proposal_id);
            $order->save();
            
            return $order->get_id();
            
        } catch (\Exception $e) {
            return new \WP_Error('order_creation_failed', $e->getMessage());
        }
    }
}
