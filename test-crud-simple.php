<?php
/**
 * Simple CRUD Test Script
 * 
 * Quick test to verify CRUD infrastructure is working
 * 
 * Usage: 
 * - WordPress Admin: Navigate to /wp-admin/ and add ?crud_test=1 to URL
 * - WP-CLI: wp eval-file test-crud-simple.php
 * - Direct: php test-crud-simple.php (from WordPress root)
 */

// WordPress bootstrap
if (!defined('ABSPATH')) {
    if (file_exists('wp-load.php')) {
        require_once('wp-load.php');
    } else {
        die('WordPress not found. Run this from WordPress root directory.');
    }
}

// Check if we should run the test
if (!isset($_GET['crud_test']) && !defined('WP_CLI') && php_sapi_name() !== 'cli') {
    die('Add ?crud_test=1 to the URL to run this test.');
}

echo "=== ARSOL PFW CRUD Infrastructure Test ===\n";
echo "Testing Phase 1: Basic CRUD Operations\n\n";

$errors = array();
$successes = array();

// Test 1: Class Loading
echo "1. Testing class loading...\n";
try {
    if (class_exists('Arsol_Projects_For_Woo\ARSOL_PFW_Request')) {
        $successes[] = "✓ ARSOL_PFW_Request class loaded";
    } else {
        $errors[] = "✗ ARSOL_PFW_Request class not found";
    }
    
    if (class_exists('Arsol_Projects_For_Woo\ARSOL_PFW_Data_Stores')) {
        $successes[] = "✓ ARSOL_PFW_Data_Stores class loaded";
    } else {
        $errors[] = "✗ ARSOL_PFW_Data_Stores class not found";
    }
} catch (Exception $e) {
    $errors[] = "✗ Class loading error: " . $e->getMessage();
}

// Test 2: WooCommerce Integration
echo "2. Testing WooCommerce integration...\n";
try {
    if (class_exists('WooCommerce')) {
        $data_store = WC_Data_Store::load('arsol-pfw-request');
        if ($data_store instanceof Arsol_Projects_For_Woo\ARSOL_PFW_Request_Data_Store) {
            $successes[] = "✓ Data store registered with WooCommerce";
        } else {
            $errors[] = "✗ Data store not properly registered";
        }
    } else {
        $errors[] = "✗ WooCommerce not active";
    }
} catch (Exception $e) {
    $errors[] = "✗ WooCommerce integration error: " . $e->getMessage();
}

// Test 3: Factory Functions
echo "3. Testing factory functions...\n";
try {
    if (function_exists('Arsol_Projects_For_Woo\arsol_pfw_create_request')) {
        $successes[] = "✓ arsol_pfw_create_request function exists";
    } else {
        $errors[] = "✗ arsol_pfw_create_request function not found";
    }
    
    if (function_exists('Arsol_Projects_For_Woo\arsol_pfw_get_request')) {
        $successes[] = "✓ arsol_pfw_get_request function exists";
    } else {
        $errors[] = "✗ arsol_pfw_get_request function not found";
    }
} catch (Exception $e) {
    $errors[] = "✗ Factory function error: " . $e->getMessage();
}

// Test 4: Basic CRUD Operations
echo "4. Testing basic CRUD operations...\n";
$test_request_id = null;
try {
    // CREATE
    $request = new Arsol_Projects_For_Woo\ARSOL_PFW_Request();
    $request->set_name('Test Request');
    $request->set_description('Test Description');
    $request->set_customer_id(1);
    $request->set_stage('pending-review');
    $request->save();
    
    if ($request->get_id() > 0) {
        $successes[] = "✓ CREATE: Request created (ID: " . $request->get_id() . ")";
        $test_request_id = $request->get_id();
    } else {
        $errors[] = "✗ CREATE: Failed to create request";
    }
    
    // READ
    if ($test_request_id) {
        $loaded_request = new Arsol_Projects_For_Woo\ARSOL_PFW_Request($test_request_id);
        if ($loaded_request->get_name() === 'Test Request') {
            $successes[] = "✓ READ: Request loaded successfully";
        } else {
            $errors[] = "✗ READ: Request data not loaded correctly";
        }
        
        // UPDATE
        $loaded_request->set_name('Updated Test Request');
        $loaded_request->save();
        
        $reloaded_request = new Arsol_Projects_For_Woo\ARSOL_PFW_Request($test_request_id);
        if ($reloaded_request->get_name() === 'Updated Test Request') {
            $successes[] = "✓ UPDATE: Request updated successfully";
        } else {
            $errors[] = "✗ UPDATE: Request update failed";
        }
        
        // DELETE
        $loaded_request->delete(true);
        $successes[] = "✓ DELETE: Request deleted successfully";
    }
    
} catch (Exception $e) {
    $errors[] = "✗ CRUD operation error: " . $e->getMessage();
}

// Test 5: Stage Management
echo "5. Testing stage management...\n";
try {
    $request = new Arsol_Projects_For_Woo\ARSOL_PFW_Request();
    $request->set_name('Stage Test Request');
    $request->set_customer_id(1);
    $request->save();
    
    if ($request->get_stage() === 'pending-review') {
        $successes[] = "✓ Initial stage set correctly";
    } else {
        $errors[] = "✗ Initial stage not set correctly";
    }
    
    $request->approve();
    if ($request->get_stage() === 'approved') {
        $successes[] = "✓ Stage management (approve) works";
    } else {
        $errors[] = "✗ Stage management (approve) failed";
    }
    
    // Clean up
    $request->delete(true);
    
} catch (Exception $e) {
    $errors[] = "✗ Stage management error: " . $e->getMessage();
}

// Display Results
echo "\n=== TEST RESULTS ===\n";
echo "Successes: " . count($successes) . "\n";
echo "Errors: " . count($errors) . "\n\n";

if (!empty($successes)) {
    echo "SUCCESSES:\n";
    foreach ($successes as $success) {
        echo "  " . $success . "\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "ERRORS:\n";
    foreach ($errors as $error) {
        echo "  " . $error . "\n";
    }
    echo "\n";
}

if (empty($errors)) {
    echo "🎉 ALL TESTS PASSED! CRUD infrastructure is working correctly.\n";
    echo "✅ Ready for Phase 2: Wrapper Implementation\n";
} else {
    echo "❌ Some tests failed. Please fix the issues before proceeding.\n";
}

echo "\n=== END TEST ===\n"; 