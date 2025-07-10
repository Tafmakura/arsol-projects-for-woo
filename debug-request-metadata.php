<?php
/**
 * Diagnostic script to check request metadata
 * 
 * Usage: Access via yoursite.com/debug-request-metadata.php
 */

// Load WordPress
require_once('wp-load.php');

// Ensure we're in admin context
if (!current_user_can('manage_options')) {
    wp_die('Insufficient permissions');
}

echo "<h1>ARSOL PFW Request Metadata Diagnostic</h1>";

// Find all requests
$requests = get_posts(array(
    'post_type' => 'arsol-pfw-request',
    'post_status' => 'publish',
    'numberposts' => 5
));

if (empty($requests)) {
    echo "<p>❌ No requests found</p>";
    exit;
}

echo "<p>Found " . count($requests) . " requests:</p>";

foreach ($requests as $request) {
    echo "<h3>Request #{$request->ID}: {$request->post_title}</h3>";
    
    // Get all metadata
    $all_meta = get_post_meta($request->ID);
    
    echo "<h4>All Metadata:</h4>";
    echo "<pre>" . print_r($all_meta, true) . "</pre>";
    
    // Check specific expected keys
    $expected_keys = array(
        '_arsol_pfw_request_title',
        '_arsol_pfw_request_date',
        '_arsol_pfw_request_budget',
        '_arsol_pfw_request_start_date',
        '_arsol_pfw_request_delivery_date',
        '_arsol_pfw_request_attachments',
        '_arsol_pfw_request_customer_notice'
    );
    
    echo "<h4>Expected Keys Check:</h4>";
    foreach ($expected_keys as $key) {
        $value = get_post_meta($request->ID, $key, true);
        if (!empty($value)) {
            echo "<p>✅ <strong>{$key}</strong>: " . print_r($value, true) . "</p>";
        } else {
            echo "<p>❌ <strong>{$key}</strong>: <em>empty or not found</em></p>";
        }
    }
    
    // Check request object properties
    echo "<h4>Request Object Properties:</h4>";
    if (function_exists('arsol_pfw_get_request')) {
        $request_obj = arsol_pfw_get_request($request->ID);
        if ($request_obj) {
            echo "<p>✅ Request object loaded successfully</p>";
            echo "<p><strong>Name:</strong> " . $request_obj->get_name() . "</p>";
            echo "<p><strong>Customer ID:</strong> " . $request_obj->get_customer_id() . "</p>";
            echo "<p><strong>Budget:</strong> " . print_r($request_obj->get_budget(), true) . "</p>";
            echo "<p><strong>Description:</strong> " . substr($request_obj->get_prop('description'), 0, 100) . "...</p>";
        } else {
            echo "<p>❌ Failed to load request object</p>";
        }
    } else {
        echo "<p>❌ arsol_pfw_get_request function not found</p>";
    }
    
    // Check taxonomies
    echo "<h4>Taxonomies:</h4>";
    $taxonomies = get_object_taxonomies('arsol-pfw-request');
    foreach ($taxonomies as $taxonomy) {
        $terms = wp_get_object_terms($request->ID, $taxonomy);
        if (!empty($terms) && !is_wp_error($terms)) {
            echo "<p>✅ <strong>{$taxonomy}</strong>: " . implode(', ', wp_list_pluck($terms, 'name')) . "</p>";
        } else {
            echo "<p>❌ <strong>{$taxonomy}</strong>: <em>no terms</em></p>";
        }
    }
    
    echo "<hr>";
}

// Check how requests are created
echo "<h3>Request Creation Analysis</h3>";

// Look for request creation functions
$functions_to_check = array(
    'arsol_pfw_create_request',
    'arsol_pfw_get_request',
    'arsol_pfw_save_request'
);

foreach ($functions_to_check as $func) {
    if (function_exists($func)) {
        echo "<p>✅ Function <strong>{$func}</strong> exists</p>";
    } else {
        echo "<p>❌ Function <strong>{$func}</strong> not found</p>";
    }
}

// Check for request data store
if (class_exists('\Arsol_Projects_For_Woo\Data_Stores\Request_Data_Store')) {
    echo "<p>✅ Request_Data_Store class exists</p>";
} else {
    echo "<p>❌ Request_Data_Store class not found</p>";
}

echo "<h3>Recommendations:</h3>";
echo "<ol>";
echo "<li>Check how requests are being created - they may not be saving the expected metadata</li>";
echo "<li>Verify the meta keys used during request creation match the expected keys</li>";
echo "<li>Check if there are custom fields or ACF fields that should be copied</li>";
echo "<li>Ensure the request creation process saves all required metadata</li>";
echo "</ol>";
?> 