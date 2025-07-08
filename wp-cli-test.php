<?php
/**
 * WP-CLI Test Script for CRUD Infrastructure
 * 
 * Usage: wp eval-file wp-cli-test.php
 */

if (!defined('WP_CLI') && php_sapi_name() !== 'cli') {
    die('This script is for WP-CLI only');
}

WP_CLI::line('🧪 CRUD Infrastructure Test');
WP_CLI::line('================================');

$errors = 0;
$tests = 0;

// Test 1: Class Loading
WP_CLI::line('1. Testing class loading...');
$tests++;
if (class_exists('Arsol_Projects_For_Woo\ARSOL_PFW_Request')) {
    WP_CLI::success('✓ ARSOL_PFW_Request class loaded');
} else {
    WP_CLI::error('✗ ARSOL_PFW_Request class not found');
    $errors++;
}

$tests++;
if (class_exists('Arsol_Projects_For_Woo\ARSOL_PFW_Data_Stores')) {
    WP_CLI::success('✓ ARSOL_PFW_Data_Stores class loaded');
} else {
    WP_CLI::error('✗ ARSOL_PFW_Data_Stores class not found');
    $errors++;
}

// Test 2: WooCommerce Integration
WP_CLI::line('2. Testing WooCommerce integration...');
$tests++;
if (class_exists('WooCommerce')) {
    try {
        $store = WC_Data_Store::load('arsol-pfw-request');
        if ($store instanceof Arsol_Projects_For_Woo\ARSOL_PFW_Request_Data_Store) {
            WP_CLI::success('✓ Data store registered with WooCommerce');
        } else {
            WP_CLI::error('✗ Data store not properly registered');
            $errors++;
        }
    } catch (Exception $e) {
        WP_CLI::error('✗ WooCommerce integration error: ' . $e->getMessage());
        $errors++;
    }
} else {
    WP_CLI::warning('WooCommerce not active - skipping integration test');
}

// Test 3: CRUD Operations
WP_CLI::line('3. Testing CRUD operations...');
$tests++;
try {
    $request = new Arsol_Projects_For_Woo\ARSOL_PFW_Request();
    $request->set_name('CLI Test Request');
    $request->set_description('Testing from WP-CLI');
    $request->set_customer_id(1);
    $request->save();
    
    if ($request->get_id() > 0) {
        WP_CLI::success('✓ CREATE: Request created (ID: ' . $request->get_id() . ')');
        
        // Test READ
        $loaded = new Arsol_Projects_For_Woo\ARSOL_PFW_Request($request->get_id());
        if ($loaded->get_name() === 'CLI Test Request') {
            WP_CLI::success('✓ READ: Request loaded successfully');
        } else {
            WP_CLI::error('✗ READ: Request data not loaded correctly');
            $errors++;
        }
        
        // Test UPDATE
        $loaded->set_name('Updated CLI Test');
        $loaded->save();
        
        $reloaded = new Arsol_Projects_For_Woo\ARSOL_PFW_Request($request->get_id());
        if ($reloaded->get_name() === 'Updated CLI Test') {
            WP_CLI::success('✓ UPDATE: Request updated successfully');
        } else {
            WP_CLI::error('✗ UPDATE: Request update failed');
            $errors++;
        }
        
        // Test DELETE
        $request->delete(true);
        WP_CLI::success('✓ DELETE: Request deleted successfully');
        
    } else {
        WP_CLI::error('✗ CREATE: Failed to create request');
        $errors++;
    }
} catch (Exception $e) {
    WP_CLI::error('✗ CRUD operation error: ' . $e->getMessage());
    $errors++;
}

// Test 4: Stage Management
WP_CLI::line('4. Testing stage management...');
$tests++;
try {
    $request = new Arsol_Projects_For_Woo\ARSOL_PFW_Request();
    $request->set_name('Stage Test Request');
    $request->set_customer_id(1);
    $request->save();
    
    if ($request->get_stage() === 'pending-review') {
        WP_CLI::success('✓ Initial stage set correctly');
    } else {
        WP_CLI::error('✗ Initial stage not set correctly');
        $errors++;
    }
    
    $request->approve();
    if ($request->get_stage() === 'approved') {
        WP_CLI::success('✓ Stage management works');
    } else {
        WP_CLI::error('✗ Stage management failed');
        $errors++;
    }
    
    // Clean up
    $request->delete(true);
    
} catch (Exception $e) {
    WP_CLI::error('✗ Stage management error: ' . $e->getMessage());
    $errors++;
}

// Summary
WP_CLI::line('');
WP_CLI::line('================================');
WP_CLI::line('TEST SUMMARY');
WP_CLI::line('================================');
WP_CLI::line('Total tests: ' . $tests);

if ($errors === 0) {
    WP_CLI::success('🎉 ALL TESTS PASSED!');
    WP_CLI::line('✅ CRUD infrastructure is working correctly');
    WP_CLI::line('✅ Ready for Phase 2: Wrapper Implementation');
} else {
    WP_CLI::warning('❌ ' . $errors . ' test(s) failed');
    WP_CLI::line('Please fix the issues before proceeding to Phase 2');
}

WP_CLI::line('');
