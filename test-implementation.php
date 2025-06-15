<?php
/**
 * Test Implementation for WooCommerce Billing System
 * 
 * This file demonstrates how to use the implemented billing classes
 * following WordPress standards and WooCommerce best practices.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include the necessary classes (assuming they're autoloaded)
use Arsol_Projects_For_Woo\Woocommerce_Biller;
use Arsol_Projects_For_Woo\Woocommerce_Subscriptions_Biller;

/**
 * Test data structure that matches the expected format
 */
function get_test_line_items() {
    return array(
        'products' => array(
            '1' => array(
                'product_id' => 123,
                'quantity' => 2,
                'price' => 50.00,
                'sale_price' => 45.00
            )
        ),
        'one_time_fees' => array(
            '3' => array(
                'name' => 'Setup Fee',
                'amount' => '234.00',
                'tax_class' => 'standard'
            )
        ),
        'shipping_fees' => array(
            '5' => array(
                'description' => 'Express Shipping',
                'amount' => '34.00',
                'tax_class' => 'no-tax'
            )
        ),
        'recurring_fees' => array(
            '4' => array(
                'name' => 'Monthly Service Fee',
                'amount' => '23.00',
                'interval' => '1',
                'period' => 'month',
                'tax_class' => 'standard'
            )
        )
    );
}

/**
 * Example 1: Create a simple order with one-time items only
 */
function test_create_order() {
    echo "<h3>Test 1: Creating Order</h3>\n";
    
    $proposal_id = 123; // Replace with actual proposal ID
    
    // Create order
    $result = Woocommerce_Biller::create_order($proposal_id);
    
    if (is_wp_error($result)) {
        echo "Error: " . $result->get_error_message() . "\n";
    } else {
        echo "Success: " . $result['message'] . "\n";
        echo "Order ID: " . $result['order_id'] . "\n";
        echo "Order Total: $" . number_format($result['order_total'], 2) . "\n";
    }
    
    echo "\n";
}

/**
 * Example 2: Create order with subscription
 */
function test_create_order_with_subscription() {
    echo "<h3>Test 2: Creating Order with Subscription</h3>\n";
    
    $proposal_id = 124; // Replace with actual proposal ID
    
    // Create order and subscription
    $result = Woocommerce_Biller::create_order_with_subscription($proposal_id);
    
    if (is_wp_error($result)) {
        echo "Error: " . $result->get_error_message() . "\n";
    } else {
        echo "Success: " . $result['message'] . "\n";
        echo "Order ID: " . $result['order_id'] . "\n";
        echo "Order Total: $" . number_format($result['order_total'], 2) . "\n";
        echo "Subscription ID: " . $result['subscription_id'] . "\n";
        echo "Subscription Total: $" . number_format($result['subscription_total'], 2) . "\n";
    }
    
    echo "\n";
}

/**
 * Example 3: Create subscription only
 */
function test_create_subscription() {
    echo "<h3>Test 3: Creating Subscription Only</h3>\n";
    
    $proposal_id = 125; // Replace with actual proposal ID
    
    // Create subscription
    $result = Woocommerce_Subscriptions_Biller::create_subscription($proposal_id);
    
    if (is_wp_error($result)) {
        echo "Error: " . $result->get_error_message() . "\n";
    } else {
        echo "Success: " . $result['message'] . "\n";
        echo "Subscription ID: " . $result['subscription_id'] . "\n";
        echo "Subscription Total: $" . number_format($result['subscription_total'], 2) . "\n";
        echo "Billing: Every " . $result['billing_interval'] . " " . $result['billing_period'] . "(s)\n";
    }
    
    echo "\n";
}

/**
 * Example 4: Check if proposal is already billed
 */
