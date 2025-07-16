<?php

namespace Arsol_Projects_For_Woo\Integrations\WooCommerce;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Logs Class
 * 
 * Centralized logging functionality with configurable debug options
 */
class Logs {
    
    /**
     * Log sources for different components
     */
    const LOG_SOURCES = array(
        'request_to_proposal_conversion' => 'arsol-pfw-request-to-proposal',
        'proposal_to_project_conversion' => 'arsol-pfw-proposal-to-project',
        'woocommerce_billing_operations' => 'arsol-pfw-woocommerce-billing',
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
     * Log request to proposal conversion messages
     * 
     * @param string $level Log level
     * @param string $message Log message
     */
    public static function log_request_to_proposal_conversion($level, $message) {
        self::log($level, $message, 'request_to_proposal_conversion', 'enable_request_to_proposal_conversion_logs');
    }
    
    /**
     * Log proposal to project conversion messages
     * 
     * @param string $level Log level
     * @param string $message Log message
     */
    public static function log_proposal_to_project_conversion($level, $message) {
        self::log($level, $message, 'proposal_to_project_conversion', 'enable_proposal_to_project_conversion_logs');
    }
    
    /**
     * Log WooCommerce billing operations (orders, subscriptions, etc.)
     * 
     * @param string $level Log level
     * @param string $message Log message
     */
    public static function log_woocommerce_billing($level, $message) {
        self::log($level, $message, 'woocommerce_billing_operations', 'enable_woocommerce_billing_logs');
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
     * Log workflow-related messages (conversions, transactions, rollbacks)
     * 
     * @param string $level Log level
     * @param string $message Log message
     */
    public static function log_workflow($level, $message) {
        self::log($level, $message, 'general', 'enable_workflow_logs');
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
        
        self::log_woocommerce_billing('info', sprintf('Starting debug analysis for proposal #%d', $proposal_id));
        
        // Check proposal exists
        $proposal = get_post($proposal_id);
        $debug_info['proposal_exists'] = !empty($proposal);
        $debug_info['proposal_type'] = $proposal ? $proposal->post_type : 'N/A';
        $debug_info['proposal_stage'] = $proposal ? $proposal->post_status : 'N/A';
        $debug_info['proposal_author'] = $proposal ? $proposal->post_author : 'N/A';
        
        // Get proposal type
        $cost_proposal_type = get_post_meta($proposal_id, '_arsol_pfw_proposal_costing_type', true) ?: 'none';
        
        $debug_info['cost_proposal_type'] = $cost_proposal_type;
        $debug_info['should_create_orders'] = ($cost_proposal_type === 'quotation');
        
        // Check quotation line items
        $line_items = get_post_meta($proposal_id, '_arsol_pfw_proposed_project_quotation_line_items', true);
        $debug_info['has_quotation_line_items'] = !empty($line_items);
        $debug_info['quotation_line_items_structure'] = !empty($line_items) ? array_keys($line_items) : array();
        
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
        
        self::log_woocommerce_billing('info', sprintf('Debug analysis complete for proposal #%d: %s', $proposal_id, wp_json_encode($debug_info)));
        
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
        
        self::log_woocommerce_billing('info', sprintf('=== DETAILED DEBUG ANALYSIS FOR PROPOSAL #%d ===', $proposal_id));
        
        // Check proposal exists
        $proposal = get_post($proposal_id);
        $debug_info['proposal_exists'] = !empty($proposal);
        $debug_info['proposal_type'] = $proposal ? $proposal->post_type : 'N/A';
        $debug_info['proposal_stage'] = $proposal ? $proposal->post_status : 'N/A';
        $debug_info['proposal_author'] = $proposal ? $proposal->post_author : 'N/A';
        
        self::log_woocommerce_billing('info', sprintf('Proposal exists: %s, Type: %s, Status: %s, Author: %s', 
            $debug_info['proposal_exists'] ? 'YES' : 'NO',
            $debug_info['proposal_type'],
            $debug_info['proposal_stage'],
            $debug_info['proposal_author']));
        
        // Get proposal type
        $cost_proposal_type = get_post_meta($proposal_id, '_arsol_pfw_proposal_costing_type', true) ?: 'none';
        
        $debug_info['cost_proposal_type'] = $cost_proposal_type;
        $debug_info['should_create_orders'] = ($cost_proposal_type === 'quotation');
        
        self::log_woocommerce_billing('info', sprintf('Cost proposal type: %s, Should create orders: %s', 
            $cost_proposal_type, $debug_info['should_create_orders'] ? 'YES' : 'NO'));
        
        // Check quotation line items
        $line_items = get_post_meta($proposal_id, '_arsol_pfw_proposed_project_quotation_line_items', true);
        $debug_info['has_quotation_line_items'] = !empty($line_items);
        
        if (!empty($line_items)) {
            $debug_info['quotation_line_items_structure'] = array_keys($line_items);
            
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
                    
                    self::log_woocommerce_billing('info', sprintf('%s (%d items): %s', 
                        ucwords(str_replace('_', ' ', $section)), 
                        $debug_info[$section . '_count'],
                        wp_json_encode($debug_info[$section . '_details'])));
                } else {
                    $debug_info[$section . '_count'] = 0;
                    self::log_woocommerce_billing('info', sprintf('%s: NONE', ucwords(str_replace('_', ' ', $section))));
                }
            }
        } else {
            self::log_woocommerce_billing('error', 'NO LINE ITEMS FOUND');
        }
        
        // Check if customer exists
        if ($proposal) {
            $customer = new \WC_Customer($proposal->post_author);
            $debug_info['customer_exists'] = $customer && $customer->get_id();
            $debug_info['customer_email'] = $customer ? $customer->get_billing_email() : 'N/A';
            
            self::log_woocommerce_billing('info', sprintf('Customer #%d exists: %s, Email: %s', 
                $proposal->post_author, 
                $debug_info['customer_exists'] ? 'YES' : 'NO',
                $debug_info['customer_email']));
        }
        
        // Check WooCommerce Subscriptions
        $debug_info['wc_subscriptions_active'] = class_exists('WC_Subscriptions') && function_exists('wcs_create_subscription');
        self::log_woocommerce_billing('info', sprintf('WooCommerce Subscriptions active: %s', 
            $debug_info['wc_subscriptions_active'] ? 'YES' : 'NO'));
        
        self::log_woocommerce_billing('info', '=== END DETAILED DEBUG ANALYSIS ===');
        
        return $debug_info;
    }

    /**
     * Log quotation line items for debugging
     * 
     * @param int $proposal_id
     * @param string $context
     */
    public static function log_quotation_line_items($proposal_id, $context = '') {
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);
        $line_items = $proposal->get_quotation_line_items() ?: array();
        
        $log_message = sprintf(
            'Quotation line items for proposal #%d (%s): %s',
            $proposal_id,
            $context,
            json_encode($line_items, JSON_PRETTY_PRINT)
        );
        
        self::log_woocommerce_billing('debug', $log_message);
    }
    
    /**
     * Log quotation totals for debugging
     * 
     * @param int $proposal_id
     * @param string $context
     */
    public static function log_quotation_totals($proposal_id, $context = '') {
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);
        $onetime_total = $proposal->get_quotation_onetime_total() ?: 0;
        $recurring_totals = $proposal->get_quotation_recurring_totals() ?: array();
        
        $log_message = sprintf(
            'Quotation totals for proposal #%d (%s): One-time: %s, Recurring: %s',
            $proposal_id,
            $context,
            $onetime_total,
            json_encode($recurring_totals, JSON_PRETTY_PRINT)
        );
        
        self::log_woocommerce_billing('debug', $log_message);
    }
} 