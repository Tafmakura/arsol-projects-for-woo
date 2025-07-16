<?php
/**
 * Test script for manual loader
 * This script tests if all the required files can be loaded without errors
 */

// Define plugin constants
define('ARSOL_PROJECTS_PLUGIN_FILE', __FILE__);
define('ARSOL_PROJECTS_PLUGIN_DIR', dirname(__FILE__) . '/');
define('ARSOL_PROJECTS_PLUGIN_BASENAME', 'arsol-projects-for-woo/arsol-projects-for-woo.php');

echo "Testing manual loader...\n";

// Test loading core files directly
$core_files = [
    'includes/core/assets.php',
    'includes/core/capabilities.php',
    'includes/core/permissions.php',
    'includes/core/stage-handler.php',
    'includes/core/workflow-handler.php',
    'includes/core/conversion-handler.php',
    'includes/core/shortcodes.php',
];

echo "Testing core files:\n";
foreach ($core_files as $file) {
    if (file_exists($file)) {
        echo "✅ {$file} exists\n";
        try {
            require_once $file;
            echo "   ✅ Loaded successfully\n";
        } catch (Exception $e) {
            echo "   ❌ Error loading: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ {$file} not found\n";
    }
}

// Test loading frontend files
$frontend_files = [
    'includes/frontend/handler/handler.php',
    'includes/frontend/request/request.php',
    'includes/frontend/proposal/proposal.php',
];

echo "\nTesting frontend files:\n";
foreach ($frontend_files as $file) {
    if (file_exists($file)) {
        echo "✅ {$file} exists\n";
        try {
            require_once $file;
            echo "   ✅ Loaded successfully\n";
        } catch (Exception $e) {
            echo "   ❌ Error loading: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ {$file} not found\n";
    }
}

// Test if classes exist after loading
echo "\nTesting class existence:\n";

if (class_exists('\Arsol_Projects_For_Woo\Workflow\Workflow_Handler')) {
    echo "✅ Workflow_Handler class found\n";
} else {
    echo "❌ Workflow_Handler class not found\n";
}

if (class_exists('\Arsol_Projects_For_Woo\Frontend\Frontend_Handler')) {
    echo "✅ Frontend_Handler class found\n";
} else {
    echo "❌ Frontend_Handler class not found\n";
}

if (class_exists('\Arsol_Projects_For_Woo\Frontend\Request_Frontend')) {
    echo "✅ Request_Frontend class found\n";
} else {
    echo "❌ Request_Frontend class not found\n";
}

if (class_exists('\Arsol_Projects_For_Woo\Frontend\Proposal_Frontend')) {
    echo "✅ Proposal_Frontend class found\n";
} else {
    echo "❌ Proposal_Frontend class not found\n";
}

echo "\nManual loader test completed!\n"; 