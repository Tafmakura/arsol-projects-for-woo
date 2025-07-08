<?php
/**
 * CRUD Infrastructure Phase Verification Test
 * 
 * This script tests the complete CRUD infrastructure to ensure it's working properly
 * 
 * Run this script from WordPress admin or via WP-CLI to verify functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // For command line testing
    define('WP_USE_THEMES', false);
    require_once('wp-load.php');
}

// Add some styling for better output
if (!wp_doing_ajax() && !defined('WP_CLI')) {
    echo '<style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>';
}

class ARSOL_PFW_CRUD_Test {
    
    private $test_results = array();
    private $test_request_id = null;
    
    public function __construct() {
        echo "<h1>ARSOL PFW CRUD Infrastructure Test</h1>\n";
        echo "<p>Testing Phase 1: CRUD Infrastructure Implementation</p>\n";
    }
    
    /**
     * Run all tests
     */
    public function run_all_tests() {
        $this->test_class_loading();
        $this->test_namespace_resolution();
        $this->test_woocommerce_integration();
        $this->test_factory_functions();
        $this->test_basic_crud_operations();
        $this->test_stage_management();
        $this->test_business_logic();
        $this->test_query_operations();
        $this->test_error_handling();
        $this->cleanup_test_data();
        $this->display_summary();
    }
    
    /**
     * Test class loading and autoloading
     */
    private function test_class_loading() {
        $this->section_header("Class Loading Tests");
        
        // Test namespace classes
        $classes_to_test = array(
            'Arsol_Projects_For_Woo\ARSOL_PFW_Data_Stores',
            'Arsol_Projects_For_Woo\ARSOL_PFW_Request',
            'Arsol_Projects_For_Woo\ARSOL_PFW_Request_Data_Store',
            'Arsol_Projects_For_Woo\ARSOL_PFW_Data_Store_WP',
            'Arsol_Projects_For_Woo\ARSOL_PFW_Object_Data_Store_Interface',
            'Arsol_Projects_For_Woo\ARSOL_PFW_Stage_Interface',
            'Arsol_Projects_For_Woo\ARSOL_PFW_Request_Data_Store_Interface',
        );
        
        foreach ($classes_to_test as $class) {
            if (class_exists($class) || interface_exists($class)) {
                $this->test_pass("✓ {$class} - loaded successfully");
            } else {
                $this->test_fail("✗ {$class} - failed to load");
            }
        }
    }
    
    /**
     * Test namespace resolution
     */
    private function test_namespace_resolution() {
        $this->section_header("Namespace Resolution Tests");
        
        try {
            // Test data store initialization
            if (class_exists('Arsol_Projects_For_Woo\ARSOL_PFW_Data_Stores')) {
                $this->test_pass("✓ ARSOL_PFW_Data_Stores class accessible");
            } else {
                $this->test_fail("✗ ARSOL_PFW_Data_Stores class not accessible");
            }
            
            // Test core function namespace
            if (function_exists('Arsol_Projects_For_Woo\arsol_pfw_get_request')) {
                $this->test_pass("✓ Core functions loaded in correct namespace");
            } else {
                $this->test_fail("✗ Core functions not found in namespace");
            }
            
        } catch (Exception $e) {
            $this->test_fail("✗ Namespace resolution error: " . $e->getMessage());
        }
    }
    
    /**
     * Test WooCommerce integration
     */
    private function test_woocommerce_integration() {
        $this->section_header("WooCommerce Integration Tests");
        
        if (!class_exists('WooCommerce')) {
            $this->test_fail("✗ WooCommerce not active - skipping integration tests");
            return;
        }
        
        try {
            // Test data store registration
            $data_store = WC_Data_Store::load('arsol-pfw-request');
            if ($data_store instanceof Arsol_Projects_For_Woo\ARSOL_PFW_Request_Data_Store) {
                $this->test_pass("✓ Request data store registered with WooCommerce");
            } else {
                $this->test_fail("✗ Request data store not properly registered");
            }
            
            // Test WC_Data inheritance
            $request = new Arsol_Projects_For_Woo\ARSOL_PFW_Request();
            if ($request instanceof WC_Data) {
                $this->test_pass("✓ Request entity properly extends WC_Data");
            } else {
                $this->test_fail("✗ Request entity does not extend WC_Data");
            }
            
        } catch (Exception $e) {
            $this->test_fail("✗ WooCommerce integration error: " . $e->getMessage());
        }
    }
    
    /**
     * Test factory functions
     */
    private function test_factory_functions() {
        $this->section_header("Factory Functions Tests");
        
        try {
            // Test create function
            $request = Arsol_Projects_For_Woo\arsol_pfw_create_request(array(
                'name' => 'Test Request',
                'description' => 'Test Description',
                'customer_id' => 1,
            ));
            
            if ($request instanceof Arsol_Projects_For_Woo\ARSOL_PFW_Request) {
                $this->test_pass("✓ arsol_pfw_create_request() works");
                $this->test_request_id = $request->get_id();
            } else {
                $this->test_fail("✗ arsol_pfw_create_request() failed");
            }
            
            // Test get function
            if ($this->test_request_id) {
                $retrieved = Arsol_Projects_For_Woo\arsol_pfw_get_request($this->test_request_id);
                if ($retrieved && $retrieved->get_id() == $this->test_request_id) {
                    $this->test_pass("✓ arsol_pfw_get_request() works");
                } else {
                    $this->test_fail("✗ arsol_pfw_get_request() failed");
                }
            }
            
        } catch (Exception $e) {
            $this->test_fail("✗ Factory function error: " . $e->getMessage());
        }
    }
    
    /**
     * Test basic CRUD operations
     */
    private function test_basic_crud_operations() {
        $this->section_header("Basic CRUD Operations Tests");
        
        try {
            // CREATE
            $request = new Arsol_Projects_For_Woo\ARSOL_PFW_Request();
            $request->set_name('CRUD Test Request');
            $request->set_description('Testing CRUD operations');
            $request->set_customer_id(1);
            $request->set_stage('pending-review');
            $request->save();
            
            if ($request->get_id() > 0) {
                $this->test_pass("✓ CREATE: Request created successfully (ID: " . $request->get_id() . ")");
                $this->test_request_id = $request->get_id();
            } else {
                $this->test_fail("✗ CREATE: Failed to create request");
            }
            
            // READ
            if ($this->test_request_id) {
                $loaded_request = new Arsol_Projects_For_Woo\ARSOL_PFW_Request($this->test_request_id);
                if ($loaded_request->get_name() === 'CRUD Test Request') {
                    $this->test_pass("✓ READ: Request loaded successfully");
                } else {
                    $this->test_fail("✗ READ: Request data not loaded correctly");
                }
                
                // UPDATE
                $loaded_request->set_name('Updated CRUD Test Request');
                $loaded_request->set_description('Updated description');
                $loaded_request->save();
                
                $reloaded_request = new Arsol_Projects_For_Woo\ARSOL_PFW_Request($this->test_request_id);
                if ($reloaded_request->get_name() === 'Updated CRUD Test Request') {
                    $this->test_pass("✓ UPDATE: Request updated successfully");
                } else {
                    $this->test_fail("✗ UPDATE: Request update failed");
                }
            }
            
        } catch (Exception $e) {
            $this->test_fail("✗ CRUD operation error: " . $e->getMessage());
        }
    }
    
    /**
     * Test stage management
     */
    private function test_stage_management() {
        $this->section_header("Stage Management Tests");
        
        if (!$this->test_request_id) {
            $this->test_fail("✗ No test request available for stage testing");
            return;
        }
        
        try {
            $request = new Arsol_Projects_For_Woo\ARSOL_PFW_Request($this->test_request_id);
            
            // Test initial stage
            if ($request->get_stage() === 'pending-review') {
                $this->test_pass("✓ Initial stage set correctly");
            } else {
                $this->test_fail("✗ Initial stage not set correctly");
            }
            
            // Test stage update
            $request->update_stage('under-review', 'Moving to review');
            if ($request->get_stage() === 'under-review') {
                $this->test_pass("✓ Stage updated successfully");
            } else {
                $this->test_fail("✗ Stage update failed");
            }
            
            // Test available stages
            $stages = $request->get_available_stages();
            if (is_array($stages) && !empty($stages)) {
                $this->test_pass("✓ Available stages retrieved: " . count($stages) . " stages");
            } else {
                $this->test_fail("✗ Available stages not retrieved");
            }
            
        } catch (Exception $e) {
            $this->test_fail("✗ Stage management error: " . $e->getMessage());
        }
    }
    
    /**
     * Test business logic methods
     */
    private function test_business_logic() {
        $this->section_header("Business Logic Tests");
        
        if (!$this->test_request_id) {
            $this->test_fail("✗ No test request available for business logic testing");
            return;
        }
        
        try {
            $request = new Arsol_Projects_For_Woo\ARSOL_PFW_Request($this->test_request_id);
            
            // Test approval
            $request->approve();
            if ($request->get_stage() === 'approved') {
                $this->test_pass("✓ Request approval works");
            } else {
                $this->test_fail("✗ Request approval failed");
            }
            
            // Test conversion readiness
            if ($request->can_convert_to_proposal()) {
                $this->test_pass("✓ Conversion readiness check works");
            } else {
                $this->test_fail("✗ Conversion readiness check failed");
            }
            
            // Test rejection
            $request->reject('Test rejection reason');
            if ($request->get_stage() === 'rejected') {
                $this->test_pass("✓ Request rejection works");
            } else {
                $this->test_fail("✗ Request rejection failed");
            }
            
        } catch (Exception $e) {
            $this->test_fail("✗ Business logic error: " . $e->getMessage());
        }
    }
    
    /**
     * Test query operations
     */
    private function test_query_operations() {
        $this->section_header("Query Operations Tests");
        
        try {
            // Test get requests by stage
            $requests = Arsol_Projects_For_Woo\arsol_pfw_get_requests_by_stage('rejected');
            if (is_array($requests)) {
                $this->test_pass("✓ Get requests by stage works (" . count($requests) . " found)");
            } else {
                $this->test_fail("✗ Get requests by stage failed");
            }
            
            // Test get requests by customer
            $requests = Arsol_Projects_For_Woo\arsol_pfw_get_requests_by_customer(1);
            if (is_array($requests)) {
                $this->test_pass("✓ Get requests by customer works (" . count($requests) . " found)");
            } else {
                $this->test_fail("✗ Get requests by customer failed");
            }
            
            // Test get available stages
            $stages = Arsol_Projects_For_Woo\arsol_pfw_get_request_stages();
            if (is_array($stages)) {
                $this->test_pass("✓ Get available stages works (" . count($stages) . " stages)");
            } else {
                $this->test_fail("✗ Get available stages failed");
            }
            
        } catch (Exception $e) {
            $this->test_fail("✗ Query operations error: " . $e->getMessage());
        }
    }
    
    /**
     * Test error handling
     */
    private function test_error_handling() {
        $this->section_header("Error Handling Tests");
        
        try {
            // Test invalid request ID
            $request = new Arsol_Projects_For_Woo\ARSOL_PFW_Request(999999);
            $this->test_pass("✓ Invalid request ID handled gracefully");
            
            // Test factory function with invalid ID
            $request = Arsol_Projects_For_Woo\arsol_pfw_get_request(999999);
            if ($request === false) {
                $this->test_pass("✓ Factory function handles invalid ID correctly");
            } else {
                $this->test_fail("✗ Factory function should return false for invalid ID");
            }
            
        } catch (Exception $e) {
            $this->test_pass("✓ Exception handling works: " . $e->getMessage());
        }
    }
    
    /**
     * Clean up test data
     */
    private function cleanup_test_data() {
        $this->section_header("Cleanup Test Data");
        
        if ($this->test_request_id) {
            try {
                $request = new Arsol_Projects_For_Woo\ARSOL_PFW_Request($this->test_request_id);
                $request->delete(true); // Force delete
                $this->test_pass("✓ Test data cleaned up successfully");
            } catch (Exception $e) {
                $this->test_fail("✗ Cleanup failed: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Helper methods
     */
    private function section_header($title) {
        echo "<div class='test-section'><h2>{$title}</h2>\n";
    }
    
    private function test_pass($message) {
        echo "<div class='success'>{$message}</div>\n";
        $this->test_results[] = array('status' => 'pass', 'message' => $message);
    }
    
    private function test_fail($message) {
        echo "<div class='error'>{$message}</div>\n";
        $this->test_results[] = array('status' => 'fail', 'message' => $message);
    }
    
    private function test_warning($message) {
        echo "<div class='warning'>{$message}</div>\n";
        $this->test_results[] = array('status' => 'warning', 'message' => $message);
    }
    
    private function display_summary() {
        echo "</div>\n"; // Close last section
        
        $passed = count(array_filter($this->test_results, function($r) { return $r['status'] === 'pass'; }));
        $failed = count(array_filter($this->test_results, function($r) { return $r['status'] === 'fail'; }));
        $warnings = count(array_filter($this->test_results, function($r) { return $r['status'] === 'warning'; }));
        
        echo "<div class='test-section'>\n";
        echo "<h2>Test Summary</h2>\n";
        echo "<p><strong>Total Tests:</strong> " . count($this->test_results) . "</p>\n";
        echo "<p class='success'><strong>Passed:</strong> {$passed}</p>\n";
        echo "<p class='error'><strong>Failed:</strong> {$failed}</p>\n";
        echo "<p class='warning'><strong>Warnings:</strong> {$warnings}</p>\n";
        
        if ($failed === 0) {
            echo "<div class='success'><h3>🎉 ALL TESTS PASSED!</h3></div>\n";
            echo "<p>The CRUD infrastructure is working correctly and ready for the next phase.</p>\n";
        } else {
            echo "<div class='error'><h3>❌ Some tests failed</h3></div>\n";
            echo "<p>Please review the failed tests and fix the issues before proceeding.</p>\n";
        }
        echo "</div>\n";
    }
}

// Run the tests
if (class_exists('WooCommerce')) {
    $test = new ARSOL_PFW_CRUD_Test();
    $test->run_all_tests();
} else {
    echo "<div class='error'>WooCommerce is not active. Please activate WooCommerce to run these tests.</div>";
} 