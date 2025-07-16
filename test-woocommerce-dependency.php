<?php
/**
 * Test script for WooCommerce dependency loading
 */

// Define plugin constants
define('ARSOL_PROJECTS_PLUGIN_FILE', __FILE__);
define('ARSOL_PROJECTS_PLUGIN_DIR', dirname(__FILE__) . '/');
define('ARSOL_PROJECTS_PLUGIN_BASENAME', 'arsol-projects-for-woo/arsol-projects-for-woo.php');

echo "Testing WooCommerce dependency loading...\n";

// Test 1: Check if WC_Email exists (should not exist without WooCommerce)
if (class_exists('WC_Email')) {
    echo "✅ WC_Email class exists (WooCommerce is loaded)\n";
} else {
    echo "❌ WC_Email class not found (WooCommerce not loaded)\n";
}

// Test 2: Try to load email classes without WooCommerce
echo "\nTesting email class loading without WooCommerce:\n";
try {
    require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/email/class-arsol-pfw-wc-email-admin-new-project.php';
    echo "❌ Email class loaded without WooCommerce (this should fail)\n";
} catch (Error $e) {
    echo "✅ Email class correctly failed to load: " . $e->getMessage() . "\n";
}

// Test 3: Check if the email class exists after loading
if (class_exists('WC_Email_Admin_New_Project')) {
    echo "❌ WC_Email_Admin_New_Project class exists (should not without WooCommerce)\n";
} else {
    echo "✅ WC_Email_Admin_New_Project class not found (correct behavior)\n";
}

echo "\nTest completed!\n";
echo "The manual loader should defer email class loading until WooCommerce is available.\n"; 