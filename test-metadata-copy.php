<?php
/**
 * Test script to verify metadata copying during conversions
 * 
 * Usage: 
 * 1. Place this file in your WordPress root directory
 * 2. Access it via: yoursite.com/test-metadata-copy.php
 * 3. Check the output and error logs
 */

// Load WordPress
require_once('wp-load.php');

// Ensure we're in admin context
if (!current_user_can('manage_options')) {
    wp_die('Insufficient permissions');
}

echo "<h1>ARSOL PFW Metadata Copy Test</h1>";

// Test 1: Check if Conversion_Handler class exists
if (class_exists('\Arsol_Projects_For_Woo\Core\Conversion_Handler')) {
    echo "<p>✅ Conversion_Handler class found</p>";
} else {
    echo "<p>❌ Conversion_Handler class not found</p>";
    exit;
}

// Test 2: Check for test data
$test_request_id = 0;
$test_proposal_id = 0;

// Find a test request
$requests = get_posts(array(
    'post_type' => 'arsol-pfw-request',
    'post_status' => 'publish',
    'numberposts' => 1
));

if (!empty($requests)) {
    $test_request_id = $requests[0]->ID;
    echo "<p>✅ Found test request: #{$test_request_id}</p>";
    
    // Show request metadata
    echo "<h3>Request #{$test_request_id} Metadata:</h3>";
    $request_meta = get_post_meta($test_request_id);
    echo "<pre>" . print_r($request_meta, true) . "</pre>";
} else {
    echo "<p>❌ No test requests found</p>";
}

// Find a test proposal
$proposals = get_posts(array(
    'post_type' => 'arsol-pfw-proposal',
    'post_status' => 'publish',
    'numberposts' => 1
));

if (!empty($proposals)) {
    $test_proposal_id = $proposals[0]->ID;
    echo "<p>✅ Found test proposal: #{$test_proposal_id}</p>";
    
    // Show proposal metadata
    echo "<h3>Proposal #{$test_proposal_id} Metadata:</h3>";
    $proposal_meta = get_post_meta($test_proposal_id);
    echo "<pre>" . print_r($proposal_meta, true) . "</pre>";
} else {
    echo "<p>❌ No test proposals found</p>";
}

// Test 3: Check factory functions
echo "<h3>Factory Function Tests:</h3>";

if (function_exists('arsol_pfw_get_request')) {
    echo "<p>✅ arsol_pfw_get_request function exists</p>";
    if ($test_request_id) {
        $request_obj = arsol_pfw_get_request($test_request_id);
        if ($request_obj) {
            echo "<p>✅ Successfully loaded request object</p>";
        } else {
            echo "<p>❌ Failed to load request object</p>";
        }
    }
} else {
    echo "<p>❌ arsol_pfw_get_request function not found</p>";
}

if (function_exists('arsol_pfw_get_proposal')) {
    echo "<p>✅ arsol_pfw_get_proposal function exists</p>";
    if ($test_proposal_id) {
        $proposal_obj = arsol_pfw_get_proposal($test_proposal_id);
        if ($proposal_obj) {
            echo "<p>✅ Successfully loaded proposal object</p>";
        } else {
            echo "<p>❌ Failed to load proposal object</p>";
        }
    }
} else {
    echo "<p>❌ arsol_pfw_get_proposal function not found</p>";
}

if (function_exists('arsol_pfw_get_project')) {
    echo "<p>✅ arsol_pfw_get_project function exists</p>";
} else {
    echo "<p>❌ arsol_pfw_get_project function not found</p>";
}

// Test 4: Check error logs
echo "<h3>Recent Error Logs (ARSOL PFW DEBUG):</h3>";
$log_file = WP_CONTENT_DIR . '/debug.log';
if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    $arsol_logs = array();
    $lines = explode("\n", $log_content);
    foreach ($lines as $line) {
        if (strpos($line, 'ARSOL PFW DEBUG') !== false) {
            $arsol_logs[] = $line;
        }
    }
    
    if (!empty($arsol_logs)) {
        echo "<p>Found " . count($arsol_logs) . " ARSOL PFW debug log entries:</p>";
        echo "<pre>" . implode("\n", array_slice($arsol_logs, -20)) . "</pre>";
    } else {
        echo "<p>No ARSOL PFW debug logs found</p>";
    }
} else {
    echo "<p>No debug.log file found</p>";
}

echo "<h3>Instructions for Testing:</h3>";
echo "<ol>";
echo "<li>Perform a conversion (request → proposal or proposal → project)</li>";
echo "<li>Check the error logs for ARSOL PFW DEBUG messages</li>";
echo "<li>Compare the metadata before and after conversion</li>";
echo "<li>Look for any error messages in the debug output</li>";
echo "</ol>";

echo "<h3>Manual Conversion Test:</h3>";
if ($test_request_id) {
    echo "<p><a href='" . admin_url("admin-post.php?action=arsol_convert_to_proposal&request_id={$test_request_id}&_wpnonce=" . wp_create_nonce('arsol_convert_to_proposal_nonce')) . "'>Convert Request #{$test_request_id} to Proposal</a></p>";
}

if ($test_proposal_id) {
    echo "<p><a href='" . admin_url("admin-post.php?action=arsol_convert_to_project&proposal_id={$test_proposal_id}&_wpnonce=" . wp_create_nonce('arsol_convert_to_project_nonce')) . "'>Convert Proposal #{$test_proposal_id} to Project</a></p>";
}
?> 