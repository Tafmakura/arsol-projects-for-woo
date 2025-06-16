<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Logs Class
 * 
 * Centralized logging functionality with configurable debug options
 */
class Woocommerce_Logs {
    
    /**
     * Log sources for different components
     */
    const LOG_SOURCES = array(
        'conversion' => 'arsol-pfw-conversion',
        'order_creation' => 'arsol-pfw-order-creation',
        'subscription_creation' => 'arsol-pfw-subscription-creation',
        'checkout' => 'arsol-pfw-checkout',
        'general' => 'arsol-pfw-general'
    );
    
    /**
     * Debug options setting key
     */
    const DEBUG_OPTIONS_KEY = 'arsol_pfw_debug_options';
    
    /**
     * Get debug options from settings
     * 
     * @return array Debug options
     */
    public static function get_debug_options() {
        return get_option(self::DEBUG_OPTIONS_KEY, array());
    }
    
    /**
     * Check if a specific debug option is enabled
     * 
     * @param string $option The debug option to check
     * @return bool True if enabled, false otherwise
     */
    public static function is_debug_enabled($option) {
        $debug_options = self::get_debug_options();
        return !empty($debug_options[$option]);
    }
    
    /**
     * Log a message if the corresponding debug option is enabled
     * 
     * @param string $level Log level (info, warning, error, debug)
     * @param string $message Log message
     * @param string $source Log source/component
     * @param string $debug_option Debug option to check (optional)
     */
    public static function log($level, $message, $source = 'general', $debug_option = null) {
        // If debug option is specified, check if it's enabled
        if ($debug_option && !self::is_debug_enabled($debug_option)) {
            return;
        }
        
        // Get WooCommerce logger
        if (!function_exists('wc_get_logger')) {
            return;
        }
        
        $logger = wc_get_logger();
        $log_source = isset(self::LOG_SOURCES[$source]) ? self::LOG_SOURCES[$source] : self::LOG_SOURCES['general'];
        
        // Log the message
        switch ($level) {
            case 'error':
                $logger->error($message, array('source' => $log_source));
                break;
            case 'warning':
                $logger->warning($message, array('source' => $log_source));
                break;
            case 'debug':
                $logger->debug($message, array('source' => $log_source));
                break;
            case 'info':
            default:
                $logger->info($message, array('source' => $log_source));
                break;
        }
    }
    
    /**
     * Log conversion-related messages
     * 
     * @param string $level Log level
     * @param string $message Log message
     */
    public static function log_conversion($level, $message) {
        self::log($level, $message, 'conversion', 'enable_conversion_logs');
    }
    
    /**
     * Log order creation messages
     * 
     * @param string $level Log level
     * @param string $message Log message
     */
    public static function log_order_creation($level, $message) {
        self::log($level, $message, 'order_creation', 'enable_order_creation_logs');
    }
    
    /**
     * Log subscription creation messages
     * 
     * @param string $level Log level
     * @param string $message Log message
     */
    public static function log_subscription_creation($level, $message) {
        self::log($level, $message, 'subscription_creation', 'enable_subscription_creation_logs');
    }
    
    /**
     * Log checkout-related messages
     * 
     * @param string $level Log level
     * @param string $message Log message
     */
    public static function log_checkout($level, $message) {
        self::log($level, $message, 'checkout', 'enable_checkout_logs');
    }
    
    /**
     * Log general messages
     * 
     * @param string $level Log level
     * @param string $message Log message
     */
    public static function log_general($level, $message) {
        self::log($level, $message, 'general', 'enable_general_logs');
    }
    
    /**
     * Get all available debug options
     * 
     * @return array Debug options with labels
     */
    public static function get_available_debug_options() {
        return array(
            'enable_conversion_logs' => __('Proposal to Project Conversion', 'arsol-pfw'),
            'enable_order_creation_logs' => __('Order Creation', 'arsol-pfw'),
            'enable_subscription_creation_logs' => __('Subscription Creation', 'arsol-pfw'),
            'enable_checkout_logs' => __('Checkout Process', 'arsol-pfw'),
            'enable_general_logs' => __('General Operations', 'arsol-pfw'),
        );
    }
    
    /**
     * Clear all logs for this plugin
     */
    public static function clear_logs() {
        if (!function_exists('wc_get_logger')) {
            return false;
        }
        
        $logger = wc_get_logger();
        
        // Clear logs for each source
        foreach (self::LOG_SOURCES as $source) {
            $logger->clear($source);
        }
        
        return true;
    }
    
