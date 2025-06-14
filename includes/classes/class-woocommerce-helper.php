<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Helper Class
 * 
 * Contains WooCommerce-related utility functions for the Arsol Projects for Woo plugin
 * including customer management, dropdown generation, and WooCommerce integrations
 */
class Woocommerce_Helper {
    
    /**
     * Get WooCommerce customers with first + last name display
     * 
     * @param array $additional_args Additional arguments for get_users()
     * @return array Array of customer objects with formatted display names
     */
    public static function get_customers($additional_args = array()) {
        $default_args = array(
            'role__in' => array('customer', 'subscriber'),
            'fields' => array('ID', 'user_login', 'user_email'),
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'first_name',
                    'value' => '',
                    'compare' => '!='
                ),
                array(
                    'key' => 'last_name',
                    'value' => '',
                    'compare' => '!='
                )
            ),
            'orderby' => 'meta_value',
            'meta_key' => 'first_name'
        );
        
        $args = array_merge($default_args, $additional_args);
        $users = get_users($args);
        
        $customers = array();
        
        foreach ($users as $user) {
            $customers[] = (object) array(
                'ID' => $user->ID,
                'user_login' => $user->user_login,
                'user_email' => $user->user_email,
                'display_name' => Helper::format_user_display_name($user->ID, true)
            );
        }
        
        return $customers;
    }
    
    /**
     * Generate customer dropdown HTML
     * 
     * @param string $name Field name
     * @param int|string $selected Selected value
     * @param array $attributes Additional HTML attributes
     * @return string HTML dropdown
     */
    public static function generate_customer_dropdown($name, $selected = '', $attributes = array()) {
        $default_attributes = array(
            'id' => 'filter-by-customer',
            'class' => 'wc-customer-search select2-hidden-accessible enhanced',
            'data-placeholder' => __('Filter by customer', 'arsol-pfw'),
            'data-allow_clear' => 'true'
        );
        
        $attributes = array_merge($default_attributes, $attributes);
        $customers = self::get_customers();
        
        return Helper::generate_user_dropdown(
            $name, 
            $customers, 
            $selected, 
            __('Filter by customer', 'arsol-pfw'), 
            $attributes
        );
    }
    
    /**
     * Generate customer dropdown for registered customers
     * 
     * @param string $name Field name
     * @param int|string $selected Selected value
     * @param array $attributes Additional HTML attributes
     * @return string HTML dropdown
     */
    public static function generate_registered_customer_dropdown($name, $selected = '', $attributes = array()) {
        $default_attributes = array(
            'class' => 'arsol-pfw-admin-select2',
            'data-placeholder' => __('Filter by registered customer', 'arsol-pfw'),
            'data-allow_clear' => 'true'
        );
        
        $attributes = array_merge($default_attributes, $attributes);
        $customers = self::get_customers();
        
        return Helper::generate_user_dropdown(
            $name, 
            $customers, 
            $selected, 
            __('Filter by registered customer', 'arsol-pfw'), 
            $attributes
        );
    }
    
    /**
     * Get customer billing details
     * 
     * @param int $customer_id Customer user ID
     * @return array Customer billing details
     */
    public static function get_customer_billing_details($customer_id) {
        $billing_details = array();
        
        $billing_fields = array(
            'billing_first_name',
            'billing_last_name',
            'billing_company',
            'billing_address_1',
            'billing_address_2',
            'billing_city',
            'billing_state',
            'billing_postcode',
            'billing_country',
            'billing_email',
            'billing_phone'
        );
        
        foreach ($billing_fields as $field) {
            $billing_details[$field] = get_user_meta($customer_id, $field, true);
        }
        
        return $billing_details;
    }
    
    /**
     * Get customer shipping details
     * 
     * @param int $customer_id Customer user ID
     * @return array Customer shipping details
     */
    public static function get_customer_shipping_details($customer_id) {
        $shipping_details = array();
        
        $shipping_fields = array(
            'shipping_first_name',
            'shipping_last_name',
            'shipping_company',
            'shipping_address_1',
            'shipping_address_2',
            'shipping_city',
            'shipping_state',
            'shipping_postcode',
            'shipping_country'
        );
        
        foreach ($shipping_fields as $field) {
            $shipping_details[$field] = get_user_meta($customer_id, $field, true);
        }
        
        return $shipping_details;
    }
    
    /**
     * Get customer orders
     * 
     * @param int $customer_id Customer user ID
     * @param array $args Additional arguments for wc_get_orders()
     * @return array Array of WC_Order objects
     */
    public static function get_customer_orders($customer_id, $args = array()) {
        if (!Helper::is_woocommerce_active()) {
            return array();
        }
        
        $default_args = array(
            'customer' => $customer_id,
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $args = array_merge($default_args, $args);
        
        return wc_get_orders($args);
    }
    
    /**
     * Get customer subscriptions
     * 
     * @param int $customer_id Customer user ID
     * @param array $args Additional arguments for wcs_get_subscriptions()
     * @return array Array of WC_Subscription objects
     */
    public static function get_customer_subscriptions($customer_id, $args = array()) {
        if (!Helper::is_woocommerce_active() || !function_exists('wcs_get_subscriptions')) {
            return array();
        }
        
        $default_args = array(
            'customer_id' => $customer_id,
            'subscriptions_per_page' => -1,
            'orderby' => 'start_date',
            'order' => 'DESC'
        );
        
        $args = array_merge($default_args, $args);
        
        return wcs_get_subscriptions($args);
    }
    
    /**
     * Check if customer has orders
     * 
     * @param int $customer_id Customer user ID
     * @return bool True if customer has orders
     */
    public static function customer_has_orders($customer_id) {
        $orders = self::get_customer_orders($customer_id, array('limit' => 1));
        return !empty($orders);
    }
    
    /**
     * Check if customer has subscriptions
     * 
     * @param int $customer_id Customer user ID
     * @return bool True if customer has subscriptions
     */
    public static function customer_has_subscriptions($customer_id) {
        $subscriptions = self::get_customer_subscriptions($customer_id, array('subscriptions_per_page' => 1));
        return !empty($subscriptions);
    }
    
    /**
     * Get customer's total spent
     * 
     * @param int $customer_id Customer user ID
     * @return float Total amount spent
     */
    public static function get_customer_total_spent($customer_id) {
        if (!Helper::is_woocommerce_active()) {
            return 0;
        }
        
        $customer = new \WC_Customer($customer_id);
        return $customer->get_total_spent();
    }
    
    /**
     * Get customer's order count
     * 
     * @param int $customer_id Customer user ID
     * @return int Number of orders
     */
    public static function get_customer_order_count($customer_id) {
        if (!Helper::is_woocommerce_active()) {
            return 0;
        }
        
        $customer = new \WC_Customer($customer_id);
        return $customer->get_order_count();
    }
    
    /**
     * Format price with WooCommerce currency
     * 
     * @param float $price Price to format
     * @param array $args Additional arguments for wc_price()
     * @return string Formatted price
     */
    public static function format_price($price, $args = array()) {
        if (!Helper::is_woocommerce_active()) {
            return number_format($price, 2);
        }
        
        return wc_price($price, $args);
    }
    
    /**
     * Get WooCommerce currency symbol
     * 
     * @return string Currency symbol
     */
    public static function get_currency_symbol() {
        if (!Helper::is_woocommerce_active()) {
            return '$';
        }
        
        return get_woocommerce_currency_symbol();
    }
    
    /**
     * Get WooCommerce currency code
     * 
     * @return string Currency code
     */
    public static function get_currency_code() {
        if (!Helper::is_woocommerce_active()) {
            return 'USD';
        }
        
        return get_woocommerce_currency();
    }
    
    /**
     * Check if WooCommerce Subscriptions is active
     * 
     * @return bool True if WooCommerce Subscriptions is active
     */
    public static function is_subscriptions_active() {
        return class_exists('WC_Subscriptions') && function_exists('wcs_create_subscription');
    }
    
    /**
     * Get tax classes
     * 
     * @return array Array of tax classes
     */
    public static function get_tax_classes() {
        if (!Helper::is_woocommerce_active()) {
            return array();
        }
        
        $tax_classes = array(
            '' => __('Standard', 'arsol-pfw'),
            'no-tax' => __('No Tax', 'arsol-pfw'),
            'none' => __('None', 'arsol-pfw')
        );
        
        // Get additional tax classes
        $additional_classes = \WC_Tax::get_tax_classes();
        foreach ($additional_classes as $class) {
            $tax_classes[sanitize_title($class)] = $class;
        }
        
        return $tax_classes;
    }
    
    /**
     * Validate WooCommerce product ID
     * 
     * @param int $product_id Product ID to validate
     * @return bool True if valid product
     */
    public static function is_valid_product($product_id) {
        if (!Helper::is_woocommerce_active()) {
            return false;
        }
        
        $product = wc_get_product($product_id);
        return $product && $product->exists();
    }
    
    /**
     * Check if product is a subscription
     * 
     * @param int $product_id Product ID
     * @return bool True if product is a subscription
     */
    public static function is_subscription_product($product_id) {
        if (!self::is_subscriptions_active()) {
            return false;
        }
        
        $product = wc_get_product($product_id);
        return $product && \WC_Subscriptions_Product::is_subscription($product);
    }
}
