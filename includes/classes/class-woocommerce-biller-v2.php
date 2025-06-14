<?php
/**
 * WooCommerce Biller Class - Version 2
 * 
 * Reimplemented from scratch following strict WooCommerce documentation
 * Handles regular order creation with proper fee handling
 * 
 * @package Arsol_Projects_For_Woo
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Woocommerce_Biller_V2 {
    
    private $log_source = 'order_creation';
    
    /**
     * Create WooCommerce order from proposal
     * Following official WooCommerce order creation patterns
     * 
     * @param int $proposal_id Proposal ID
     * @return array Result array with success status and order ID
     */
    public function create_order_from_proposal($proposal_id) {
        Woocommerce_Logs::log($this->log_source, 'info', 
            sprintf('Starting order creation from proposal #%d', $proposal_id));
        
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
            // Create order using WooCommerce function
            $order = wc_create_order();
            
            if (is_wp_error($order)) {
                Woocommerce_Logs::log($this->log_source, 'error', 
                    'Failed to create order: ' . $order->get_error_message());
                return array('success' => false, 'message' => 'Failed to create order');
            }
            
            Woocommerce_Logs::log($this->log_source, 'info', 
                sprintf('Created order #%d', $order->get_id()));
            
            // Add customer information if available
            if (isset($proposal_data['customer_id'])) {
                $order->set_customer_id($proposal_data['customer_id']);
                Woocommerce_Logs::log($this->log_source, 'info', 
                    sprintf('Set customer ID: %d', $proposal_data['customer_id']));
            }
            
            // Add products to order
            $this->add_products_to_order($order, $proposal_data);
            
            // Save order after adding products
            $order->save();
            Woocommerce_Logs::log($this->log_source, 'info', 
                sprintf('Order #%d saved after adding products', $order->get_id()));
            
            // Add fees using the helper class
            $this->add_fees_to_order($order, $proposal_data);
            
            // Set order status
            $order->set_status('pending');
            
            // Final save
            $order->save();
            
            // Link proposal to order
            $order->update_meta_data('_proposal_id', $proposal_id);
            $order->save_meta_data();
            
            Woocommerce_Logs::log($this->log_source, 'success', 
                sprintf('Successfully created order #%d from proposal #%d. Order total: $%.2f', 
                    $order->get_id(), $proposal_id, $order->get_total()));
            
            return array(
                'success' => true, 
                'order_id' => $order->get_id(),
                'order_total' => $order->get_total()
            );
            
        } catch (Exception $e) {
            Woocommerce_Logs::log($this->log_source, 'error', 
                'Exception during order creation: ' . $e->getMessage());
            return array('success' => false, 'message' => $e->getMessage());
        }
    }
    
    /**
     * Add products to order
     * 
     * @param WC_Order $order Order object
     * @param array $proposal_data Proposal data
     */
    private function add_products_to_order($order, $proposal_data) {
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
            
            $order->add_product($product, $quantity, array(
                'subtotal' => $price * $quantity,
                'total' => $sale_price * $quantity
            ));
            
            Woocommerce_Logs::log($this->log_source, 'info', 
                sprintf('Added product #%d (%s) x%d to order', 
                    $product_data['product_id'], $product->get_name(), $quantity));
        }
    }
    
    /**
     * Add all fees to order
     * 
     * @param WC_Order $order Order object
     * @param array $proposal_data Proposal data
     */
    private function add_fees_to_order($order, $proposal_data) {
        $total_fees_added = 0;
        
        // Add one-time fees
        if (isset($proposal_data['one_time_fees']) && is_array($proposal_data['one_time_fees'])) {
            Woocommerce_Logs::log($this->log_source, 'info', 
                sprintf('Processing %d one-time fees', count($proposal_data['one_time_fees'])));
            
            if (Woocommerce_Biller_Helper_V2::add_order_fee($order, $proposal_data['one_time_fees'], 'one_time', $this->log_source)) {
                $total_fees_added += count($proposal_data['one_time_fees']);
            }
        }
        
        // Add shipping fees
        if (isset($proposal_data['shipping_fees']) && is_array($proposal_data['shipping_fees'])) {
            Woocommerce_Logs::log($this->log_source, 'info', 
                sprintf('Processing %d shipping fees', count($proposal_data['shipping_fees'])));
            
            if (Woocommerce_Biller_Helper_V2::add_order_fee($order, $proposal_data['shipping_fees'], 'shipping', $this->log_source)) {
                $total_fees_added += count($proposal_data['shipping_fees']);
            }
        }
        
        Woocommerce_Logs::log($this->log_source, 'info', 
            sprintf('Total fees added to order: %d', $total_fees_added));
    }
} 