function test_check_billing_status() {
    echo "<h3>Test 4: Checking Billing Status</h3>\n";
    
    $proposal_id = 123; // Replace with actual proposal ID
    
    // Check if already billed
    $is_billed = Woocommerce_Biller::is_proposal_billed($proposal_id);
    $has_subscription = Woocommerce_Subscriptions_Biller::has_subscription($proposal_id);
    
    echo "Proposal #$proposal_id:\n";
    echo "- Has Order: " . ($is_billed ? 'Yes' : 'No') . "\n";
    echo "- Has Subscription: " . ($has_subscription ? 'Yes' : 'No') . "\n";
    
    if ($is_billed) {
        $order = Woocommerce_Biller::get_order_by_proposal($proposal_id);
        if ($order) {
            echo "- Order ID: " . $order->get_id() . "\n";
            echo "- Order Status: " . $order->get_status() . "\n";
        }
    }
    
    if ($has_subscription) {
        $subscription = Woocommerce_Subscriptions_Biller::get_subscription_by_proposal($proposal_id);
        if ($subscription) {
            echo "- Subscription ID: " . $subscription->get_id() . "\n";
            echo "- Subscription Status: " . $subscription->get_status() . "\n";
        }
    }
    
    echo "\n";
}

/**
 * Example 5: Validate fee data
 */
function test_fee_validation() {
    echo "<h3>Test 5: Fee Validation</h3>\n";
    
    $valid_fee = array(
        'name' => 'Test Fee',
        'amount' => '50.00',
        'tax_class' => 'standard'
    );
    
    $invalid_fee = array(
        'name' => 'Invalid Fee',
        'amount' => '-10.00' // Negative amount
    );
    
    echo "Valid fee: " . (Woocommerce_Biller::validate_fee_data($valid_fee) ? 'PASS' : 'FAIL') . "\n";
    echo "Invalid fee: " . (Woocommerce_Biller::validate_fee_data($invalid_fee) ? 'FAIL' : 'PASS') . "\n";
    
    echo "\n";
}

/**
 * Example 6: Test billing schedule extraction
 */
function test_billing_schedule() {
    echo "<h3>Test 6: Billing Schedule</h3>\n";
    
    $recurring_fees = array(
        '1' => array(
            'name' => 'Monthly Fee',
            'amount' => '25.00',
            'interval' => '1',
            'period' => 'month'
        ),
        '2' => array(
            'name' => 'Quarterly Fee',
            'amount' => '75.00',
            'interval' => '3',
            'period' => 'month'
        )
    );
    
    $schedule = Woocommerce_Biller::get_billing_schedule($recurring_fees);
    
    echo "Billing Schedule:\n";
    echo "- Interval: " . $schedule['interval'] . "\n";
    echo "- Period: " . $schedule['period'] . "\n";
    
    echo "\n";
}

/**
 * Run all tests
 */
function run_all_tests() {
    echo "<h2>WooCommerce Billing System Tests</h2>\n";
    echo "=====================================\n\n";
    
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        echo "Error: WooCommerce is not active!\n";
        return;
    }
    
    // Check if WooCommerce Subscriptions is active
    $subscriptions_active = Woocommerce_Subscriptions_Biller::is_subscriptions_active();
    echo "WooCommerce Subscriptions: " . ($subscriptions_active ? 'Active' : 'Not Active') . "\n\n";
    
    // Run tests
    test_fee_validation();
    test_billing_schedule();
    test_check_billing_status();
    
    // Uncomment these to test actual order/subscription creation
    // test_create_order();
    // test_create_subscription();
    // test_create_order_with_subscription();
    
    echo "Tests completed!\n";
}

// Run tests if this file is accessed directly (for testing purposes)
if (defined('WP_CLI') && WP_CLI) {
    run_all_tests();
}

/**
 * Usage Examples for Integration:
 * 
 * 1. Create Order Only:
 *    $result = Woocommerce_Biller::create_order($proposal_id);
 * 
 * 2. Create Order + Subscription:
 *    $result = Woocommerce_Biller::create_order_with_subscription($proposal_id);
 * 
 * 3. Create Subscription Only:
 *    $result = Woocommerce_Subscriptions_Biller::create_subscription($proposal_id);
 * 
 * 4. Check Status:
 *    $is_billed = Woocommerce_Biller::is_proposal_billed($proposal_id);
 *    $has_subscription = Woocommerce_Subscriptions_Biller::has_subscription($proposal_id);
 * 
 * 5. Update Subscription:
 *    $result = Woocommerce_Subscriptions_Biller::update_subscription($subscription_id, $proposal_id);
 * 
 * 6. Cancel Subscription:
 *    $result = Woocommerce_Subscriptions_Biller::cancel_subscription_by_proposal($proposal_id);
 */ 