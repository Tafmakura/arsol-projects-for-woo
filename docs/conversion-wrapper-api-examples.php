<?php
/**
 * Conversion Wrapper API Examples
 * 
 * This file demonstrates how to use the new wrapper methods for entity conversions.
 * These methods provide a cleaner, more object-oriented approach to conversions.
 */

// Example 1: Convert a Request to a Proposal
$request_id = 123;
$request = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($request_id);

if ($request->exists()) {
    $proposal_id = $request->convert_to_proposal();
    
    if (is_wp_error($proposal_id)) {
        echo "Conversion failed: " . $proposal_id->get_error_message();
    } else {
        echo "Request #{$request_id} successfully converted to Proposal #{$proposal_id}";
    }
}

// Example 2: Convert a Proposal to a Project
$proposal_id = 456;
$proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);

if ($proposal->exists()) {
    // For admin-initiated conversions (with security checks)
    $project_id = $proposal->convert_to_project();
    
    // For internal/programmatic conversions (skips security checks)
    $project_id = $proposal->convert_to_project(true);
    
    if (is_wp_error($project_id)) {
        echo "Conversion failed: " . $project_id->get_error_message();
    } else {
        echo "Proposal #{$proposal_id} successfully converted to Project #{$project_id}";
    }
}

// Example 3: Using factory functions (alternative approach)
$request = arsol_pfw_get_request(123);
if ($request) {
    $proposal_id = $request->convert_to_proposal();
}

$proposal = arsol_pfw_get_proposal(456);
if ($proposal) {
    $project_id = $proposal->convert_to_project();
}

// Example 4: Error handling with validation
$request = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request(999);

if (!$request->exists()) {
    echo "Request does not exist";
} else {
    // Check if request is in correct stage for conversion
    if ($request->get_stage() === 'approved') {
        $proposal_id = $request->convert_to_proposal();
        
        if (is_wp_error($proposal_id)) {
            echo "Conversion failed: " . $proposal_id->get_error_message();
        } else {
            echo "Conversion successful! New proposal ID: {$proposal_id}";
        }
    } else {
        echo "Request must be in 'approved' stage to convert to proposal";
    }
}

// Example 5: Batch conversion (use with caution)
$request_ids = [123, 124, 125];
$converted_proposals = [];

foreach ($request_ids as $request_id) {
    $request = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($request_id);
    
    if ($request->exists() && $request->get_stage() === 'approved') {
        $proposal_id = $request->convert_to_proposal();
        
        if (!is_wp_error($proposal_id)) {
            $converted_proposals[] = $proposal_id;
        }
    }
}

echo "Successfully converted " . count($converted_proposals) . " requests to proposals";

/**
 * Benefits of the Wrapper Approach:
 * 
 * 1. Cleaner API: $request->convert_to_proposal() vs $conversion_handler->convert_request_to_proposal($request_id)
 * 2. Better encapsulation: Conversion logic is part of the entity's lifecycle
 * 3. Automatic validation: Checks if entity exists before attempting conversion
 * 4. Consistent error handling: Returns WP_Error objects for failures
 * 5. Easier testing: Can mock individual entity conversions
 * 6. More intuitive: Follows OOP principles where objects manage their own state changes
 */ 