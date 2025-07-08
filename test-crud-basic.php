<?php
/**
 * Basic CRUD Test
 *
 * Quick test to verify the CRUD system is working
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Tests
 */

// Only run this test if WP_DEBUG is enabled
if (!defined('WP_DEBUG') || !WP_DEBUG) {
    return;
}

/**
 * Test the CRUD system
 */
function arsol_pfw_test_crud_basic() {
    // Test 1: Create a request
    echo "=== Testing Request CRUD ===\n";
    
    try {
        // Create a new request
        $request = arsol_pfw_create_request(array(
            'name' => 'Test Request',
            'description' => 'This is a test request for CRUD verification',
            'customer_id' => 1,
            'stage' => 'pending-review'
        ));
        
        // Save the request
        $request_id = $request->save();
        
        echo "✓ Request created with ID: {$request_id}\n";
        
        // Test 2: Read the request
        $retrieved_request = arsol_pfw_get_request($request_id);
        
        if ($retrieved_request && $retrieved_request->get_name() === 'Test Request') {
            echo "✓ Request retrieved successfully\n";
            echo "  Name: " . $retrieved_request->get_name() . "\n";
            echo "  Stage: " . $retrieved_request->get_stage() . "\n";
            echo "  Customer ID: " . $retrieved_request->get_customer_id() . "\n";
        } else {
            echo "✗ Failed to retrieve request\n";
            return false;
        }
        
        // Test 3: Update the request
        $retrieved_request->set_name('Updated Test Request');
        $retrieved_request->update_stage('under-review');
        $retrieved_request->save();
        
        echo "✓ Request updated successfully\n";
        
        // Test 4: Verify update
        $updated_request = arsol_pfw_get_request($request_id);
        
        if ($updated_request->get_name() === 'Updated Test Request' && 
            $updated_request->get_stage() === 'under-review') {
            echo "✓ Request update verified\n";
        } else {
            echo "✗ Request update failed\n";
            return false;
        }
        
        // Test 5: Test business logic methods
        $updated_request->approve();
        $approved_request = arsol_pfw_get_request($request_id);
        
        if ($approved_request->get_stage() === 'approved') {
            echo "✓ Request approved successfully\n";
            echo "  Can convert to proposal: " . ($approved_request->can_convert_to_proposal() ? 'Yes' : 'No') . "\n";
        } else {
            echo "✗ Request approval failed\n";
            return false;
        }
        
        // Test 6: Test query methods
        $approved_requests = arsol_pfw_get_requests_by_stage('approved');
        
        if (count($approved_requests) > 0) {
            echo "✓ Query by stage working: " . count($approved_requests) . " approved requests found\n";
        } else {
            echo "✗ Query by stage failed\n";
            return false;
        }
        
        // Test 7: Test notes
        $note_id = $approved_request->add_note('Test note added via CRUD');
        
        if ($note_id) {
            echo "✓ Note added successfully (ID: {$note_id})\n";
            
            $notes = $approved_request->get_notes();
            if (count($notes) > 0) {
                echo "✓ Notes retrieved: " . count($notes) . " notes found\n";
            } else {
                echo "✗ Notes retrieval failed\n";
                return false;
            }
        } else {
            echo "✗ Note addition failed\n";
            return false;
        }
        
        // Test 8: Clean up (delete the test request)
        $approved_request->delete();
        echo "✓ Request deleted successfully\n";
        
        echo "\n=== All CRUD tests passed! ===\n";
        return true;
        
    } catch (Exception $e) {
        echo "✗ Error during CRUD test: " . $e->getMessage() . "\n";
        echo "  File: " . $e->getFile() . "\n";
        echo "  Line: " . $e->getLine() . "\n";
        return false;
    }
}

// Run the test if we're in admin or during plugin activation
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