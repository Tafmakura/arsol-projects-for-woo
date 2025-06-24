<?php
/**
 * New Request Email Template (Plain Text)
 * 
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

echo "= " . $email_heading . " =\n\n";

echo "Hello " . ($customer->first_name ?: $customer->display_name) . ",\n\n";

echo "Thank you for submitting your project request! We've received your request and our team will review it shortly.\n\n";

echo "REQUEST DETAILS:\n";
echo "Request ID: #" . $request->ID . "\n";
echo "Title: " . $request->post_title . "\n";
echo "Submitted: " . date_i18n(get_option('date_format'), strtotime($request->post_date)) . "\n\n";

echo "WHAT HAPPENS NEXT?\n";
echo "1. Our team will review your request (typically within 1-2 business days)\n";
echo "2. We'll contact you if we need any additional information\n";
echo "3. Once approved, we'll create a detailed proposal for your project\n";
echo "4. You can review and approve the proposal in your customer portal\n\n";

echo "View your request online: " . $portal_url . "\n\n";

echo "You can track the progress of your request and view all communications in your customer portal at any time.\n\n";

echo "If you have any questions, please don't hesitate to contact us.\n\n";

echo "Best regards,\n";
echo "The " . get_bloginfo('name') . " Team\n";
