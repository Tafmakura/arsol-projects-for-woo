<?php
/**
 * Test Billing Integration
 * 
 * This file tests that the biller classes are properly instantiated and integrated
 * with the proposal conversion workflow.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Test if biller classes are loaded and available
 */
function test_biller_classes_loaded() {
    echo "<h3>Testing Biller Classes</h3>\n";
    
    // Test if classes exist
    $classes = array(
        'Arsol_Projects_For_Woo\\Woocommerce_Biller_Helper',
        'Arsol_Projects_For_Woo\\Woocommerce_Biller',
        'Arsol_Projects_For_Woo\\Woocommerce_Subscriptions_Biller'
    );
    
    foreach ($classes as $class) {
        if (class_exists($class)) {
            echo "✅ {$class} - LOADED\n";
        } else {
            echo "❌ {$class} - NOT FOUND\n";
        }
    }
    
    echo "\n";
}

/**
 * Test if the integration hook is properly set up
 */
function test_billing_integration_hook() {
    echo "<h3>Testing Billing Integration Hook</h3>\n";
    
    // Check if the action hook is registered
    $priority = has_action('arsol_proposal_converted_to_project', array('Arsol_Projects_For_Woo\\Woocommerce', 'handle_proposal_billing'));
    
    if ($priority !== false) {
        echo "✅ Billing integration hook is registered (priority: {$priority})\n";
    } else {
        echo "❌ Billing integration hook is NOT registered\n";
    }
    
    echo "\n";
}

/**
 * Test static method availability
 */
function test_static_methods() {
    echo "<h3>Testing Static Methods</h3>\n";
    
    $methods = array(
        'Arsol_Projects_For_Woo\\Woocommerce_Biller::create_order',
        'Arsol_Projects_For_Woo\\Woocommerce_Biller::create_order_with_subscription',
        'Arsol_Projects_For_Woo\\Woocommerce_Biller::is_proposal_billed',
        'Arsol_Projects_For_Woo\\Woocommerce_Subscriptions_Biller::create_subscription',
        'Arsol_Projects_For_Woo\\Woocommerce_Subscriptions_Biller::is_subscriptions_active',
        'Arsol_Projects_For_Woo\\Woocommerce_Biller_Helper::validate_fee_data'
    );
    
    foreach ($methods as $method) {
        if (is_callable($method)) {
            echo "✅ {$method} - CALLABLE\n";
        } else {
            echo "❌ {$method} - NOT CALLABLE\n";
        }
    }
    
    echo "\n";
}

/**
 * Test WooCommerce dependencies
 */
function test_woocommerce_dependencies() {
    echo "<h3>Testing WooCommerce Dependencies</h3>\n";
    
    // Check WooCommerce
    if (class_exists('WooCommerce')) {
        echo "✅ WooCommerce - ACTIVE\n";
    } else {
        echo "❌ WooCommerce - NOT ACTIVE\n";
    }
    
    // Check WooCommerce Subscriptions
    if (class_exists('WC_Subscriptions') && function_exists('wcs_create_subscription')) {
        echo "✅ WooCommerce Subscriptions - ACTIVE\n";
    } else {
        echo "⚠️ WooCommerce Subscriptions - NOT ACTIVE (optional)\n";
    }
    
    echo "\n";
}

/**
 * Test fee validation
 */
function test_fee_validation() {
    echo "<h3>Testing Fee Validation</h3>\n";
    
    if (!class_exists('Arsol_Projects_For_Woo\\Woocommerce_Biller_Helper')) {
        echo "❌ Helper class not available for testing\n";
        return;
    }
    
    // Test valid fee
    $valid_fee = array(
        'name' => 'Test Fee',
        'amount' => '50.00',
        'tax_class' => 'standard'
    );
    
    $is_valid = Arsol_Projects_For_Woo\Woocommerce_Biller_Helper::validate_fee_data($valid_fee);
    echo $is_valid ? "✅ Valid fee validation - PASS\n" : "❌ Valid fee validation - FAIL\n";
    
    // Test invalid fee
    $invalid_fee = array(
        'name' => 'Invalid Fee',
        'amount' => '-10.00' // Negative amount
    );
    
    $is_invalid = !Arsol_Projects_For_Woo\Woocommerce_Biller_Helper::validate_fee_data($invalid_fee);
    echo $is_invalid ? "✅ Invalid fee validation - PASS\n" : "❌ Invalid fee validation - FAIL\n";
    
    echo "\n";
}

/**
 * Test subscription availability
 */
function test_subscription_availability() {
    echo "<h3>Testing Subscription Availability</h3>\n";
    
    if (!class_exists('Arsol_Projects_For_Woo\\Woocommerce_Subscriptions_Biller')) {
        echo "❌ Subscription biller class not available\n";
        return;
    }
    
    $is_active = Arsol_Projects_For_Woo\Woocommerce_Subscriptions_Biller::is_subscriptions_active();
    echo $is_active ? "✅ Subscription functionality - AVAILABLE\n" : "⚠️ Subscription functionality - NOT AVAILABLE\n";
    
    echo "\n";
}

/**
 * Run all tests
 */
function run_billing_integration_tests() {
    echo "<h2>Billing Integration Tests</h2>\n";
    echo "==============================\n\n";
    
    test_biller_classes_loaded();
    test_billing_integration_hook();
    test_static_methods();
    test_woocommerce_dependencies();
    test_fee_validation();
    test_subscription_availability();
    
    echo "<h3>Summary</h3>\n";
    echo "If all tests show ✅, the billing integration is properly set up.\n";
    echo "⚠️ warnings are for optional features.\n";
    echo "❌ errors indicate missing components that need attention.\n\n";
    
    echo "To test actual billing, convert a proposal with line items to a project.\n";
    echo "Check the WooCommerce logs for detailed billing process information.\n";
}

// Run tests if this file is accessed directly (for testing purposes)
if (defined('WP_CLI') && WP_CLI) {
    run_billing_integration_tests();
}

// Also provide a function that can be called from WordPress admin
if (is_admin() && current_user_can('manage_options')) {
    add_action('wp_ajax_test_billing_integration', function() {
        ob_start();
        run_billing_integration_tests();
        $output = ob_get_clean();
        
        wp_send_json_success(array(
            'output' => nl2br(esc_html($output))
        ));
    });
} 