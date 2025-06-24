<?php
/**
 * Project Creation Email Template (Plain Text)
 * 
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

echo "= YOUR ORDER IS READY! =\n\n";

echo "Hello " . ($customer->first_name ?: $customer->display_name) . ",\n\n";

echo "Congratulations! Your project has been created and your order is ready for completion.\n\n";

echo "ORDER DETAILS:\n";
echo "Project ID: #" . $project->ID . "\n";
echo "Order ID: #" . $order->get_id() . "\n";
echo "Project Title: " . $project->post_title . "\n";
echo "Total Amount: " . strip_tags($order->get_formatted_order_total()) . "\n";
if ($project_lead) {
    echo "Project Lead: " . $project_lead->first_name . ' ' . $project_lead->last_name . "\n";
}
echo "\n";

echo "COMPLETE YOUR PURCHASE:\n";
echo "To begin work on your project, please complete your payment by visiting:\n";
echo $checkout_url . "\n\n";

echo "WHAT HAPPENS AFTER PAYMENT:\n";
echo "1. Project Activation: Your project will be immediately activated\n";
echo "2. Team Assignment: Your dedicated project team will begin work\n";
echo "3. Kickoff Communication: You'll receive a project kickoff email with next steps\n";
echo "4. Regular Updates: We'll keep you informed throughout the project lifecycle\n\n";

if ($project_lead) {
    echo "YOUR PROJECT TEAM:\n";
    echo $project_lead->first_name . ' ' . $project_lead->last_name . " will be leading your project and will be your primary point of contact once work begins.\n\n";
}

echo "NEED TO REVIEW THE DETAILS?\n";
echo "View project details: " . $portal_url . "\n\n";

echo "You can always view your project details, track progress, and communicate with your team through your customer portal.\n\n";

echo "Thank you for choosing us for your project. We're excited to get started!\n\n";

echo "Best regards,\n";
echo "The " . get_bloginfo('name') . " Team\n";