    /**
     * Get log file paths for download/viewing
     * 
     * @return array Log file information
     */
    public static function get_log_files() {
        $log_files = array();
        
        if (!function_exists('wc_get_log_file_path')) {
            return $log_files;
        }
        
        foreach (self::LOG_SOURCES as $key => $source) {
            $log_file_path = wc_get_log_file_path($source);
            if (file_exists($log_file_path)) {
                $log_files[$key] = array(
                    'name' => ucwords(str_replace('_', ' ', $key)),
                    'source' => $source,
                    'path' => $log_file_path,
                    'size' => filesize($log_file_path),
                    'modified' => filemtime($log_file_path)
                );
            }
        }
        
        return $log_files;
    }
    
    /**
     * Debug function to test proposal conversion
     * 
     * @param int $proposal_id The proposal ID to test
     * @return array Debug information
     */
    public static function debug_proposal_conversion($proposal_id) {
        $debug_info = array();
        
        self::log_conversion('info', sprintf('Starting debug analysis for proposal #%d', $proposal_id));
        
        // Check proposal exists
        $proposal = get_post($proposal_id);
        $debug_info['proposal_exists'] = !empty($proposal);
        $debug_info['proposal_type'] = $proposal ? $proposal->post_type : 'N/A';
        $debug_info['proposal_status'] = $proposal ? $proposal->post_status : 'N/A';
        $debug_info['proposal_author'] = $proposal ? $proposal->post_author : 'N/A';
        
        // Check cost proposal type
        $cost_proposal_type = get_post_meta($proposal_id, '_cost_proposal_type', true);
        $debug_info['cost_proposal_type'] = $cost_proposal_type;
        $debug_info['should_create_orders'] = ($cost_proposal_type === 'quotation_line_items');
        
        // Check line items
        $line_items = get_post_meta($proposal_id, '_arsol_proposal_line_items', true);
        $debug_info['has_line_items'] = !empty($line_items);
        $debug_info['line_items_structure'] = !empty($line_items) ? array_keys($line_items) : array();
        
        if (!empty($line_items)) {
            $debug_info['products_count'] = !empty($line_items['products']) ? count($line_items['products']) : 0;
            $debug_info['one_time_fees_count'] = !empty($line_items['one_time_fees']) ? count($line_items['one_time_fees']) : 0;
            $debug_info['recurring_fees_count'] = !empty($line_items['recurring_fees']) ? count($line_items['recurring_fees']) : 0;
            $debug_info['shipping_fees_count'] = !empty($line_items['shipping_fees']) ? count($line_items['shipping_fees']) : 0;
        }
        
        // Check if customer exists
        if ($proposal) {
            $customer = new \WC_Customer($proposal->post_author);
            $debug_info['customer_exists'] = $customer && $customer->get_id();
            $debug_info['customer_email'] = $customer ? $customer->get_billing_email() : 'N/A';
        }
        
        // Check WooCommerce Subscriptions
        $debug_info['wc_subscriptions_active'] = class_exists('WC_Subscriptions') && function_exists('wcs_create_subscription');
        
        self::log_conversion('info', sprintf('Debug analysis complete for proposal #%d: %s', $proposal_id, wp_json_encode($debug_info)));
        
        return $debug_info;
    }
    
