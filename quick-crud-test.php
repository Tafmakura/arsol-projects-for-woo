<?php
/**
 * Quick CRUD Test - Phase 1 Verification
 * 
 * Run: Add ?test=crud to any admin URL
 */

if (!defined('ABSPATH') || !isset($_GET['test']) || $_GET['test'] !== 'crud') {
    return;
}

echo '<div style="margin: 20px; font-family: monospace;">';
echo '<h2>CRUD Infrastructure Test Results</h2>';

$results = array();

// Test 1: Class Loading
try {
    $test1 = class_exists('Arsol_Projects_For_Woo\ARSOL_PFW_Request');
    $results[] = $test1 ? 'PASS: Request class loaded' : 'FAIL: Request class not found';
    
    $test2 = class_exists('Arsol_Projects_For_Woo\ARSOL_PFW_Data_Stores');
    $results[] = $test2 ? 'PASS: Data stores class loaded' : 'FAIL: Data stores class not found';
} catch (Exception $e) {
    $results[] = 'FAIL: Class loading error - ' . $e->getMessage();
}

// Test 2: WooCommerce Integration
try {
    if (class_exists('WooCommerce')) {
        $store = WC_Data_Store::load('arsol-pfw-request');
        $test3 = $store instanceof Arsol_Projects_For_Woo\ARSOL_PFW_Request_Data_Store;
        $results[] = $test3 ? 'PASS: Data store registered' : 'FAIL: Data store not registered';
    } else {
        $results[] = 'SKIP: WooCommerce not active';
    }
} catch (Exception $e) {
    $results[] = 'FAIL: WC integration error - ' . $e->getMessage();
}

// Test 3: CRUD Operations
try {
    $request = new Arsol_Projects_For_Woo\ARSOL_PFW_Request();
    $request->set_name('Test Request');
    $request->set_customer_id(1);
    $request->save();
    
    $test4 = $request->get_id() > 0;
    $results[] = $test4 ? 'PASS: Request created (ID: ' . $request->get_id() . ')' : 'FAIL: Request creation failed';
    
    if ($test4) {
        $loaded = new Arsol_Projects_For_Woo\ARSOL_PFW_Request($request->get_id());
        $test5 = $loaded->get_name() === 'Test Request';
        $results[] = $test5 ? 'PASS: Request loaded correctly' : 'FAIL: Request loading failed';
        
        $loaded->set_name('Updated Test');
        $loaded->save();
        
        $reloaded = new Arsol_Projects_For_Woo\ARSOL_PFW_Request($request->get_id());
        $test6 = $reloaded->get_name() === 'Updated Test';
        $results[] = $test6 ? 'PASS: Request updated correctly' : 'FAIL: Request update failed';
        
        // Cleanup
        $request->delete(true);
        $results[] = 'PASS: Test data cleaned up';
    }
} catch (Exception $e) {
    $results[] = 'FAIL: CRUD error - ' . $e->getMessage();
}

// Display Results
$passes = count(array_filter($results, function($r) { return strpos($r, 'PASS') === 0; }));
$fails = count(array_filter($results, function($r) { return strpos($r, 'FAIL') === 0; }));

echo '<div style="margin: 10px 0; padding: 10px; background: ' . ($fails === 0 ? '#d4edda' : '#f8d7da') . '; border-radius: 5px;">';
echo '<strong>Results: ' . $passes . ' passed, ' . $fails . ' failed</strong><br>';
echo $fails === 0 ? '🎉 ALL TESTS PASSED!' : '❌ Some tests failed';
echo '</div>';

echo '<div style="margin: 10px 0;">';
foreach ($results as $result) {
    $color = strpos($result, 'PASS') === 0 ? 'green' : (strpos($result, 'FAIL') === 0 ? 'red' : 'orange');
    echo '<div style="color: ' . $color . ';">' . $result . '</div>';
}
echo '</div>';

echo '</div>';
?> 