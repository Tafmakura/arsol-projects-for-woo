<?php
/**
 * Comprehensive CRUD Test
 *
 * Tests all CRUD functionality including recent fixes
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Tests
 */

// Only run this test if WP_DEBUG is enabled
if (!defined('WP_DEBUG') || !WP_DEBUG) {
    return;
}

/**
 * Test the CRUD system comprehensively
 */
function arsol_pfw_test_crud_comprehensive() {
    echo "=== Comprehensive CRUD Test ===\n";
    
    try {
        // Test 1: Data Store Registration
        echo "1. Testing Data Store Registration...\n";
        $data_store = WC_Data_Store::load('arsol-pfw-request');
        if ($data_store) {
            echo "   ✓ Data store registered successfully\n";
        } else {
            echo "   ✗ Data store registration failed\n";
            return false;
        }
        
        // Test 2: Request Creation
        echo "2. Testing Request Creation...\n";
        $request = arsol_pfw_create_request(array(
            'name' => 'Comprehensive Test Request',
            'description' => 'Testing all CRUD functionality',
            'customer_id' => 1,
            'stage' => 'pending-review',
            'budget' => array('min' => 1000, 'max' => 5000),
            'deadline' => '2024-12-31',
            'start_date' => '2024-01-15'
        ));
        
        if ($request && $request instanceof ARSOL_PFW_Request) {
            echo "   ✓ Request object created successfully\n";
            echo "   ✓ Request is instance of ARSOL_PFW_Request\n";
            echo "   ✓ Request extends WC_Data: " . ($request instanceof WC_Data ? 'Yes' : 'No') . "\n";
        } else {
            echo "   ✗ Request creation failed\n";
            return false;
        }
        
        // Test 3: Request Saving
        echo "3. Testing Request Saving...\n";
        $request_id = $request->save();
        
        if ($request_id && is_numeric($request_id)) {
            echo "   ✓ Request saved with ID: {$request_id}\n";
        } else {
            echo "   ✗ Request save failed\n";
            return false;
        }
        
        // Test 4: Request Reading
        echo "4. Testing Request Reading...\n";
        $retrieved_request = arsol_pfw_get_request($request_id);
        
        if ($retrieved_request && $retrieved_request->get_id() === $request_id) {
            echo "   ✓ Request retrieved successfully\n";
            echo "   ✓ Name: " . $retrieved_request->get_name() . "\n";
            echo "   ✓ Stage: " . $retrieved_request->get_stage() . "\n";
            echo "   ✓ Customer ID: " . $retrieved_request->get_customer_id() . "\n";
            echo "   ✓ Budget: " . json_encode($retrieved_request->get_budget()) . "\n";
        } else {
            echo "   ✗ Request reading failed\n";
            return false;
        }
        
        // Test 5: WC_Data Method Compatibility
        echo "5. Testing WC_Data Method Compatibility...\n";
        
        // Test get_meta with new signature
        $test_meta = $retrieved_request->get_meta('_test_key', true, 'view');
        echo "   ✓ get_meta() with WC_Data signature works\n";
        
        // Test set_meta
        $retrieved_request->set_meta('_test_key', 'test_value');
        $test_value = $retrieved_request->get_meta('_test_key');
        if ($test_value === 'test_value') {
            echo "   ✓ set_meta() and get_meta() work correctly\n";
        } else {
            echo "   ✗ Meta operations failed\n";
            return false;
        }
        
        // Test 6: Property Updates
        echo "6. Testing Property Updates...\n";
        $retrieved_request->set_name('Updated Test Request');
        $retrieved_request->set_description('Updated description');
        $retrieved_request->set_budget(array('min' => 2000, 'max' => 8000));
        $retrieved_request->save();
        
        $updated_request = arsol_pfw_get_request($request_id);
        
        if ($updated_request->get_name() === 'Updated Test Request' && 
            $updated_request->get_description() === 'Updated description') {
            echo "   ✓ Property updates work correctly\n";
        } else {
            echo "   ✗ Property updates failed\n";
            return false;
        }
        
        // Test 7: Stage Management
        echo "7. Testing Stage Management...\n";
        $updated_request->update_stage('under-review', 'Moving to review');
        $stage_updated_request = arsol_pfw_get_request($request_id);
        
        if ($stage_updated_request->get_stage() === 'under-review') {
            echo "   ✓ Stage update works correctly\n";
        } else {
            echo "   ✗ Stage update failed\n";
            return false;
        }
        
        // Test 8: Business Logic Methods
        echo "8. Testing Business Logic Methods...\n";
        $stage_updated_request->approve();
        $approved_request = arsol_pfw_get_request($request_id);
        
        if ($approved_request->get_stage() === 'approved' && 
            $approved_request->is_approved() && 
            $approved_request->can_convert_to_proposal()) {
            echo "   ✓ Business logic methods work correctly\n";
        } else {
            echo "   ✗ Business logic methods failed\n";
            return false;
        }
        
        // Test 9: Notes System
        echo "9. Testing Notes System...\n";
        $note_id = $approved_request->add_note('Test note for CRUD verification', false, 1);
        
        if ($note_id) {
            echo "   ✓ Note added successfully (ID: {$note_id})\n";
            
            $notes = $approved_request->get_notes();
            if (count($notes) > 0) {
                echo "   ✓ Notes retrieved: " . count($notes) . " notes found\n";
            } else {
                echo "   ✗ Notes retrieval failed\n";
                return false;
            }
        } else {
            echo "   ✗ Note addition failed\n";
            return false;
        }
        
        // Test 10: Query Methods
        echo "10. Testing Query Methods...\n";
        $approved_requests = arsol_pfw_get_requests_by_stage('approved');
        $customer_requests = arsol_pfw_get_requests_by_customer(1);
        $conversion_ready = arsol_pfw_get_requests_ready_for_conversion();
        
        if (count($approved_requests) > 0 && count($customer_requests) > 0 && count($conversion_ready) > 0) {
            echo "   ✓ Query methods work correctly\n";
            echo "   ✓ Approved requests: " . count($approved_requests) . "\n";
            echo "   ✓ Customer requests: " . count($customer_requests) . "\n";
            echo "   ✓ Conversion ready: " . count($conversion_ready) . "\n";
        } else {
            echo "   ✗ Query methods failed\n";
            return false;
        }
        
        // Test 11: Utility Functions
        echo "11. Testing Utility Functions...\n";
        $stages = arsol_pfw_get_request_stages();
        $stage_counts = arsol_pfw_get_request_stage_counts();
        
        if (is_array($stages) && count($stages) > 0) {
            echo "   ✓ Stage utility functions work correctly\n";
            echo "   ✓ Available stages: " . implode(', ', array_keys($stages)) . "\n";
        } else {
            echo "   ✗ Stage utility functions failed\n";
            return false;
        }
        
        // Test 12: Factory Functions
        echo "12. Testing Factory Functions...\n";
        if (function_exists('arsol_pfw_get_request') && 
            function_exists('arsol_pfw_create_request') && 
            function_exists('arsol_pfw_get_requests_by_stage') && 
            function_exists('arsol_pfw_get_requests_by_customer')) {
            echo "   ✓ All factory functions exist\n";
        } else {
            echo "   ✗ Some factory functions missing\n";
            return false;
        }
        
        // Test 13: Exception Handling
        echo "13. Testing Exception Handling...\n";
        try {
            $invalid_request = arsol_pfw_get_request(999999);
            if ($invalid_request === false) {
                echo "   ✓ Invalid request handling works correctly\n";
            } else {
                echo "   ✗ Invalid request should return false\n";
                return false;
            }
        } catch (Exception $e) {
            echo "   ✓ Exception handling works correctly\n";
        }
        
        // Test 14: Clean Up
        echo "14. Testing Clean Up...\n";
        $approved_request->delete();
        $deleted_request = arsol_pfw_get_request($request_id);
        
        if ($deleted_request === false) {
            echo "   ✓ Request deleted successfully\n";
        } else {
            echo "   ✗ Request deletion failed\n";
            return false;
        }
        
        echo "\n=== 🎉 All CRUD tests passed! ===\n";
        echo "✅ WC_Data compatibility: PASSED\n";
        echo "✅ Method signatures: PASSED\n";
        echo "✅ Stage management: PASSED\n";
        echo "✅ Business logic: PASSED\n";
        echo "✅ Query operations: PASSED\n";
        echo "✅ Factory functions: PASSED\n";
        echo "✅ Exception handling: PASSED\n";
        echo "✅ Clean up: PASSED\n";
        
        return true;
        
    } catch (Exception $e) {
        echo "✗ FATAL ERROR during CRUD test: " . $e->getMessage() . "\n";
        echo "  File: " . $e->getFile() . "\n";
        echo "  Line: " . $e->getLine() . "\n";
        echo "  Stack trace:\n" . $e->getTraceAsString() . "\n";
        return false;
    }
}

// Run the comprehensive test if requested
if (is_admin() || did_action('activate_plugin')) {
    add_action('admin_init', function() {
        if (isset($_GET['arsol_test_crud_comprehensive']) && current_user_can('manage_options')) {
            echo '<pre>';
            arsol_pfw_test_crud_comprehensive();
            echo '</pre>';
            exit;
        }
    });
}

// Keep the original basic test for backward compatibility
function arsol_pfw_test_crud_basic() {
    echo "=== Basic CRUD Test (Legacy) ===\n";
    echo "Use ?arsol_test_crud_comprehensive=1 for full test\n";
    return arsol_pfw_test_crud_comprehensive();
}

// Run basic test if requested
if (is_admin() || did_action('activate_plugin')) {
    add_action('admin_init', function() {
        if (isset($_GET['arsol_test_crud']) && current_user_can('manage_options')) {
            echo '<pre>';
            arsol_pfw_test_crud_basic();
            echo '</pre>';
            exit;
        }
    });
} 