    /**
     * Debug function to test proposal conversion with detailed logging
     * 
     * @param int $proposal_id The proposal ID to test
     * @return array Debug information
     */
    public static function debug_proposal_conversion_detailed($proposal_id) {
        $debug_info = array();
        
        self::log_conversion('info', sprintf('=== DETAILED DEBUG ANALYSIS FOR PROPOSAL #%d ===', $proposal_id));
        
        // Check proposal exists
        $proposal = get_post($proposal_id);
        $debug_info['proposal_exists'] = !empty($proposal);
        $debug_info['proposal_type'] = $proposal ? $proposal->post_type : 'N/A';
        $debug_info['proposal_status'] = $proposal ? $proposal->post_status : 'N/A';
        $debug_info['proposal_author'] = $proposal ? $proposal->post_author : 'N/A';
        
        self::log_conversion('info', sprintf('Proposal exists: %s, Type: %s, Status: %s, Author: %s', 
            $debug_info['proposal_exists'] ? 'YES' : 'NO',
            $debug_info['proposal_type'],
            $debug_info['proposal_status'],
            $debug_info['proposal_author']));
        
        // Check cost proposal type
        $cost_proposal_type = get_post_meta($proposal_id, '_cost_proposal_type', true);
        $debug_info['cost_proposal_type'] = $cost_proposal_type;
        $debug_info['should_create_orders'] = ($cost_proposal_type === 'quotation_line_items');
        
        self::log_conversion('info', sprintf('Cost proposal type: %s, Should create orders: %s', 
            $cost_proposal_type, $debug_info['should_create_orders'] ? 'YES' : 'NO'));
        
        // Check line items in detail
        $line_items = get_post_meta($proposal_id, '_arsol_proposal_line_items', true);
        $debug_info['has_line_items'] = !empty($line_items);
        
        if (!empty($line_items)) {
            $debug_info['line_items_structure'] = array_keys($line_items);
            
            // Analyze each section
            foreach (['products', 'one_time_fees', 'recurring_fees', 'shipping_fees'] as $section) {
                if (!empty($line_items[$section])) {
                    $debug_info[$section . '_count'] = count($line_items[$section]);
                    $debug_info[$section . '_details'] = array();
                    
                    foreach ($line_items[$section] as $key => $item) {
                        $item_debug = array('key' => $key);
                        
                        if ($section === 'products') {
                            $item_debug['product_id'] = $item['product_id'] ?? 'missing';
                            $item_debug['quantity'] = $item['quantity'] ?? 'missing';
                            $item_debug['price'] = $item['price'] ?? 'missing';
                            $item_debug['sale_price'] = $item['sale_price'] ?? 'empty';
                            
                            if (!empty($item['product_id'])) {
                                $product = wc_get_product($item['product_id']);
                                $item_debug['product_exists'] = !empty($product);
                                $item_debug['product_type'] = $product ? $product->get_type() : 'N/A';
                                $item_debug['is_subscription'] = $product ? $product->is_type(array('subscription', 'subscription_variation')) : false;
                            }
                        } elseif ($section === 'one_time_fees') {
                            $item_debug['name'] = $item['name'] ?? 'empty';
                            $item_debug['amount'] = $item['amount'] ?? 'missing';
                            $item_debug['tax_class'] = $item['tax_class'] ?? 'missing';
                            $item_debug['amount_valid'] = (!empty($item['amount']) && floatval($item['amount']) > 0);
                        } elseif ($section === 'recurring_fees') {
                            $item_debug['name'] = $item['name'] ?? 'empty';
                            $item_debug['amount'] = $item['amount'] ?? 'missing';
                            $item_debug['interval'] = $item['interval'] ?? 'missing';
                            $item_debug['period'] = $item['period'] ?? 'missing';
                            $item_debug['amount_valid'] = (!empty($item['amount']) && floatval($item['amount']) > 0);
                        } elseif ($section === 'shipping_fees') {
                            $item_debug['description'] = $item['description'] ?? 'empty';
                            $item_debug['amount'] = $item['amount'] ?? 'missing';
                            $item_debug['amount_valid'] = (!empty($item['amount']) && floatval($item['amount']) > 0);
                        }
                        
                        $debug_info[$section . '_details'][] = $item_debug;
                    }
                    
                    self::log_conversion('info', sprintf('%s (%d items): %s', 
                        ucwords(str_replace('_', ' ', $section)), 
                        $debug_info[$section . '_count'],
                        wp_json_encode($debug_info[$section . '_details'])));
                } else {
                    $debug_info[$section . '_count'] = 0;
                    self::log_conversion('info', sprintf('%s: NONE', ucwords(str_replace('_', ' ', $section))));
                }
            }
        } else {
            self::log_conversion('error', 'NO LINE ITEMS FOUND');
        }
        
        // Check if customer exists
        if ($proposal) {
            $customer = new \WC_Customer($proposal->post_author);
            $debug_info['customer_exists'] = $customer && $customer->get_id();
            $debug_info['customer_email'] = $customer ? $customer->get_billing_email() : 'N/A';
            
            self::log_conversion('info', sprintf('Customer #%d exists: %s, Email: %s', 
                $proposal->post_author, 
                $debug_info['customer_exists'] ? 'YES' : 'NO',
                $debug_info['customer_email']));
        }
        
        // Check WooCommerce Subscriptions
        $debug_info['wc_subscriptions_active'] = class_exists('WC_Subscriptions') && function_exists('wcs_create_subscription');
        self::log_conversion('info', sprintf('WooCommerce Subscriptions active: %s', 
            $debug_info['wc_subscriptions_active'] ? 'YES' : 'NO'));
        
        self::log_conversion('info', '=== END DETAILED DEBUG ANALYSIS ===');
        
        return $debug_info;
    }
} 