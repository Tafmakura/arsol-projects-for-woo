<?php
/**
 * Test script for Shortcodes class loading
 */

// Define plugin constants
define('ARSOL_PROJECTS_PLUGIN_FILE', __FILE__);
define('ARSOL_PFW_PLUGIN_DIR', dirname(__FILE__) . '/');
define('ARSOL_PROJECTS_PLUGIN_BASENAME', 'arsol-projects-for-woo/arsol-projects-for-woo.php');

echo "Testing Shortcodes class loading...\n";

// Test loading the shortcodes file
$shortcodes_file = ARSOL_PFW_PLUGIN_DIR . 'includes/core/shortcodes.php';
if (file_exists($shortcodes_file)) {
    echo "✅ Shortcodes file exists: {$shortcodes_file}\n";
    try {
        require_once $shortcodes_file;
        echo "✅ Shortcodes file loaded successfully\n";
    } catch (Exception $e) {
        echo "❌ Error loading shortcodes file: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Shortcodes file not found: {$shortcodes_file}\n";
}

// Test if the Shortcodes class exists
if (class_exists('\Arsol_Projects_For_Woo\Shortcodes')) {
    echo "✅ Shortcodes class found in correct namespace\n";
} else {
    echo "❌ Shortcodes class not found in correct namespace\n";
}

// Test if the wrong namespace class exists
if (class_exists('\Arsol_Projects_For_Woo\Core\Shortcodes')) {
    echo "❌ Shortcodes class found in wrong namespace (Core)\n";
} else {
    echo "✅ Shortcodes class not found in wrong namespace (Core) - correct\n";
}

echo "\nTest completed!\n"; 