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
                    
                    Woocommerce_Logs::log_order_creation('info', 
                        sprintf('Successfully created and linked order #%d to project #%d', 
                            $order->get_id(), $project_id));
                }
            } else {
                // Log error for debugging
                Woocommerce_Logs::log_order_creation('error', 
                    sprintf('Failed to create order from proposal #%d: %s', 
                        $proposal_id, $order_result->get_error_message()));
                
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
        // Enhanced logging for debugging
        Woocommerce_Logs::log_order_creation('info', 
            sprintf('Starting order creation from proposal #%d', $proposal_id));
        
        // Get proposal data using helper
        $proposal_data = Woocommerce_Biller_Helper::get_proposal_data($proposal_id);
        if (is_wp_error($proposal_data)) {
            Woocommerce_Logs::log_order_creation('error', 
                sprintf('Failed to get proposal data: %s', $proposal_data->get_error_message()));
            return $proposal_data;
        }
        
        $proposal = $proposal_data['proposal'];
        $line_items = $proposal_data['line_items'];
        $customer_id = $proposal_data['customer_id'];
        
        // Log proposal details
        Woocommerce_Logs::log_order_creation('info', 
            sprintf('Found proposal #%d: "%s" by customer #%d', 
                $proposal_id, $proposal->post_title, $customer_id));
        
        // Log line items structure
        Woocommerce_Logs::log_order_creation('debug', 
            sprintf('Line items structure for proposal #%d: %s', 
                $proposal_id, wp_json_encode($line_items)));
        
        // Create the order
        try {
            Woocommerce_Logs::log_order_creation('info', 
                sprintf('Creating WooCommerce order for customer #%d', $customer_id));
            
            $order = wc_create_order(array(
                'customer_id' => $customer_id,
                'status' => 'pending'
            ));
            
            if (is_wp_error($order)) {
                Woocommerce_Logs::log_order_creation('error', 
                    sprintf('Failed to create WooCommerce order: %s', $order->get_error_message()));
                return $order;
            }
            
            Woocommerce_Logs::log_order_creation('info', 
                sprintf('Created WooCommerce order #%d', $order->get_id()));
            
            $items_added = 0;
            
            // Add products to order (only non-subscription products)
            if (!empty($line_items['products'])) {
                Woocommerce_Logs::log_order_creation('info', 
                    sprintf('Processing %d products from proposal #%d', 
                        count($line_items['products']), $proposal_id));
                
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
                            
                            $items_added++;
                            
                            Woocommerce_Logs::log_order_creation('info', 
                                sprintf('Added product #%d (%s) x%d at %s to order #%d', 
                                    $product->get_id(), $product->get_name(), $quantity, 
                                    wc_price($price), $order->get_id()));
                        } else {
                            Woocommerce_Logs::log_order_creation('info', 
                                sprintf('Skipped product #%d (subscription or invalid)', 
                                    $item['product_id']));
                        }
                    }
                }
            }
            
            // Add one-time fees using helper
            if (!empty($line_items['one_time_fees'])) {
                $fees_added = Woocommerce_Biller_Helper::add_one_time_fees($order, $line_items['one_time_fees'], 'order_creation');
                $items_added += $fees_added;
            }
            
            // Add shipping fees using helper
            if (!empty($line_items['shipping_fees'])) {
                $shipping_added = Woocommerce_Biller_Helper::add_shipping_fees($order, $line_items['shipping_fees'], 'order_creation');
                $items_added += $shipping_added;
            }
            
            // Check if any items were added
            if ($items_added === 0) {
                Woocommerce_Logs::log_order_creation('warning', 
                    sprintf('No items added to order #%d from proposal #%d', 
                        $order->get_id(), $proposal_id));
            }
            
            // Calculate totals
            $order->calculate_totals();
            
            // Log order breakdown using helper
            Woocommerce_Biller_Helper::log_order_breakdown($order, 'order_creation', $items_added);
            
            // Add order note
            $order->add_order_note(
                sprintf(__('Order created from proposal #%d', 'arsol-pfw'), $proposal_id)
            );
            
            // Link order to proposal/project
            $order->update_meta_data('_arsol_proposal_id', $proposal_id);
            $order->save();
            
            Woocommerce_Logs::log_order_creation('info', 
                sprintf('Successfully created and saved order #%d from proposal #%d', 
                    $order->get_id(), $proposal_id));
            
            return $order->get_id();
            
        } catch (\Exception $e) {
            $error = new \WP_Error('order_creation_failed', $e->getMessage());
            Woocommerce_Logs::log_order_creation('error', 
                sprintf('Exception during order creation from proposal #%d: %s', 
                    $proposal_id, $e->getMessage()));
            return $error;
        }
    }
}
