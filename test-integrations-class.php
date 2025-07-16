<?php
/**
 * Test script to verify Integrations class loading
 */

// Define the plugin directory constant
define('ARSOL_PROJECTS_PLUGIN_DIR', __DIR__ . '/');

// Load the integrations file
require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/settings/integrations.php';

// Test if the class exists
if (class_exists('\Arsol_Projects_For_Woo\Admin\Settings\Integrations')) {
    echo "✅ SUCCESS: Integrations class found!\n";
    
    // Test instantiation
    try {
        $integrations = new \Arsol_Projects_For_Woo\Admin\Settings\Integrations();
        echo "✅ SUCCESS: Integrations class instantiated successfully!\n";
        
        // Test static method
        $available = \Arsol_Projects_For_Woo\Admin\Settings\Integrations::get_available_integrations();
        echo "✅ SUCCESS: Static method works! Available integrations: " . count($available) . "\n";
        
    } catch (Exception $e) {
        echo "❌ ERROR: Failed to instantiate Integrations class: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ ERROR: Integrations class not found!\n";
    
    // Check if file exists
    $file_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/settings/integrations.php';
    if (file_exists($file_path)) {
        echo "✅ File exists at: $file_path\n";
    } else {
        echo "❌ File does not exist at: $file_path\n";
    }
} 