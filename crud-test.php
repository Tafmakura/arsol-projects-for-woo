<?php
/**
 * CRUD Test Script
 * Run from WordPress admin by adding ?crud_test=1 to any admin URL
 */

if (!defined('ABSPATH') || !isset($_GET['crud_test'])) {
    return;
}

echo '<div style="margin: 20px; font-family: monospace; background: #f9f9f9; padding: 15px; border-radius: 5px;">';
echo '<h2>🧪 CRUD Infrastructure Test</h2>';

$tests = array();

// Test 1: Class Existence
$tests['classes'] = array(
    'Arsol_Projects_For_Woo\ARSOL_PFW_Request' => class_exists('Arsol_Projects_For_Woo\ARSOL_PFW_Request'),
    'Arsol_Projects_For_Woo\ARSOL_PFW_Data_Stores' => class_exists('Arsol_Projects_For_Woo\ARSOL_PFW_Data_Stores'),
    'Arsol_Projects_For_Woo\ARSOL_PFW_Request_Data_Store' => class_exists('Arsol_Projects_For_Woo\ARSOL_PFW_Request_Data_Store')
);

// Test 2: WooCommerce Integration
$wc_test = false;
if (class_exists('WooCommerce')) {
    try {
        $store = WC_Data_Store::load('arsol-pfw-request');
        $wc_test = $store instanceof Arsol_Projects_For_Woo\ARSOL_PFW_Request_Data_Store;
    } catch (Exception $e) {
        $wc_test = false;
    }
}
$tests['woocommerce'] = $wc_test;

// Test 3: CRUD Operations
$crud_test = false;
$test_id = null;
try {
    $request = new Arsol_Projects_For_Woo\ARSOL_PFW_Request();
    $request->set_name('Test Request');
    $request->set_customer_id(1);
    $request->save();
    
    if ($request->get_id() > 0) {
        $test_id = $request->get_id();
        $loaded = new Arsol_Projects_For_Woo\ARSOL_PFW_Request($test_id);
        $crud_test = $loaded->get_name() === 'Test Request';
        
        // Cleanup
        $request->delete(true);
    }
} catch (Exception $e) {
    $crud_test = false;
}
$tests['crud'] = $crud_test;

// Display Results
echo '<h3>Test Results:</h3>';
echo '<ul>';

foreach ($tests['classes'] as $class => $result) {
    $status = $result ? '✅ PASS' : '❌ FAIL';
    echo "<li>{$status} Class: {$class}</li>";
}

$wc_status = $tests['woocommerce'] ? '✅ PASS' : '❌ FAIL';
echo "<li>{$wc_status} WooCommerce Integration</li>";

$crud_status = $tests['crud'] ? '✅ PASS' : '❌ FAIL';
echo "<li>{$crud_status} CRUD Operations</li>";

echo '</ul>';

// Summary
$all_passed = $tests['woocommerce'] && $tests['crud'] && !in_array(false, $tests['classes']);
if ($all_passed) {
    echo '<div style="background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;">';
    echo '<strong>🎉 ALL TESTS PASSED!</strong><br>';
    echo 'CRUD infrastructure is working correctly. Ready for Phase 2.';
    echo '</div>';
} else {
    echo '<div style="background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;">';
    echo '<strong>❌ Some tests failed</strong><br>';
    echo 'Please check the errors above and fix them before proceeding.';
    echo '</div>';
}

echo '</div>';
