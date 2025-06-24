<?php
/**
 * Request Status Email Template (Plain Text)
 * 
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

echo "= REQUEST STATUS UPDATE =\n\n";

echo "Hello " . ($customer->first_name ?: $customer->display_name) . ",\n\n";

echo "We wanted to update you on the status of your project request.\n\n";

echo "REQUEST DETAILS:\n";
echo "Request ID: #" . $request->ID . "\n";
echo "Title: " . $request->post_title . "\n";
echo "Previous Status: " . ($old_status ? ucfirst(str_replace('-', ' ', $old_status)) : 'N/A') . "\n";
echo "New Status: " . $status_label . "\n\n";

if ($new_status === 'under-review') {
    echo "WHAT THIS MEANS:\n";
    echo "Our team is now actively reviewing your request. We're evaluating the requirements and will contact you if we need any additional information.\n\n";
    
    echo "NEXT STEPS:\n";
    echo "- We'll complete our review within 2-3 business days\n";
    echo "- You may receive follow-up questions from our team\n";
    echo "- Once approved, we'll begin creating your detailed proposal\n\n";
    
} elseif ($new_status === 'on-hold') {
    echo "WHAT THIS MEANS:\n";
    echo "Your request has been temporarily placed on hold. This may be due to:\n";
    echo "- Additional information needed from you\n";
    echo "- Current capacity constraints\n";
    echo "- Technical clarifications required\n\n";
    
    echo "NEXT STEPS:\n";
    echo "Our team will contact you directly with details about why your request is on hold and what steps are needed to proceed.\n\n";
    
} elseif ($new_status === 'approved') {
    echo "GREAT NEWS!\n";
    echo "Your request has been approved! Our team will now begin creating a detailed proposal for your project.\n\n";
    
    echo "WHAT HAPPENS NEXT:\n";
    echo "1. A project lead will be assigned to your request\n";
    echo "2. We'll create a detailed proposal with scope, timeline, and pricing\n";
    echo "3. You'll receive a notification when the proposal is ready for review\n";
    echo "4. You can review and approve the proposal in your customer portal\n\n";
}

echo "View your request online: " . $portal_url . "\n\n";

echo "You can always check the current status and view all communications in your customer portal.\n\n";

echo "If you have any questions, please don't hesitate to contact us.\n\n";

echo "Best regards,\n";
echo "The " . get_bloginfo('name') . " Team\n";
