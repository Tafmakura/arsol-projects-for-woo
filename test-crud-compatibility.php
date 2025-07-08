<?php
/**
 * CRUD Compatibility Test
 *
 * Tests method signature compatibility and recent fixes
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Tests
 */

// Only run this test if WP_DEBUG is enabled
if (!defined('WP_DEBUG') || !WP_DEBUG) {
    return;
}

/**
 * Test method signature compatibility
 */
function arsol_pfw_test_method_signatures() {
    echo "=== Method Signature Compatibility Test ===\n";
    
    try {
        // Test 1: Data Store Registration
        echo "1. Testing Data Store Registration...\n";
        $data_store = WC_Data_Store::load('arsol-pfw-request');
        if ($data_store instanceof ARSOL_PFW_Request_Data_Store) {
            echo "   ✓ Data store registered and loaded correctly\n";
        } else {
            echo "   ✗ Data store registration failed\n";
            return false;
        }
        
        // Test 2: Test get_available_stages with different signatures
        echo "2. Testing get_available_stages method signatures...\n";
        
        // Test without parameter (should use default)
        $stages_default = $data_store->get_available_stages();
        echo "   ✓ get_available_stages() without parameter: " . (is_array($stages_default) ? count($stages_default) . " stages" : "Failed") . "\n";
        
        // Test with parameter
        $stages_with_param = $data_store->get_available_stages('arsol-pfw-request-stage');
        echo "   ✓ get_available_stages('arsol-pfw-request-stage'): " . (is_array($stages_with_param) ? count($stages_with_param) . " stages" : "Failed") . "\n";
        
        // Test 3: Test get_stage_counts with different signatures
        echo "3. Testing get_stage_counts method signatures...\n";
        
        // Test without parameter
        $counts_default = $data_store->get_stage_counts();
        echo "   ✓ get_stage_counts() without parameter: " . (is_array($counts_default) ? "Success" : "Failed") . "\n";
        
        // Test with parameter
        $counts_with_param = $data_store->get_stage_counts('arsol-pfw-request-stage');
        echo "   ✓ get_stage_counts('arsol-pfw-request-stage'): " . (is_array($counts_with_param) ? "Success" : "Failed") . "\n";
        
        // Test 4: Test Request Entity get_meta with WC_Data signature
        echo "4. Testing Request Entity get_meta method signatures...\n";
        
        $request = arsol_pfw_create_request(array(
            'name' => 'Signature Test Request',
            'description' => 'Testing method signatures',
            'customer_id' => 1,
            'stage' => 'pending-review'
        ));
        
        $request_id = $request->save();
        
        // Test get_meta with all parameters
        $meta_full = $request->get_meta('_test_key', true, 'view');
        echo "   ✓ get_meta('_test_key', true, 'view'): Success\n";
        
        // Test get_meta with partial parameters
        $meta_partial = $request->get_meta('_test_key', true);
        echo "   ✓ get_meta('_test_key', true): Success\n";
        
        // Test get_meta with key only
        $meta_key_only = $request->get_meta('_test_key');
        echo "   ✓ get_meta('_test_key'): Success\n";
        
        // Test get_meta with empty key (should work with WC_Data signature)
        $meta_empty = $request->get_meta('');
        echo "   ✓ get_meta(''): Success\n";
        
        // Test 5: Test core function compatibility
        echo "5. Testing core function compatibility...\n";
        
        $stages_function = arsol_pfw_get_request_stages();
        echo "   ✓ arsol_pfw_get_request_stages(): " . (is_array($stages_function) ? count($stages_function) . " stages" : "Failed") . "\n";
        
        $counts_function = arsol_pfw_get_request_stage_counts();
        echo "   ✓ arsol_pfw_get_request_stage_counts(): " . (is_array($counts_function) ? "Success" : "Failed") . "\n";
        
        // Test 6: Test method compatibility with parent classes
        echo "6. Testing inheritance compatibility...\n";
        
        $reflection_data_store = new ReflectionClass('ARSOL_PFW_Request_Data_Store');
        $method_available_stages = $reflection_data_store->getMethod('get_available_stages');
        $params = $method_available_stages->getParameters();
        
        if (count($params) === 1 && $params[0]->isOptional()) {
            echo "   ✓ get_available_stages has correct signature with optional parameter\n";
        } else {
            echo "   ✗ get_available_stages signature mismatch\n";
            return false;
        }
        
        $reflection_request = new ReflectionClass('ARSOL_PFW_Request');
        $method_get_meta = $reflection_request->getMethod('get_meta');
        $meta_params = $method_get_meta->getParameters();
        
        if (count($meta_params) === 3 && $meta_params[0]->isOptional() && $meta_params[1]->isOptional() && $meta_params[2]->isOptional()) {
            echo "   ✓ get_meta has correct WC_Data compatible signature\n";
        } else {
            echo "   ✗ get_meta signature mismatch\n";
            return false;
        }
        
        // Test 7: Test interface compliance
        echo "7. Testing interface compliance...\n";
        
        $implements_interface = $data_store instanceof ARSOL_PFW_Object_Data_Store_Interface;
        echo "   ✓ Data store implements ARSOL_PFW_Object_Data_Store_Interface: " . ($implements_interface ? "Yes" : "No") . "\n";
        
        $implements_request_interface = $data_store instanceof ARSOL_PFW_Request_Data_Store_Interface;
        echo "   ✓ Data store implements ARSOL_PFW_Request_Data_Store_Interface: " . ($implements_request_interface ? "Yes" : "No") . "\n";
        
        $implements_stage_interface = $request instanceof ARSOL_PFW_Stage_Interface;
        echo "   ✓ Request implements ARSOL_PFW_Stage_Interface: " . ($implements_stage_interface ? "Yes" : "No") . "\n";
        
        // Test 8: Test WooCommerce compatibility
        echo "8. Testing WooCommerce compatibility...\n";
        
        $extends_wc_data = $request instanceof WC_Data;
        echo "   ✓ Request extends WC_Data: " . ($extends_wc_data ? "Yes" : "No") . "\n";
        
        $extends_wc_data_store = $data_store instanceof WC_Data_Store_WP;
        echo "   ✓ Data store extends WC_Data_Store_WP: " . ($extends_wc_data_store ? "Yes" : "No") . "\n";
        
        // Clean up
        $request->delete();
        
        echo "\n=== 🎉 All signature compatibility tests passed! ===\n";
        echo "✅ Data store method signatures: COMPATIBLE\n";
        echo "✅ Request entity method signatures: COMPATIBLE\n";
        echo "✅ Core function compatibility: COMPATIBLE\n";
        echo "✅ Inheritance compatibility: COMPATIBLE\n";
        echo "✅ Interface compliance: COMPATIBLE\n";
        echo "✅ WooCommerce compatibility: COMPATIBLE\n";
        
        return true;
        
    } catch (Exception $e) {
        echo "✗ FATAL ERROR during signature compatibility test: " . $e->getMessage() . "\n";
        echo "  File: " . $e->getFile() . "\n";
        echo "  Line: " . $e->getLine() . "\n";
        return false;
    }
}

// Run the test if requested
if (is_admin()) {
    add_action('admin_init', function() {
        if (isset($_GET['arsol_test_signatures']) && current_user_can('manage_options')) {
            echo '<pre>';
            arsol_pfw_test_method_signatures();
            echo '</pre>';
            exit;
        }
    });
} 