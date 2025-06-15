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
 * @since 1.0.0
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
        // Add action hooks for proposal conversion
        add_action('wp_ajax_convert_proposal_to_order', array($this, 'ajax_convert_proposal_to_order'));
        add_action('wp_ajax_nopriv_convert_proposal_to_order', array($this, 'ajax_convert_proposal_to_order'));
        
        // Add meta box to proposal edit screen
        add_action('add_meta_boxes', array($this, 'add_conversion_meta_box'));
        
        // Add conversion button to proposal actions
        add_action('post_submitbox_misc_actions', array($this, 'add_conversion_button'));
    }
    
    /**
     * Declare HPOS compatibility
     */
    private function declare_hpos_compatibility() {
        add_action('before_woocommerce_init', function() {
            if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', ARSOL_PROJECTS_PLUGIN_FILE, true);
            }
        });
    }
    
    /**
     * Check if HPOS is enabled
     * 
     * @return bool
     */
    private function is_hpos_enabled() {
        if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil')) {
            return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
        }
        return false;
    }
    
    /**
     * Convert proposal to WooCommerce order and subscription
     * 
     * @param int $proposal_id The proposal ID
     * @return array Result with order_id, subscription_id, and status
     */
    public function convert_proposal_to_order($proposal_id) {
        try {
            // Validate proposal
            if (!$this->validate_proposal($proposal_id)) {
                throw new \Exception(__('Invalid proposal or proposal cannot be converted.', 'arsol-pfw'));
            }
            
            // Get proposal data
            $proposal_data = $this->get_proposal_data($proposal_id);
            
            // Step 1: Create parent order with all line items
            $parent_order = $this->create_parent_order($proposal_data);
            
            // Step 2: Create subscription if recurring items exist
            $subscription = $this->create_subscription_if_needed($parent_order, $proposal_data);
            
            // Update proposal status
            $this->update_proposal_status($proposal_id, 'converted', array(
                'order_id' => $parent_order->get_id(),
                'subscription_id' => $subscription ? $subscription->get_id() : null
            ));
            
            return array(
                'success' => true,
                'order_id' => $parent_order->get_id(),
                'subscription_id' => $subscription ? $subscription->get_id() : null,
                'message' => __('Proposal successfully converted to order.', 'arsol-pfw')
            );
            
        } catch (\Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }
    
    /**
     * Create parent order from proposal data
     * 
     * @param array $proposal_data
     * @return \WC_Order
     */
    private function create_parent_order($proposal_data) {
        // Create order using modern WC function
        $order = wc_create_order(array(
            'status' => 'pending',
            'customer_id' => $proposal_data['customer_id'],
            'created_via' => 'proposal_conversion'
        ));
        
        // Add project reference
        if (!empty($proposal_data['project_id'])) {
            $order->update_meta_data(Woocommerce::PROJECT_META_KEY, $proposal_data['project_id']);
        }
        
        // Add proposal reference
        $order->update_meta_data('_arsol_proposal_id', $proposal_data['proposal_id']);
        
        // Add all line items to parent order
        $this->add_products_to_order($order, $proposal_data['line_items']['products']);
        $this->add_fees_to_order($order, $proposal_data['line_items']['onetime_fees']);
        $this->add_fees_to_order($order, $proposal_data['line_items']['recurring_fees']);
        $this->add_shipping_to_order($order, $proposal_data['line_items']['shipping_fees']);
        
        // Set addresses
        $this->set_order_addresses($order, $proposal_data);
        
        // Set currency if specified
        if (!empty($proposal_data['currency'])) {
            $order->set_currency($proposal_data['currency']);
        }
        
        // Calculate totals and save
        $order->calculate_totals();
        $order->save();
        
        return $order;
    }
    
    /**
     * Create subscription if recurring items exist
     * 
     * @param \WC_Order $parent_order
     * @param array $proposal_data
     * @return \WC_Subscription|null
     */
    private function create_subscription_if_needed($parent_order, $proposal_data) {
        // Check if we have recurring items
        if (!$this->has_recurring_items($proposal_data['line_items'])) {
            return null;
        }
        
        // Determine billing schedule
        $billing_schedule = $this->determine_billing_schedule($proposal_data['line_items']);
        
        // Create subscription using WC Subscriptions function
        if (function_exists('wcs_create_subscription')) {
            $subscription = wcs_create_subscription(array(
                'order_id' => $parent_order->get_id(),
                'status' => 'pending',
                'billing_period' => $billing_schedule['period'],
                'billing_interval' => $billing_schedule['interval']
            ));
        } else {
            // Fallback to manual subscription creation
            $subscription = new \WC_Subscription();
            $subscription->set_parent_id($parent_order->get_id());
            $subscription->set_status('pending');
        }
        
        // Add only recurring items to subscription
        $this->add_recurring_products_to_subscription($subscription, $proposal_data['line_items']['products']);
        $this->add_recurring_fees_to_subscription($subscription, $proposal_data['line_items']['recurring_fees']);
        
        // Copy addresses from parent order
        $subscription->set_billing_address($parent_order->get_billing());
        $subscription->set_shipping_address($parent_order->get_shipping());
        
        // Set currency
        if (!empty($proposal_data['currency'])) {
            $subscription->set_currency($proposal_data['currency']);
        }
        
        // Set next payment date
        $next_payment = $this->calculate_next_payment_date($billing_schedule);
        $subscription->set_date('next_payment', $next_payment);
        
        // Add project and proposal references
        if (!empty($proposal_data['project_id'])) {
            $subscription->update_meta_data(Woocommerce::PROJECT_META_KEY, $proposal_data['project_id']);
        }
        $subscription->update_meta_data('_arsol_proposal_id', $proposal_data['proposal_id']);
        
        // Calculate and save
        $subscription->calculate_totals();
        $subscription->save();
        
        return $subscription;
    }
    
    /**
     * Add products to order using HPOS-compatible methods
     * 
     * @param \WC_Order $order
     * @param array $products
     */
    private function add_products_to_order($order, $products) {
        foreach ($products as $product_data) {
            $product = wc_get_product($product_data['product_id']);
            
            if (!$product) {
                continue;
            }
            
            $item = $order->add_product($product, $product_data['quantity']);
            
            // Set custom price if specified
            if (isset($product_data['custom_price']) && $product_data['custom_price'] > 0) {
                $total = $product_data['custom_price'] * $product_data['quantity'];
                $item->set_subtotal($total);
                $item->set_total($total);
            }
            
            // Add start date for subscription products
            if ($product_data['product_type'] === 'subscription' && !empty($product_data['start_date'])) {
                $item->add_meta_data('_subscription_start_date', $product_data['start_date']);
            }
        }
    }
    
    /**
     * Add fees to order using modern WC_Order_Item_Fee
     * 
     * @param \WC_Order $order
     * @param array $fees
     */
    private function add_fees_to_order($order, $fees) {
        foreach ($fees as $fee_data) {
            $fee = new \WC_Order_Item_Fee();
            $fee->set_name($fee_data['name']);
            $fee->set_amount($fee_data['amount']);
            $fee->set_total($fee_data['amount']);
            
            // Add fee type meta for tracking
            $fee->add_meta_data('_fee_type', $fee_data['type'] ?? 'general');
            
            $order->add_item($fee);
        }
    }
    
    /**
     * Add shipping to order using modern WC_Order_Item_Shipping
     * 
     * @param \WC_Order $order
     * @param array $shipping_fees
     */
    private function add_shipping_to_order($order, $shipping_fees) {
        foreach ($shipping_fees as $shipping_data) {
            $shipping = new \WC_Order_Item_Shipping();
            $shipping->set_method_title($shipping_data['method_title']);
            $shipping->set_method_id($shipping_data['method_id']);
            $shipping->set_total($shipping_data['cost']);
            
            $order->add_item($shipping);
        }
    }
    
    /**
     * Set order addresses using CRUD methods
     * 
     * @param \WC_Order $order
     * @param array $proposal_data
     */
    private function set_order_addresses($order, $proposal_data) {
        $customer_id = $proposal_data['customer_id'];
        
        if ($customer_id) {
            $customer = new \WC_Customer($customer_id);
            
            // Set billing address
            $billing = $customer->get_billing();
            if (!empty($billing)) {
                $order->set_billing_address($billing);
            }
            
            // Set shipping address
            $shipping = $customer->get_shipping();
            if (!empty($shipping)) {
                $order->set_shipping_address($shipping);
            } else {
                // Use billing as shipping if no shipping address
                $order->set_shipping_address($billing);
            }
        }
    }
    
    /**
     * Check if proposal has recurring items
     * 
     * @param array $line_items
     * @return bool
     */
    private function has_recurring_items($line_items) {
        // Check for subscription products
        foreach ($line_items['products'] as $product_data) {
            if (in_array($product_data['product_type'], ['subscription', 'subscription_variation'])) {
                return true;
            }
        }
        
        // Check for recurring fees
        if (!empty($line_items['recurring_fees'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Determine billing schedule from line items
     * 
     * @param array $line_items
     * @return array
     */
    private function determine_billing_schedule($line_items) {
        // Default schedule
        $schedule = array(
            'period' => 'month',
            'interval' => 1
        );
        
        // Check subscription products for billing schedule
        foreach ($line_items['products'] as $product_data) {
            if (in_array($product_data['product_type'], ['subscription', 'subscription_variation'])) {
                $product = wc_get_product($product_data['product_id']);
                
                if ($product && method_exists($product, 'get_billing_period')) {
                    $schedule['period'] = $product->get_billing_period();
                    $schedule['interval'] = $product->get_billing_interval();
                    break; // Use first subscription product's schedule
                }
            }
        }
        
        return $schedule;
    }
    
    /**
     * Calculate next payment date
     * 
     * @param array $billing_schedule
     * @return string
     */
    private function calculate_next_payment_date($billing_schedule) {
        $interval = $billing_schedule['interval'];
        $period = $billing_schedule['period'];
        
        $next_payment = new \DateTime();
        
        switch ($period) {
            case 'day':
                $next_payment->add(new \DateInterval("P{$interval}D"));
                break;
            case 'week':
                $weeks = $interval * 7;
                $next_payment->add(new \DateInterval("P{$weeks}D"));
                break;
            case 'month':
                $next_payment->add(new \DateInterval("P{$interval}M"));
                break;
            case 'year':
                $next_payment->add(new \DateInterval("P{$interval}Y"));
                break;
        }
        
        return $next_payment->format('Y-m-d H:i:s');
    }
    
    /**
     * Add recurring products to subscription
     * 
     * @param \WC_Subscription $subscription
     * @param array $products
     */
    private function add_recurring_products_to_subscription($subscription, $products) {
        foreach ($products as $product_data) {
            // Only add subscription products to subscription
            if (!in_array($product_data['product_type'], ['subscription', 'subscription_variation'])) {
                continue;
            }
            
            $product = wc_get_product($product_data['product_id']);
            
            if (!$product) {
                continue;
            }
            
            $item = $subscription->add_product($product, $product_data['quantity']);
            
            // Set custom price if specified
            if (isset($product_data['custom_price']) && $product_data['custom_price'] > 0) {
                $total = $product_data['custom_price'] * $product_data['quantity'];
                $item->set_subtotal($total);
                $item->set_total($total);
            }
        }
    }
    
    /**
     * Add recurring fees to subscription
     * 
     * @param \WC_Subscription $subscription
     * @param array $recurring_fees
     */
    private function add_recurring_fees_to_subscription($subscription, $recurring_fees) {
        foreach ($recurring_fees as $fee_data) {
            $fee = new \WC_Order_Item_Fee();
            $fee->set_name($fee_data['name']);
            $fee->set_amount($fee_data['amount']);
            $fee->set_total($fee_data['amount']);
            $fee->add_meta_data('_fee_type', 'recurring');
            
            $subscription->add_item($fee);
        }
    }
    
    /**
     * Get proposal data for conversion
     * 
     * @param int $proposal_id
     * @return array
     */
    private function get_proposal_data($proposal_id) {
        $line_items = get_post_meta($proposal_id, '_arsol_proposal_line_items', true);
        $customer_id = get_post_meta($proposal_id, '_arsol_proposal_customer_id', true);
        $project_id = get_post_meta($proposal_id, '_arsol_proposal_project_id', true);
        $currency = get_post_meta($proposal_id, '_arsol_proposal_currency', true);
        
        return array(
            'proposal_id' => $proposal_id,
            'customer_id' => $customer_id,
            'project_id' => $project_id,
            'currency' => $currency ?: get_woocommerce_currency(),
            'line_items' => $line_items ?: array(
                'products' => array(),
                'onetime_fees' => array(),
                'recurring_fees' => array(),
                'shipping_fees' => array()
            )
        );
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
        if (!$proposal || $proposal->post_type !== 'project-proposal') {
            return false;
        }
        
        // Check if already converted
        $converted_order_id = get_post_meta($proposal_id, '_arsol_converted_order_id', true);
        if (!empty($converted_order_id)) {
            return false;
        }
        
        // Check if has line items
        $line_items = get_post_meta($proposal_id, '_arsol_proposal_line_items', true);
        if (empty($line_items) || empty($line_items['products'])) {
            return false;
        }
        
        // Check if has customer
        $customer_id = get_post_meta($proposal_id, '_arsol_proposal_customer_id', true);
        if (empty($customer_id)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Update proposal status after conversion
     * 
     * @param int $proposal_id
     * @param string $status
     * @param array $conversion_data
     */
    private function update_proposal_status($proposal_id, $status, $conversion_data = array()) {
        // Update proposal status
        wp_update_post(array(
            'ID' => $proposal_id,
            'post_status' => $status
        ));
        
        // Store conversion data
        if (!empty($conversion_data['order_id'])) {
            update_post_meta($proposal_id, '_arsol_converted_order_id', $conversion_data['order_id']);
        }
        
        if (!empty($conversion_data['subscription_id'])) {
            update_post_meta($proposal_id, '_arsol_converted_subscription_id', $conversion_data['subscription_id']);
        }
        
        // Store conversion timestamp
        update_post_meta($proposal_id, '_arsol_conversion_date', current_time('mysql'));
    }
    
    /**
     * AJAX handler for proposal conversion
     */
    public function ajax_convert_proposal_to_order() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'convert_proposal_nonce')) {
            wp_die(__('Security check failed.', 'arsol-pfw'));
        }
        
        // Check permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to perform this action.', 'arsol-pfw'));
        }
        
        $proposal_id = intval($_POST['proposal_id']);
        
        $result = $this->convert_proposal_to_order($proposal_id);
        
        wp_send_json($result);
    }
    
    /**
     * Add conversion meta box to proposal edit screen
     */
    public function add_conversion_meta_box() {
        add_meta_box(
            'arsol-proposal-conversion',
            __('Convert to Order', 'arsol-pfw'),
            array($this, 'render_conversion_meta_box'),
            'project-proposal',
            'side',
            'high'
        );
    }
    
    /**
     * Render conversion meta box
     * 
     * @param \WP_Post $post
     */
    public function render_conversion_meta_box($post) {
        // Check if already converted
        $converted_order_id = get_post_meta($post->ID, '_arsol_converted_order_id', true);
        
        if ($converted_order_id) {
            $order_edit_url = admin_url('post.php?post=' . $converted_order_id . '&action=edit');
            echo '<p>' . __('This proposal has been converted to order:', 'arsol-pfw') . '</p>';
            echo '<p><a href="' . esc_url($order_edit_url) . '" class="button">' . sprintf(__('View Order #%s', 'arsol-pfw'), $converted_order_id) . '</a></p>';
            return;
        }
        
        // Check if can be converted
        if (!$this->validate_proposal($post->ID)) {
            echo '<p>' . __('This proposal cannot be converted. Please ensure it has line items and a customer assigned.', 'arsol-pfw') . '</p>';
            return;
        }
        
        wp_nonce_field('convert_proposal_nonce', 'convert_proposal_nonce');
        ?>
        <p><?php _e('Convert this proposal to a WooCommerce order and subscription (if applicable).', 'arsol-pfw'); ?></p>
        <button type="button" id="convert-proposal-btn" class="button button-primary">
            <?php _e('Convert to Order', 'arsol-pfw'); ?>
        </button>
        <div id="conversion-result" style="margin-top: 10px;"></div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#convert-proposal-btn').on('click', function() {
                var $btn = $(this);
                var $result = $('#conversion-result');
                
                $btn.prop('disabled', true).text('<?php _e('Converting...', 'arsol-pfw'); ?>');
                $result.html('');
                
                $.post(ajaxurl, {
                    action: 'convert_proposal_to_order',
                    proposal_id: <?php echo $post->ID; ?>,
                    nonce: $('#convert_proposal_nonce').val()
                }, function(response) {
                    if (response.success) {
                        $result.html('<div class="notice notice-success"><p>' + response.message + '</p></div>');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $result.html('<div class="notice notice-error"><p>' + response.message + '</p></div>');
                        $btn.prop('disabled', false).text('<?php _e('Convert to Order', 'arsol-pfw'); ?>');
                    }
                }).fail(function() {
                    $result.html('<div class="notice notice-error"><p><?php _e('An error occurred during conversion.', 'arsol-pfw'); ?></p></div>');
                    $btn.prop('disabled', false).text('<?php _e('Convert to Order', 'arsol-pfw'); ?>');
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Add conversion button to proposal actions
     * 
     * @param \WP_Post $post
     */
    public function add_conversion_button($post) {
        if ($post->post_type !== 'project-proposal') {
            return;
        }
        
        // Check if already converted
        $converted_order_id = get_post_meta($post->ID, '_arsol_converted_order_id', true);
        if ($converted_order_id) {
            return;
        }
        
        // Check if can be converted
        if (!$this->validate_proposal($post->ID)) {
            return;
        }
        
        ?>
        <div class="misc-pub-section">
            <span><?php _e('Conversion:', 'arsol-pfw'); ?></span>
            <button type="button" class="button button-small" onclick="jQuery('#convert-proposal-btn').click();">
                <?php _e('Convert to Order', 'arsol-pfw'); ?>
            </button>
        </div>
        <?php
    }
} 