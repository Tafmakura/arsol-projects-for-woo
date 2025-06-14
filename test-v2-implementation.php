<?php
/**
 * Test script for V2 WooCommerce Biller Implementation
 * 
 * This script tests the new implementation based on official WooCommerce documentation
 */

// Ensure this runs in WordPress context
if (!defined('ABSPATH')) {
    echo "This script must be run in WordPress context.\n";
    exit(1);
}

echo "=== Testing V2 WooCommerce Biller Implementation ===\n\n";

// Test 1: Basic fee addition to order
echo "Test 1: Basic fee addition to order\n";
echo "-----------------------------------\n";

$test_order = wc_create_order();
if (is_wp_error($test_order)) {
    echo "ERROR: Failed to create test order\n";
    exit(1);
}

echo "Created test order #" . $test_order->get_id() . "\n";

// Test fee data structure based on our proposal format
$test_fee_data = array(
    '1' => array(
        'name' => 'Test One-Time Fee',
        'amount' => '25.00',
        'tax_class' => 'no-tax'
    ),
    '2' => array(
        'description' => 'Test Shipping Fee',
        'amount' => '15.50',
        'tax_class' => 'standard'
    )
);

echo "Test fee data: " . json_encode($test_fee_data) . "\n";

// Test the helper class method
if (class_exists('Woocommerce_Biller_Helper_V2')) {
    echo "Testing Woocommerce_Biller_Helper_V2::add_order_fee()...\n";
    
    $result = Woocommerce_Biller_Helper_V2::add_order_fee($test_order, $test_fee_data, 'one_time', 'test');
    
    if ($result) {
        echo "SUCCESS: Fees added successfully\n";
        echo "Order total after fees: $" . number_format($test_order->get_total(), 2) . "\n";
        
        // Check fees
        $fees = $test_order->get_fees();
        echo "Number of fees in order: " . count($fees) . "\n";
        
        foreach ($fees as $fee) {
            echo "- Fee: " . $fee->get_name() . " = $" . number_format($fee->get_total(), 2) . "\n";
        }
    } else {
        echo "FAILED: Could not add fees to order\n";
    }
} else {
    echo "ERROR: Woocommerce_Biller_Helper_V2 class not found\n";
}

// Clean up test order
wp_delete_post($test_order->get_id(), true);
echo "Cleaned up test order\n\n";

// Test 2: Fee validation
echo "Test 2: Fee validation\n";
echo "----------------------\n";

$invalid_fee_data = array(
    '1' => array(
        'name' => 'Invalid Fee',
        'amount' => 'not_a_number'
    ),
    '2' => array(
        'amount' => '0'  // Zero amount should be invalid
    ),
    '3' => array(
        'name' => 'Valid Fee',
        'amount' => '10.00',
        'tax_class' => 'no-tax'
    )
);

echo "Testing fee validation with mixed valid/invalid data...\n";

$test_order2 = wc_create_order();
if (!is_wp_error($test_order2)) {
    if (class_exists('Woocommerce_Biller_Helper_V2')) {
        $result2 = Woocommerce_Biller_Helper_V2::add_order_fee($test_order2, $invalid_fee_data, 'test', 'test');
        
        $fees2 = $test_order2->get_fees();
        echo "Fees added from mixed data: " . count($fees2) . " (should be 1)\n";
        
        if (count($fees2) === 1) {
            echo "SUCCESS: Validation working correctly\n";
        } else {
            echo "FAILED: Validation not working as expected\n";
        }
    }
    
    wp_delete_post($test_order2->get_id(), true);
    echo "Cleaned up test order 2\n\n";
}

// Test 3: Cart fee addition (if cart is available)
echo "Test 3: Cart fee addition\n";
echo "-------------------------\n";

if (WC()->cart) {
    echo "Testing cart fee addition...\n";
    
    // Clear cart first
    WC()->cart->empty_cart();
    
    $cart_fee_data = array(
        '1' => array(
            'name' => 'Cart Test Fee',
            'amount' => '5.00',
            'tax_class' => 'no-tax'
        )
    );
    
    if (class_exists('Woocommerce_Biller_Helper_V2')) {
        $cart_result = Woocommerce_Biller_Helper_V2::add_cart_fee(WC()->cart, $cart_fee_data, 'test');
        
        if ($cart_result) {
            echo "SUCCESS: Cart fee method executed\n";
        } else {
            echo "FAILED: Cart fee method failed\n";
        }
    }
    
    // Clear cart after test
    WC()->cart->empty_cart();
} else {
    echo "SKIPPED: WC()->cart not available\n";
}

echo "\n=== V2 Implementation Test Complete ===\n";
echo "Check WooCommerce logs for detailed fee processing information.\n"; 