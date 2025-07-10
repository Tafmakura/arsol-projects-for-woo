<?php
/**
 * Debug script to test data transfer from proposal to project
 */

// Load WordPress
require_once 'arsol-projects-for-woo.php';

// Test data transfer
echo "Testing data transfer from proposal to project...\n";

// Get a proposal
$proposals = get_posts(array(
    'post_type' => 'arsol-pfw-proposal',
    'posts_per_page' => 1,
    'post_status' => 'publish'
));

if (empty($proposals)) {
    echo "No proposals found. Please create a proposal first.\n";
    exit;
}

$proposal_post = $proposals[0];
$proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Project_Proposal_CPT($proposal_post->ID);

echo "Proposal ID: " . $proposal->get_id() . "\n";
echo "Proposal Title: " . $proposal->get_title() . "\n";
echo "Proposal Customer ID: " . $proposal->get_customer_id() . "\n";

// Test conversion
try {
    $converter = new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Proposal_Conversion_Handler();
    $project_id = $converter->convert_to_project($proposal->get_id());
    
    if ($project_id && !is_wp_error($project_id)) {
        $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Project_CPT($project_id);
        
        echo "\nProject created successfully!\n";
        echo "Project ID: " . $project->get_id() . "\n";
        echo "Project Title: " . $project->get_title() . "\n";
        echo "Project Customer ID: " . $project->get_customer_id() . "\n";
        
        // Check if data was transferred correctly
        if ($project->get_title() === $proposal->get_title()) {
            echo "✓ Title transferred correctly\n";
        } else {
            echo "✗ Title transfer failed\n";
        }
        
        if ($project->get_customer_id() === $proposal->get_customer_id()) {
            echo "✓ Customer ID transferred correctly\n";
        } else {
            echo "✗ Customer ID transfer failed\n";
        }
    } else {
        echo "Failed to create project: " . (is_wp_error($project_id) ? $project_id->get_error_message() : 'Unknown error') . "\n";
    }
} catch (Exception $e) {
    echo "Error during conversion: " . $e->getMessage() . "\n";
} 