<?php
/**
 * Proposal Ready Email Template (Plain Text)
 * 
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

echo "= YOUR PROPOSAL IS READY FOR REVIEW =\n\n";

echo "Hello " . ($customer->first_name ?: $customer->display_name) . ",\n\n";

echo "Exciting news! Your project proposal is now complete and ready for your review.\n\n";

echo "PROPOSAL DETAILS:\n";
echo "Proposal ID: #" . $proposal->ID . "\n";
echo "Title: " . $proposal->post_title . "\n";
echo "Status: Ready for Review\n";
echo "Completed: " . date_i18n(get_option('date_format'), strtotime($proposal->post_modified)) . "\n\n";

echo "WHAT'S INCLUDED IN YOUR PROPOSAL:\n";
echo "- Detailed project scope and requirements\n";
echo "- Complete timeline and milestones\n";
echo "- Transparent pricing breakdown\n";
echo "- Technical specifications and deliverables\n";
echo "- Terms and conditions\n\n";

echo "ACTION REQUIRED:\n";
echo "Please review your proposal and let us know if you'd like to proceed. You can approve or request changes directly through your customer portal.\n\n";

echo "Review your proposal: " . $portal_url . "\n\n";

echo "NEXT STEPS:\n";
echo "1. Click the link above to access your proposal\n";
echo "2. Review all sections carefully\n";
echo "3. Approve the proposal if you're satisfied\n";
echo "4. Or request changes if needed\n";
echo "5. Once approved, we'll create your project and order\n\n";

echo "QUESTIONS ABOUT YOUR PROPOSAL?\n";
echo "Our team is here to help! Feel free to reach out if you have any questions or need clarification on any aspect of the proposal.\n\n";

echo "We're excited to potentially work with you on this project!\n\n";

echo "Best regards,\n";
echo "The " . get_bloginfo('name') . " Team\n";
