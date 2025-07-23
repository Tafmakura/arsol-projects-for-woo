<?php
/**
 * Test Simplified Override System
 *
 * Tests the simplified override system implementation.
 *
 * @package Arsol_Projects_For_Woo
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Test class for simplified override system
 */
class Test_Simplified_Override_System {
    
    /**
     * Run all tests
     */
    public static function run_tests() {
        echo "<h2>Testing Simplified Override System</h2>\n";
        
        self::test_override_logic();
        self::test_user_meta_storage();
        self::test_capability_checking();
        
        echo "<h3>All tests completed!</h3>\n";
    }
    
    /**
     * Test the override logic
     */
    public static function test_override_logic() {
        echo "<h3>Testing Capability-First Override Logic</h3>\n";
        
        // Test 1: User doesn't have capability - should return false regardless of overrides
        $user = get_user_by('id', 1);
        if ($user) {
            $user->remove_cap('manage_arsol_pfw_stages'); // Remove capability
        }
        delete_user_meta(1, 'arsol_pfw_manager_override_manage_stages'); // Remove override
        
        $result = \Arsol_Projects_For_Woo\Core\Capabilities_Handler::get_effective_manager_capability(1, 'manage_stages');
        echo "Test 1 - User lacks capability: " . ($result ? 'FAIL' : 'PASS') . " (Expected: false, Got: " . ($result ? 'true' : 'false') . ")\n";
        
        // Test 2: User has capability, no overrides enabled globally
        if ($user) {
            $user->add_cap('manage_arsol_pfw_stages'); // Add capability
        }
        $settings = array(
            'allow_manager_overrides' => false
        );
        update_option('arsol_pfw_permissions_settings', $settings);
        
        $result = \Arsol_Projects_For_Woo\Core\Capabilities_Handler::get_effective_manager_capability(1, 'manage_stages');
        echo "Test 2 - User has capability, overrides disabled: " . ($result ? 'PASS' : 'FAIL') . " (Expected: true, Got: " . ($result ? 'true' : 'false') . ")\n";
        
        // Test 3: User has capability, overrides enabled, no user override
        $settings['allow_manager_overrides'] = true;
        update_option('arsol_pfw_permissions_settings', $settings);
        delete_user_meta(1, 'arsol_pfw_manager_override_manage_stages'); // No override
        
        $result = \Arsol_Projects_For_Woo\Core\Capabilities_Handler::get_effective_manager_capability(1, 'manage_stages');
        echo "Test 3 - User has capability, no override: " . ($result ? 'PASS' : 'FAIL') . " (Expected: true, Got: " . ($result ? 'true' : 'false') . ")\n";
        
        // Test 4: User has capability, overrides enabled, user explicitly enables
        update_user_meta(1, 'arsol_pfw_manager_override_manage_stages', '1');
        $result = \Arsol_Projects_For_Woo\Core\Capabilities_Handler::get_effective_manager_capability(1, 'manage_stages');
        echo "Test 4 - User has capability, explicitly enabled: " . ($result ? 'PASS' : 'FAIL') . " (Expected: true, Got: " . ($result ? 'true' : 'false') . ")\n";
        
        // Test 5: User has capability, overrides enabled, user explicitly disables
        update_user_meta(1, 'arsol_pfw_manager_override_manage_stages', '0');
        $result = \Arsol_Projects_For_Woo\Core\Capabilities_Handler::get_effective_manager_capability(1, 'manage_stages');
        echo "Test 5 - User has capability, explicitly disabled: " . ($result ? 'FAIL' : 'PASS') . " (Expected: false, Got: " . ($result ? 'true' : 'false') . ")\n";
        
        echo "<br>\n";
    }
    
    /**
     * Test user meta storage
     */
    public static function test_user_meta_storage() {
        echo "<h3>Testing User Meta Storage</h3>\n";
        
        // Test storing and retrieving overrides
        $user_id = 1;
        
        // Test explicit enable
        update_user_meta($user_id, 'arsol_pfw_manager_override_manage_stages', '1');
        $value = get_user_meta($user_id, 'arsol_pfw_manager_override_manage_stages', true);
        echo "Test 1 - Store explicit enable: " . ($value === '1' ? 'PASS' : 'FAIL') . " (Got: $value)\n";
        
        // Test explicit disable
        update_user_meta($user_id, 'arsol_pfw_manager_override_manage_stages', '0');
        $value = get_user_meta($user_id, 'arsol_pfw_manager_override_manage_stages', true);
        echo "Test 2 - Store explicit disable: " . ($value === '0' ? 'PASS' : 'FAIL') . " (Got: $value)\n";
        
        // Test no override (delete meta)
        delete_user_meta($user_id, 'arsol_pfw_manager_override_manage_stages');
        $value = get_user_meta($user_id, 'arsol_pfw_manager_override_manage_stages', true);
        echo "Test 3 - No override (empty): " . (empty($value) ? 'PASS' : 'FAIL') . " (Got: '$value')\n";
        
        echo "<br>\n";
    }
    
    /**
     * Test capability checking methods
     */
    public static function test_capability_checking() {
        echo "<h3>Testing Capability Checking Methods</h3>\n";
        
        // Test the specific capability checking methods
        $user_id = 1;
        
        // Mock user as manager
        $user = get_userdata($user_id);
        if ($user) {
            $user->add_cap('arsol_pfw_manage');
        }
        
        // Test can_manage_stages
        $result = \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_manage_stages($user_id);
        echo "Test 1 - can_manage_stages: " . ($result ? 'PASS' : 'FAIL') . " (Got: " . ($result ? 'true' : 'false') . ")\n";
        
        // Test can_manage_workflows
        $result = \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_manage_workflows($user_id);
        echo "Test 2 - can_manage_workflows: " . ($result ? 'PASS' : 'FAIL') . " (Got: " . ($result ? 'true' : 'false') . ")\n";
        
        // Test can_manage_settings
        $result = \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_manage_settings($user_id);
        echo "Test 3 - can_manage_settings: " . ($result ? 'PASS' : 'FAIL') . " (Got: " . ($result ? 'true' : 'false') . ")\n";
        
        // Test can_manage_permissions
        $result = \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_manage_permissions($user_id);
        echo "Test 4 - can_manage_permissions: " . ($result ? 'PASS' : 'FAIL') . " (Got: " . ($result ? 'true' : 'false') . ")\n";
        
        echo "<br>\n";
    }
}

// Run tests if this file is accessed directly
if (defined('WP_CLI') && WP_CLI) {
    Test_Simplified_Override_System::run_tests();
} elseif (isset($_GET['test_override_system']) && current_user_can('manage_options')) {
    Test_Simplified_Override_System::run_tests();
} 