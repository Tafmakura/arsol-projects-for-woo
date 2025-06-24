<?php
/**
 * Test Email Trigger
 * 
 * Visit this file to test the email system
 * Add this to your site: yoursite.com/wp-content/plugins/arsol-projects-for-woo/test-email.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied');
}

echo '<h1>Testing Email System</h1>';

// Test if WooCommerce is active
if (!class_exists('WooCommerce')) {
    echo '<p style="color: red;">WooCommerce is not active!</p>';
    exit;
}

// Test if our email class is registered
$emails = WC()->mailer()->get_emails();
echo '<h2>Registered Email Classes:</h2>';
echo '<ul>';
foreach ($emails as $email_id => $email_obj) {
    echo '<li>' . $email_id . ' - ' . get_class($email_obj) . '</li>';
}
echo '</ul>';

// Check if our email is there
if (isset($emails['WC_Email_New_Request'])) {
    echo '<p style="color: green;">✓ Our New Request email is registered!</p>';
    
    // Try to trigger it
    echo '<h2>Testing Email Trigger:</h2>';
    
    // Create a dummy request post
    $dummy_request = wp_insert_post(array(
        'post_title' => 'Test Request for Email',
        'post_type' => 'project-request',
        'post_status' => 'publish'
    ));
    
    if ($dummy_request) {
        echo '<p>Created dummy request ID: ' . $dummy_request . '</p>';
        
        // Trigger the email
        do_action('arsol_pfw_new_request_notification', $dummy_request);
        
        echo '<p style="color: green;">✓ Email trigger fired! Check your email.</p>';
        
        // Clean up - delete the dummy post
        wp_delete_post($dummy_request, true);
        echo '<p>Dummy request deleted.</p>';
    } else {
        echo '<p style="color: red;">Failed to create dummy request.</p>';
    }
    
} else {
    echo '<p style="color: red;">✗ Our New Request email is NOT registered!</p>';
    echo '<p>Available emails: ' . implode(', ', array_keys($emails)) . '</p>';
}

echo '<p><a href="' . admin_url('admin.php?page=wc-settings&tab=email') . '">View WooCommerce Email Settings</a></p>';
?> 