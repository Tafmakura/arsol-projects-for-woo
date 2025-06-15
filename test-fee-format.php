<?php
/**
 * Test script to verify WooCommerce fee addition format
 * This should be run in a WordPress environment with WooCommerce active
 */

// Ensure this is run in WordPress context
if (!defined('ABSPATH')) {
    echo "This script must be run in WordPress context.\n";
    exit(1);
}

echo "=== Testing WooCommerce Fee Addition Format ===\n";

// Test 1: Create order and add fee with individual parameters (CORRECT)
echo "\nTest 1: Adding fee with individual parameters (correct format)\n";
$order1 = wc_create_order();
if (is_wp_error($order1)) {
    echo "Error creating order: " . $order1->get_error_message() . "\n";
    exit(1);
}

echo "Created order #" . $order1->get_id() . "\n";

// Add fee with individual parameters (CORRECT FORMAT)
$order1->add_fee('Test Fee 1', 10.50, true, '');
$order1->save();

$fees1 = $order1->get_fees();
echo "Fees added: " . count($fees1) . "\n";
echo "Order total: $" . $order1->get_total() . "\n";

foreach ($fees1 as $fee) {
    echo "- Fee: " . $fee->get_name() . " = $" . $fee->get_amount() . "\n";
}

// Test 2: Try adding fee with array format (INCORRECT - should fail)
echo "\nTest 2: Adding fee with array format (incorrect format)\n";
$order2 = wc_create_order();
echo "Created order #" . $order2->get_id() . "\n";

// This should NOT work properly
try {
    $order2->add_fee(array(
        'name' => 'Test Fee 2',
        'amount' => 15.75,
        'taxable' => true,
        'tax_class' => ''
    ));
    $order2->save();
    
    $fees2 = $order2->get_fees();
    echo "Fees added: " . count($fees2) . "\n";
    echo "Order total: $" . $order2->get_total() . "\n";
    
    foreach ($fees2 as $fee) {
        echo "- Fee: " . $fee->get_name() . " = $" . $fee->get_amount() . "\n";
    }
} catch (Exception $e) {
    echo "Error (expected): " . $e->getMessage() . "\n";
}

// Test 3: Test our helper class format
echo "\nTest 3: Testing helper class format\n";
if (class_exists('Arsol_Projects_For_Woo\\Woocommerce_Biller')) {
    echo "\n=== Fee Addition Test ===\n";
    
    // Create a test order
    $order3 = new WC_Order();
    $order3->set_customer_id(1);
    $order3->save();
    
    $test_fees = array(
        'fee1' => array('name' => 'Test Fee 1', 'amount' => 25.00),
        'fee2' => array('name' => 'Test Fee 2', 'amount' => 15.50, 'taxable' => true)
    );
    
    $fees_added = Arsol_Projects_For_Woo\Woocommerce_Biller::add_fees_to_order($order3, $test_fees, 'test');
    
    $fees3 = $order3->get_fees();
    echo "Fees added by helper: " . $fees_added . "\n";
    echo "Total fees in order: " . count($fees3) . "\n";
    echo "Order total: $" . $order3->get_total() . "\n";
    
    foreach ($fees3 as $fee) {
        echo "- Fee: " . $fee->get_name() . " = $" . $fee->get_amount() . "\n";
    }
    
    // Clean up
    wp_delete_post($order3->get_id(), true);
} else {
    echo "Helper class not found\n";
}

// Clean up test orders
wp_delete_post($order1->get_id(), true);
wp_delete_post($order2->get_id(), true);

echo "\n=== Test Complete ===\n"; 