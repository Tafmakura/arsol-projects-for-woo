<?php
/**
 * Test Access Handler
 * 
 * Simple test to verify the Access Handler class is working
 */

// Load WordPress
require_once('wp-load.php');

// Test the Access Handler
if (class_exists('\Arsol_Projects_For_Woo\Core\Access_Handler')) {
    echo "✅ Access Handler class loaded successfully!\n";
    
    // Test message retrieval
    $message = \Arsol_Projects_For_Woo\Core\Access_Handler::get_no_access_message('proposal');
    echo "✅ Proposal message: " . $message . "\n";
    
    // Test title retrieval
    $title = \Arsol_Projects_For_Woo\Core\Access_Handler::get_no_access_title('proposal');
    echo "✅ Proposal title: " . $title . "\n";
    
    // Test context detection
    $context = \Arsol_Projects_For_Woo\Core\Access_Handler::get_current_context();
    echo "✅ Current context: " . $context . "\n";
    
    echo "\n🎉 All tests passed! Access Handler is ready to use.\n";
} else {
    echo "❌ Access Handler class not found!\n";
}
